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