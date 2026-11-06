<?php

namespace App\Console\Commands;

use App\Models\Presentation;
use App\Support\CanonicalRevision;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class PublicationExportCommand extends Command
{
    protected $signature = 'publications:export {id : Canonical presentation UUID} {--base=http://127.0.0.1:8017}';

    protected $description = 'Render every canonical frame into a validated fresh PDF (local server required)';

    public function handle(PresentationCompiler $compiler): int
    {
        $deck = Presentation::findOrFail($this->argument('id'));
        $slides = $compiler->flattenSlides($compiler->compile($deck->source_slug)['slides']);
        if (count($slides) !== $deck->slide_count) {
            $this->error('Inventory changed; run publications:catalog before exporting.');

            return self::FAILURE;
        }
        $directory = storage_path('app/publications/'.$deck->id.'/'.Str::uuid());
        mkdir($directory, 0700, true);
        $revision = app(CanonicalRevision::class)->hash();
        $manifest = ['id' => $deck->id, 'slug' => $deck->source_slug, 'title' => $deck->title, 'revision' => $revision,
            'slides' => array_map(fn ($slide) => ['id' => $slide->id, 'fragments' => $slide->fragments], $slides)];
        file_put_contents($directory.'/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $commands = [
            ['node', base_path('bin/publication-export.cjs'), $directory.'/manifest.json', $directory, $this->option('base')],
            [getenv('PUBLICATION_PYTHON') ?: 'python3', base_path('bin/publication-merge.py'), $directory, $directory.'/presentation.pdf', (string) count($slides)],
        ];
        foreach ($commands as $command) {
            $process = new Process($command, base_path(), timeout: 600);
            $process->run();
            if (! $process->isSuccessful()) {
                $this->error('Export rejected; no publication was prepared. '.$process->getErrorOutput());

                return self::FAILURE;
            }
        }
        if (app(CanonicalRevision::class)->hash() !== $revision) {
            unlink($directory.'/pdf-validation.json');
            $this->error('Sources changed during export; artifact rejected.');

            return self::FAILURE;
        }
        $this->line($directory.'/presentation.pdf');
        $this->line(file_get_contents($directory.'/pdf-validation.json'));

        return self::SUCCESS;
    }
}
