# Upgrade Notes

## 1.1.0
* Added new PDF processor [DomPdf](../01_Doc_Types_and_Available_Processors.md#available-pdf-processors) ([@PRinguinDevs](https://github.com/open-dxp/web-to-print-bundle/pull/5))

## Migrating from `pimcore/web-to-print-bundle` to `open-dxp/web-to-print-bundle`
* Renamed bundle to `OpenDxpWebToPrintBundle` (composer package: `open-dxp/web-to-print-bundle`)
* BC breaks
  * Removed deprecated `Chromium` driver. If you want to continue chromium, migrate to Gotenberg Chromium instead of native 
    Chromium driver.
  * Removed support for `Gotenberg` v1 (`gotenberg/gotenberg-php`). Use version `^2.2` instead.
