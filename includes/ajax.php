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

        // Se c'è un torneo, aggiungiamo l'ordinamento per round
            $args['meta_key'] = '_round';
            $args['orderby'] = array(
            'meta_value_num' => 'ASC',
            'date' => 'DESC'
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
            $data_query = array(
                'key' => '_data_partita',
                'type' => 'DATE'
            );

            // Se abbiamo entrambe le date, usa BETWEEN
            if (!empty($_POST['data_da']) && !empty($_POST['data_a'])) {
                $data_query['value'] = array(
                    sanitize_text_field($_POST['data_da']),
                    sanitize_text_field($_POST['data_a'])
                );
                $data_query['compare'] = 'BETWEEN';
            }
            // Se abbiamo solo data_da, usa >=
            elseif (!empty($_POST['data_da'])) {
                $data_query['value'] = sanitize_text_field($_POST['data_da']);
                $data_query['compare'] = '>=';
            }
            // Se abbiamo solo data_a, usa <=
            elseif (!empty($_POST['data_a'])) {
                $data_query['value'] = sanitize_text_field($_POST['data_a']);
                $data_query['compare'] = '<=';
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
            error_log('Meta _round: ' . get_post_meta($post_id, '_round', true)); // Aggiunto log per round
            
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