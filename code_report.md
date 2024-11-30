# Report del Codice Sorgente

Generato il: 2024-11-25 09:01:16

## Indice dei Contenuti

- [.](#.)
  - [scacchitrack.php](#.-scacchitrack-php)
  - [code-scanner.py](#.-code-scanner-py)
- [css](#css)
  - [scacchitrack.css](#css-scacchitrack-css)
  - [style.css](#css-style-css)
- [js](#js)
  - [filters.js](#js-filters-js)
  - [scacchitrack.js](#js-scacchitrack-js)
- [includes](#includes)
  - [functions.php](#includes-functions-php)
  - [shortcodes.php](#includes-shortcodes-php)
  - [cpt.php](#includes-cpt-php)
  - [admin-columns.php](#includes-admin-columns-php)
  - [ajax.php](#includes-ajax-php)
  - [scripts.php](#includes-scripts-php)
  - [metaboxes.php](#includes-metaboxes-php)
  - [frontend.php](#includes-frontend-php)
- [templates](#templates)
  - [list-partite.php](#templates-list-partite-php)
  - [admin-page.php](#templates-admin-page-php)
  - [partita-item.php](#templates-partita-item-php)
  - [content-partita.php](#templates-content-partita-php)
  - [partite-loop.php](#templates-partite-loop-php)
  - [single-partita.php](#templates-single-partita-php)
- [templates/admin](#templates-admin)
  - [dashboard.php](#templates-admin-dashboard-php)
  - [import.php](#templates-admin-import-php)
  - [settings.php](#templates-admin-settings-php)
  - [stats.php](#templates-admin-stats-php)

---

## . {#.}

### scacchitrack.php {#.-scacchitrack-php}

```php
<?php
/*
Plugin Name: ScacchiTrack
Plugin URI: https://connecta.app
Description: Plugin per caricare, gestire e visualizzare partite di scacchi con scacchiera interattiva.
Version: 0.9d
Author: Andrea Tozzi per Empoli Scacchi ASD
Author URI: https://connecta.app
License: GPL2
Text Domain: scacchitrack
*/

// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Aumenta il limite di memoria se necessario
if (WP_DEBUG) {
    $current = wp_convert_hr_to_bytes( ini_get('memory_limit') );
    $wp_max = wp_convert_hr_to_bytes( WP_MAX_MEMORY_LIMIT );
    $needed = 268435456; // 256MB
    if ($current < $needed && (!$wp_max || $wp_max > $needed)) {
        ini_set('memory_limit', '256M');
    }
}

// Definizione costanti
define('SCACCHITRACK_VERSION', '1.0.0');
define('SCACCHITRACK_DIR', plugin_dir_path(__FILE__));
define('SCACCHITRACK_URL', plugin_dir_url(__FILE__));

// Caricamento dei file necessari
require_once SCACCHITRACK_DIR . 'includes/functions.php'; 
require_once SCACCHITRACK_DIR . 'includes/cpt.php';
require_once SCACCHITRACK_DIR . 'includes/metaboxes.php';
require_once SCACCHITRACK_DIR . 'includes/shortcodes.php';
require_once SCACCHITRACK_DIR . 'includes/admin-columns.php';
require_once SCACCHITRACK_DIR . 'includes/scripts.php';
require_once SCACCHITRACK_DIR . 'includes/frontend.php'; 
require_once SCACCHITRACK_DIR . 'includes/ajax.php'; 

// Attivazione del plugin
function scacchitrack_activate() {
    // Aggiungi le capabilities
    scacchitrack_add_capabilities();
    
    // Registra il CPT
    scacchitrack_register_cpt();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'scacchitrack_activate');

// Disattivazione del plugin
function scacchitrack_deactivate() {
    // Rimuovi le capabilities
    scacchitrack_remove_capabilities();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'scacchitrack_deactivate');

// Inizializzazione del plugin
function scacchitrack_init() {
    // Carica il dominio di traduzione
    load_plugin_textdomain('scacchitrack', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'scacchitrack_init');

// Aggiungi menu amministratore
function scacchitrack_admin_menu() {
    add_menu_page(
        __('ScacchiTrack', 'scacchitrack'),
        __('ScacchiTrack', 'scacchitrack'),
        'edit_posts',
        'scacchitrack',
        'scacchitrack_admin_page',
        'dashicons-games',
        20
    );
}
add_action('admin_menu', 'scacchitrack_admin_menu');

// Pagina amministratore
function scacchitrack_admin_page() {
    include SCACCHITRACK_DIR . 'templates/admin-page.php';
}
```

### code-scanner.py {#.-code-scanner-py}

```py
import os
import datetime

def is_code_file(filename):
    """
    Determina se un file è un file di codice in base all'estensione.
    Aggiungere altre estensioni secondo necessità.
    """
    code_extensions = {
        '.py', '.java', '.cpp', '.c', '.h', '.hpp', '.js', '.ts',
        '.html', '.css', '.php', '.rb', '.go', '.rs', '.swift',
        '.kt', '.scala', '.sh', '.ps1', '.sql'
    }
    return os.path.splitext(filename)[1].lower() in code_extensions

def scan_directory(start_path='.'):
    """
    Scansiona ricorsivamente una directory e raccoglie il contenuto di tutti i file di codice.
    Restituisce un dizionario organizzato per cartelle.
    """
    result = {}
    
    # Converti il percorso in assoluto per avere riferimenti completi
    start_path = os.path.abspath(start_path)
    
    for root, dirs, files in os.walk(start_path):
        # Ignora le cartelle nascoste e le cartelle comuni da escludere
        dirs[:] = [d for d in dirs if not d.startswith('.') and d not in {'venv', 'node_modules', '__pycache__', 'dist', 'build'}]
        
        # Filtra solo i file di codice
        code_files = [f for f in files if is_code_file(f) and not f.startswith('.')]
        
        if code_files:
            # Usa il percorso relativo come chiave
            relative_path = os.path.relpath(root, start_path)
            result[relative_path] = {}
            
            for file in code_files:
                try:
                    file_path = os.path.join(root, file)
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()
                        result[relative_path][file] = content
                except Exception as e:
                    print(f"Errore nella lettura del file {file_path}: {str(e)}")

    return result

def generate_report(code_contents, output_file='code_report.md'):
    """
    Genera un report in formato Markdown con il contenuto di tutti i file di codice.
    """
    with open(output_file, 'w', encoding='utf-8') as f:
        # Scrivi l'intestazione del report
        f.write(f"# Report del Codice Sorgente\n\n")
        f.write(f"Generato il: {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
        f.write("## Indice dei Contenuti\n\n")
        
        # Genera l'indice
        for folder in code_contents.keys():
            folder_display = '.' if folder == '.' else folder
            f.write(f"- [{folder_display}](#{folder.replace('/', '-')})\n")
            for file in code_contents[folder].keys():
                f.write(f"  - [{file}](#{folder.replace('/', '-')}-{file.replace('.', '-')})\n")
        
        f.write("\n---\n\n")
        
        # Scrivi il contenuto di ogni file
        for folder, files in code_contents.items():
            folder_display = '.' if folder == '.' else folder
            f.write(f"## {folder_display} {'{#' + folder.replace('/', '-') + '}'}\n\n")
            
            for filename, content in files.items():
                f.write(f"### {filename} {'{#' + folder.replace('/', '-') + '-' + filename.replace('.', '-') + '}'}\n\n")
                f.write("```" + os.path.splitext(filename)[1][1:] + "\n")
                f.write(content)
                f.write("\n```\n\n")

def main():
    """
    Funzione principale che esegue la scansione e genera il report.
    """
    try:
        print("Scansione delle directory in corso...")
        code_contents = scan_directory()
        
        if not code_contents:
            print("Nessun file di codice trovato nella directory corrente e nelle sottodirectory.")
            return
        
        output_file = 'code_report.md'
        generate_report(code_contents, output_file)
        print(f"\nReport generato con successo: {os.path.abspath(output_file)}")
        
        # Stampa alcune statistiche
        total_files = sum(len(files) for files in code_contents.values())
        total_dirs = len(code_contents)
        print(f"\nStatistiche:")
        print(f"- Directory scansionate: {total_dirs}")
        print(f"- File di codice trovati: {total_files}")
        
    except Exception as e:
        print(f"Si è verificato un errore: {str(e)}")

if __name__ == "__main__":
    main()
```

## css {#css}

### scacchitrack.css {#css-scacchitrack-css}

```css
/* Stili per la scacchiera e i controlli */
.scacchiera-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

#scacchiera {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

/* Controlli della scacchiera */
.scacchiera-controlli {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.control-button {
    padding: 8px 12px;
    border: none;
    background: #f0f0f0;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.control-button:hover {
    background: #e0e0e0;
}

.control-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.control-button .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    line-height: 20px;
}

/* Visualizzatore PGN */
.pgn-viewer {
    margin-top: 20px;
    padding: 15px;
    background: #f8f8f8;
    border-radius: 4px;
    font-family: monospace;
    line-height: 1.5;
}

.pgn-moves {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.move-number {
    color: #666;
    user-select: none;
    cursor: default;
}

.move {
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 3px;
    transition: background-color 0.2s;
}

.move:hover {
    background: #e0e0e0;
}

.move.current {
    background: #4CAF50;
    color: white;
}

/* Controllo velocità */
.velocita-controllo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 15px;
}

.velocita-controllo input[type="range"] {
    width: 100px;
}

/* Responsive */
@media (max-width: 600px) {
    .scacchiera-controlli {
        flex-direction: column;
        align-items: stretch;
    }
    
    .velocita-controllo {
        margin: 10px 0;
    }
}

/* Stili pezzi degli scacchi hover */
.square-55d63 {
    transition: background-color 0.2s;
}

.square-55d63:hover {
    background-color: rgba(255, 255, 0, 0.2);
}

/* Evidenziazione ultima mossa */
.highlight-square {
    box-shadow: inset 0 0 3px 3px yellow;
}

/* Animazioni */
@keyframes moveHighlight {
    from { background-color: rgba(255, 255, 0, 0.4); }
    to { background-color: transparent; }
}

.move-highlight {
    animation: moveHighlight 1s ease-out;
}
```

### style.css {#css-style-css}

```css
/* ScacchiTrack Styles */

/* Container principale */
.scacchitrack-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Filtri */
.scacchitrack-filters {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-column label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.filter-column input,
.filter-column select {
    width: 100%;
}

.filter-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}

/* Lista partite */
.scacchitrack-results table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.scacchitrack-results th,
.scacchitrack-results td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.scacchitrack-results th {
    background: #f5f5f5;
    font-weight: 600;
}

/* Scacchiera */
.scacchiera-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.scacchiera {
    max-width: 600px;
    margin: 0 auto;
}

/* Controlli scacchiera */
.scacchiera-controlli {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.control-button {
    padding: 8px 12px;
    border: none;
    background: #f0f0f0;
    border-radius: 4px;
    cursor: pointer;
}

.control-button:hover {
    background: #e0e0e0;
}

.control-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Visualizzatore PGN */
.pgn-viewer {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.move {
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 3px;
}

.move:hover {
    background: #f0f0f0;
}

.move.current {
    background: #4CAF50;
    color: white;
}

/* Paginazione */
.scacchitrack-pagination {
    margin-top: 20px;
    text-align: center;
}

.scacchitrack-pagination .page-numbers {
    display: inline-block;
    padding: 5px 10px;
    margin: 0 2px;
    border: 1px solid #ddd;
    border-radius: 3px;
    text-decoration: none;
}

.scacchitrack-pagination .page-numbers.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .scacchiera-controlli {
        flex-direction: column;
    }
    
    .control-button {
        width: 100%;
    }
}

/* Stati di caricamento */
.loading {
    opacity: 0.5;
    pointer-events: none;
}

/* Messaggi */
.scacchitrack-message {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
}

.scacchitrack-message.error {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.scacchitrack-message.success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}
```

## js {#js}

### filters.js {#js-filters-js}

```js
(function($) {
    'use strict';

    class ScacchiTrackFilters {
        constructor() {
            console.log('🔍 ScacchiTrackFilters inizializzato');
            this.form = $('#scacchitrack-filter-form');
            this.resultsContainer = $('#scacchitrack-results tbody');
            this.pagination = $('.scacchitrack-pagination');
            this.currentPage = 1;
            this.debounceTimeout = null;
            
            console.log('🔍 Form trovato:', this.form.length > 0);
            this.bindEvents();
        }

        bindEvents() {
            // Input e select cambiano immediatamente
            this.form.find('input, select').on('change keyup', (e) => {
                console.log('🔍 Campo cambiato:', $(e.target).attr('name'));
                
                // Usa debounce per evitare troppe richieste
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = setTimeout(() => {
                    this.currentPage = 1;
                    this.filterGames();
                }, 500);
            });

            // Reset button
            this.form.find('button[type="reset"]').on('click', (e) => {
                e.preventDefault();
                console.log('🔍 Reset form');
                this.form[0].reset();
                this.currentPage = 1;
                this.filterGames();
            });

            // Previeni il submit del form
            this.form.on('submit', (e) => {
                e.preventDefault();
            });

            // Paginazione
            $(document).on('click', '.scacchitrack-pagination a', (e) => {
                e.preventDefault();
                this.currentPage = parseInt($(e.currentTarget).data('page'));
                this.filterGames();
            });
        }

        filterGames() {
            console.log('🔍 Inizio filterGames');
            // Raccogli solo i valori non vuoti
            let data = {
                action: 'scacchitrack_filter_games',
                nonce: scacchitrackData.filterNonce,
                paged: this.currentPage
            };

            // Aggiungi solo i filtri che hanno un valore
            const torneo = $('#torneo').val();
            if (torneo) data.torneo = torneo;

            const giocatore = $('#giocatore').val();
            if (giocatore) data.giocatore = giocatore;

            const data_da = $('#data_da').val();
            if (data_da) data.data_da = data_da;

            const data_a = $('#data_a').val();
            if (data_a) data.data_a = data_a;

            console.log('🔍 Dati filtro:', data);

            // Aggiungi classe loading
            this.resultsContainer.addClass('loading');

            // Esegui la richiesta AJAX
            $.ajax({
                url: scacchitrackData.ajaxUrl,
                type: 'POST',
                data: data,
                success: (response) => {
                    console.log('🔍 Risposta AJAX ricevuta:', response);
                    if (response.success) {
                        if (!response.data || !response.data.html) {
                            console.error('🔍 HTML vuoto nella risposta');
                            this.resultsContainer.html(
                                '<tr><td colspan="6">' +
                                (scacchitrackData.i18n.noResults || 'Nessun risultato trovato') +
                                '</td></tr>'
                            );
                            this.pagination.hide();
                        } else {
                            console.log('🔍 Contenuto HTML:', response.data.html);
                            console.log('🔍 Numero risultati trovati:', response.data.found);
                            console.log('🔍 Numero pagine totali:', response.data.max_pages);
                            
                            this.resultsContainer.html(response.data.html);
                            if (response.data.max_pages && response.data.max_pages > 1) {
                                this.updatePagination(response.data.max_pages);
                            } else {
                                this.pagination.hide();
                            }
                        }
                    } else {
                        console.error('🔍 Errore nella risposta:', response);
                        this.resultsContainer.html(
                            '<tr><td colspan="6">' + 
                            (scacchitrackData.i18n.errorLoading || 'Errore nel caricamento dei risultati') + 
                            '</td></tr>'
                        );
                        this.pagination.hide();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('🔍 Errore AJAX:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    this.resultsContainer.html(
                        '<tr><td colspan="6">' + 
                        scacchitrackData.i18n.errorLoading + 
                        '</td></tr>'
                    );
                },
                complete: () => {
                    this.resultsContainer.removeClass('loading');
                }
            });
        }

        updatePagination(maxPages) {
            if (maxPages <= 1) {
                this.pagination.hide();
                return;
            }

            let html = '';
            for (let i = 1; i <= maxPages; i++) {
                html += `
                    <a href="#" 
                       class="page-numbers ${i === this.currentPage ? 'current' : ''}"
                       data-page="${i}">${i}</a>
                `;
            }

            this.pagination.html(html).show();
        }
    }

    // Inizializzazione
    $(document).ready(() => {
        if ($('#scacchitrack-filter-form').length) {
            window.scacchitrackFilters = new ScacchiTrackFilters();
        }
    });

})(jQuery);
```

### scacchitrack.js {#js-scacchitrack-js}

```js
/* ScacchiTrack - Script principale per la gestione della scacchiera interattiva */

(function($) {
    'use strict';

    class ScacchiTrack {
        constructor() {
            // Stato iniziale
            this.game = new Chess();
            this.pgnIndex = 0;
            this.moves = [];
            this.isPlaying = false;
            this.playInterval = null;
            this.playSpeed = 2000;
            this.boardOrientation = 'white';
    
            // Configurazione scacchiera
            this.config = {
                position: 'start',
                pieceTheme: (piece) => {
                    return scacchitrackData.pieces[piece];
                },
                showNotation: true,
                draggable: false,
                orientation: 'white'
            };
    
     // Elementi DOM
     this.elements = {
        board: $('#scacchiera'),
        pgnViewer: $('#pgn-viewer'),
        startBtn: $('#startBtn'),
        prevBtn: $('#prevBtn'),
        playBtn: $('#playBtn'),
        nextBtn: $('#nextBtn'),
        endBtn: $('#endBtn'),
        flipBtn: $('#flipBtn'),
        velocitaRange: $('#velocitaRange'),
        togglePgnBtn: $('#togglePgn')
    };
    
            this.init();
        }

        init() {
            // Inizializza la scacchiera
            this.board = Chessboard('scacchiera', this.config);

            // Carica il PGN se disponibile
            if (scacchitrackData.pgn) {
                this.loadPgn(scacchitrackData.pgn);
            }

            // Bind degli eventi
            this.bindEvents();

            // Inizializza il visualizzatore PGN
            this.initPgnViewer();

            // Responsive resize
            $(window).resize(() => {
                this.board.resize();
            });
        }

        loadPgn(pgn) {
            try {
                // Carica il PGN nel gioco
                this.game.load_pgn(pgn);
                
                // Estrai le mosse
                this.moves = this.parseMoves();
                
                // Reset della posizione
                this.pgnIndex = 0;
                this.board.position('start');
                
                // Aggiorna il visualizzatore PGN
                this.updatePgnViewer();
                
                return true;
            } catch (e) {
                console.error('Errore nel caricamento del PGN:', e);
                return false;
            }
        }

        parseMoves() {
            const history = this.game.history({ verbose: true });
            return history.map(move => ({
                from: move.from,
                to: move.to,
                promotion: move.promotion,
                san: move.san
            }));
        }

        bindEvents() {
            // Bottoni di controllo
            this.elements.startBtn.on('click', () => this.goToStart());
            this.elements.prevBtn.on('click', () => this.prevMove());
            this.elements.playBtn.on('click', () => this.togglePlay());
            this.elements.nextBtn.on('click', () => this.nextMove());
            this.elements.endBtn.on('click', () => this.goToEnd());
            this.elements.flipBtn.on('click', () => this.flipBoard());

            // Controllo velocità
            this.elements.velocitaRange.on('input', (e) => {
                this.playSpeed = 3000 / parseInt(e.target.value);
                if (this.isPlaying) {
                    this.togglePlay();
                    this.togglePlay();
                }
            });

            // Toggle PGN
            this.elements.togglePgnBtn.on('click', () => {
                $('.pgn-raw').slideToggle();
                this.elements.togglePgnBtn.text(
                    $('.pgn-raw').is(':visible') ? 
                    scacchitrackData.i18n.hidePgn : 
                    scacchitrackData.i18n.showPgn
                );
            });

            // Click sulle mosse nel visualizzatore PGN
            this.elements.pgnViewer.on('click', '.move', (e) => {
                const moveIndex = $(e.target).data('move-index');
                if (typeof moveIndex !== 'undefined') {
                    this.goToMove(moveIndex);
                }
            });

            // Gestione tasti freccia
            $(document).on('keydown', (e) => {
                if (!this.isPlaying) {
                    switch(e.which) {
                        case 37: // freccia sinistra
                            this.prevMove();
                            break;
                        case 39: // freccia destra
                            this.nextMove();
                            break;
                    }
                }
            });
        }

        initPgnViewer() {
            this.elements.pgnViewer.empty();
            if (this.moves.length === 0) {
                this.elements.pgnViewer.html(`<p>${scacchitrackData.i18n.noMoves}</p>`);
                return;
            }
            this.updatePgnViewer();
        }

        updatePgnViewer() {
            const container = $('<div class="pgn-moves"></div>');
            
            for (let i = 0; i < this.moves.length; i++) {
                if (i % 2 === 0) {
                    container.append(
                        $(`<span class="move-number">${(i/2 + 1)}.</span>`)
                    );
                }
                
                const moveSpan = $(
                    `<span class="move ${i === this.pgnIndex - 1 ? 'current' : ''}" ` +
                    `data-move-index="${i}">${this.moves[i].san}</span>`
                );
                
                container.append(moveSpan);
                container.append(' ');
            }

            this.elements.pgnViewer.html(container);

            // Scroll alla mossa corrente
            const currentMove = this.elements.pgnViewer.find('.current');
            if (currentMove.length) {
                currentMove[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }

        goToStart() {
            this.stopPlay();
            this.game.reset();
            this.pgnIndex = 0;
            this.board.position('start');
            this.updateStatus();
        }

        goToEnd() {
            this.stopPlay();
            while (this.pgnIndex < this.moves.length) {
                this.game.move(this.moves[this.pgnIndex]);
                this.pgnIndex++;
            }
            this.board.position(this.game.fen());
            this.updateStatus();
        }

        prevMove() {
            if (this.pgnIndex > 0) {
                this.stopPlay();
                this.game.undo();
                this.pgnIndex--;
                this.board.position(this.game.fen());
                this.updateStatus();
            }
        }

        nextMove() {
            if (this.pgnIndex < this.moves.length) {
                this.stopPlay();
                this.game.move(this.moves[this.pgnIndex]);
                this.pgnIndex++;
                this.board.position(this.game.fen());
                this.updateStatus();
            }
        }

        goToMove(index) {
            this.stopPlay();
            this.game.reset();
            this.board.position('start');
            this.pgnIndex = 0;
            
            for (let i = 0; i <= index; i++) {
                this.game.move(this.moves[i]);
                this.pgnIndex++;
            }
            
            this.board.position(this.game.fen());
            this.updateStatus();
        }

        togglePlay() {
            if (this.isPlaying) {
                this.stopPlay();
            } else {
                this.startPlay();
            }
        }

        startPlay() {
            if (this.pgnIndex >= this.moves.length) {
                this.goToStart();
            }
            
            this.isPlaying = true;
            this.elements.playBtn.find('.dashicons')
                .removeClass('dashicons-controls-play')
                .addClass('dashicons-controls-pause');
            
            this.playInterval = setInterval(() => {
                if (this.pgnIndex < this.moves.length) {
                    this.nextMove();
                } else {
                    this.stopPlay();
                }
            }, this.playSpeed);
        }

        stopPlay() {
            this.isPlaying = false;
            this.elements.playBtn.find('.dashicons')
                .removeClass('dashicons-controls-pause')
                .addClass('dashicons-controls-play');
            
            if (this.playInterval) {
                clearInterval(this.playInterval);
                this.playInterval = null;
            }
        }

        flipBoard() {
            this.boardOrientation = this.boardOrientation === 'white' ? 'black' : 'white';
            this.board.flip();
        }

        updateStatus() {
            // Aggiorna il visualizzatore PGN
            this.updatePgnViewer();
            
            // Aggiorna lo stato dei bottoni
            this.elements.prevBtn.prop('disabled', this.pgnIndex === 0);
            this.elements.nextBtn.prop('disabled', this.pgnIndex >= this.moves.length);
            this.elements.startBtn.prop('disabled', this.pgnIndex === 0);
            this.elements.endBtn.prop('disabled', this.pgnIndex >= this.moves.length);
            
            // Emetti evento personalizzato per lo stato
            $(document).trigger('scacchitrack:moveChanged', {
                index: this.pgnIndex,
                totalMoves: this.moves.length,
                position: this.game.fen(),
                move: this.pgnIndex > 0 ? this.moves[this.pgnIndex - 1] : null,
                isCheck: this.game.in_check(),
                isCheckmate: this.game.in_checkmate(),
                isStalemate: this.game.in_stalemate(),
                isDraw: this.game.in_draw()
            });
        }
    }

    // Inizializzazione quando il documento è pronto
    $(document).ready(() => {
        if ($('#scacchiera').length) {
            window.scacchitrack = new ScacchiTrack();
        }
    });

})(jQuery);
```

## includes {#includes}

### functions.php {#includes-functions-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recupera i nomi unici dei tornei dal database
 *
 * @return array Array di nomi dei tornei
 */
function get_unique_tournament_names() {
    global $wpdb;
    
    $results = $wpdb->get_col(
        "SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_nome_torneo' 
        AND meta_value != '' 
        ORDER BY meta_value ASC"
    );
    
    return array_filter($results);
}

/**
 * Recupera le statistiche generali
 *
 * @return array Array di statistiche
 */
function get_scacchitrack_statistics() {
    global $wpdb;
    
    $stats = array();
    
    // Totale tornei
    $stats['total_tournaments'] = count(get_unique_tournament_names());
    
    // Giocatori unici
    $white_players = $wpdb->get_col(
        "SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_giocatore_bianco' 
        AND meta_value != ''"
    );
    
    $black_players = $wpdb->get_col(
        "SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_giocatore_nero' 
        AND meta_value != ''"
    );
    
    $unique_players = array_unique(array_merge($white_players, $black_players));
    $stats['unique_players'] = count($unique_players);
    
    // Risultati
    $results = $wpdb->get_results(
        "SELECT meta_value as risultato, COUNT(*) as count 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_risultato' 
        GROUP BY meta_value"
    );
    
    $total_games = 0;
    $white_wins = 0;
    $black_wins = 0;
    $draws = 0;
    
    foreach ($results as $result) {
        $total_games += $result->count;
        switch ($result->risultato) {
            case '1-0':
                $white_wins = $result->count;
                break;
            case '0-1':
                $black_wins = $result->count;
                break;
            case '½-½':
                $draws = $result->count;
                break;
        }
    }
    
    $stats['white_win_percentage'] = $total_games ? ($white_wins / $total_games) * 100 : 0;
    $stats['black_win_percentage'] = $total_games ? ($black_wins / $total_games) * 100 : 0;
    $stats['draw_percentage'] = $total_games ? ($draws / $total_games) * 100 : 0;
    
    // Timeline data
    $timeline = $wpdb->get_results(
        "SELECT 
            DATE_FORMAT(meta_value, '%Y-%m') as month,
            COUNT(*) as count
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_data_partita'
        GROUP BY month
        ORDER BY month ASC
        LIMIT 12"
    );
    
    $stats['timeline_labels'] = array_column($timeline, 'month');
    $stats['timeline_data'] = array_column($timeline, 'count');
    
    // Top players
    $stats['top_players'] = get_top_players();
    
    // Tournament stats
    $stats['tournament_stats'] = get_tournament_statistics();
    
    // Opening stats
    $stats['openings'] = get_opening_statistics();
    
    return $stats;
}

/**
 * Recupera le statistiche dei migliori giocatori
 *
 * @return array Array di statistiche dei giocatori
 */
function get_top_players() {
    global $wpdb;
    
    $players = array();
    
    // Query per le partite con il bianco
    $white_games = $wpdb->get_results(
        "SELECT 
            pm1.meta_value as player,
            pm2.meta_value as result
        FROM {$wpdb->postmeta} pm1
        JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
        WHERE pm1.meta_key = '_giocatore_bianco'
        AND pm2.meta_key = '_risultato'"
    );
    
    // Query per le partite con il nero
    $black_games = $wpdb->get_results(
        "SELECT 
            pm1.meta_value as player,
            pm2.meta_value as result
        FROM {$wpdb->postmeta} pm1
        JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
        WHERE pm1.meta_key = '_giocatore_nero'
        AND pm2.meta_key = '_risultato'"
    );
    
    // Elabora i risultati
    foreach ($white_games as $game) {
        if (!isset($players[$game->player])) {
            $players[$game->player] = array(
                'name' => $game->player,
                'total_games' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0
            );
        }
        
        $players[$game->player]['total_games']++;
        switch ($game->result) {
            case '1-0':
                $players[$game->player]['wins']++;
                break;
            case '0-1':
                $players[$game->player]['losses']++;
                break;
            case '½-½':
                $players[$game->player]['draws']++;
                break;
        }
    }
    
    foreach ($black_games as $game) {
        if (!isset($players[$game->player])) {
            $players[$game->player] = array(
                'name' => $game->player,
                'total_games' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0
            );
        }
        
        $players[$game->player]['total_games']++;
        switch ($game->result) {
            case '1-0':
                $players[$game->player]['losses']++;
                break;
            case '0-1':
                $players[$game->player]['wins']++;
                break;
            case '½-½':
                $players[$game->player]['draws']++;
                break;
        }
    }
    
    // Calcola le percentuali di vittoria
    foreach ($players as &$player) {
        $player['win_percentage'] = $player['total_games'] ? 
            ($player['wins'] / $player['total_games']) * 100 : 0;
    }
    
    // Ordina per percentuale di vittoria
    uasort($players, function($a, $b) {
        return $b['win_percentage'] <=> $a['win_percentage'];
    });
    
    return array_slice($players, 0, 10);
}

/**
 * Recupera le statistiche dei tornei
 *
 * @return array Array di statistiche dei tornei
 */
function get_tournament_statistics() {
    global $wpdb;
    
    $tournaments = get_unique_tournament_names();
    $stats = array();
    
    foreach ($tournaments as $tournament) {
        $tournament_games = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                pm1.meta_value as result,
                pm2.meta_value as white_player,
                pm3.meta_value as black_player
            FROM {$wpdb->postmeta} pm1
            JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
            JOIN {$wpdb->postmeta} pm3 ON pm1.post_id = pm3.post_id
            JOIN {$wpdb->postmeta} pm4 ON pm1.post_id = pm4.post_id
            WHERE pm1.meta_key = '_risultato'
            AND pm2.meta_key = '_giocatore_bianco'
            AND pm3.meta_key = '_giocatore_nero'
            AND pm4.meta_key = '_nome_torneo'
            AND pm4.meta_value = %s",
            $tournament
        ));
        
        if (!empty($tournament_games)) {
            $tournament_stats = array(
                'name' => $tournament,
                'total_games' => count($tournament_games),
                'white_wins' => 0,
                'black_wins' => 0,
                'draws' => 0,
                'players' => array()
            );
            
            foreach ($tournament_games as $game) {
                $tournament_stats['players'][] = $game->white_player;
                $tournament_stats['players'][] = $game->black_player;
                
                switch ($game->result) {
                    case '1-0':
                        $tournament_stats['white_wins']++;
                        break;
                    case '0-1':
                        $tournament_stats['black_wins']++;
                        break;
                    case '½-½':
                        $tournament_stats['draws']++;
                        break;
                }
            }
            
            $tournament_stats['players'] = count(array_unique($tournament_stats['players']));
            $total = $tournament_stats['total_games'];
            
            $tournament_stats['white_wins_percentage'] = ($tournament_stats['white_wins'] / $total) * 100;
            $tournament_stats['black_wins_percentage'] = ($tournament_stats['black_wins'] / $total) * 100;
            $tournament_stats['draws_percentage'] = ($tournament_stats['draws'] / $total) * 100;
            
            $stats[] = $tournament_stats;
        }
    }
    
    return $stats;
}

/**
 * Recupera le statistiche delle aperture
 *
 * @return array Array di statistiche delle aperture
 */
function get_opening_statistics() {
    global $wpdb;
    
    // Questa è una versione semplificata che estrae le prime mosse dal PGN
    $games = $wpdb->get_results(
        "SELECT 
            post_id,
            meta_value as pgn
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_pgn'"
    );
    
    $openings = array();
    
    foreach ($games as $game) {
        // Estrai le prime mosse (esempio semplificato)
        preg_match('/1\.\s*([A-Za-z0-9]+)/', $game->pgn, $matches);
        
        if (!empty($matches[1])) {
            $opening = $matches[1];
            
            if (!isset($openings[$opening])) {
                $openings[$opening] = array(
                    'name' => $opening,
                    'count' => 0,
                    'white_wins' => 0,
                    'black_wins' => 0,
                    'draws' => 0
                );
            }
            
            $openings[$opening]['count']++;
            
            // Recupera il risultato
            $result = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value 
                FROM {$wpdb->postmeta} 
                WHERE post_id = %d 
                AND meta_key = '_risultato'",
                $game->post_id
            ));
            
            switch ($result) {
                case '1-0':
                    $openings[$opening]['white_wins']++;
                    break;
                case '0-1':
                    $openings[$opening]['black_wins']++;
                    break;
                case '½-½':
                    $openings[$opening]['draws']++;
                    break;
            }
        }
    }
    
    // Ordina per frequenza
    uasort($openings, function($a, $b) {
        return $b['count'] <=> $a['count'];
    });
    
    return array_slice($openings, 0, 10);
}
```

### shortcodes.php {#includes-shortcodes-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Registrazione dello shortcode principale
function scacchitrack_partite_shortcode($atts) {
    // Normalizzazione degli attributi
    $atts = shortcode_atts(array(
        'per_page' => 10,
        'orderby' => 'data_partita',
        'order' => 'DESC',
        'torneo' => '',
        'giocatore' => ''
    ), $atts, 'scacchitrack_partite');
    
    // Sanitizzazione
    $per_page = absint($atts['per_page']);
    $orderby = sanitize_key($atts['orderby']);
    $order = sanitize_key($atts['order']);
    $torneo = sanitize_text_field($atts['torneo']);
    $giocatore = sanitize_text_field($atts['giocatore']);
    
    // Gestione della paginazione
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    
    // Preparazione della query
    $args = array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'orderby' => 'meta_value',
        'meta_key' => '_' . $orderby,
        'order' => $order,
        'meta_query' => array()
    );
    
    // Filtro per torneo
    if (!empty($torneo)) {
        $args['meta_query'][] = array(
            'key' => '_nome_torneo',
            'value' => $torneo,
            'compare' => '='
        );
    }
    
    // Filtro per giocatore (cerca sia nei bianchi che nei neri)
    if (!empty($giocatore)) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => '_giocatore_bianco',
                'value' => $giocatore,
                'compare' => 'LIKE'
            ),
            array(
                'key' => '_giocatore_nero',
                'value' => $giocatore,
                'compare' => 'LIKE'
            )
        );
    }
    
    // Esecuzione della query
    $query = new WP_Query($args);
    
    // Buffer di output
    ob_start();
    
    // Inclusione del template
    include SCACCHITRACK_DIR . 'templates/list-partite.php';
    
    return ob_get_clean();
}
add_shortcode('scacchitrack_partite', 'scacchitrack_partite_shortcode');

// Shortcode per una singola partita
function scacchitrack_partita_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0
    ), $atts, 'scacchitrack_partita');
    
    $post_id = absint($atts['id']);
    
    if (!$post_id) {
        return '';
    }
    
    $post = get_post($post_id);
    
    if (!$post || $post->post_type !== 'scacchipartita') {
        return '';
    }
    
    ob_start();
    include SCACCHITRACK_DIR . 'templates/single-partita.php';
    return ob_get_clean();
}
add_shortcode('scacchitrack_partita', 'scacchitrack_partita_shortcode');

// AJAX per filtri dinamici
function scacchitrack_ajax_filter() {
    check_ajax_referer('scacchitrack_filter', 'nonce');
    
    $filters = array(
        'torneo' => isset($_POST['torneo']) ? sanitize_text_field($_POST['torneo']) : '',
        'giocatore' => isset($_POST['giocatore']) ? sanitize_text_field($_POST['giocatore']) : '',
        'data_da' => isset($_POST['data_da']) ? sanitize_text_field($_POST['data_da']) : '',
        'data_a' => isset($_POST['data_a']) ? sanitize_text_field($_POST['data_a']) : '',
    );
    
    $args = array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => 10,
        'meta_query' => array()
    );
    
    // Applica i filtri
    if (!empty($filters['torneo'])) {
        $args['meta_query'][] = array(
            'key' => '_nome_torneo',
            'value' => $filters['torneo'],
            'compare' => '='
        );
    }
    
    if (!empty($filters['giocatore'])) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => '_giocatore_bianco',
                'value' => $filters['giocatore'],
                'compare' => 'LIKE'
            ),
            array(
                'key' => '_giocatore_nero',
                'value' => $filters['giocatore'],
                'compare' => 'LIKE'
            )
        );
    }
    
    if (!empty($filters['data_da']) || !empty($filters['data_a'])) {
        $date_query = array('key' => '_data_partita');
        
        if (!empty($filters['data_da'])) {
            $date_query['value'] = $filters['data_da'];
            $date_query['compare'] = '>=';
            $date_query['type'] = 'DATE';
        }
        
        if (!empty($filters['data_a'])) {
            $date_query['value'] = $filters['data_a'];
            $date_query['compare'] = '<=';
            $date_query['type'] = 'DATE';
        }
        
        $args['meta_query'][] = $date_query;
    }
    
    $query = new WP_Query($args);
    
    ob_start();
    include SCACCHITRACK_DIR . 'templates/partite-loop.php';
    $html = ob_get_clean();
    
    wp_send_json_success(array(
        'html' => $html,
        'found' => $query->found_posts
    ));
}
add_action('wp_ajax_scacchitrack_filter', 'scacchitrack_ajax_filter');
add_action('wp_ajax_nopriv_scacchitrack_filter', 'scacchitrack_ajax_filter');
```

### cpt.php {#includes-cpt-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Registrazione Custom Post Type
function scacchitrack_register_cpt() {
    $labels = array(
        'name'               => __('Partite di Scacchi', 'scacchitrack'),
        'singular_name'      => __('Partita di Scacchi', 'scacchitrack'),
        'menu_name'          => __('ScacchiTrack', 'scacchitrack'),
        'add_new'            => __('Aggiungi Nuova', 'scacchitrack'),
        'add_new_item'       => __('Aggiungi Nuova Partita', 'scacchitrack'),
        'edit_item'          => __('Modifica Partita', 'scacchitrack'),
        'new_item'           => __('Nuova Partita', 'scacchitrack'),
        'view_item'          => __('Visualizza Partita', 'scacchitrack'),
        'search_items'       => __('Cerca Partite', 'scacchitrack'),
        'not_found'          => __('Nessuna partita trovata', 'scacchitrack'),
        'not_found_in_trash' => __('Nessuna partita nel cestino', 'scacchitrack')
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'partita-scacchi'),
        'capability_type'     => array('partita', 'partite'),
        'map_meta_cap'        => true,
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 20,
        'supports'            => array('title'),
        'show_in_rest'        => true
    );

    register_post_type('scacchipartita', $args);
}
add_action('init', 'scacchitrack_register_cpt');

// Gestione delle capabilities
function scacchitrack_add_capabilities() {
    $roles = array('administrator', 'editor');
    
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if (!$role) continue;

        // Capabilities per il post type
        $role->add_cap('read_partita');
        $role->add_cap('read_private_partite');
        $role->add_cap('edit_partita');
        $role->add_cap('edit_partite');
        $role->add_cap('edit_others_partite');
        $role->add_cap('edit_published_partite');
        $role->add_cap('publish_partite');
        $role->add_cap('delete_partite');
        $role->add_cap('delete_others_partite');
        $role->add_cap('delete_private_partite');
        $role->add_cap('delete_published_partite');
    }
}

function scacchitrack_remove_capabilities() {
    $roles = array('administrator', 'editor');
    
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if (!$role) continue;

        // Rimuovi capabilities
        $role->remove_cap('read_partita');
        $role->remove_cap('read_private_partite');
        $role->remove_cap('edit_partita');
        $role->remove_cap('edit_partite');
        $role->remove_cap('edit_others_partite');
        $role->remove_cap('edit_published_partite');
        $role->remove_cap('publish_partite');
        $role->remove_cap('delete_partite');
        $role->remove_cap('delete_others_partite');
        $role->remove_cap('delete_private_partite');
        $role->remove_cap('delete_published_partite');
    }
}
```

### admin-columns.php {#includes-admin-columns-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Definizione delle colonne personalizzate
function scacchitrack_set_custom_columns($columns) {
    $new_columns = array();
    
    // Mantieni la checkbox di selezione se presente
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }
    
    // Aggiungi le colonne personalizzate
    $new_columns['title'] = __('Titolo', 'scacchitrack');
    $new_columns['giocatore_bianco'] = __('Giocatore Bianco', 'scacchitrack');
    $new_columns['giocatore_nero'] = __('Giocatore Nero', 'scacchitrack');
    $new_columns['nome_torneo'] = __('Torneo', 'scacchitrack');
    $new_columns['data_partita'] = __('Data', 'scacchitrack');
    $new_columns['risultato'] = __('Risultato', 'scacchitrack');
    
    return $new_columns;
}
add_filter('manage_scacchipartita_posts_columns', 'scacchitrack_set_custom_columns');

// Popolamento delle colonne personalizzate
function scacchitrack_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'giocatore_bianco':
            echo esc_html(get_post_meta($post_id, '_giocatore_bianco', true));
            break;
            
        case 'giocatore_nero':
            echo esc_html(get_post_meta($post_id, '_giocatore_nero', true));
            break;
            
        case 'nome_torneo':
            echo esc_html(get_post_meta($post_id, '_nome_torneo', true));
            break;
            
        case 'data_partita':
            $data = get_post_meta($post_id, '_data_partita', true);
            if ($data) {
                echo esc_html(date_i18n(get_option('date_format'), strtotime($data)));
            }
            break;
            
        case 'risultato':
            $risultato = get_post_meta($post_id, '_risultato', true);
            echo '<span class="risultato-' . sanitize_html_class($risultato) . '">';
            echo esc_html($risultato ?: '-');
            echo '</span>';
            break;
    }
}
add_action('manage_scacchipartita_posts_custom_column', 'scacchitrack_custom_column_content', 10, 2);

// Rendere le colonne ordinabili
function scacchitrack_sortable_columns($columns) {
    $columns['giocatore_bianco'] = 'giocatore_bianco';
    $columns['giocatore_nero'] = 'giocatore_nero';
    $columns['nome_torneo'] = 'nome_torneo';
    $columns['data_partita'] = 'data_partita';
    $columns['risultato'] = 'risultato';
    
    return $columns;
}
add_filter('manage_edit-scacchipartita_sortable_columns', 'scacchitrack_sortable_columns');

// Gestione dell'ordinamento
function scacchitrack_posts_orderby($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'scacchipartita') {
        return;
    }

    $orderby = $query->get('orderby');
    
    switch ($orderby) {
        case 'giocatore_bianco':
        case 'giocatore_nero':
        case 'nome_torneo':
        case 'data_partita':
        case 'risultato':
            $query->set('meta_key', '_' . $orderby);
            $query->set('orderby', 'meta_value');
            break;
    }
}
add_action('pre_get_posts', 'scacchitrack_posts_orderby');

// Aggiunta stili CSS per le colonne
function scacchitrack_admin_columns_css() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'scacchipartita') {
        ?>
        <style>
            .column-giocatore_bianco,
            .column-giocatore_nero,
            .column-nome_torneo,
            .column-data_partita,
            .column-risultato {
                width: 15%;
            }
            
            .risultato-1-0,
            .risultato-0-1,
            .risultato-½-½,
            .risultato-* {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-weight: bold;
            }
            
            .risultato-1-0 { background: #e8f5e9; color: #2e7d32; }
            .risultato-0-1 { background: #fce4ec; color: #c2185b; }
            .risultato-½-½ { background: #e3f2fd; color: #1565c0; }
            .risultato-* { background: #f5f5f5; color: #616161; }
        </style>
        <?php
    }
}
add_action('admin_head', 'scacchitrack_admin_columns_css');
```

### ajax.php {#includes-ajax-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class ScacchiTrack_Ajax_Handler {
    
    public function __construct() {
        add_action('wp_ajax_scacchitrack_filter_games', array($this, 'filter_games'));
        add_action('wp_ajax_nopriv_scacchitrack_filter_games', array($this, 'filter_games'));
    }

    public function filter_games() {
        // Debug log
        error_log('ScacchiTrack - Inizio filter_games');
        error_log('POST data: ' . print_r($_POST, true));

        // Verifica nonce
        check_ajax_referer('scacchitrack_filter', 'nonce');

        // Query base
        $args = array(
            'post_type'      => 'scacchipartita',
            'posts_per_page' => 10,
            'paged'         => isset($_POST['paged']) ? absint($_POST['paged']) : 1,
            'meta_query'    => array('relation' => 'AND')
        );

        // Log query base
        error_log('Query base: ' . print_r($args, true));

        // Filtro torneo
        if (!empty($_POST['torneo'])) {
            $torneo = sanitize_text_field($_POST['torneo']);
            error_log('Filtro torneo: ' . $torneo);
            $args['meta_query'][] = array(
                'key'     => '_nome_torneo',
                'value'   => $torneo,
                'compare' => 'LIKE'
            );
        }

        // Filtro giocatore
        if (!empty($_POST['giocatore'])) {
            $giocatore = sanitize_text_field($_POST['giocatore']);
            error_log('Filtro giocatore: ' . $giocatore);
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_giocatore_bianco',
                    'value'   => $giocatore,
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => '_giocatore_nero',
                    'value'   => $giocatore,
                    'compare' => 'LIKE'
                )
            );
        }

        // Filtri data
        if (!empty($_POST['data_da']) || !empty($_POST['data_a'])) {
            $data_query = array('key' => '_data_partita');
            
            if (!empty($_POST['data_da'])) {
                $data_query['value'] = sanitize_text_field($_POST['data_da']);
                $data_query['compare'] = '>=';
                $data_query['type'] = 'DATE';
            }
            
            if (!empty($_POST['data_a'])) {
                if (empty($_POST['data_da'])) {
                    $data_query['value'] = sanitize_text_field($_POST['data_a']);
                    $data_query['compare'] = '<=';
                } else {
                    $data_query['value'] = array(
                        sanitize_text_field($_POST['data_da']),
                        sanitize_text_field($_POST['data_a'])
                    );
                    $data_query['compare'] = 'BETWEEN';
                }
                $data_query['type'] = 'DATE';
            }
            
            $args['meta_query'][] = $data_query;
        }

        // Log query finale
        error_log('Query finale: ' . print_r($args, true));

        // Esegui query
        $query = new WP_Query($args);
        error_log('Numero post trovati: ' . $query->found_posts);

        // Output buffer
        ob_start();

        if ($query->have_posts()) {
            error_log('Post trovati, inizio loop');
            while ($query->have_posts()) {
                $query->the_post();
                
                // Debug meta
                $post_id = get_the_ID();
                error_log('Post ID: ' . $post_id);
                error_log('Meta _nome_torneo: ' . get_post_meta($post_id, '_nome_torneo', true));
                error_log('Meta _giocatore_bianco: ' . get_post_meta($post_id, '_giocatore_bianco', true));
                error_log('Meta _giocatore_nero: ' . get_post_meta($post_id, '_giocatore_nero', true));
                
                include SCACCHITRACK_DIR . 'templates/partita-item.php';
            }
        } else {
            error_log('Nessun post trovato');
            echo '<tr><td colspan="6">' . __('Nessuna partita trovata.', 'scacchitrack') . '</td></tr>';
        }

        $html = ob_get_clean();
        error_log('HTML generato: ' . $html);

        wp_reset_postdata();

        // Prepara risposta
        $response = array(
            'html'      => $html,
            'found'     => $query->found_posts,
            'max_pages' => $query->max_num_pages
        );

        error_log('Risposta finale: ' . print_r($response, true));
        wp_send_json_success($response);
    }
}

// Inizializza handler
new ScacchiTrack_Ajax_Handler();
```

### scripts.php {#includes-scripts-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class ScacchiTrack_Assets_Manager {
    /**
     * Versione degli asset per cache busting
     */
    private $version;

    /**
     * Costruttore
     */
    public function __construct() {
        $this->version = SCACCHITRACK_VERSION;
        
        // Inizializza i hook
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Registra tutti gli asset
     */
    public function register_assets() {
        // Registra gli stili
        wp_register_style(
            'chessboard-css',
            'https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.css',
            array(),
            '1.0.0'
        );

        wp_register_style(
            'scacchitrack-style',
            SCACCHITRACK_URL . 'css/scacchitrack.css',
            array('chessboard-css', 'dashicons'),
            $this->version
        );

        wp_register_style(
            'scacchitrack-admin',
            SCACCHITRACK_URL . 'css/admin.css',
            array(),
            $this->version
        );

        // Registra gli script
        wp_register_script(
            'chess-js',
            'https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js',
            array(),
            '0.10.3',
            true
        );

        wp_register_script(
            'chessboard-js',
            'https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_register_script(
            'scacchitrack-js',
            SCACCHITRACK_URL . 'js/scacchitrack.js',
            array('jquery', 'chess-js', 'chessboard-js'),
            $this->version,
            true
        );

        wp_register_script(
            'scacchitrack-filters',
            SCACCHITRACK_URL . 'js/filters.js',
            array('jquery'),
            $this->version,
            true
        );

        // Script per l'admin
        wp_register_script(
            'scacchitrack-admin',
            SCACCHITRACK_URL . 'js/admin.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    /**
     * Carica gli asset nel frontend quando necessario
     */
    public function enqueue_frontend_assets() {
        global $post;

        if (is_singular('scacchipartita') || 
            (is_a($post, 'WP_Post') && 
            (has_shortcode($post->post_content, 'scacchitrack_partite') || 
             has_shortcode($post->post_content, 'scacchitrack_partita')))) {
            
            // Stili
            wp_enqueue_style('dashicons');
            wp_enqueue_style('chessboard-css');
            wp_enqueue_style('scacchitrack-style');
            
            // Script
            wp_enqueue_script('chess-js');
            wp_enqueue_script('chessboard-js');
            wp_enqueue_script('scacchitrack-js');
            wp_enqueue_script('scacchitrack-filters');

            // Definisci l'array dei pezzi
            $chess_pieces = array(
                'wP' => 'https://upload.wikimedia.org/wikipedia/commons/4/45/Chess_plt45.svg',
                'wR' => 'https://upload.wikimedia.org/wikipedia/commons/7/72/Chess_rlt45.svg',
                'wN' => 'https://upload.wikimedia.org/wikipedia/commons/7/70/Chess_nlt45.svg',
                'wB' => 'https://upload.wikimedia.org/wikipedia/commons/b/b1/Chess_blt45.svg',
                'wQ' => 'https://upload.wikimedia.org/wikipedia/commons/1/15/Chess_qlt45.svg',
                'wK' => 'https://upload.wikimedia.org/wikipedia/commons/4/42/Chess_klt45.svg',
                'bP' => 'https://upload.wikimedia.org/wikipedia/commons/c/c7/Chess_pdt45.svg',
                'bR' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/Chess_rdt45.svg',
                'bN' => 'https://upload.wikimedia.org/wikipedia/commons/e/ef/Chess_ndt45.svg',
                'bB' => 'https://upload.wikimedia.org/wikipedia/commons/9/98/Chess_bdt45.svg',
                'bQ' => 'https://upload.wikimedia.org/wikipedia/commons/4/47/Chess_qdt45.svg',
                'bK' => 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Chess_kdt45.svg'
            );

            // Recupera PGN se siamo in una singola partita
            $pgn = '';
            if (is_singular('scacchipartita')) {
                $pgn = get_post_meta(get_the_ID(), '_pgn', true);
            }

            // Localizzazione per JavaScript
            wp_localize_script('scacchitrack-js', 'scacchitrackData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'pluginUrl' => SCACCHITRACK_URL,
                'nonce' => wp_create_nonce('scacchitrack_ajax'),
                'filterNonce' => wp_create_nonce('scacchitrack_filter'), // nome del nonce
                'pieces' => $chess_pieces,
                'pgn' => $pgn,
                'config' => array(
                    'showNotation' => true,
                    'draggable' => false,
                    'position' => 'start',
                    'moveSpeed' => 200,
                    'snapbackSpeed' => 500,
                    'snapSpeed' => 100,
                ),
                'i18n' => array(
                    'loading' => __('Caricamento...', 'scacchitrack'),
                    'errorLoading' => __('Errore nel caricamento della partita', 'scacchitrack'),
                    'noResults' => __('Nessuna partita trovata', 'scacchitrack'),
                    'noMoves' => __('Nessuna mossa disponibile', 'scacchitrack'),
                    'showPgn' => __('Mostra PGN', 'scacchitrack'),
                    'hidePgn' => __('Nascondi PGN', 'scacchitrack'),
                    'white' => __('Bianco', 'scacchitrack'),
                    'black' => __('Nero', 'scacchitrack'),
                    'start' => __('Inizio', 'scacchitrack'),
                    'end' => __('Fine', 'scacchitrack'),
                    'play' => __('Riproduci', 'scacchitrack'),
                    'pause' => __('Pausa', 'scacchitrack'),
                    'previous' => __('Precedente', 'scacchitrack'),
                    'next' => __('Successiva', 'scacchitrack'),
                    'flip' => __('Ruota scacchiera', 'scacchitrack'),
                    'speed' => __('Velocità', 'scacchitrack')
                )
            ));
        }
    }

    /**
     * Carica gli asset nell'admin quando necessario
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        if ($post_type === 'scacchipartita' || $hook === 'toplevel_page_scacchitrack') {
            wp_enqueue_style('dashicons');
            wp_enqueue_style('scacchitrack-admin');
            
            if ($hook === 'post.php' || $hook === 'post-new.php') {
                wp_enqueue_style('chessboard-css');
                wp_enqueue_script('chess-js');
                wp_enqueue_script('chessboard-js');
                wp_enqueue_script('scacchitrack-js');
                wp_enqueue_script('scacchitrack-admin');
                
                $pgn = isset($_GET['post']) ? get_post_meta($_GET['post'], '_pgn', true) : '';
                
                // Usa gli stessi pezzi del frontend
                $chess_pieces = array(
                    'wP' => 'https://upload.wikimedia.org/wikipedia/commons/4/45/Chess_plt45.svg',
                    'wR' => 'https://upload.wikimedia.org/wikipedia/commons/7/72/Chess_rlt45.svg',
                    'wN' => 'https://upload.wikimedia.org/wikipedia/commons/7/70/Chess_nlt45.svg',
                    'wB' => 'https://upload.wikimedia.org/wikipedia/commons/b/b1/Chess_blt45.svg',
                    'wQ' => 'https://upload.wikimedia.org/wikipedia/commons/1/15/Chess_qlt45.svg',
                    'wK' => 'https://upload.wikimedia.org/wikipedia/commons/4/42/Chess_klt45.svg',
                    'bP' => 'https://upload.wikimedia.org/wikipedia/commons/c/c7/Chess_pdt45.svg',
                    'bR' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/Chess_rdt45.svg',
                    'bN' => 'https://upload.wikimedia.org/wikipedia/commons/e/ef/Chess_ndt45.svg',
                    'bB' => 'https://upload.wikimedia.org/wikipedia/commons/9/98/Chess_bdt45.svg',
                    'bQ' => 'https://upload.wikimedia.org/wikipedia/commons/4/47/Chess_qdt45.svg',
                    'bK' => 'https://upload.wikimedia.org/wikipedia/commons/f/f0/Chess_kdt45.svg'
                );

                wp_localize_script('scacchitrack-js', 'scacchitrackData', array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'pluginUrl' => SCACCHITRACK_URL,
                    'nonce' => wp_create_nonce('scacchitrack_admin'),
                    'pieces' => $chess_pieces,
                    'pgn' => $pgn,
                    'config' => array(
                        'showNotation' => true,
                        'draggable' => true,
                        'position' => 'start'
                    )
                ));
            }
        }
    }

    /**
     * Verifica se è necessario caricare gli asset
     */
    private function should_load_assets() {
        global $post;

        return (
            is_singular('scacchipartita') ||
            (is_a($post, 'WP_Post') && (
                has_shortcode($post->post_content, 'scacchitrack_partite') ||
                has_shortcode($post->post_content, 'scacchitrack_partita') ||
                has_block('scacchitrack/partita')
            ))
        );
    }

    /**
     * Aggiunge attributi async/defer agli script quando necessario
     */
    public function add_async_defer_attributes($tag, $handle) {
        if ('chess-js' === $handle) {
            return str_replace(' src', ' async src', $tag);
        }
        return $tag;
    }
}

// Inizializza il gestore degli asset
new ScacchiTrack_Assets_Manager();
```

### metaboxes.php {#includes-metaboxes-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Aggiunta dei metabox
function scacchitrack_add_metaboxes() {
    add_meta_box(
        'scacchitrack_game_details',
        __('Dettagli Partita', 'scacchitrack'),
        'scacchitrack_game_details_callback',
        'scacchipartita',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'scacchitrack_add_metaboxes');

// Callback per il metabox dei dettagli partita
function scacchitrack_game_details_callback($post) {
    wp_nonce_field('scacchitrack_save_game_details', 'scacchitrack_game_details_nonce');
    
    // Recupero dei valori salvati
    $giocatore_bianco = get_post_meta($post->ID, '_giocatore_bianco', true);
    $giocatore_nero = get_post_meta($post->ID, '_giocatore_nero', true);
    $data_partita = get_post_meta($post->ID, '_data_partita', true);
    $nome_torneo = get_post_meta($post->ID, '_nome_torneo', true);
    $pgn = get_post_meta($post->ID, '_pgn', true);
    $risultato = get_post_meta($post->ID, '_risultato', true);
    ?>
    
    <div class="scacchitrack-metabox-container">
        <p>
            <label for="giocatore_bianco"><?php _e('Giocatore Bianco:', 'scacchitrack'); ?></label>
            <input type="text" id="giocatore_bianco" name="giocatore_bianco" 
                   value="<?php echo esc_attr($giocatore_bianco); ?>" class="widefat">
        </p>
        
        <p>
            <label for="giocatore_nero"><?php _e('Giocatore Nero:', 'scacchitrack'); ?></label>
            <input type="text" id="giocatore_nero" name="giocatore_nero" 
                   value="<?php echo esc_attr($giocatore_nero); ?>" class="widefat">
        </p>
        
        <p>
            <label for="data_partita"><?php _e('Data Partita:', 'scacchitrack'); ?></label>
            <input type="date" id="data_partita" name="data_partita" 
                   value="<?php echo esc_attr($data_partita); ?>" class="widefat">
        </p>
        
        <p>
            <label for="nome_torneo"><?php _e('Nome Torneo:', 'scacchitrack'); ?></label>
            <input type="text" id="nome_torneo" name="nome_torneo" 
                   value="<?php echo esc_attr($nome_torneo); ?>" class="widefat">
        </p>
        
        <p>
            <label for="risultato"><?php _e('Risultato:', 'scacchitrack'); ?></label>
            <select id="risultato" name="risultato" class="widefat">
                <option value=""><?php _e('Seleziona...', 'scacchitrack'); ?></option>
                <option value="1-0" <?php selected($risultato, '1-0'); ?>><?php _e('1-0 (Vincono i Bianchi)', 'scacchitrack'); ?></option>
                <option value="0-1" <?php selected($risultato, '0-1'); ?>><?php _e('0-1 (Vincono i Neri)', 'scacchitrack'); ?></option>
                <option value="½-½" <?php selected($risultato, '½-½'); ?>><?php _e('½-½ (Patta)', 'scacchitrack'); ?></option>
                <option value="*" <?php selected($risultato, '*'); ?>><?php _e('* (In corso)', 'scacchitrack'); ?></option>
            </select>
        </p>
        
        <p>
            <label for="pgn"><?php _e('PGN della Partita:', 'scacchitrack'); ?></label>
            <textarea id="pgn" name="pgn" class="widefat" rows="10"><?php echo esc_textarea($pgn); ?></textarea>
            <span class="description">
                <?php _e('Inserisci qui la notazione PGN della partita.', 'scacchitrack'); ?>
            </span>
        </p>
    </div>

    <style>
        .scacchitrack-metabox-container label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .scacchitrack-metabox-container p {
            margin-bottom: 20px;
        }
        .scacchitrack-metabox-container .description {
            font-style: italic;
            color: #666;
        }
    </style>
    <?php
}

// Salvataggio dei dati del metabox
function scacchitrack_save_game_details($post_id) {
    // Verifica del nonce
    if (!isset($_POST['scacchitrack_game_details_nonce']) || 
        !wp_verify_nonce($_POST['scacchitrack_game_details_nonce'], 'scacchitrack_save_game_details')) {
        return;
    }

    // Verifica autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verifica permessi
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Array dei campi da salvare
    $fields = array(
        'giocatore_bianco' => 'sanitize_text_field',
        'giocatore_nero' => 'sanitize_text_field',
        'data_partita' => 'sanitize_text_field',
        'nome_torneo' => 'sanitize_text_field',
        'risultato' => 'sanitize_text_field',
        'pgn' => 'wp_kses_post'
    );

    // Salvataggio dei campi
    foreach ($fields as $field => $sanitize_callback) {
        if (isset($_POST[$field])) {
            $value = call_user_func($sanitize_callback, $_POST[$field]);
            update_post_meta($post_id, '_' . $field, $value);
        }
    }
}
add_action('save_post_scacchipartita', 'scacchitrack_save_game_details');

// Aggiunta stili CSS per l'admin
function scacchitrack_admin_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'scacchipartita') {
        wp_enqueue_style('scacchitrack-admin', SCACCHITRACK_URL . 'css/admin.css', array(), SCACCHITRACK_VERSION);
    }
}
add_action('admin_enqueue_scripts', 'scacchitrack_admin_styles');
```

### frontend.php {#includes-frontend-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestisce la visualizzazione frontend delle partite
 */
class ScacchiTrack_Frontend_Display {
    
    private $is_processing = false;
    
    public function __construct() {
        add_filter('template_include', array($this, 'load_partita_template'));
        add_filter('the_content', array($this, 'filter_partita_content'));
    }

    /**
     * Carica il template personalizzato per le partite
     */
    public function load_partita_template($template) {
        if (is_singular('scacchipartita')) {
            $custom_template = SCACCHITRACK_DIR . 'templates/single-scacchipartita.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    /**
     * Filtra il contenuto per aggiungere i dettagli della partita
     */
    public function filter_partita_content($content) {
        // Previene la ricorsione infinita
        if ($this->is_processing) {
            return $content;
        }

        if (is_singular('scacchipartita') && in_the_loop() && is_main_query()) {
            $this->is_processing = true;
            
            $template_path = SCACCHITRACK_DIR . 'templates/content-partita.php';
            
            if (file_exists($template_path)) {
                ob_start();
                include $template_path;
                $new_content = ob_get_clean();
                $this->is_processing = false;
                return $new_content;
            }
            
            $this->is_processing = false;
        }
        
        return $content;
    }
}

// Inizializza la classe
global $scacchitrack_frontend;
$scacchitrack_frontend = new ScacchiTrack_Frontend_Display();
```

## templates {#templates}

### list-partite.php {#templates-list-partite-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="scacchitrack-container">
    <!-- Filtri -->
    <div class="scacchitrack-filters">
        <form id="scacchitrack-filter-form" class="scacchitrack-filter-form">
            <div class="filter-row">
                <div class="filter-column">
                    <label for="torneo"><?php _e('Torneo:', 'scacchitrack'); ?></label>
                    <select name="torneo" id="torneo">
                        <option value=""><?php _e('Tutti i tornei', 'scacchitrack'); ?></option>
                        <?php
                        $tornei = get_unique_tournament_names();
                        foreach ($tornei as $torneo) {
                            echo '<option value="' . esc_attr($torneo) . '">' . esc_html($torneo) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filter-column">
                    <label for="giocatore"><?php _e('Giocatore:', 'scacchitrack'); ?></label>
                    <input type="text" 
                           name="giocatore" 
                           id="giocatore" 
                           placeholder="<?php esc_attr_e('Nome giocatore', 'scacchitrack'); ?>">
                </div>
                
                <div class="filter-column">
                    <label for="data_da"><?php _e('Data da:', 'scacchitrack'); ?></label>
                    <input type="date" name="data_da" id="data_da">
                </div>
                
                <div class="filter-column">
                    <label for="data_a"><?php _e('Data a:', 'scacchitrack'); ?></label>
                    <input type="date" name="data_a" id="data_a">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="button">
                    <?php _e('Filtra', 'scacchitrack'); ?>
                </button>
                <button type="reset" class="button">
                    <?php _e('Reimposta', 'scacchitrack'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Risultati -->
    <div id="scacchitrack-results">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Data', 'scacchitrack'); ?></th>
                    <th><?php _e('Bianco', 'scacchitrack'); ?></th>
                    <th><?php _e('Nero', 'scacchitrack'); ?></th>
                    <th><?php _e('Risultato', 'scacchitrack'); ?></th>
                    <th><?php _e('Torneo', 'scacchitrack'); ?></th>
                    <th><?php _e('Azioni', 'scacchitrack'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php include SCACCHITRACK_DIR . 'templates/partite-loop.php'; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginazione -->
    <div class="scacchitrack-pagination">
        <?php
        echo paginate_links(array(
            'total' => $query->max_num_pages,
            'current' => max(1, get_query_var('paged')),
            'format' => '?paged=%#%',
            'show_all' => false,
            'type' => 'plain',
            'end_size' => 2,
            'mid_size' => 1,
            'prev_next' => true,
            'prev_text' => __('« Precedente', 'scacchitrack'),
            'next_text' => __('Successiva »', 'scacchitrack'),
        ));
        ?>
    </div>
</div>

<style>
.scacchitrack-container {
    margin: 20px 0;
}

.scacchitrack-filters {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-column label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.filter-column input,
.filter-column select {
    width: 100%;
}

.filter-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}

#scacchitrack-results.loading {
    opacity: 0.5;
    pointer-events: none;
}

.scacchitrack-pagination {
    margin-top: 20px;
    text-align: center;
}

.scacchitrack-pagination .page-numbers {
    padding: 5px 10px;
    margin: 0 5px;
    border: 1px solid #ddd;
    text-decoration: none;
    border-radius: 3px;
}

.scacchitrack-pagination .page-numbers.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}
</style>
```

### admin-page.php {#templates-admin-page-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Gestione delle tab
$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
?>

<div class="wrap scacchitrack-admin">
    <h1 class="wp-heading-inline">
        <?php _e('ScacchiTrack', 'scacchitrack'); ?>
    </h1>
    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=scacchipartita')); ?>" class="page-title-action">
        <?php _e('Aggiungi Nuova Partita', 'scacchitrack'); ?>
    </a>
    <hr class="wp-header-end">

    <nav class="nav-tab-wrapper wp-clearfix">
        <a href="?page=scacchitrack&tab=dashboard" 
           class="nav-tab <?php echo $current_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Dashboard', 'scacchitrack'); ?>
        </a>
        <a href="?page=scacchitrack&tab=stats" 
           class="nav-tab <?php echo $current_tab === 'stats' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Statistiche', 'scacchitrack'); ?>
        </a>
        <a href="?page=scacchitrack&tab=import" 
           class="nav-tab <?php echo $current_tab === 'import' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Importa/Esporta', 'scacchitrack'); ?>
        </a>
        <a href="?page=scacchitrack&tab=settings" 
           class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Impostazioni', 'scacchitrack'); ?>
        </a>
    </nav>

    <div class="tab-content">
        <?php
        switch ($current_tab) {
            case 'dashboard':
                include SCACCHITRACK_DIR . 'templates/admin/dashboard.php';
                break;
            case 'stats':
                include SCACCHITRACK_DIR . 'templates/admin/stats.php';
                break;
            case 'import':
                include SCACCHITRACK_DIR . 'templates/admin/import.php';
                break;
            case 'settings':
                include SCACCHITRACK_DIR . 'templates/admin/settings.php';
                break;
        }
        ?>
    </div>
</div>

<style>
.scacchitrack-admin .nav-tab-wrapper {
    margin-bottom: 20px;
}

.scacchitrack-admin .tab-content {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.scacchitrack-admin .dashboard-widgets {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.scacchitrack-admin .dashboard-widget {
    background: #fff;
    padding: 15px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.scacchitrack-admin .dashboard-widget h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
</style>
```

### partita-item.php {#templates-partita-item-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
$data_partita = get_post_meta($post_id, '_data_partita', true);
$giocatore_bianco = get_post_meta($post_id, '_giocatore_bianco', true);
$giocatore_nero = get_post_meta($post_id, '_giocatore_nero', true);
$risultato = get_post_meta($post_id, '_risultato', true);
$nome_torneo = get_post_meta($post_id, '_nome_torneo', true);

error_log("Rendering partita ID: $post_id");
error_log("Meta values: " . print_r(get_post_meta($post_id), true));
?>
<tr>
    <td><?php echo $data_partita ? esc_html(date_i18n(get_option('date_format'), strtotime($data_partita))) : ''; ?></td>
    <td><?php echo esc_html($giocatore_bianco); ?></td>
    <td><?php echo esc_html($giocatore_nero); ?></td>
    <td><?php echo esc_html($risultato); ?></td>
    <td><?php echo esc_html($nome_torneo); ?></td>
    <td>
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="button">
            <?php _e('Visualizza', 'scacchitrack'); ?>
        </a>
    </td>
</tr>
<?php
error_log("Rendered partita ID: $post_id");
?>
```

### content-partita.php {#templates-content-partita-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
$giocatore_bianco = get_post_meta($post_id, '_giocatore_bianco', true);
$giocatore_nero = get_post_meta($post_id, '_giocatore_nero', true);
$data_partita = get_post_meta($post_id, '_data_partita', true);
$nome_torneo = get_post_meta($post_id, '_nome_torneo', true);
$pgn = get_post_meta($post_id, '_pgn', true);
$risultato = get_post_meta($post_id, '_risultato', true);
?>

<div class="scacchitrack-single-partita">
    <!-- Intestazione della partita -->
    <header class="partita-header">
        <h1 class="partita-title">
            <?php the_title(); ?>
        </h1>
        
        <div class="partita-meta">
            <div class="giocatori">
                <div class="giocatore bianco">
                    <span class="label"><?php _e('Bianco:', 'scacchitrack'); ?></span>
                    <span class="nome"><?php echo esc_html($giocatore_bianco); ?></span>
                </div>
                <div class="risultato">
                    <span class="punteggio"><?php echo esc_html($risultato); ?></span>
                </div>
                <div class="giocatore nero">
                    <span class="label"><?php _e('Nero:', 'scacchitrack'); ?></span>
                    <span class="nome"><?php echo esc_html($giocatore_nero); ?></span>
                </div>
            </div>
            
            <div class="dettagli">
                <?php if ($data_partita) : ?>
                <div class="data">
                    <span class="label"><?php _e('Data:', 'scacchitrack'); ?></span>
                    <span class="valore"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data_partita))); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($nome_torneo) : ?>
                <div class="torneo">
                    <span class="label"><?php _e('Torneo:', 'scacchitrack'); ?></span>
                    <span class="valore"><?php echo esc_html($nome_torneo); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Area principale con scacchiera e controlli -->
    <div class="partita-container">
        <div class="scacchiera-wrapper">
            <!-- Scacchiera -->
            <div id="scacchiera" class="scacchiera"></div>
            
            <!-- Controlli della scacchiera -->
            <div class="scacchiera-controlli">
                <button class="button control-button" id="startBtn" title="<?php esc_attr_e('Vai all\'inizio', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-skipback"></span>
                </button>
                <button class="button control-button" id="prevBtn" title="<?php esc_attr_e('Mossa precedente', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-back"></span>
                </button>
                <button class="button control-button" id="playBtn" title="<?php esc_attr_e('Riproduci/Pausa', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-play"></span>
                </button>
                <button class="button control-button" id="nextBtn" title="<?php esc_attr_e('Mossa successiva', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-forward"></span>
                </button>
                <button class="button control-button" id="endBtn" title="<?php esc_attr_e('Vai alla fine', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-skipforward"></span>
                </button>
                
                <div class="velocita-controllo">
                    <label for="velocitaRange"><?php _e('Velocità:', 'scacchitrack'); ?></label>
                    <input type="range" id="velocitaRange" min="1" max="5" value="3">
                </div>
                
                <button class="button" id="flipBtn" title="<?php esc_attr_e('Ruota scacchiera', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-image-rotate"></span>
                </button>
            </div>
        </div>

        <!-- Area notazione -->
        <div class="notazione-container">
            <h3><?php _e('Mosse', 'scacchitrack'); ?></h3>
            <div id="pgn-viewer" class="pgn-viewer"></div>
            
            <div class="pgn-raw-container">
                <button class="button toggle-pgn" id="togglePgn">
                    <?php _e('Mostra PGN', 'scacchitrack'); ?>
                </button>
                <div class="pgn-raw" style="display: none;">
                    <pre><?php echo esc_html($pgn); ?></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenuto addizionale della partita -->
    <div class="partita-content">
        <?php the_content(); ?>
    </div>

    <?php if (comments_open() || get_comments_number()) : ?>
    <div class="partita-commenti">
        <?php comments_template(); ?>
    </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    // Dati della partita per JavaScript
    var scacchitrackPartita = {
        pgn: <?php echo json_encode($pgn); ?>,
        giocatoreBianco: <?php echo json_encode($giocatore_bianco); ?>,
        giocatoreNero: <?php echo json_encode($giocatore_nero); ?>,
        risultato: <?php echo json_encode($risultato); ?>
    };
</script>

<style>
.scacchitrack-single-partita {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.partita-header {
    margin-bottom: 30px;
    text-align: center;
}

.partita-meta {
    margin: 20px 0;
}

.giocatori {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.giocatore {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.giocatore .nome {
    font-size: 1.2em;
    font-weight: bold;
}

.risultato {
    font-size: 1.5em;
    font-weight: bold;
    padding: 0 20px;
}

.dettagli {
    display: flex;
    justify-content: center;
    gap: 20px;
    color: #666;
}

.partita-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .partita-container {
        grid-template-columns: 1fr;
    }
}

.scacchiera-wrapper {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.scacchiera {
    width: 100%;
    margin: 0 auto;
}

.scacchiera-controlli {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.control-button {
    padding: 8px 12px;
    border: none;
    background: #f0f0f0;
    border-radius: 4px;
    cursor: pointer;
}

.control-button:hover {
    background: #e0e0e0;
}

.velocita-controllo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 15px;
}

.notazione-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pgn-viewer {
    height: 400px;
    overflow-y: auto;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pgn-raw {
    margin-top: 10px;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 4px;
}

.pgn-raw pre {
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
```

### partite-loop.php {#templates-partite-loop-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php if ($query->have_posts()) : ?>
    <table class="scacchitrack-table">
        <thead>
            <tr>
                <th><?php _e('Data', 'scacchitrack'); ?></th>
                <th><?php _e('Bianco', 'scacchitrack'); ?></th>
                <th><?php _e('Nero', 'scacchitrack'); ?></th>
                <th><?php _e('Risultato', 'scacchitrack'); ?></th>
                <th><?php _e('Torneo', 'scacchitrack'); ?></th>
                <th><?php _e('Azioni', 'scacchitrack'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php
            $data = get_post_meta(get_the_ID(), '_data_partita', true);
            $bianco = get_post_meta(get_the_ID(), '_giocatore_bianco', true);
            $nero = get_post_meta(get_the_ID(), '_giocatore_nero', true);
            $risultato = get_post_meta(get_the_ID(), '_risultato', true);
            $torneo = get_post_meta(get_the_ID(), '_nome_torneo', true);
            ?>
            <tr>
                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data))); ?></td>
                <td><?php echo esc_html($bianco); ?></td>
                <td><?php echo esc_html($nero); ?></td>
                <td class="risultato-<?php echo sanitize_html_class($risultato); ?>">
                    <?php echo esc_html($risultato); ?>
                </td>
                <td><?php echo esc_html($torneo); ?></td>
                <td>
                    <a href="<?php the_permalink(); ?>" class="button button-small">
                        <?php _e('Visualizza', 'scacchitrack'); ?>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else : ?>
    <p class="scacchitrack-no-results">
        <?php _e('Nessuna partita trovata.', 'scacchitrack'); ?>
    </p>
<?php endif; ?>
<?php wp_reset_postdata(); ?>
```

### single-partita.php {#templates-single-partita-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupero dei metadati della partita
$post_id = get_the_ID();
$giocatore_bianco = get_post_meta($post_id, '_giocatore_bianco', true);
$giocatore_nero = get_post_meta($post_id, '_giocatore_nero', true);
$data_partita = get_post_meta($post_id, '_data_partita', true);
$nome_torneo = get_post_meta($post_id, '_nome_torneo', true);
$pgn = get_post_meta($post_id, '_pgn', true);
$risultato = get_post_meta($post_id, '_risultato', true);
?>

<div class="scacchitrack-single-partita">
    <!-- Intestazione della partita -->
    <header class="partita-header">
        <h2 class="partita-title">
            <?php echo esc_html($giocatore_bianco); ?> 
            <span class="vs">vs</span> 
            <?php echo esc_html($giocatore_nero); ?>
        </h2>
        
        <div class="partita-meta">
            <?php if ($data_partita) : ?>
            <div class="meta-item">
                <span class="meta-label"><?php _e('Data:', 'scacchitrack'); ?></span>
                <span class="meta-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data_partita))); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($nome_torneo) : ?>
            <div class="meta-item">
                <span class="meta-label"><?php _e('Torneo:', 'scacchitrack'); ?></span>
                <span class="meta-value"><?php echo esc_html($nome_torneo); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($risultato) : ?>
            <div class="meta-item">
                <span class="meta-label"><?php _e('Risultato:', 'scacchitrack'); ?></span>
                <span class="meta-value risultato-<?php echo sanitize_html_class($risultato); ?>">
                    <?php echo esc_html($risultato); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Contenitore principale della scacchiera e dei controlli -->
    <div class="partita-container">
        <!-- Pannello di sinistra con la scacchiera -->
        <div class="scacchiera-container">
            <div id="scacchiera" class="scacchiera"></div>
            
            <!-- Controlli della scacchiera -->
            <div class="scacchiera-controlli">
                <button class="button control-button" id="startBtn" title="<?php esc_attr_e('Vai all\'inizio', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-skipback"></span>
                </button>
                <button class="button control-button" id="prevBtn" title="<?php esc_attr_e('Mossa precedente', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-back"></span>
                </button>
                <button class="button control-button" id="playBtn" title="<?php esc_attr_e('Riproduci/Pausa', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-play"></span>
                </button>
                <button class="button control-button" id="nextBtn" title="<?php esc_attr_e('Mossa successiva', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-forward"></span>
                </button>
                <button class="button control-button" id="endBtn" title="<?php esc_attr_e('Vai alla fine', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-skipforward"></span>
                </button>
                
                <div class="velocita-controllo">
                    <label for="velocitaRange"><?php _e('Velocità:', 'scacchitrack'); ?></label>
                    <input type="range" id="velocitaRange" min="1" max="5" value="3">
                </div>
                
                <button class="button" id="flipBtn" title="<?php esc_attr_e('Ruota scacchiera', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-image-rotate"></span>
                </button>
            </div>
        </div>

        <!-- Pannello di destra con la notazione -->
        <div class="notazione-container">
            <h3><?php _e('Mosse della Partita', 'scacchitrack'); ?></h3>
            <div id="pgn-viewer" class="pgn-viewer"></div>
            
            <!-- Visualizzazione PGN grezzo -->
            <div class="pgn-raw-container">
                <button class="button toggle-pgn" id="togglePgn">
                    <?php _e('Mostra PGN', 'scacchitrack'); ?>
                </button>
                <div class="pgn-raw" style="display: none;">
                    <pre><?php echo esc_html($pgn); ?></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione per i commenti se abilitati -->
    <?php if (comments_open() || get_comments_number()) : ?>
    <div class="partita-commenti">
        <?php comments_template(); ?>
    </div>
    <?php endif; ?>
    
    <!-- Script necessari per la partita -->
    <script type="text/javascript">
        // Dati della partita per JavaScript
        var scacchitrackPartita = {
            pgn: <?php echo json_encode($pgn); ?>,
            giocatoreBianco: <?php echo json_encode($giocatore_bianco); ?>,
            giocatoreNero: <?php echo json_encode($giocatore_nero); ?>,
            risultato: <?php echo json_encode($risultato); ?>,
            postId: <?php echo json_encode($post_id); ?>
        };
    </script>
</div>

<!-- Template per la visualizzazione dei commenti -->
<div id="commento-template" style="display: none;">
    <div class="commento-mossa">
        <div class="commento-header">
            <span class="commento-autore"></span>
            <span class="commento-data"></span>
        </div>
        <div class="commento-testo"></div>
        <div class="commento-azioni">
            <button class="button button-small risposta-btn">
                <?php _e('Rispondi', 'scacchitrack'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Stili inline per garantire una visualizzazione base anche senza CSS esterno */
.scacchitrack-single-partita {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.partita-header {
    margin-bottom: 30px;
    text-align: center;
}

.partita-title {
    font-size: 24px;
    margin-bottom: 15px;
}

.partita-title .vs {
    color: #666;
    margin: 0 10px;
}

.partita-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.meta-label {
    font-weight: bold;
    color: #666;
}

.partita-container {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .partita-container {
        grid-template-columns: 1fr;
    }
}

.scacchiera-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.scacchiera {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.scacchiera-controlli {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.control-button {
    padding: 8px 12px;
}

.velocita-controllo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notazione-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pgn-viewer {
    height: 400px;
    overflow-y: auto;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pgn-raw {
    margin-top: 10px;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 4px;
}

.pgn-raw pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
}

.risultato-1-0 { color: #2e7d32; }
.risultato-0-1 { color: #c2185b; }
.risultato-½-½ { color: #1565c0; }
.risultato-* { color: #616161; }

/* Stili per i commenti */
.commento-mossa {
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.commento-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 0.9em;
    color: #666;
}

.commento-azioni {
    margin-top: 10px;
}
</style>
```

## templates/admin {#templates-admin}

### dashboard.php {#templates-admin-dashboard-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupera le statistiche
$stats = array(
    'total_games' => wp_count_posts('scacchipartita')->publish,
    'recent_games' => get_posts(array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC'
    )),
    'tournaments' => get_unique_tournament_names()
);
?>

<div class="dashboard-widgets">
    <!-- Widget Statistiche Rapide -->
    <div class="dashboard-widget">
        <h3><?php _e('Statistiche Rapide', 'scacchitrack'); ?></h3>
        <ul>
            <li>
                <?php printf(
                    __('Totale Partite: %d', 'scacchitrack'),
                    $stats['total_games']
                ); ?>
            </li>
            <li>
                <?php printf(
                    __('Tornei: %d', 'scacchitrack'),
                    count($stats['tournaments'])
                ); ?>
            </li>
        </ul>
    </div>

    <!-- Widget Ultime Partite -->
    <div class="dashboard-widget">
        <h3><?php _e('Ultime Partite', 'scacchitrack'); ?></h3>
        <?php if (!empty($stats['recent_games'])) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Data', 'scacchitrack'); ?></th>
                        <th><?php _e('Bianco', 'scacchitrack'); ?></th>
                        <th><?php _e('Nero', 'scacchitrack'); ?></th>
                        <th><?php _e('Risultato', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_games'] as $game) : ?>
                        <tr>
                            <td>
                                <?php echo get_the_date('', $game->ID); ?>
                            </td>
                            <td>
                                <?php echo esc_html(get_post_meta($game->ID, '_giocatore_bianco', true)); ?>
                            </td>
                            <td>
                                <?php echo esc_html(get_post_meta($game->ID, '_giocatore_nero', true)); ?>
                            </td>
                            <td>
                                <?php echo esc_html(get_post_meta($game->ID, '_risultato', true)); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e('Nessuna partita trovata.', 'scacchitrack'); ?></p>
        <?php endif; ?>
    </div>

    <!-- Widget Scorciatoie -->
    <div class="dashboard-widget">
        <h3><?php _e('Scorciatoie', 'scacchitrack'); ?></h3>
        <p>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=scacchipartita')); ?>" class="button button-primary">
                <?php _e('Aggiungi Nuova Partita', 'scacchitrack'); ?>
            </a>
        </p>
        <p>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=scacchipartita')); ?>" class="button">
                <?php _e('Gestisci Partite', 'scacchitrack'); ?>
            </a>
        </p>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=scacchitrack&tab=import')); ?>" class="button">
                <?php _e('Importa Partite', 'scacchitrack'); ?>
            </a>
        </p>
    </div>

    <!-- Widget Guida Rapida -->
    <div class="dashboard-widget">
        <h3><?php _e('Guida Rapida', 'scacchitrack'); ?></h3>
        <p><?php _e('Per iniziare:', 'scacchitrack'); ?></p>
        <ol>
            <li><?php _e('Aggiungi una nuova partita dal menu "Aggiungi Nuova Partita"', 'scacchitrack'); ?></li>
            <li><?php _e('Inserisci i dettagli della partita e il PGN', 'scacchitrack'); ?></li>
            <li><?php _e('Usa lo shortcode [scacchitrack_partite] per visualizzare la lista delle partite', 'scacchitrack'); ?></li>
            <li><?php _e('Usa lo shortcode [scacchitrack_partita id="X"] per visualizzare una singola partita', 'scacchitrack'); ?></li>
        </ol>
    </div>
</div>
```

### import.php {#templates-admin-import-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Gestione dell'importazione
class ScacchiTrack_Import_Handler {
    
    public function handle_pgn_import($file_path) {
        if (!file_exists($file_path)) {
            return new WP_Error('file_missing', __('File PGN non trovato', 'scacchitrack'));
        }

        // Leggi il contenuto del file
        $content = file_get_contents($file_path);
        
        // Verifica che sia codificato correttamente
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content));
        }

        // Divide le partite multiple
        $games = $this->split_pgn_games($content);
        
        $imported = 0;
        $errors = array();

        foreach ($games as $game) {
            $result = $this->import_single_game($game);
            if (is_wp_error($result)) {
                $errors[] = $result->get_error_message();
            } else {
                $imported++;
            }
        }

        return array(
            'imported' => $imported,
            'total' => count($games),
            'errors' => $errors
        );
    }

    private function split_pgn_games($content) {
        $games = array();
        $current_game = '';
        
        // Divide le righe del file
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Una nuova partita inizia con i tag del PGN
            if (preg_match('/^\[Event/', $line) && !empty($current_game)) {
                $games[] = trim($current_game);
                $current_game = '';
            }
            
            $current_game .= $line . "\n";
        }
        
        // Aggiungi l'ultima partita
        if (!empty($current_game)) {
            $games[] = trim($current_game);
        }
        
        return $games;
    }

    private function import_single_game($pgn) {
        // Estrai i tag PGN
        $tags = $this->extract_pgn_tags($pgn);
        
        // Verifica se la partita esiste già
        if ($this->game_exists($tags)) {
            return new WP_Error('duplicate', __('Partita già esistente', 'scacchitrack'));
        }

        // Crea il post della partita
        $post_data = array(
            'post_title'   => $this->generate_game_title($tags),
            'post_type'    => 'scacchipartita',
            'post_status'  => 'publish',
            'post_content' => '',
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Salva i metadati
        update_post_meta($post_id, '_giocatore_bianco', $tags['White'] ?? '');
        update_post_meta($post_id, '_giocatore_nero', $tags['Black'] ?? '');
        update_post_meta($post_id, '_data_partita', $this->format_pgn_date($tags['Date'] ?? ''));
        update_post_meta($post_id, '_nome_torneo', $tags['Event'] ?? '');
        update_post_meta($post_id, '_risultato', $tags['Result'] ?? '');
        update_post_meta($post_id, '_pgn', $pgn);

        return $post_id;
    }

    private function extract_pgn_tags($pgn) {
        $tags = array();
        preg_match_all('/\[(.*?)\s"(.*?)"\]/', $pgn, $matches);
        
        for ($i = 0; $i < count($matches[1]); $i++) {
            $tags[trim($matches[1][$i])] = trim($matches[2][$i]);
        }
        
        return $tags;
    }

    private function game_exists($tags) {
        // Verifica se esiste una partita con gli stessi dettagli
        $args = array(
            'post_type' => 'scacchipartita',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_giocatore_bianco',
                    'value' => $tags['White'] ?? '',
                ),
                array(
                    'key' => '_giocatore_nero',
                    'value' => $tags['Black'] ?? '',
                ),
                array(
                    'key' => '_data_partita',
                    'value' => $this->format_pgn_date($tags['Date'] ?? ''),
                ),
                array(
                    'key' => '_nome_torneo',
                    'value' => $tags['Event'] ?? '',
                )
            )
        );

        $query = new WP_Query($args);
        return $query->have_posts();
    }

    private function generate_game_title($tags) {
        $white = $tags['White'] ?? __('Bianco', 'scacchitrack');
        $black = $tags['Black'] ?? __('Nero', 'scacchitrack');
        $event = $tags['Event'] ?? '';
        $date = $this->format_pgn_date($tags['Date'] ?? '', true);
        
        $title = sprintf('%s vs %s', $white, $black);
        if (!empty($event)) {
            $title .= sprintf(' - %s', $event);
        }
        if (!empty($date)) {
            $title .= sprintf(' (%s)', $date);
        }
        
        return $title;
    }

    private function format_pgn_date($date, $display = false) {
        if (empty($date) || $date == '????.??.??') {
            return '';
        }
        
        // Converte il formato PGN (YYYY.MM.DD) in formato MySQL (YYYY-MM-DD)
        $date = str_replace('.', '-', $date);
        
        if ($display) {
            return date_i18n(get_option('date_format'), strtotime($date));
        }
        
        return $date;
    }
}

/**
 * Funzione helper per gestire l'importazione
 */
function handle_pgn_import($file_path) {
    $importer = new ScacchiTrack_Import_Handler();
    return $importer->handle_pgn_import($file_path);
}

// Gestione dell'esportazione
if (isset($_POST['scacchitrack_export'])) {
    check_admin_referer('scacchitrack_export');
    
    $from_date = isset($_POST['export_from']) ? sanitize_text_field($_POST['export_from']) : '';
    $to_date = isset($_POST['export_to']) ? sanitize_text_field($_POST['export_to']) : '';
    $tournament = isset($_POST['export_tournament']) ? sanitize_text_field($_POST['export_tournament']) : '';
    
    // Genera il file PGN
    $pgn_content = generate_pgn_export($from_date, $to_date, $tournament);
    
    // Forza il download
    header('Content-Type: application/x-chess-pgn');
    header('Content-Disposition: attachment; filename="scacchitrack-export-' . date('Y-m-d') . '.pgn"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    echo $pgn_content;
    exit;
}

// Recupera la lista dei tornei per il filtro di esportazione
$tournaments = get_unique_tournament_names();
?>

<div class="scacchitrack-import-export">
    <?php if (isset($error_message)) : ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success_message)) : ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Sezione Importazione -->
    <div class="import-section">
        <h2><?php _e('Importa Partite', 'scacchitrack'); ?></h2>
        <div class="card">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('scacchitrack_import'); ?>
                
                <p>
                    <label for="pgn_file">
                        <?php _e('Seleziona file PGN da importare:', 'scacchitrack'); ?>
                    </label>
                </p>
                <p>
                    <input type="file" 
                           id="pgn_file" 
                           name="pgn_file" 
                           accept=".pgn" 
                           required>
                </p>

                <p>
                    <label>
                        <input type="checkbox" 
                               name="skip_duplicates" 
                               value="1" 
                               checked>
                        <?php _e('Salta partite duplicate', 'scacchitrack'); ?>
                    </label>
                </p>

                <p>
                    <input type="submit" 
                           name="scacchitrack_import" 
                           class="button button-primary" 
                           value="<?php esc_attr_e('Importa Partite', 'scacchitrack'); ?>">
                </p>
            </form>
        </div>
    </div>

    <!-- Sezione Esportazione -->
    <div class="export-section">
        <h2><?php _e('Esporta Partite', 'scacchitrack'); ?></h2>
        <div class="card">
            <form method="post">
                <?php wp_nonce_field('scacchitrack_export'); ?>
                
                <p>
                    <label for="export_from">
                        <?php _e('Data inizio:', 'scacchitrack'); ?>
                    </label>
                    <br>
                    <input type="date" 
                           id="export_from" 
                           name="export_from" 
                           class="regular-text">
                </p>

                <p>
                    <label for="export_to">
                        <?php _e('Data fine:', 'scacchitrack'); ?>
                    </label>
                    <br>
                    <input type="date" 
                           id="export_to" 
                           name="export_to" 
                           class="regular-text">
                </p>

                <p>
                    <label for="export_tournament">
                        <?php _e('Torneo:', 'scacchitrack'); ?>
                    </label>
                    <br>
                    <select id="export_tournament" 
                            name="export_tournament" 
                            class="regular-text">
                        <option value="">
                            <?php _e('Tutti i tornei', 'scacchitrack'); ?>
                        </option>
                        <?php foreach ($tournaments as $tournament) : ?>
                            <option value="<?php echo esc_attr($tournament); ?>">
                                <?php echo esc_html($tournament); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <input type="submit" 
                           name="scacchitrack_export" 
                           class="button button-primary" 
                           value="<?php esc_attr_e('Esporta Partite', 'scacchitrack'); ?>">
                </p>
            </form>
        </div>
    </div>
</div>

<style>
.scacchitrack-import-export {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.scacchitrack-import-export .card {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.scacchitrack-import-export h2 {
    margin-top: 0;
    color: #23282d;
    font-size: 1.3em;
    margin-bottom: 1em;
}

.scacchitrack-import-export .regular-text {
    width: 100%;
}

.scacchitrack-import-export label {
    font-weight: 600;
    display: inline-block;
    margin-bottom: 5px;
}

.scacchitrack-import-export select {
    max-width: 100%;
}
</style>
```

### settings.php {#templates-admin-settings-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Salva le impostazioni
if (isset($_POST['scacchitrack_save_settings'])) {
    check_admin_referer('scacchitrack_settings');
    
    $settings = array(
        'partite_per_pagina' => absint($_POST['partite_per_pagina']),
        'tema_scacchiera' => sanitize_text_field($_POST['tema_scacchiera']),
        'animazioni' => isset($_POST['animazioni']),
        'notazione_algebrica' => isset($_POST['notazione_algebrica']),
        'commenti_abilitati' => isset($_POST['commenti_abilitati'])
    );
    
    update_option('scacchitrack_settings', $settings);
    add_settings_error(
        'scacchitrack_messages',
        'scacchitrack_message',
        __('Impostazioni salvate con successo.', 'scacchitrack'),
        'updated'
    );
}

// Recupera le impostazioni correnti
$settings = get_option('scacchitrack_settings', array(
    'partite_per_pagina' => 10,
    'tema_scacchiera' => 'default',
    'animazioni' => true,
    'notazione_algebrica' => true,
    'commenti_abilitati' => true
));
?>

<div class="scacchitrack-settings">
    <?php settings_errors('scacchitrack_messages'); ?>

    <form method="post" action="">
        <?php wp_nonce_field('scacchitrack_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="partite_per_pagina">
                        <?php _e('Partite per Pagina', 'scacchitrack'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" 
                           id="partite_per_pagina" 
                           name="partite_per_pagina" 
                           value="<?php echo esc_attr($settings['partite_per_pagina']); ?>" 
                           min="1" 
                           max="100" 
                           class="small-text">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="tema_scacchiera">
                        <?php _e('Tema Scacchiera', 'scacchitrack'); ?>
                    </label>
                </th>
                <td>
                    <select id="tema_scacchiera" name="tema_scacchiera">
                        <option value="default" <?php selected($settings['tema_scacchiera'], 'default'); ?>>
                            <?php _e('Default', 'scacchitrack'); ?>
                        </option>
                        <option value="blue" <?php selected($settings['tema_scacchiera'], 'blue'); ?>>
                            <?php _e('Blu', 'scacchitrack'); ?>
                        </option>
                        <option value="green" <?php selected($settings['tema_scacchiera'], 'green'); ?>>
                            <?php _e('Verde', 'scacchitrack'); ?>
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Opzioni Visualizzazione', 'scacchitrack'); ?></th>
                <td>
                    <fieldset>
                        <label for="animazioni">
                            <input type="checkbox" 
                                   id="animazioni" 
                                   name="animazioni" 
                                   <?php checked($settings['animazioni']); ?>>
                            <?php _e('Abilita animazioni pezzi', 'scacchitrack'); ?>
                        </label>
                        <br>
                        <label for="notazione_algebrica">
                            <input type="checkbox" 
                                   id="notazione_algebrica" 
                                   name="notazione_algebrica" 
                                   <?php checked($settings['notazione_algebrica']); ?>>
                            <?php _e('Mostra notazione algebrica', 'scacchitrack'); ?>
                        </label>
                        <br>
                        <label for="commenti_abilitati">
                            <input type="checkbox" 
                                   id="commenti_abilitati" 
                                   name="commenti_abilitati" 
                                   <?php checked($settings['commenti_abilitati']); ?>>
                            <?php _e('Abilita commenti sulle partite', 'scacchitrack'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" 
                   name="scacchitrack_save_settings" 
                   class="button button-primary" 
                   value="<?php esc_attr_e('Salva Impostazioni', 'scacchitrack'); ?>">
        </p>
    </form>
</div>
```

### stats.php {#templates-admin-stats-php}

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupera le statistiche
$total_games = wp_count_posts('scacchipartita')->publish;
$stats = get_scacchitrack_statistics();
?>

<div class="scacchitrack-stats">
    <!-- Statistiche Generali -->
    <div class="stats-section">
        <h2><?php _e('Statistiche Generali', 'scacchitrack'); ?></h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($total_games); ?></div>
                <div class="stat-label"><?php _e('Partite Totali', 'scacchitrack'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($stats['total_tournaments']); ?></div>
                <div class="stat-label"><?php _e('Tornei', 'scacchitrack'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($stats['unique_players']); ?></div>
                <div class="stat-label"><?php _e('Giocatori Unici', 'scacchitrack'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($stats['avg_moves']); ?></div>
                <div class="stat-label"><?php _e('Media Mosse per Partita', 'scacchitrack'); ?></div>
            </div>
        </div>
    </div>

    <!-- Statistiche Risultati -->
    <div class="stats-section">
        <h2><?php _e('Risultati', 'scacchitrack'); ?></h2>
        <div class="stats-chart">
            <canvas id="resultChart"></canvas>
        </div>
        <div class="stats-legend">
            <div class="legend-item">
                <span class="color-box white-wins"></span>
                <?php printf(
                    __('Vittorie Bianco: %s%%', 'scacchitrack'),
                    number_format_i18n($stats['white_win_percentage'], 1)
                ); ?>
            </div>
            <div class="legend-item">
                <span class="color-box black-wins"></span>
                <?php printf(
                    __('Vittorie Nero: %s%%', 'scacchitrack'),
                    number_format_i18n($stats['black_win_percentage'], 1)
                ); ?>
            </div>
            <div class="legend-item">
                <span class="color-box draws"></span>
                <?php printf(
                    __('Patte: %s%%', 'scacchitrack'),
                    number_format_i18n($stats['draw_percentage'], 1)
                ); ?>
            </div>
        </div>
    </div>

    <!-- Top Giocatori -->
    <div class="stats-section">
        <h2><?php _e('Top Giocatori', 'scacchitrack'); ?></h2>
        <div class="stats-table-wrapper">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Giocatore', 'scacchitrack'); ?></th>
                        <th><?php _e('Partite', 'scacchitrack'); ?></th>
                        <th><?php _e('Vittorie', 'scacchitrack'); ?></th>
                        <th><?php _e('Patte', 'scacchitrack'); ?></th>
                        <th><?php _e('Sconfitte', 'scacchitrack'); ?></th>
                        <th><?php _e('% Vittorie', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['top_players'] as $player) : ?>
                        <tr>
                            <td><?php echo esc_html($player['name']); ?></td>
                            <td><?php echo number_format_i18n($player['total_games']); ?></td>
                            <td><?php echo number_format_i18n($player['wins']); ?></td>
                            <td><?php echo number_format_i18n($player['draws']); ?></td>
                            <td><?php echo number_format_i18n($player['losses']); ?></td>
                            <td><?php echo number_format_i18n($player['win_percentage'], 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grafici Temporali -->
    <div class="stats-section">
        <h2><?php _e('Andamento Temporale', 'scacchitrack'); ?></h2>
        <div class="stats-chart">
            <canvas id="timelineChart"></canvas>
        </div>
    </div>
</div>

<style>
.scacchitrack-stats {
    margin-top: 20px;
}

.stats-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.stat-value {
    font-size: 2em;
    font-weight: bold;
    color: #2271b1;
    margin-bottom: 10px;
}

.stat-label {
    color: #646970;
    font-size: 0.9em;
}

.stats-chart {
    margin: 20px 0;
    position: relative;
    height: 300px;
}

.stats-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 10px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.color-box {
    width: 16px;
    height: 16px;
    border-radius: 3px;
}

.color-box.white-wins { background-color: #4CAF50; }
.color-box.black-wins { background-color: #f44336; }
.color-box.draws { background-color: #2196F3; }

.stats-table-wrapper {
    margin-top: 20px;
    overflow-x: auto;
}

@media (max-width: 782px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-legend {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
// Inizializzazione dei grafici con Chart.js
jQuery(document).ready(function($) {
    // Dati per il grafico dei risultati
    const resultCtx = document.getElementById('resultChart').getContext('2d');
    new Chart(resultCtx, {
        type: 'doughnut',
        data: {
            labels: [
                '<?php _e("Vittorie Bianco", "scacchitrack"); ?>',
                '<?php _e("Vittorie Nero", "scacchitrack"); ?>',
                '<?php _e("Patte", "scacchitrack"); ?>'
            ],
            datasets: [{
                data: [
                    <?php echo $stats['white_win_percentage']; ?>,
                    <?php echo $stats['black_win_percentage']; ?>,
                    <?php echo $stats['draw_percentage']; ?>
                ],
                backgroundColor: [
                    '#4CAF50',  // verde per vittorie bianco
                    '#f44336',  // rosso per vittorie nero
                    '#2196F3'   // blu per patte
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: '<?php _e("Distribuzione Risultati", "scacchitrack"); ?>'
                }
            }
        }
    });

    // Dati per il grafico temporale
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($stats['timeline_labels']); ?>,
            datasets: [{
                label: '<?php _e("Partite per Mese", "scacchitrack"); ?>',
                data: <?php echo json_encode($stats['timeline_data']); ?>,
                borderColor: '#2271b1',
                backgroundColor: 'rgba(34, 113, 177, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: '<?php _e("Andamento Partite nel Tempo", "scacchitrack"); ?>'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>

<!-- Statistiche Dettagliate per Torneo -->
<div class="stats-section">
    <h2><?php _e('Statistiche per Torneo', 'scacchitrack'); ?></h2>
    <?php if (!empty($stats['tournament_stats'])) : ?>
        <div class="tournament-stats">
            <?php foreach ($stats['tournament_stats'] as $tournament) : ?>
                <div class="tournament-card">
                    <h3><?php echo esc_html($tournament['name']); ?></h3>
                    <div class="tournament-info">
                        <div class="info-item">
                            <span class="info-label"><?php _e('Partite:', 'scacchitrack'); ?></span>
                            <span class="info-value"><?php echo number_format_i18n($tournament['total_games']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php _e('Giocatori:', 'scacchitrack'); ?></span>
                            <span class="info-value"><?php echo number_format_i18n($tournament['players']); ?></span>
                        </div>
                        <div class="info-grid">
                            <div class="info-stat">
                                <div class="stat-circle white-wins">
                                    <?php echo number_format_i18n($tournament['white_wins_percentage'], 1); ?>%
                                </div>
                                <span><?php _e('Vittorie Bianco', 'scacchitrack'); ?></span>
                            </div>
                            <div class="info-stat">
                                <div class="stat-circle black-wins">
                                    <?php echo number_format_i18n($tournament['black_wins_percentage'], 1); ?>%
                                </div>
                                <span><?php _e('Vittorie Nero', 'scacchitrack'); ?></span>
                            </div>
                            <div class="info-stat">
                                <div class="stat-circle draws">
                                    <?php echo number_format_i18n($tournament['draws_percentage'], 1); ?>%
                                </div>
                                <span><?php _e('Patte', 'scacchitrack'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p><?php _e('Nessun torneo trovato.', 'scacchitrack'); ?></p>
    <?php endif; ?>
</div>

<!-- Statistiche Aperture -->
<div class="stats-section">
    <h2><?php _e('Aperture Più Comuni', 'scacchitrack'); ?></h2>
    <?php if (!empty($stats['openings'])) : ?>
        <div class="opening-stats">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Apertura', 'scacchitrack'); ?></th>
                        <th><?php _e('Partite', 'scacchitrack'); ?></th>
                        <th><?php _e('Vittorie Bianco', 'scacchitrack'); ?></th>
                        <th><?php _e('Vittorie Nero', 'scacchitrack'); ?></th>
                        <th><?php _e('Patte', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['openings'] as $opening) : ?>
                        <tr>
                            <td><?php echo esc_html($opening['name']); ?></td>
                            <td><?php echo number_format_i18n($opening['count']); ?></td>
                            <td><?php echo number_format_i18n($opening['white_wins']); ?></td>
                            <td><?php echo number_format_i18n($opening['black_wins']); ?></td>
                            <td><?php echo number_format_i18n($opening['draws']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p><?php _e('Nessuna statistica sulle aperture disponibile.', 'scacchitrack'); ?></p>
    <?php endif; ?>
</div>

<style>
/* Stili aggiuntivi per le statistiche dei tornei */
.tournament-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.tournament-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 20px;
}

.tournament-card h3 {
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

.tournament-info .info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 15px;
}

.info-stat {
    text-align: center;
}

.stat-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 5px;
    color: white;
    font-weight: bold;
}

.stat-circle.white-wins { background-color: #4CAF50; }
.stat-circle.black-wins { background-color: #f44336; }
.stat-circle.draws { background-color: #2196F3; }

/* Stili responsive aggiuntivi */
@media (max-width: 782px) {
    .tournament-card {
        margin-bottom: 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-circle {
        width: 50px;
        height: 50px;
        font-size: 0.9em;
    }
}
</style>
```

