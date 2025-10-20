<?php
if (!defined('ABSPATH')) {
    exit;
}

// Gestione dell'importazione da file singolo
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

// Gestione importazione da testo incollato
if (isset($_POST['scacchitrack_import_paste']) && !empty($_POST['pgn_paste'])) {
    check_admin_referer('scacchitrack_import_paste');

    $pgn_content = wp_unslash($_POST['pgn_paste']);
    $importer = new ScacchiTrack_Import_Handler();

    // Salva temporaneamente il contenuto in un file
    $temp_file = wp_tempnam('pgn-paste-');
    file_put_contents($temp_file, $pgn_content);

    $result = $importer->handle_pgn_import($temp_file);
    unlink($temp_file);

    if (is_wp_error($result)) {
        $error_message = $result->get_error_message();
    } else {
        $success_message = sprintf(
            __('Importate con successo %d partite su %d.', 'scacchitrack'),
            $result['imported'],
            $result['total']
        );
        if (!empty($result['errors'])) {
            $error_message = implode('<br>', array_slice($result['errors'], 0, 10));
            if (count($result['errors']) > 10) {
                $error_message .= '<br>' . sprintf(__('...e altri %d errori', 'scacchitrack'), count($result['errors']) - 10);
            }
        }
    }
}

// Gestione importazione batch (file multipli)
if (isset($_POST['scacchitrack_import_batch']) && !empty($_FILES['pgn_files'])) {
    check_admin_referer('scacchitrack_import_batch');

    $files = $_FILES['pgn_files'];
    $importer = new ScacchiTrack_Import_Handler();

    $total_imported = 0;
    $total_games = 0;
    $all_errors = array();

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $all_errors[] = sprintf(__('Errore nel file %s', 'scacchitrack'), $files['name'][$i]);
            continue;
        }

        $result = $importer->handle_pgn_import($files['tmp_name'][$i]);

        if (is_wp_error($result)) {
            $all_errors[] = sprintf(__('Errore nel file %s: %s', 'scacchitrack'), $files['name'][$i], $result->get_error_message());
        } else {
            $total_imported += $result['imported'];
            $total_games += $result['total'];
            if (!empty($result['errors'])) {
                $all_errors = array_merge($all_errors, $result['errors']);
            }
        }
    }

    $success_message = sprintf(
        __('Importate con successo %d partite su %d da %d file.', 'scacchitrack'),
        $total_imported,
        $total_games,
        count($files['name'])
    );

    if (!empty($all_errors)) {
        $error_message = implode('<br>', array_slice($all_errors, 0, 10));
        if (count($all_errors) > 10) {
            $error_message .= '<br>' . sprintf(__('...e altri %d errori', 'scacchitrack'), count($all_errors) - 10);
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

    private function import_single_game($pgn, $skip_duplicates = true) {
        // Validazione PGN
        $validation = $this->validate_pgn($pgn);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $tags = $this->extract_pgn_tags($pgn);

        if ($skip_duplicates && $this->game_exists($tags)) {
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

    /**
     * Validazione PGN
     */
    private function validate_pgn($pgn) {
        $errors = array();

        // Verifica presenza tag obbligatori
        $required_tags = array('Event', 'White', 'Black', 'Result');
        $tags = $this->extract_pgn_tags($pgn);

        foreach ($required_tags as $tag) {
            if (empty($tags[$tag]) || $tags[$tag] === '?') {
                $errors[] = sprintf(__('Tag obbligatorio mancante: %s', 'scacchitrack'), $tag);
            }
        }

        // Verifica formato risultato
        if (!empty($tags['Result'])) {
            $valid_results = array('1-0', '0-1', '1/2-1/2', '*');
            if (!in_array($tags['Result'], $valid_results)) {
                $errors[] = sprintf(__('Risultato non valido: %s', 'scacchitrack'), $tags['Result']);
            }
        }

        // Verifica presenza mosse (almeno qualcosa che sembri una mossa)
        if (!preg_match('/\d+\.\s*[a-h1-8NBRQK]/i', $pgn)) {
            $errors[] = __('Nessuna mossa trovata nel PGN', 'scacchitrack');
        }

        if (!empty($errors)) {
            return new WP_Error('validation_error', implode('; ', $errors));
        }

        return true;
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
            <div class="import-tabs">
                <button class="import-tab-btn active" data-tab="file">
                    <?php _e('Carica File', 'scacchitrack'); ?>
                </button>
                <button class="import-tab-btn" data-tab="paste">
                    <?php _e('Incolla PGN', 'scacchitrack'); ?>
                </button>
                <button class="import-tab-btn" data-tab="batch">
                    <?php _e('Caricamento Batch', 'scacchitrack'); ?>
                </button>
            </div>

            <!-- Tab: Carica Singolo File -->
            <div class="import-tab-content active" id="tab-file">
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

            <!-- Tab: Incolla PGN -->
            <div class="import-tab-content" id="tab-paste">
                <form method="post">
                    <?php wp_nonce_field('scacchitrack_import_paste'); ?>

                    <p>
                        <label for="pgn_paste">
                            <?php _e('Incolla uno o più PGN:', 'scacchitrack'); ?>
                        </label>
                    </p>
                    <p>
                        <textarea id="pgn_paste"
                                  name="pgn_paste"
                                  rows="15"
                                  class="large-text code"
                                  placeholder="[Event &quot;Torneo&quot;]&#10;[White &quot;Giocatore1&quot;]&#10;[Black &quot;Giocatore2&quot;]&#10;..."
                                  required></textarea>
                    </p>

                    <p>
                        <label>
                            <input type="checkbox"
                                   name="skip_duplicates_paste"
                                   value="1"
                                   checked>
                            <?php _e('Salta partite duplicate', 'scacchitrack'); ?>
                        </label>
                    </p>

                    <p>
                        <input type="submit"
                               name="scacchitrack_import_paste"
                               class="button button-primary"
                               value="<?php esc_attr_e('Importa da Testo', 'scacchitrack'); ?>">
                    </p>
                </form>
            </div>

            <!-- Tab: Batch Upload -->
            <div class="import-tab-content" id="tab-batch">
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('scacchitrack_import_batch'); ?>

                    <p>
                        <label for="pgn_files_batch">
                            <?php _e('Seleziona più file PGN:', 'scacchitrack'); ?>
                        </label>
                    </p>
                    <p>
                        <input type="file"
                               id="pgn_files_batch"
                               name="pgn_files[]"
                               accept=".pgn"
                               multiple
                               required>
                    </p>

                    <p>
                        <label>
                            <input type="checkbox"
                                   name="skip_duplicates_batch"
                                   value="1"
                                   checked>
                            <?php _e('Salta partite duplicate', 'scacchitrack'); ?>
                        </label>
                    </p>

                    <p>
                        <input type="submit"
                               name="scacchitrack_import_batch"
                               class="button button-primary"
                               value="<?php esc_attr_e('Importa File Multipli', 'scacchitrack'); ?>">
                    </p>
                </form>

                <div id="batch-progress" style="display:none; margin-top: 20px;">
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: 0%"></div>
                    </div>
                    <p class="progress-text">0 / 0</p>
                </div>
            </div>
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

/* Tab Styles */
.import-tabs {
    display: flex;
    border-bottom: 1px solid #ccd0d4;
    margin-bottom: 20px;
    gap: 5px;
}

.import-tab-btn {
    background: #f0f0f1;
    border: 1px solid #ccd0d4;
    border-bottom: none;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 14px;
    border-radius: 4px 4px 0 0;
    transition: background-color 0.2s;
}

.import-tab-btn:hover {
    background: #fff;
}

.import-tab-btn.active {
    background: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
    font-weight: 600;
}

.import-tab-content {
    display: none;
}

.import-tab-content.active {
    display: block;
}

/* Progress Bar */
.progress-bar {
    width: 100%;
    height: 30px;
    background-color: #f0f0f1;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-bar-fill {
    height: 100%;
    background-color: #2271b1;
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.progress-text {
    text-align: center;
    font-weight: 600;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.import-tab-btn').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');

        // Remove active class from all tabs and contents
        $('.import-tab-btn').removeClass('active');
        $('.import-tab-content').removeClass('active');

        // Add active class to clicked tab and corresponding content
        $(this).addClass('active');
        $('#tab-' + tab).addClass('active');
    });
});
</script>