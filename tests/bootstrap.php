<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Autoload classes from stub files
spl_autoload_register(function ($class_name) {
    $stub_path = implode(
        DIRECTORY_SEPARATOR,
        [
            dirname(__FILE__, 2),
            'stubs',
            ...explode('\\', $class_name),
        ]
    ) . '.php';
    if (file_exists($stub_path)) {
        include_once($stub_path);
    }
});
