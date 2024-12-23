# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

- `Added` for new features.
- `Changed` for changes in existing functionality.
- `Deprecated` for soon-to-be removed features.
- `Removed` for now removed features.
- `Fixed` for any bug fixes.
- `Security` in case of vulnerabilities

## 3.0.1 - 2024.12.23

### Added

- Tested up to PHP v8.4.
- Updated GitHub issue templates.

## 3.0.0 - 2023.04.25

### Added

- Added `start()` method since sessions are no longer started when the `Session` class is instantiated.

### Changed

- Sessions will no longer be started when the `Session` class is instantiated. Instead, the `start()` method must be used.

## 2.1.0 - 2023.04.17

### Added

- Added `up()` method in `PdoHandler` to create required database table.

### Changed

- Renamed handlers to `LocalHandler` and `PdoHandler`.
- Miscellaneous code cleanup.

## [2.0.0] - 2023.01.26

### Added

- Added support for PHP 8.

### Removed

- Removed Flysystem handler

## [1.1.0] - 2021.03.19

### Added

- Added `cookie_same_site` config parameter.

## [1.0.1] - 2020.09.15

### Fixed

- Updated handler variable name in `Session` class.
- Updated documentation.

## [1.0.0] - 2020.09.14

### Added

- Initial release.