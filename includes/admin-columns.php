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
    $new_columns['round'] = __('Turno', 'scacchitrack'); // Nuova colonna
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

        case 'round':
                $round = get_post_meta($post_id, '_round', true);
                echo $round ? esc_html($round) : '-';
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
    $columns['round'] = 'round'; // Aggiunto ordinamento per round
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
            .column-round,
            .column-data_partita,
            .column-risultato {
                width: 12%;
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