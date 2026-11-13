#!/usr/bin/env php
<?php

declare(strict_types=1);

function cardsFail(string $message): never
{
    fwrite(STDERR, "cards: {$message}\n");
    exit(1);
}

/** @return array{code:int,out:string,err:string} */
function cardsRun(array $command, ?string $stdin = null): array
{
    $pipes = [];
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        cardsFail('unable to start '.implode(' ', $command));
    }
    fwrite($pipes[0], $stdin ?? '');
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    return ['code' => proc_close($process), 'out' => $out, 'err' => $err];
}

/** @return array{code:int,out:string,err:string} */
function cardsGit(string $root, array $arguments, ?string $stdin = null): array
{
    return cardsRun(array_merge(['git', '-C', $root], $arguments), $stdin);
}

/** @return array<string,mixed> */
function cardsReadJson(string $path): array
{
    if (!is_file($path)) {
        cardsFail("missing {$path}");
    }

    try {
        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        cardsFail("invalid JSON in {$path}: ".$exception->getMessage());
    }
    if (!is_array($data)) {
        cardsFail("invalid object in {$path}");
    }

    return $data;
}

/** @return array<string,array<string,mixed>> */
function cardsIndex(array $catalogue): array
{
    $index = [];
    foreach ($catalogue['projects'] ?? [] as $card) {
        if (is_array($card) && is_string($card['id'] ?? null)) {
            $index[$card['id']] = $card;
        }
    }

    return $index;
}

function cardsSparse(string $root): bool
{
    $result = cardsGit($root, ['config', '--bool', 'core.sparseCheckout']);

    return 0 === $result['code'] && 'true' === trim($result['out']);
}

function cardsLoaded(string $root, array $card, bool $sparse): bool
{
    if (!$sparse) {
        return true;
    }
    $result = cardsGit($root, ['ls-files', '-t', '--', (string) $card['path']]);
    if (0 !== $result['code']) {
        return false;
    }
    foreach (preg_split('/\R/', trim($result['out'])) ?: [] as $line) {
        if ('' !== $line && !str_starts_with($line, 'S ')) {
            return true;
        }
    }

    return false;
}

/** @return list<string> */
function cardsDependencies(array $card): array
{
    $dependencies = $card['dependencies'] ?? [];

    return is_array($dependencies)
        ? array_values(array_filter($dependencies, 'is_string'))
        : [];
}

/** @return list<string> */
function cardsResolveLoad(array $index, string $id, array &$external, array $seen = []): array
{
    if (isset($seen[$id])) {
        return [];
    }
    if (!isset($index[$id])) {
        $external[] = $id;
        return [];
    }
    $seen[$id] = true;
    $resolved = [];
    foreach (cardsDependencies($index[$id]) as $dependency) {
        $resolved = array_merge($resolved, cardsResolveLoad($index, $dependency, $external, $seen));
    }
    $resolved[] = $id;

    return array_values(array_unique($resolved));
}

/** @return list<string> */
function cardsSparseSelection(string $root): array
{
    $result = cardsGit($root, ['sparse-checkout', 'list']);
    if (0 !== $result['code']) {
        cardsFail('unable to read sparse selection: '.trim($result['err']));
    }

    return array_values(array_filter(preg_split('/\R/', trim($result['out'])) ?: []));
}

function cardsTreeSize(string $path): int
{
    if (!file_exists($path) && !is_link($path)) {
        return 0;
    }
    $result = cardsRun(['du', '-sk', $path]);
    if (0 !== $result['code']) {
        return 0;
    }

    return 1024 * (int) strtok(trim($result['out']), " \t");
}

function cardsHumanSize(int $bytes): string
{
    $units = ['B', 'KiB', 'MiB', 'GiB'];
    $value = (float) $bytes;
    $unit = 0;
    while ($value >= 1024 && $unit < count($units) - 1) {
        $value /= 1024;
        ++$unit;
    }

    return sprintf($unit > 1 ? '%.1f %s' : '%.0f %s', $value, $units[$unit]);
}

