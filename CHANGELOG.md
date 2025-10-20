# Changelog

All notable changes to ScacchiTrack will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-10-20

### Added

#### Professional CMS Management
- Custom taxonomies: Chess Openings, Game Types, and Tags
- Advanced admin filters by tournament, result, and year
- Bulk actions: Export PGN, assign tournament, change result
- Quick edit functionality for inline editing
- Game duplication feature with complete metadata copying
- Enhanced dashboard with comprehensive statistics
- Top players widget with win percentages
- Recent tournaments overview

#### Advanced PGN Import System
- Single file upload import
- Multi-file batch import
- Paste PGN text import
- Complete PGN validation with error reporting
- Configurable duplicate detection
- UTF-8 encoding support with auto-detection
- Tabbed interface for import methods

#### Game Analysis Features
- Dual-mode position evaluation (Simple + Stockfish.js)
- Real-time position evaluation display
- Evaluation graph visualization with Chart.js
- Best move suggestions from Stockfish
- Automatic blunder/mistake/inaccuracy detection
- Color-coded move annotations
- Complete analysis statistics dashboard
- Configurable analysis depth (1-20)

### Fixed

#### Security
- All SQL queries now use wpdb->prepare() for injection prevention
- Input sanitization across all admin forms and imports
- XSS prevention with proper output escaping
- Nonce verification for all form submissions
- NULL checks in database queries

#### Import System
- Fixed skip_duplicates parameter not being respected
- Fixed mb_detect_encoding() potential false return
- Proper parameter passing through all import methods
- Improved file upload error handling

#### Performance
- Added is_main_query() checks to admin filters
- Optimized database queries with proper WHERE clauses
- Prevented filters from affecting unintended queries
- Lazy loading for Stockfish.js

#### Data Validation
- Array validation in dashboard statistics
- Prevention of "Undefined index" errors
- Fallback values for missing data
- Type checking for critical variables

### Changed
- Enhanced dashboard with real-time statistics
- Improved import interface with tabbed layout
- Better error messages in import process
- Updated admin UI with modern styling

### Technical Details
- 16 files modified
- 2,585 lines added
- 210 lines removed
- 3 new major components
- 100% WordPress coding standards compliance

## [Unreleased]

### Planned for v1.1.0
- Opening repertoire analysis
- Player performance tracking
- Tournament pairing management
- ECO code classification

### Planned for v1.2.0
- Multi-language support
- Custom evaluation engines
- Game comparison tools
- Advanced search filters

---

[1.0.0]: https://github.com/tozzilla/wp_chess/releases/tag/v1.0.0
