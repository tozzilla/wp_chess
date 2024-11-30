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