/** @return list<string> */
function cardsSystemReferences(string $absolutePath): array
{
    $references = [];
    foreach (['/etc/frankenphp', '/etc/systemd/system', '/etc/systemd/user'] as $base) {
        if (!is_dir($base) || !is_readable($base)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if (!$file->isFile() || !$file->isReadable() || $file->getSize() > 1024 * 1024) {
                continue;
            }
            $contents = @file_get_contents($file->getPathname());
            if (is_string($contents) && str_contains($contents, $absolutePath)) {
                $references[] = $file->getPathname();
            }
        }
    }

    return $references;
}

/** @return list<string> */
function cardsExternalSymlinks(string $root, string $absolutePath): array
{
    $result = cardsRun([
        'find', $root,
        '-path', $root.'/.git', '-prune', '-o',
        '-path', $absolutePath, '-prune', '-o',
        '-type', 'l', '-print',
    ]);
    if (0 !== $result['code']) {
        return [];
    }
    $links = [];
    foreach (array_filter(preg_split('/\R/', trim($result['out'])) ?: []) as $link) {
        $target = realpath($link);
        if (false !== $target && ($target === $absolutePath || str_starts_with($target, $absolutePath.'/'))) {
            $links[] = $link;
        }
    }

    return $links;
}

function cardsPreflightUnload(string $root, array $index, string $id, bool $sparse): void
{
    $card = $index[$id] ?? null;
    if (!is_array($card)) {
        cardsFail("unknown card {$id}");
    }
    if (!$sparse) {
        cardsFail('unload requires a sparse checkout; this full clone remains loaded');
    }
    if (!cardsLoaded($root, $card, true)) {
        return;
    }
    $path = (string) $card['path'];
    $absolutePath = $root.'/'.$path;

    $dirty = cardsGit($root, ['status', '--porcelain=v1', '--untracked-files=all', '--', $path]);
    if ('' !== trim($dirty['out'])) {
        cardsFail("{$id} has tracked or untracked changes; preserve them before unload");
    }
    $ignored = cardsGit($root, ['ls-files', '--others', '--ignored', '--exclude-standard', '--', $path]);
    if ('' !== trim($ignored['out'])) {
        cardsFail("{$id} contains ignored files; classify or remove them explicitly before unload");
    }

    foreach ($index as $otherId => $other) {
        if ($otherId !== $id && cardsLoaded($root, $other, true) && in_array($id, cardsDependencies($other), true)) {
            cardsFail("{$id} is required by loaded card {$otherId}");
        }
    }

    $lsof = cardsRun(['sh', '-c', 'command -v lsof']);
    if (0 !== $lsof['code']) {
        cardsFail('lsof is required for a safe unload (provide it through Nix)');
    }
    $open = cardsRun(['lsof', '-t', '+D', $absolutePath]);
    if ('' !== trim($open['out'])) {
        cardsFail("{$id} is used by process(es): ".implode(', ', preg_split('/\R/', trim($open['out'])) ?: []));
    }

    $references = cardsSystemReferences($absolutePath);
    if ([] !== $references) {
        cardsFail("{$id} is referenced by system configuration: ".implode(', ', $references));
    }
    $links = cardsExternalSymlinks($root, $absolutePath);
    if ([] !== $links) {
        cardsFail("{$id} is targeted by external symlink(s): ".implode(', ', $links));
    }
}

function cardsCurrentProfile(string $root): ?string
{
    $result = cardsGit($root, ['config', '--worktree', '--get', 'darkwood.cardsProfile']);
    if (0 !== $result['code'] || '' === trim($result['out'])) {
        return null;
    }

    return trim($result['out']);
}

function cardsSetProfile(string $root, string $profile): void
{
    cardsGit($root, ['config', 'extensions.worktreeConfig', 'true']);
    $result = cardsGit($root, ['config', '--worktree', 'darkwood.cardsProfile', $profile]);
    if (0 !== $result['code']) {
        cardsFail('unable to record the worktree profile: '.trim($result['err']));
    }
}

