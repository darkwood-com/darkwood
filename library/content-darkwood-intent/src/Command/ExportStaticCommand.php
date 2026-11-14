<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\IntentExamples;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

#[AsCommand(name: 'app:export-static', description: 'Export the six public pages and a strict whitelist of assets.')]
final class ExportStaticCommand extends Command
{
    public function __construct(private readonly Environment $twig, #[Autowire('%kernel.project_dir%')] private readonly string $projectDir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('base-path', null, InputOption::VALUE_REQUIRED, 'URL prefix, empty for a custom domain.', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $prefix = rtrim((string) $input->getOption('base-path'), '/');
        if ('' !== $prefix && !preg_match('~^/[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_-]+)*$~D', $prefix)) {
            $output->writeln('<error>Invalid base path.</error>');
            return Command::INVALID;
        }

        $directory = $this->projectDir.'/dist';
        if (is_link($directory)) {
            throw new \RuntimeException('dist/ must not be a symlink.');
        }
        $assets = ['style.css', 'themes.js', 'static.js', 'intent-model.js', 'logo-darkwood.png', 'fonts/Jost-400-Book.otf', 'fonts/Jost-600-Semi.otf', 'fonts/OFL.txt', 'aftertrace/teaser.mp4', 'aftertrace/remembered-route.png'];
        $allowed = array_merge(['index.html', '.nojekyll'], $assets);
        foreach (array_keys(IntentExamples::DEMOS) as $slug) {
            $allowed[] = 'demo/'.$slug.'/index.html';
        }
        if (is_dir($directory)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $file) {
                if ($file->isLink() || ($file->isFile() && !in_array(substr($file->getPathname(), strlen($directory) + 1), $allowed, true))) {
                    throw new \RuntimeException('Unexpected file in dist/: use a clean export directory.');
                }
            }
        }

        $context = ['static_mode' => true, 'base_path' => $prefix, 'demos' => IntentExamples::DEMOS];
        $pages = ['index.html' => ['home.html.twig', $context]];
        foreach (IntentExamples::DEMOS as $slug => $demo) {
            $pages['demo/'.$slug.'/index.html'] = ['demo.html.twig', $context + ['slug' => $slug, 'demo' => $demo]];
        }
        foreach ($pages as $path => [$template, $values]) {
            if (!is_dir(dirname($directory.'/'.$path))) {
                mkdir(dirname($directory.'/'.$path), 0775, true);
            }
            file_put_contents($directory.'/'.$path, $this->twig->render($template, $values));
        }
        foreach ($assets as $asset) {
            if (!is_dir(dirname($directory.'/'.$asset))) {
                mkdir(dirname($directory.'/'.$asset), 0775, true);
            }
            if (!copy($this->projectDir.'/public/'.$asset, $directory.'/'.$asset)) {
                throw new \RuntimeException('Unable to export asset '.$asset);
            }
        }
        file_put_contents($directory.'/.nojekyll', '');
        $output->writeln('Exported six pages and '.count($assets).' assets to dist/ (base path: '.('' === $prefix ? '/' : $prefix).').');
        return Command::SUCCESS;
    }
}
