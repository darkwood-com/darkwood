<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use WendellAdriel\SlideWire\Support\PresentationPathResolver;

class MakeSlideCommand extends Command
{
    protected $signature = 'make:slidewire
        {name? : Canonical source locator in lowercase kebab-case}
        {--presentation= : Source locator override}
        {--title= : First slide title, as plain text}
        {--md : Write Markdown content inside native Blade components}
        {--multi : Create a native multi-file Blade deck}
        {--force : Overwrite scaffold files in the same format}';

    protected $description = 'Create a native Darkwood presentation scaffold';

    public function handle(PresentationPathResolver $resolver): int
    {
        $name = (string) ($this->option('presentation') ?? $this->argument('name') ?? '');
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $name)) {
            $this->error('Use a lowercase kebab-case source locator, such as my-talk.');

            return self::FAILURE;
        }
        $title = trim((string) ($this->option('title') ?? Str::headline($name)));
        if ($title === '') {
            $this->error('The slide title cannot be empty.');

            return self::FAILURE;
        }
        $multi = (bool) $this->option('multi');
        $target = $resolver->firstRoot().DIRECTORY_SEPARATOR.$name.($multi ? '' : '.blade.php');
        $existing = $resolver->presentationPath($name);
        if ($existing !== null && (! $this->option('force') || $existing !== $target)) {
            $this->error('The source already exists. --force only overwrites scaffold files in the same format.');

            return self::FAILURE;
        }

        // Escape plain-text titles, including Blade directive delimiters.
        $values = ['__TITLE__' => strtr(e($title), ['@' => '&#64;', '{' => '&#123;', '}' => '&#125;']), '__DATE__' => now()->format('d-m-Y')];
        $stub = fn (string $file) => strtr(File::get(base_path('stubs/slidewire/'.$file.'.stub')), $values);
        $content = $this->option('md') ? 'content-markdown' : 'content';
        File::ensureDirectoryExists($multi ? $target : dirname($target));
        if ($multi) {
            File::put($target.'/deck.blade.php', $stub('deck'));
            File::put($target.'/01-intro.blade.php', $stub('cover'));
            File::put($target.'/02-content.blade.php', $stub($content));
        } else {
            File::put($target, "<x-darkwood.deck>\n".$stub('cover')."\n".$stub($content)."\n</x-darkwood.deck>\n");
        }
        $this->info('Created native presentation at '.$target);
        $this->line('Register its stable UUID once in resources/presentations.json, then run publications:catalog. Its catalogue entry and reader route are automatic.');

        return self::SUCCESS;
    }
}
