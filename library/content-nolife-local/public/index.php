<?php

use App\Kernel;

// Required for PHP FFI access to libdepthanything.dylib
ini_set('ffi.enable', '1');

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