function cardsPrintTable(array $rows): void
{
    $widths = [];
    foreach ($rows as $row) {
        foreach ($row as $column => $value) {
            $widths[$column] = max($widths[$column] ?? 0, strlen($value));
        }
    }
    foreach ($rows as $row) {
        $formatted = [];
        foreach ($row as $column => $value) {
            $formatted[] = str_pad($value, $widths[$column]);
        }
        fwrite(STDOUT, rtrim(implode('  ', $formatted))."\n");
    }
}

function cardsMain(array $argv): void
{
    $toolRoot = realpath(dirname(__DIR__));
    $rootResult = false === $toolRoot
        ? ['code' => 1, 'out' => '', 'err' => '']
        : cardsRun(['git', '-C', $toolRoot, 'rev-parse', '--show-toplevel']);
    if (0 !== $rootResult['code']) {
        cardsFail('run this command inside a Darkwood monorepo');
    }
    $root = trim($rootResult['out']);
    $catalogue = cardsReadJson($root.'/tags.json');
    $index = cardsIndex($catalogue);
    $profiles = cardsReadJson($root.'/card-profiles.json')['profiles'] ?? [];
    $args = array_slice($argv, 1);
    if (($args[0] ?? null) === 'cards') {
        array_shift($args);
    }
    $command = array_shift($args) ?? 'status';
    $sparse = cardsSparse($root);

    if ('list' === $command) {
        $rows = [['ID', 'STATUS', 'KIND', 'PHYSICAL', 'DEPENDENCIES']];
        foreach ($index as $id => $card) {
            $rows[] = [
                $id,
                (string) $card['status'],
                (string) $card['kind'],
                cardsLoaded($root, $card, $sparse) ? 'loaded' : 'unloaded',
                implode(',', cardsDependencies($card)) ?: '-',
            ];
        }
        cardsPrintTable($rows);
        return;
    }

    if ('status' === $command) {
        $loaded = array_filter($index, static fn (array $card): bool => cardsLoaded($root, $card, $sparse));
        $statuses = [];
        foreach ($index as $card) {
            $statuses[$card['status']] = ($statuses[$card['status']] ?? 0) + 1;
        }
        fwrite(STDOUT, 'mode: '.($sparse ? 'sparse' : 'full')."\n");
        fwrite(STDOUT, 'profile: '.(cardsCurrentProfile($root) ?? 'unmanaged')."\n");
        fwrite(STDOUT, sprintf("cards: %d loaded, %d unloaded\n", count($loaded), count($index) - count($loaded)));
        foreach (['backlog', 'working', 'done'] as $status) {
            fwrite(STDOUT, sprintf("%s: %d\n", $status, $statuses[$status] ?? 0));
        }
        return;
    }

    if ('load' === $command) {
        $id = $args[0] ?? '';
        if (!isset($index[$id])) {
            cardsFail("unknown card {$id}");
        }
        if (!$sparse) {
            fwrite(STDOUT, "{$id}: already loaded (full checkout)\n");
            return;
        }
        $external = [];
        $ids = cardsResolveLoad($index, $id, $external);
        $paths = array_map(static fn (string $resolved): string => (string) $index[$resolved]['path'], $ids);
        $result = cardsGit($root, ['sparse-checkout', 'add', '--stdin'], implode("\n", $paths)."\n");
        if (0 !== $result['code']) {
            cardsFail('load failed: '.trim($result['err']));
        }
        fwrite(STDOUT, 'loaded: '.implode(', ', $ids)."\n");
        if ([] !== $external) {
            fwrite(STDOUT, 'external dependencies (not loaded here): '.implode(', ', array_unique($external))."\n");
        }
        return;
    }

    if ('unload' === $command) {
        $id = $args[0] ?? '';
        if (!isset($index[$id])) {
            cardsFail("unknown card {$id}");
        }
        $profile = cardsCurrentProfile($root);
        if (null !== $profile && isset($profiles[$profile]) && in_array($id, $profiles[$profile]['cards'] ?? [], true)) {
            cardsFail("{$id} is required by active profile {$profile}");
        }
        cardsPreflightUnload($root, $index, $id, $sparse);
        if (!cardsLoaded($root, $index[$id], $sparse)) {
            fwrite(STDOUT, "{$id}: already unloaded\n");
            return;
        }
        $target = (string) $index[$id]['path'];
        $selection = cardsSparseSelection($root);
        foreach ($selection as $selected) {
            if ($selected !== $target && str_starts_with($target.'/', rtrim($selected, '/').'/')) {
                cardsFail("cannot unload {$id}: parent sparse path {$selected} includes it");
            }
        }
        $remaining = array_values(array_filter(
            $selection,
            static fn (string $selected): bool => $selected !== $target && !str_starts_with($selected, $target.'/'),
        ));
        if ([] === $remaining) {
            cardsFail('refusing to create an empty sparse selection; apply a profile instead');
        }
        $before = cardsTreeSize($root.'/'.$target);
        $result = cardsGit($root, ['sparse-checkout', 'set', '--stdin'], implode("\n", $remaining)."\n");
        if (0 !== $result['code']) {
            cardsFail('unload failed: '.trim($result['err']));
        }
        $after = cardsTreeSize($root.'/'.$target);
        fwrite(STDOUT, sprintf("unloaded: %s; materialized tree: %s -> %s\n", $id, cardsHumanSize($before), cardsHumanSize($after)));
        return;
    }

    if ('profile' === $command) {
        $action = array_shift($args) ?? 'list';
        if ('list' === $action) {
            $rows = [['PROFILE', 'MODE', 'CARDS', 'DESCRIPTION']];
            foreach ($profiles as $name => $profile) {
                $rows[] = [$name, (string) ($profile['mode'] ?? ''), (string) count($profile['cards'] ?? []), (string) ($profile['description'] ?? '')];
            }
            cardsPrintTable($rows);
            return;
        }
        $name = array_shift($args) ?? '';
        if (!isset($profiles[$name])) {
            cardsFail("unknown profile {$name}");
        }
        $selected = $profiles[$name];
        if ('show' === $action) {
            fwrite(STDOUT, json_encode($selected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
            return;
        }
        if ('apply' !== $action) {
            cardsFail('usage: profile list | profile show NAME | profile apply NAME');
        }
        $mode = $selected['mode'] ?? null;
        if ('manual' === $mode) {
            cardsFail("profile {$name} is documentation-only until its branch policy is resolved");
        }
        if ('full' === $mode) {
            if ($sparse) {
                $result = cardsGit($root, ['sparse-checkout', 'disable']);
                if (0 !== $result['code']) {
                    cardsFail('unable to disable sparse checkout: '.trim($result['err']));
                }
            }
            cardsSetProfile($root, $name);
            fwrite(STDOUT, "profile applied: {$name} (full)\n");
            return;
        }
        if ('sparse' !== $mode) {
            cardsFail("profile {$name} has an invalid mode");
        }
        $requested = [];
        $external = [];
        foreach ($selected['cards'] ?? [] as $id) {
            $requested = array_merge($requested, cardsResolveLoad($index, (string) $id, $external));
        }
        $requested = array_values(array_unique($requested));
        foreach ($index as $id => $card) {
            if ($sparse && cardsLoaded($root, $card, true) && !in_array($id, $requested, true)) {
                cardsPreflightUnload($root, $index, $id, true);
            }
        }
        $paths = array_merge($selected['keepPaths'] ?? [], array_map(static fn (string $id): string => (string) $index[$id]['path'], $requested));
        $paths = array_values(array_unique(array_filter($paths, 'is_string')));
        if ([] === $paths) {
            cardsFail("profile {$name} has an empty sparse selection");
        }
        $result = cardsGit($root, ['sparse-checkout', 'set', '--stdin'], implode("\n", $paths)."\n");
        if (0 !== $result['code']) {
            cardsFail('profile apply failed: '.trim($result['err']));
        }
        cardsSetProfile($root, $name);
        fwrite(STDOUT, "profile applied: {$name}; loaded cards: ".implode(', ', $requested)."\n");
        if ([] !== $external) {
            fwrite(STDOUT, 'external dependencies (not loaded here): '.implode(', ', array_unique($external))."\n");
        }
        return;
    }

    cardsFail('usage: darkwood cards list | status | load ID | unload ID | profile list|show|apply');
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    cardsMain($argv);
}

