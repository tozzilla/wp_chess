<?php
if (!defined('ABSPATH')) {
    exit;
}

// Gestione dell'importazione
if (isset($_POST['scacchitrack_import']) && isset($_FILES['pgn_file'])) {
    check_admin_referer('scacchitrack_import');
    
    $file = $_FILES['pgn_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = __('Errore nel caricamento del file.', 'scacchitrack');
    } else {
        $importer = new ScacchiTrack_Import_Handler();
        $result = $importer->handle_pgn_import($file['tmp_name']);
        
        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
        } else {
            $success_message = sprintf(
                __('Importate con successo %d partite su %d.', 'scacchitrack'),
                $result['imported'],
                $result['total']
            );
            if (!empty($result['errors'])) {
                $error_message = implode('<br>', $result['errors']);
            }
        }
    }
}

// Gestione dell'esportazione
if (isset($_POST['scacchitrack_export'])) {
    check_admin_referer('scacchitrack_export');
    
    $from_date = isset($_POST['export_from']) ? sanitize_text_field($_POST['export_from']) : '';
    $to_date = isset($_POST['export_to']) ? sanitize_text_field($_POST['export_to']) : '';
    $tournament = isset($_POST['export_tournament']) ? sanitize_text_field($_POST['export_tournament']) : '';
    
    $exporter = new ScacchiTrack_Export_Handler();
    $pgn_content = $exporter->generate_pgn_export($from_date, $to_date, $tournament);
    
    if (!empty($pgn_content)) {
        header('Content-Type: application/x-chess-pgn');
        header('Content-Disposition: attachment; filename="scacchitrack-export-' . date('Y-m-d') . '.pgn"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        echo $pgn_content;
        exit;
    } else {
        $error_message = __('Nessuna partita trovata con i criteri specificati.', 'scacchitrack');
    }
}

/**
 * Classe per la gestione dell'importazione
 */
class ScacchiTrack_Import_Handler {
    
    public function handle_pgn_import($file_path) {
        if (!file_exists($file_path)) {
            return new WP_Error('file_missing', __('File PGN non trovato', 'scacchitrack'));
        }

        $content = file_get_contents($file_path);
        
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content));
        }

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
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (preg_match('/^\[Event/', $line) && !empty($current_game)) {
                $games[] = trim($current_game);
                $current_game = '';
            }
            
            $current_game .= $line . "\n";
        }
        
        if (!empty($current_game)) {
            $games[] = trim($current_game);
        }
        
        return $games;
    }

    private function import_single_game($pgn) {
        $tags = $this->extract_pgn_tags($pgn);
        
        if ($this->game_exists($tags)) {
            return new WP_Error('duplicate', __('Partita già esistente', 'scacchitrack'));
        }

        $title = scacchitrack_generate_game_title(
            $tags['Event'] ?? '',
            $tags['Round'] ?? '',
            $tags['White'] ?? '',
            $tags['Black'] ?? ''
        );

        $post_data = array(
            'post_title'   => $title,
            'post_type'    => 'scacchipartita',
            'post_status'  => 'publish',
            'post_content' => ''
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $meta_fields = array(
            '_giocatore_bianco' => 'White',
            '_giocatore_nero' => 'Black',
            '_data_partita' => 'Date',
            '_nome_torneo' => 'Event',
            '_round' => 'Round',
            '_risultato' => 'Result',
            '_pgn' => $pgn
        );

        foreach ($meta_fields as $meta_key => $pgn_tag) {
            if ($meta_key === '_data_partita' && isset($tags[$pgn_tag])) {
                $value = $this->format_pgn_date($tags[$pgn_tag]);
            } elseif ($meta_key === '_pgn') {
                $value = $pgn;
            } else {
                $value = $tags[$pgn_tag] ?? '';
            }
            update_post_meta($post_id, $meta_key, $value);
        }

        return $post_id;
    }

    private function game_exists($tags) {
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
                ),
                array(
                    'key' => '_round',
                    'value' => $tags['Round'] ?? '',
                )
            )
        );

        $query = new WP_Query($args);
        return $query->have_posts();
    }

    private function extract_pgn_tags($pgn) {
        $tags = array();
        preg_match_all('/\[(.*?)\s"(.*?)"\]/', $pgn, $matches);
        
        for ($i = 0; $i < count($matches[1]); $i++) {
            $tags[trim($matches[1][$i])] = trim($matches[2][$i]);
        }
        
        return $tags;
    }

    private function format_pgn_date($date) {
        if (empty($date) || $date == '????.??.??') {
            return '';
        }
        return str_replace('.', '-', $date);
    }
}

/**
 * Classe per la gestione dell'esportazione
 */
class ScacchiTrack_Export_Handler {
    
    public function generate_pgn_export($from_date = '', $to_date = '', $tournament = '') {
        $args = array(
            'post_type' => 'scacchipartita',
            'posts_per_page' => -1,
            'meta_query' => array()
        );

        if (!empty($tournament)) {
            $args['meta_query'][] = array(
                'key' => '_nome_torneo',
                'value' => $tournament
            );
        }

        if (!empty($from_date) || !empty($to_date)) {
            $date_query = array('key' => '_data_partita');
            
            if (!empty($from_date)) {
                $date_query['value'] = $from_date;
                $date_query['compare'] = '>=';
                $date_query['type'] = 'DATE';
            }
            
            if (!empty($to_date)) {
                if (empty($from_date)) {
                    $date_query['value'] = $to_date;
                    $date_query['compare'] = '<=';
                } else {
                    $date_query['value'] = array($from_date, $to_date);
                    $date_query['compare'] = 'BETWEEN';
                }
                $date_query['type'] = 'DATE';
            }
            
            $args['meta_query'][] = $date_query;
        }

        $query = new WP_Query($args);
        $pgn_content = '';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $pgn_content .= get_post_meta(get_the_ID(), '_pgn', true) . "\n\n";
            }
        }

        wp_reset_postdata();
        return $pgn_content;
    }
}

// Recupera la lista dei tornei per il filtro di esportazione
$tournaments = get_unique_tournament_names();
?>

<!-- Template HTML per l'interfaccia -->
<div class="scacchitrack-import-export">
    <?php if (isset($error_message)) : ?>
        <div class="notice notice-error">
            <p><?php echo wp_kses_post($error_message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success_message)) : ?>
        <div class="notice notice-success">
            <p><?php echo wp_kses_post($success_message); ?></p>
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