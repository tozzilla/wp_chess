# ScacchiTrack - Architettura di Progetto

## Obiettivo del Progetto

ScacchiTrack è un plugin WordPress completo progettato per assistere i circoli scacchistici nella gestione e visualizzazione delle loro partite. Il plugin permette di archiviare partite in formato PGN, visualizzarle con una scacchiera interattiva, analizzarle e generare statistiche dettagliate.

**Target**: Circoli scacchistici che vogliono:
- Archiviare e organizzare le partite dei tornei
- Condividere le partite con i soci (controllo accessi)
- Analizzare le performance dei giocatori
- Visualizzare partite con scacchiera interattiva

## Informazioni Generali

- **Nome**: ScacchiTrack
- **Versione**: 0.9e (prossima release: 1.0.0)
- **Linguaggio**: Italiano (default)
- **Autore**: Andrea Tozzi per Empoli Scacchi ASD
- **Licenza**: GPL2

## Struttura del Progetto

```
wp_chess/
├── scacchitrack.php              # File principale del plugin
├── includes/                     # Logica backend PHP
│   ├── cpt.php                  # Custom Post Type e Taxonomies
│   ├── metaboxes.php            # Metabox per dettagli partita
│   ├── functions.php            # Funzioni utility e statistiche
│   ├── ajax.php                 # Handler AJAX per filtri
│   ├── shortcodes.php           # Shortcodes per frontend
│   ├── admin-columns.php        # Colonne personalizzate admin
│   ├── admin-enhancements.php   # Miglioramenti CMS (filtri, bulk actions)
│   ├── scripts.php              # Enqueue scripts e stili
│   └── frontend.php             # Funzioni frontend
├── js/                          # JavaScript frontend
│   ├── scacchitrack.js          # Classe principale scacchiera
│   ├── evaluation.js            # Valutazione posizioni (simple/advanced)
│   ├── analysis.js              # Analisi partite e grafici
│   └── filters.js               # Filtri dinamici frontend
├── css/                         # Stili
│   └── scacchitrack.css         # Stili principali
├── templates/                   # Template PHP
│   ├── admin/                   # Template amministrativi
│   │   ├── dashboard.php        # Dashboard con statistiche
│   │   ├── import.php           # Importazione PGN
│   │   ├── settings.php         # Impostazioni plugin
│   │   ├── stats.php            # Statistiche avanzate
│   │   └── tournaments.php      # Vista tornei
│   ├── content-partita.php      # Template archivio partite
│   ├── single-partita.php       # Template singola partita
│   ├── list-partite.php         # Lista partite (shortcode)
│   ├── partita-item.php         # Singolo item partita
│   └── login-form.php           # Form di login
└── .agent/                      # Documentazione progetto
    ├── README.md                # Indice documentazione
    ├── System/                  # Documentazione sistema
    │   ├── project_architecture.md
    │   └── database_schema.md
    ├── Tasks/                   # PRD e implementation plans
    └── SOP/                     # Best practices e procedure
```

## Stack Tecnologico

### Backend (WordPress/PHP)

**Core WordPress**:
- Custom Post Type: `scacchipartita`
- Custom Taxonomies: `apertura_scacchi`, `tipo_partita`, `etichetta_partita`
- Custom Capabilities per controllo accessi
- WordPress Meta API per dati partite

**Componenti PHP Principali**:
1. **ScacchiTrack_Import_Handler** - Gestione import PGN (singolo/batch/paste)
2. **ScacchiTrack_Ajax_Handler** - Gestione chiamate AJAX per filtri
3. **ScacchiTrack_Admin_Enhancements** - Miglioramenti CMS (filtri, bulk actions, quick edit, duplicate)

**Funzionalità Backend**:
- Import PGN con validazione e gestione duplicati
- Sistema di statistiche completo (giocatori, tornei, aperture)
- Filtri avanzati nell'admin (torneo, anno, risultato)
- Bulk actions personalizzate
- Quick edit per partite
- Duplicazione partite

### Frontend (JavaScript)

