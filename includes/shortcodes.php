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