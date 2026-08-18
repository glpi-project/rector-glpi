<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorGlpi\Rector\Glpi120x\ReplaceHardcodedRightnameByCommonDBTMRightnamePropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReplaceHardcodedRightnameByCommonDBTMRightnamePropertyRector::class);
};
