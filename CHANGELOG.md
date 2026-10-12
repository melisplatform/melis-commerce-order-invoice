# Changelog

## v6.0.4 - 2026-09-25
### Security
* **security:** invoice tab follows the Orders right (audit item 7.0)

## v6.0.3 - 2026-09-23
### Security
* **security:** parameterised SQL for date filters, ORDER BY and hand-quoted values (audit item 11.0)

## v6.0.2 - 2026-08-20
### Docs
* **melisai:** React back-office AI documentation

## v6.0.1 - 2026-08-11
* Maintenance release.

## v6.0.0 - 2026-08-11
### Security
* **security:** add SECURITY.md (private vulnerability reporting policy)
* Fix audit findings
### Added
* **composer:** add docs link and authors block, swap zf2 keyword for laminas, bump melis-commerce/php constraints
* Added invoice tab on order
* **ui-react:** add invoice-download React brick
### Fixed
* Fixed download invoice

## v5.3.0 - 2024-09-25
### Dependencies & build
* Rebuilt the asset bundle

## v5.2.0 - 2024-06-06
* Maintenance release.

## v5.1.0 - 2024-02-13
### Changed
* Deprecated explode on null
* Deprecated strftime

## v5.0.1 - 2023-05-24
### Changed
* Renamed sql
* Converts table to utf8mb4

## v5.0.0 - 2022-06-22
### Changed
* Removed number_format in setting the partial sub total to fix the non-numeric error encountered
