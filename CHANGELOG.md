# Changelog

All notable changes to `filament-take-picture-field` will be documented in this file.

## v1.4.0 - 2026-10-19

### Fixed
- Black camera preview on iOS Safari and other iOS browsers (#21)
- `useModal(false)` now shows the camera inline below the field
- Escape now consistently cancels the capture
- Camera turns off when leaving the page
- Multiple-shot tile actions now work on touch screens

### Added
- `mirroredView(?bool $mirrored = true)` option (based on #14 by @fsavina)
- Filament FileUpload-style photo previews with hover zoom; missing shots shown as dashed tiles

### Security
- Captured photos are validated as real JPEG, PNG, or WebP images before being stored

### Changed
- JavaScript moved into a lazy-loaded Alpine component; run `php artisan filament:assets` after updating
- Codebase split into traits, JS modules, and Blade partials
- `->live()` is now respected
- Requirements corrected to Laravel ^11.28 / ^12 / ^13 and PHP ^8.2, matching Filament v4 and v5

## v1.3.0 - 2026-04-30

## v1.2.1 - 2025-12-13

## v1.2.0 - 2025-12-13

## v1.1.0 - 2025-09-23

## v1.0.0 - 2025-04-17

- Initial release