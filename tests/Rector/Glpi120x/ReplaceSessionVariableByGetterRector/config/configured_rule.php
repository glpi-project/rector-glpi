<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorGlpi\Rector\Glpi120x\ReplaceSessionVariableByGetterRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReplaceSessionVariableByGetterRector::class);
};