**Librerie Esterne**:
- **chess.js** - Logica scacchistica (validazione mosse, PGN parsing)
- **chessboard.js** - Rendering scacchiera interattiva
- **Chart.js** - Grafici per analisi (evaluation graph)
- **Stockfish** (opzionale) - Engine per valutazione avanzata

**Moduli JavaScript**:
1. **ScacchiTrack** (scacchitrack.js):
   - Gestione scacchiera interattiva
   - Navigazione mosse (play/pause, prev/next)
   - Flip board
   - Integrazione con evaluator e analyzer

2. **PositionEvaluator** (evaluation.js):
   - Modalità Simple: valutazione materiale statica
   - Modalità Advanced: integrazione Stockfish via Web Worker
   - Barra di valutazione visuale

3. **GameAnalyzer** (analysis.js):
   - Analisi completa partita
   - Grafico valutazione mosse (Chart.js)
   - Identificazione best moves
   - Rilevamento blunders/mistakes/inaccuracies
   - Annotazioni automatiche mosse

**Funzionalità Frontend**:
- Scacchiera interattiva con navigazione mosse
- Riproduzione automatica partite
- Sistema di filtri AJAX (torneo, giocatore, date)
- Visualizzazione PGN formattato
- Valutazione posizioni in tempo reale
- Grafici analisi partite

### CSS

- Design responsive
- Tema coerente con WordPress
- Componenti custom: scacchiera, evaluation bar, grafici
- Media queries per mobile/tablet

## Punti di Integrazione

### WordPress Hooks

**Actions**:
- `init` - Registrazione CPT, taxonomies, textdomain
- `admin_menu` - Aggiunta menu amministratore
- `admin_enqueue_scripts` - Caricamento assets admin
- `wp_enqueue_scripts` - Caricamento assets frontend
- `save_post_scacchipartita` - Salvataggio metadati partita
- `restrict_manage_posts` - Filtri admin
- `quick_edit_custom_box` - Quick edit
- `wp_ajax_*` / `wp_ajax_nopriv_*` - Handler AJAX

**Filters**:
- `post_row_actions` - Azione "Duplica" nella lista partite
- `bulk_actions-edit-scacchipartita` - Bulk actions personalizzate
- `parse_query` - Applicazione filtri admin

### AJAX Endpoints

**scacchitrack_filter_games**:
- Metodo: POST
- Parametri: `torneo`, `giocatore`, `data_da`, `data_a`, `paged`, `nonce`
- Risposta: `{html, found, max_pages}`
- Utilizzo: Filtri dinamici frontend

### Shortcodes

**[scacchitrack_partite]**:
- Attributi: `per_page`, `orderby`, `order`, `torneo`, `giocatore`
- Output: Lista partite filtrata con paginazione

**[scacchitrack_partita]**:
- Attributi: `id`
- Output: Singola partita con scacchiera interattiva

## Funzionalità Core

### 1. Gestione Partite

**Custom Post Type**: `scacchipartita`
- Titolo auto-generato: `{Torneo} R.{Round}: {Bianco}-{Nero}`
- Custom fields (post meta):
  - `_giocatore_bianco`
  - `_giocatore_nero`
  - `_data_partita`
  - `_nome_torneo`
  - `_round`
  - `_risultato` (1-0, 0-1, ½-½, *)
  - `_pgn` (notazione completa partita)

**Taxonomies**:
- `apertura_scacchi` (hierarchical)
- `tipo_partita` (hierarchical)
- `etichetta_partita` (non-hierarchical tags)

### 2. Import PGN

**Modalità supportate**:
1. **File Upload singolo** - Upload file .pgn
2. **Batch Import** - Upload multipli file contemporaneamente
3. **Paste PGN** - Incolla testo PGN direttamente

**Caratteristiche**:
- Parsing PGN standard con chess.js
- Validazione completa (mosse valide, formato corretto)
- Gestione duplicati (skip opzionale)
- Report dettagliato errori
- Batch processing per grandi volumi

