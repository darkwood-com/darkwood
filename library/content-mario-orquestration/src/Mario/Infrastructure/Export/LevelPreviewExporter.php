<?php

declare(strict_types=1);

namespace App\Mario\Infrastructure\Export;

use App\Mario\Infrastructure\AI\MarioAgentOrchestrator;
use App\Mario\Infrastructure\AI\MarioMultiAgentFactory;

final class LevelPreviewExporter
{
    /** @param array<string, mixed> $level @param array<string, mixed> $evidence */
    public function write(array $level, array $evidence, string $root): string
    {
        if (!(new MarioAgentOrchestrator(new MarioMultiAgentFactory()))->validatePayload($level)->isValid()) {
            throw new \InvalidArgumentException('Cannot preview invalid geometry.');
        }
        if (is_link($root)) {
            throw new \RuntimeException('Export root must not be a symlink.');
        }
        if (!is_dir($root) && !mkdir($root, 0700, true)) {
            throw new \RuntimeException('Cannot create export root.');
        }
        $directory = $root.'/'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
        if (!mkdir($directory, 0700)) {
            throw new \RuntimeException('Cannot create unique export directory.');
        }
        $json = static fn (array $data): string => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_THROW_ON_ERROR)."\n";
        file_put_contents($directory.'/level.json', $json($level));
        file_put_contents($directory.'/evidence.json', $json($evidence));
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $shapes = '';
        foreach ($level['terrain'] as $piece) {
            $color = 'gap' === ($piece['type'] ?? '') ? '#f8d181' : '#50865b';
            $shapes .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="%s"/>', $piece['x'], $piece['y'], $piece['w'], $piece['h'], $color);
        }
        foreach ($level['objects'] as $object) {
            $shapes .= sprintf('<circle cx="%s" cy="%s" r="0.25" fill="#ead26a"><title>%s</title></circle>', $object['x'] + 0.5, $object['y'] + 0.5, $escape((string) ($object['type'] ?? 'object')));
        }
        foreach (['player_start' => '#70d7ff', 'goal' => '#e7a7ff'] as $key => $color) {
            $shapes .= sprintf('<rect x="%d" y="%d" width="1" height="1" fill="%s"><title>%s</title></rect>', $level[$key]['x'], $level[$key]['y'], $color, $key);
        }
        $html = '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><meta http-equiv="Content-Security-Policy" content="default-src &#39;none&#39;; style-src &#39;unsafe-inline&#39;"><title>Darkwood level geometry</title><style>body{margin:4vw;background:#101821;color:#f1f5f9;font:22px system-ui}svg{width:100%;background:#192b38;border:1px solid #4a6070}pre{font-size:16px;white-space:pre-wrap}a{color:#70d7ff}</style><h1>'.$escape($level['name']).'</h1><p>Actual generated geometry · '.$level['width'].' × '.$level['height'].' tiles</p><svg role="img" aria-label="Generated level geometry" viewBox="0 0 '.$level['width'].' '.$level['height'].'">'.$shapes.'</svg><p>Blue: start · Pink: goal · Green: terrain · Gold: objects and declared gaps</p><p>This is a data preview, not a playable game. Geometry validation does not prove reachability.</p><p><a href="level.json">Level JSON</a> · <a href="evidence.json">Execution evidence</a></p><pre>'.$escape((string) ($evidence['summary'] ?? '')).'</pre></html>';
        file_put_contents($directory.'/preview.html', $html);
        return $directory;
    }
}
