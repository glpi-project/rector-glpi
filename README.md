# Rector GLPI extension

This repository provides a Rector extension that can be used in both GLPI and GLPI plugins.

## Installation

To install this Rector extension, run the `composer require --dev glpi-project/rector-glpi`.
Once installed, you will need to register the GLPI rule set in you Rector configuration file:
```diff
return RectorConfig::configure()
    ->withSets([
+        \RectorGlpi\Set\GlpiSetList::GLPI_DEFAULT_SET,
    ])
;
```
