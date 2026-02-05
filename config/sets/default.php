<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorGlpi\Rector\Glpi120x\ReplaceCommonGlpiGetTypeByClassConstantRector;

return static function (RectorConfig $rector_config): void {
    $glpi_directory = null;
    $glpi_version   = null;

    $expected_directories = [
        // Expected directory when `rector-glpi` in required by GLPI itself:
        // `glpi/` <- `vendor/` <- `glpi-project/` <- `rector-glpi/` <- `config/` <- `sets/`
        \dirname(__DIR__, 5),

        // Expected directory when `rector-glpi` in required a GLPI plugin:
        // `glpi/` <- `plugins/` <- `{$plugin_key}/` <- `vendor/` <- `glpi-project/` <- `rector-glpi/` <- `config/` <- `sets/`
        \dirname(__DIR__, 7),
    ];
    foreach ($expected_directories as $directory) {
        if (\is_file(\implode(DIRECTORY_SEPARATOR, [$directory, 'src', 'CommonGLPI.php']))) {
            $glpi_directory = $directory;
            break;
        }
    }

    if ($glpi_directory === null) {
        // rector-glpi rules are not expected to be executed outside the GLPI context.
        return;
    }

    $version_dir = \implode(DIRECTORY_SEPARATOR, [$glpi_directory, 'version']);

    if (\is_dir($version_dir)) {
        $file_iterator = new FilesystemIterator($version_dir);
        $files = \iterator_to_array($file_iterator);
        $version_file = \end($files);

        if ($version_file instanceof SplFileInfo) {
            $glpi_version = $version_file->getBaseName();
        }
    }

    if ($glpi_version === null) {
        // rector-glpi rules are not expected to be executed with older GLPI versions.
        return;
    }

    if (\version_compare($glpi_version, '12.0.0-dev', '>=')) {
        $rector_config->rule(ReplaceCommonGlpiGetTypeByClassConstantRector::class);
    }
};
