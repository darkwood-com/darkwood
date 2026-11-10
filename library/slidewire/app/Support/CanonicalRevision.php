<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class CanonicalRevision
{
    /** Conservative dependency closure: source, compiler, assets and renderer. */
    public function hash(): string
    {
        $files = [];
        foreach (['resources', 'app', 'config', 'public', 'bin'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                $files[$file->getRelativePathname().':'.$directory] = hash_file('sha256', $file->getPathname());
            }
        }
        foreach (['composer.lock', 'package-lock.json'] as $file) {
            $files[$file] = hash_file('sha256', base_path($file));
        }
        ksort($files);

        return hash('sha256', json_encode($files, JSON_THROW_ON_ERROR));
    }
}