**Classe**: `ScacchiTrack_Import_Handler` (in admin-enhancements.php)

### 3. Sistema Statistiche

**Statistiche Globali**:
- Totale partite
- Totale tornei
- Giocatori unici
- Percentuali risultati (vittorie bianco/nero, patte)
- Timeline partite (ultimi 12 mesi)

**Top Players**:
- Classifica per win percentage
- Statistiche individuali: partite giocate, vittorie, patte, sconfitte

**Tournament Stats**:
- Statistiche per torneo
- Partecipanti unici
- Distribuzione risultati

**Opening Stats**:
- Aperture più giocate
- Success rate per apertura

### 4. Analisi Partite

**Valutazione Posizioni**:

*Modalità Simple*:
- Valutazione materiale statica
- Calcolo centipawn advantage
- Considerazione posizione pezzi
- Veloce, no dipendenze esterne

*Modalità Advanced* (opzionale):
- Integrazione Stockfish via Web Worker
- Valutazione engine-based
- Best move calculation
- Depth configurabile

**Analisi Completa Partita**:
- Grafico valutazione mosse (Chart.js)
- Identificazione critical moments
- Annotazioni automatiche:
  - ?? = Blunder (perdita > 2 pedoni)
  - ? = Mistake (perdita > 1 pedone)
  - ?! = Inaccuracy (perdita > 0.5 pedoni)
  - ! = Good move (guadagno > 0.3)
  - !! = Brilliant move (guadagno > 1 pedone)

### 5. Controllo Accessi

**Capabilities System**:
- Custom capabilities per post type `partita`/`partite`
- Ruoli supportati: Administrator, Editor
- Controllo granulare: read, edit, delete, publish

**Frontend Access Control**:
- Login form per contenuti riservati
- Visibilità condizionale basata su capabilities
- Integrazione con sistema utenti WordPress

### 6. CMS Enhancements (Pacchetto B)

**Admin Filters**:
- Filtro per Torneo
- Filtro per Risultato
- Filtro per Anno

**Bulk Actions**:
- Azioni multiple personalizzate
- Gestione batch efficiente

**Quick Edit**:
- Modifica rapida metadati dalla lista
- JavaScript inline per popolazione campi

**Duplicate Game**:
- Clonazione partita completa
- Link "Duplica" nelle row actions

## Configurazione e Settings

**Plugin Settings** (accessibili da admin):
1. **Evaluation Mode**:
   - Simple: Valutazione materiale
   - Advanced: Stockfish integration

2. **Frontend Display**:
   - Abilitazione valutazione posizioni
   - Visibilità grafici analisi

3. **Import Settings**:
   - Skip duplicates di default
   - Timeout import

## Security e Best Practices

**Security**:
- Nonce verification su tutte le form
- Capability checks per operazioni sensibili
- Sanitization input (sanitize_text_field, wp_kses_post)
- Prepared statements per query database
- Prevention direct file access (`ABSPATH` check)

**Performance**:
- Caching statistiche (considerare transient API)
- Lazy loading scacchiera
- AJAX pagination per grandi dataset
- Stockfish in Web Worker (non-blocking)

**Code Quality**:
- Namespace implicito con prefisso `scacchitrack_`
- Classi per componenti complessi
- Separazione concerns (MVC-like)
- PHPDoc comments
- Error logging in debug mode

## Deployment e Versioning

**Versioning**:
- Attuale: 0.9e
- Prossima: 1.0.0 (stable release)
- Semantic versioning

**Activation/Deactivation**:
- Hook activation: registra CPT, flush rewrite rules, add capabilities
- Hook deactivation: remove capabilities, flush rewrite rules
- No data deletion su deactivation

**Compatibilità**:
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

## Roadmap e Future Features

**Previste per v1.0**:
- Documentazione completa
- Testing completo
- Performance optimization

**Future considerazioni**:
- REST API endpoints
- Gutenberg blocks
- Mobile app integration
- Export statistiche (PDF/CSV)
- AI-powered game analysis
- Multi-lingua (WPML ready)
