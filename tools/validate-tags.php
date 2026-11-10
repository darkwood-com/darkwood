#!/usr/bin/env php
<?php

declare(strict_types=1);

function fail(string $message): never
{
    fwrite(STDERR, "tags.json: {$message}\n");
    exit(1);
}

function validateTags(string $repositoryRoot): void
{
    $cataloguePath = $repositoryRoot.'/tags.json';
    $schemaPath = $repositoryRoot.'/tags.schema.json';

    if (!is_file($cataloguePath) || !is_file($schemaPath)) {
        fail('tags.json and tags.schema.json must both exist');
    }

    try {
        $catalogue = json_decode((string) file_get_contents($cataloguePath), true, 512, JSON_THROW_ON_ERROR);
        json_decode((string) file_get_contents($schemaPath), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        fail('invalid JSON: '.$exception->getMessage());
    }

    if (1 !== ($catalogue['schemaVersion'] ?? null) || !is_string($catalogue['repository'] ?? null)) {
        fail('invalid root metadata');
    }
    if (!is_array($catalogue['projects'] ?? null) || !array_is_list($catalogue['projects']) || [] === $catalogue['projects']) {
        fail('projects must be a non-empty list');
    }

    $ids = [];
    $paths = [];
    foreach ($catalogue['projects'] as $project) {
        if (!is_array($project) || array_keys($project) !== ['id', 'path', 'domain', 'description', 'topics']) {
            fail('each project must contain only id, path, domain, description and topics');
        }
        $id = $project['id'];
        $path = $project['path'];
        $topics = $project['topics'];
        if (!is_string($id) || 1 !== preg_match('~^[a-z0-9][a-z0-9.-]*$~', $id) || isset($ids[$id])) {
            fail('project ids must be valid and unique');
        }
        if (!is_string($path) || 1 !== preg_match('~^library/[^/]+$~', $path) || isset($paths[$path])) {
            fail('project paths must be direct, valid and unique');
        }
        if (!is_string($project['description']) || '' === trim($project['description'])) {
            fail($id.' must have a description');
        }
        if (!in_array($project['domain'], ['darkwood', 'matyo91'], true)) {
            fail($id.' must have a public logical domain');
        }
        if (!is_array($topics) || !array_is_list($topics) || [] === $topics) {
            fail($id.' must have topics');
        }
        $sortedTopics = $topics;
        sort($sortedTopics, SORT_STRING);
        if ($topics !== $sortedTopics || count($topics) !== count(array_unique($topics))) {
            fail($id.' topics must be sorted and unique');
        }
        if (!is_dir($repositoryRoot.'/'.$path) || is_link($repositoryRoot.'/'.$path)) {
            fail($id.' directory is missing or is a symlink');
        }
        $ids[$id] = true;
        $paths[$path] = true;
    }

    $directories = array_values(array_filter(glob($repositoryRoot.'/library/*') ?: [], 'is_dir'));
    $organizationProfile = $repositoryRoot.'/library/.github';
    if (is_dir($organizationProfile)) {
        $directories[] = $organizationProfile;
    }
    $declared = array_map(static fn (string $path): string => $repositoryRoot.'/'.$path, array_keys($paths));
    sort($directories);
    sort($declared);
    if ($directories !== $declared) {
        fail('every public library directory must be declared exactly once');
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    validateTags(realpath($argv[1] ?? getcwd()) ?: getcwd());
    fwrite(STDOUT, "tags.json: valid\n");
}
