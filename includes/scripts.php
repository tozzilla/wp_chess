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

        wp_register_script(
            'scacchitrack-evaluation',
            SCACCHITRACK_URL . 'js/evaluation.js',
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
            wp_enqueue_script('scacchitrack-evaluation');
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

            // Recupera le impostazioni di valutazione
            $settings = get_option('scacchitrack_settings', array());
            $evaluation_enabled = isset($settings['evaluation_enabled']) ? $settings['evaluation_enabled'] : false;
            $evaluation_mode = isset($settings['evaluation_mode']) ? $settings['evaluation_mode'] : 'simple';
            $evaluation_depth = isset($settings['evaluation_depth']) ? $settings['evaluation_depth'] : 15;

            // Localizzazione per JavaScript
            wp_localize_script('scacchitrack-js', 'scacchitrackData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'pluginUrl' => SCACCHITRACK_URL,
                'nonce' => wp_create_nonce('scacchitrack_ajax'),
                'filterNonce' => wp_create_nonce('scacchitrack_filter'), // nome del nonce
                'pieces' => $chess_pieces,
                'pgn' => $pgn,
                'evaluationEnabled' => $evaluation_enabled,
                'evaluationMode' => $evaluation_mode,
                'evaluationDepth' => $evaluation_depth,
                'stockfishUrl' => 'https://cdn.jsdelivr.net/npm/stockfish@15.0.0/src/stockfish-nnue-16.js',
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

                $pgn = isset($_GET['post']) ? get_post_meta(absint($_GET['post']), '_pgn', true) : '';
                
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
}

// Inizializza il gestore degli asset
new ScacchiTrack_Assets_Manager();