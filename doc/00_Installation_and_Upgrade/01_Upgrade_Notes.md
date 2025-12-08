# Upgrade Notes

## Migrating from `pimcore/web-to-print-bundle` to `open-dxp/web-to-print-bundle`
* Renamed bundle to `OpenDxpWebToPrintBundle` (composer package: `open-dxp/web-to-print-bundle`)
* BC breaks
  * Removed deprecated `Chromium` driver. If you want to continue chromium, migrate to Gotenberg Chromium instead of native 
    Chromium driver.
  * Removed support for `Gotenberg` v1 (`gotenberg/gotenberg-php`). Use version `^2.2` instead.
