# ScacchiTrack

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL-green.svg)

**A comprehensive WordPress plugin designed to help chess clubs manage, analyze, and showcase their games.**

ScacchiTrack provides professional-grade chess game management with advanced features like position evaluation, game analysis with Stockfish, batch PGN import, and comprehensive tournament tracking.

## ✨ Features

### 📚 Professional CMS Management
- **Custom Taxonomies**: Organize games by openings, types, and custom tags
- **Advanced Filters**: Filter by tournament, result, year in admin interface
- **Bulk Actions**: Export PGN, assign tournaments, change results for multiple games
- **Quick Edit**: Inline editing for fast updates
- **Game Duplication**: Copy games with all metadata
- **Enhanced Dashboard**: Real-time statistics, top players, recent tournaments

### 📥 Advanced Import System
- **Multiple Import Methods**: File upload, batch upload, or paste PGN text
- **Smart Validation**: Complete PGN validation with detailed error reporting
- **Duplicate Detection**: Configurable duplicate checking
- **Encoding Support**: UTF-8 with auto-detection and conversion

### 🔍 Game Analysis
- **Position Evaluation**: Dual-mode system (Simple material-based + Stockfish.js)
- **Visual Analysis**: Interactive evaluation graph with Chart.js
- **Move Quality**: Automatic detection of blunders, mistakes, and brilliant moves
- **Statistics**: Comprehensive analysis dashboard with detailed metrics
- **Best Moves**: Stockfish-powered move suggestions

### 🎨 User Experience
- **Member-Exclusive Access**: Restrict game visibility to club members (optional)
- **Responsive Design**: Works seamlessly on all devices
- **WordPress Integration**: Native WordPress admin interface
- **Shortcode Support**: Easy embedding in posts and pages

## 📦 Installation

### Method 1: WordPress Admin (Recommended)
1. Download the latest release from [Releases](https://github.com/tozzilla/wp_chess/releases)
2. Go to **WordPress Admin** → **Plugins** → **Add New** → **Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Click **Activate Plugin**

### Method 2: Manual Installation
1. Download and extract the plugin
2. Upload the `scacchitrack` folder to `/wp-content/plugins/`
3. Activate through the **Plugins** menu in WordPress

### Method 3: Git Clone
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/tozzilla/wp_chess.git scacchitrack
```

## 🚀 Quick Start

### Basic Setup
1. After activation, go to **ScacchiTrack** in WordPress admin
2. Configure settings in **ScacchiTrack** → **Settings**
3. Import games via **ScacchiTrack** → **Import/Export**

### Importing Games
```
ScacchiTrack → Import/Export
→ Choose import method (File/Paste/Batch)
→ Select PGN file(s) or paste content
→ Enable/disable "Skip duplicates"
→ Click Import
```

### Displaying Games
Use shortcodes in your posts/pages:

```
[scacchitrack_partite]
Display list of all games

[scacchitrack_partita id="123"]
Display single game with analysis
```

### Analyzing Games
1. View any game on your site
2. Enable **Advanced Mode** in **Settings** → **Evaluation**
3. Click **Analyze Game** button
4. View evaluation graph and move annotations

## 📖 Documentation

### Shortcodes

#### Game List
```php
[scacchitrack_partite tornei="Campionato 2025" risultato="1-0" limit="10"]
```

Parameters:
- `tornei`: Filter by tournament name
- `risultato`: Filter by result (1-0, 0-1, ½-½, *)
- `limit`: Number of games to display
- `orderby`: Sort order (date, title)

#### Single Game
```php
[scacchitrack_partita id="123"]
```

Parameters:
- `id`: Game post ID (required)

### Taxonomies

#### Chess Openings (apertura_scacchi)
Hierarchical taxonomy for opening classification
```
Sicilian Defense
  ├─ Najdorf Variation
  ├─ Dragon Variation
  └─ Sveshnikov Variation
```

#### Game Types (tipo_partita)
Hierarchical taxonomy for game classification
```
Tournament Games
  ├─ Rapid
  ├─ Blitz
  └─ Classical
```

#### Tags (etichetta_partita)
Non-hierarchical tags for flexible labeling
```
brilliant-game, endgame-study, tactical-shot
```

## 🔧 Configuration

### Evaluation Settings
**Settings** → **Evaluation**
- **Enable Evaluation**: Turn on/off position evaluation
- **Evaluation Mode**: Simple (material) or Advanced (Stockfish)
- **Analysis Depth**: 1-20 (higher = more accurate, slower)

### Access Control
**Settings** → **Access Control**
- **Restrict Access**: Require password for game viewing
- **Access Password**: Set password for restricted access

## 🛠️ Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher

### External Dependencies (CDN)
- **Stockfish.js**: For advanced position evaluation
- **Chart.js**: For evaluation graphs

## 📊 Technical Details

### Database Tables
Uses WordPress custom post type `scacchipartita` with custom meta fields:
- `_giocatore_bianco`: White player name
- `_giocatore_nero`: Black player name
- `_data_partita`: Game date
- `_nome_torneo`: Tournament name
- `_round`: Round number
- `_risultato`: Game result
- `_pgn`: Complete PGN notation

### File Structure
```
scacchitrack/
├── css/                    # Stylesheets
├── js/                     # JavaScript files
│   ├── analysis.js         # Game analysis engine
│   ├── evaluation.js       # Position evaluation
│   └── scacchitrack.js     # Main JS
├── includes/               # PHP classes
│   ├── admin-enhancements.php
│   ├── ajax.php
│   ├── cpt.php
│   ├── frontend.php
│   ├── functions.php
│   ├── scripts.php
│   └── shortcodes.php
├── templates/              # Template files
│   ├── admin/              # Admin templates
│   └── content-partita.php # Game display template
├── CHANGELOG.md
├── README.md
└── scacchitrack.php        # Main plugin file
```

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### Development Guidelines
- Follow WordPress coding standards
- Use `wpdb->prepare()` for all SQL queries
- Sanitize all inputs, escape all outputs
- Add PHPDoc comments to functions
- Test on multiple WordPress versions

## 🐛 Bug Reports

Found a bug? Please [open an issue](https://github.com/tozzilla/wp_chess/issues) with:
- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed list of changes in each version.

## 🗺️ Roadmap

### v1.1.0 (Planned)
- Opening repertoire analysis
- Player performance tracking
- Tournament pairing management
- ECO code classification

### v1.2.0 (Planned)
- Multi-language support
- Custom evaluation engines
- Game comparison tools
- Advanced search filters

## 📄 License

This project is licensed under the GPL License - see the [LICENSE](LICENSE) file for details.

ScacchiTrack is open-source software developed by Andrea Tozzi for Empoli Scacchi ASD, available for anyone to use, modify, and enhance, provided the original source is credited.

## 👥 Credits

- **Development**: Andrea Tozzi (AI-assisted with Claude Code)
- **Original Club**: Empoli Scacchi ASD
- **Chess Engine**: [Stockfish.js](https://github.com/nmrugg/stockfish.js/)
- **Charting**: [Chart.js](https://www.chartjs.org/)
- **Chess Library**: [chess.js](https://github.com/jhlywa/chess.js)
- **Chessboard**: [chessboard.js](https://chessboardjs.com/)

## 🔗 Links

- **Repository**: https://github.com/tozzilla/wp_chess
- **Issues**: https://github.com/tozzilla/wp_chess/issues
- **Releases**: https://github.com/tozzilla/wp_chess/releases

## 💖 Support

If you find ScacchiTrack useful, please consider:
- ⭐ **Star** the repository
- 🐛 **Report** bugs and issues
- 💡 **Suggest** new features
- 🤝 **Contribute** code or documentation
- 📢 **Share** with other chess clubs

---

**Happy chess playing!** ♟️
