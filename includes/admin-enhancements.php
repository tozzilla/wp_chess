<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Miglioramenti CMS per ScacchiTrack
 * - Filtri avanzati
 * - Bulk actions
 * - Quick edit
 * - Duplica partita
 */

class ScacchiTrack_Admin_Enhancements {

    public function __construct() {
        // Filtri nella lista admin
        add_action('restrict_manage_posts', array($this, 'add_admin_filters'));
        add_filter('parse_query', array($this, 'filter_admin_query'));

        // Bulk actions
        add_filter('bulk_actions-edit-scacchipartita', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-scacchipartita', array($this, 'handle_bulk_actions'), 10, 3);
        add_action('admin_notices', array($this, 'bulk_action_admin_notice'));

        // Quick edit
        add_action('quick_edit_custom_box', array($this, 'quick_edit_fields'), 10, 2);
        add_action('save_post_scacchipartita', array($this, 'save_quick_edit'), 10, 2);
        add_action('admin_footer', array($this, 'quick_edit_javascript'));

        // Duplica partita
        add_filter('post_row_actions', array($this, 'add_duplicate_link'), 10, 2);
        add_action('admin_action_duplicate_game', array($this, 'duplicate_game'));
    }

    /**
     * Aggiunge filtri nella lista admin
     */
    public function add_admin_filters($post_type) {
        if ($post_type !== 'scacchipartita') {
            return;
        }

        // Filtro Torneo
        $selected_torneo = isset($_GET['filter_torneo']) ? $_GET['filter_torneo'] : '';
        $tornei = get_unique_tournament_names();

        echo '<select name="filter_torneo">';
        echo '<option value="">' . __('Tutti i Tornei', 'scacchitrack') . '</option>';
        foreach ($tornei as $torneo) {
            echo '<option value="' . esc_attr($torneo) . '" ' . selected($selected_torneo, $torneo, false) . '>';
            echo esc_html($torneo);
            echo '</option>';
        }
        echo '</select>';

        // Filtro Risultato
        $selected_result = isset($_GET['filter_risultato']) ? $_GET['filter_risultato'] : '';
        $results = array('1-0', '0-1', '½-½', '*');

        echo '<select name="filter_risultato">';
        echo '<option value="">' . __('Tutti i Risultati', 'scacchitrack') . '</option>';
        foreach ($results as $result) {
            echo '<option value="' . esc_attr($result) . '" ' . selected($selected_result, $result, false) . '>';
            echo esc_html($result);
            echo '</option>';
        }
        echo '</select>';

        // Filtro Anno
        $selected_year = isset($_GET['filter_anno']) ? $_GET['filter_anno'] : '';
        $years = $this->get_game_years();

        if (!empty($years)) {
            echo '<select name="filter_anno">';
            echo '<option value="">' . __('Tutti gli Anni', 'scacchitrack') . '</option>';
            foreach ($years as $year) {
                echo '<option value="' . esc_attr($year) . '" ' . selected($selected_year, $year, false) . '>';
                echo esc_html($year);
                echo '</option>';
            }
            echo '</select>';
        }
    }

    /**
     * Ottiene gli anni delle partite
     */
    private function get_game_years() {
        global $wpdb;

        $years = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT YEAR(meta_value) as year
                FROM {$wpdb->postmeta}
                WHERE meta_key = %s
                AND meta_value != ''
                AND meta_value IS NOT NULL
                ORDER BY year DESC",
                '_data_partita'
            )
        );

        return array_filter($years);
    }

    /**
     * Applica i filtri alla query
     */
    public function filter_admin_query($query) {
        global $pagenow, $typenow;

        if ($pagenow !== 'edit.php' || $typenow !== 'scacchipartita' || !$query->is_main_query()) {
            return;
        }

        $meta_query = array('relation' => 'AND');

        // Filtro Torneo
        if (!empty($_GET['filter_torneo'])) {
            $meta_query[] = array(
                'key' => '_nome_torneo',
                'value' => sanitize_text_field($_GET['filter_torneo']),
                'compare' => '='
            );
        }

        // Filtro Risultato
        if (!empty($_GET['filter_risultato'])) {
            $meta_query[] = array(
                'key' => '_risultato',
                'value' => sanitize_text_field($_GET['filter_risultato']),
                'compare' => '='
            );
        }

        // Filtro Anno
        if (!empty($_GET['filter_anno'])) {
            $year = absint($_GET['filter_anno']);
            $meta_query[] = array(
                'key' => '_data_partita',
                'value' => array($year . '-01-01', $year . '-12-31'),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            );
        }

        if (count($meta_query) > 1) {
            $query->set('meta_query', $meta_query);
        }
    }

    /**
     * Registra le bulk actions personalizzate
     */
    public function register_bulk_actions($bulk_actions) {
        $bulk_actions['export_pgn'] = __('Esporta PGN', 'scacchitrack');
        $bulk_actions['assign_tournament'] = __('Assegna a Torneo', 'scacchitrack');
        $bulk_actions['change_result'] = __('Cambia Risultato', 'scacchitrack');

        return $bulk_actions;
    }

    /**
     * Gestisce le bulk actions
     */
    public function handle_bulk_actions($redirect_to, $action, $post_ids) {
        if ($action === 'export_pgn') {
            $this->export_games_pgn($post_ids);
            exit;
        }

        if ($action === 'assign_tournament') {
            $redirect_to = add_query_arg('bulk_assigned', count($post_ids), $redirect_to);
            set_transient('scacchitrack_bulk_tournament', $post_ids, 60);
            return $redirect_to;
        }

        if ($action === 'change_result') {
            $redirect_to = add_query_arg('bulk_result_change', count($post_ids), $redirect_to);
            set_transient('scacchitrack_bulk_result', $post_ids, 60);
            return $redirect_to;
        }

        return $redirect_to;
    }

    /**
     * Esporta partite selezionate in PGN
     */
    private function export_games_pgn($post_ids) {
        $pgn_output = '';

        foreach ($post_ids as $post_id) {
            $pgn = get_post_meta($post_id, '_pgn', true);
            if ($pgn) {
                $pgn_output .= $pgn . "\n\n";
            }
        }

        $filename = 'partite_' . date('Y-m-d_H-i-s') . '.pgn';

        header('Content-Type: application/x-chess-pgn');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pgn_output));

        echo $pgn_output;
    }

    /**
     * Mostra notifiche per le bulk actions
     */
    public function bulk_action_admin_notice() {
        if (!empty($_REQUEST['bulk_assigned'])) {
            $count = intval($_REQUEST['bulk_assigned']);
            echo '<div class="notice notice-success is-dismissible">';
            printf(
                _n(
                    '%s partita pronta per l\'assegnazione al torneo.',
                    '%s partite pronte per l\'assegnazione al torneo.',
                    $count,
                    'scacchitrack'
                ),
                $count
            );
            echo '</div>';
        }

        if (!empty($_REQUEST['bulk_result_change'])) {
            $count = intval($_REQUEST['bulk_result_change']);
            echo '<div class="notice notice-success is-dismissible">';
            printf(
                _n(
                    '%s partita aggiornata.',
                    '%s partite aggiornate.',
                    $count,
                    'scacchitrack'
                ),
                $count
            );
            echo '</div>';
        }
    }

    /**
     * Campi custom per quick edit
     */
    public function quick_edit_fields($column_name, $post_type) {
        if ($post_type !== 'scacchipartita') {
            return;
        }

        if ($column_name !== 'torneo') {
            return;
        }

        $tornei = get_unique_tournament_names();
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e('Torneo', 'scacchitrack'); ?></span>
                    <span class="input-text-wrap">
                        <select name="nome_torneo" class="nome_torneo">
                            <option value=""><?php _e('-- Seleziona Torneo --', 'scacchitrack'); ?></option>
                            <?php foreach ($tornei as $torneo): ?>
                                <option value="<?php echo esc_attr($torneo); ?>"><?php echo esc_html($torneo); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </span>
                </label>

                <label>
                    <span class="title"><?php _e('Round', 'scacchitrack'); ?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="round" class="round" value="">
                    </span>
                </label>

                <label>
                    <span class="title"><?php _e('Risultato', 'scacchitrack'); ?></span>
                    <span class="input-text-wrap">
                        <select name="risultato" class="risultato">
                            <option value="">-- <?php _e('Seleziona', 'scacchitrack'); ?> --</option>
                            <option value="1-0">1-0</option>
                            <option value="0-1">0-1</option>
                            <option value="½-½">½-½</option>
                            <option value="*">*</option>
                        </select>
                    </span>
                </label>

                <label>
                    <span class="title"><?php _e('Data Partita', 'scacchitrack'); ?></span>
                    <span class="input-text-wrap">
                        <input type="date" name="data_partita" class="data_partita" value="">
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }

    /**
     * Salva i campi del quick edit
     */
    public function save_quick_edit($post_id, $post) {
        // Verifica se è una chiamata di quick edit
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Aggiorna i meta se presenti
        if (isset($_POST['nome_torneo']) && !empty($_POST['nome_torneo'])) {
            update_post_meta($post_id, '_nome_torneo', sanitize_text_field($_POST['nome_torneo']));
        }

        if (isset($_POST['round'])) {
            update_post_meta($post_id, '_round', sanitize_text_field($_POST['round']));
        }

        if (isset($_POST['risultato'])) {
            update_post_meta($post_id, '_risultato', sanitize_text_field($_POST['risultato']));
        }

        if (isset($_POST['data_partita'])) {
            update_post_meta($post_id, '_data_partita', sanitize_text_field($_POST['data_partita']));
        }
    }

    /**
     * JavaScript per popolare il quick edit
     */
    public function quick_edit_javascript() {
        global $current_screen;

        if ($current_screen->id !== 'edit-scacchipartita') {
            return;
        }
        ?>
        <script type="text/javascript">
        (function($) {
            // Popola i campi del quick edit con i valori correnti
            var $wp_inline_edit = inlineEditPost.edit;

            inlineEditPost.edit = function(id) {
                $wp_inline_edit.apply(this, arguments);

                var post_id = 0;
                if (typeof(id) == 'object') {
                    post_id = parseInt(this.getId(id));
                }

                if (post_id > 0) {
                    var $row = $('#post-' + post_id);
                    var torneo = $row.find('.column-torneo').text();
                    var round = $row.find('.column-round').text();
                    var risultato = $row.find('.column-risultato').text();

                    $('.nome_torneo').val(torneo);
                    $('.round').val(round);
                    $('.risultato').val(risultato);
                }
            };
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Aggiunge link "Duplica" alle azioni riga
     */
    public function add_duplicate_link($actions, $post) {
        if ($post->post_type !== 'scacchipartita') {
            return $actions;
        }

        if (!current_user_can('edit_posts')) {
            return $actions;
        }

        $url = wp_nonce_url(
            admin_url('admin.php?action=duplicate_game&post=' . $post->ID),
            'duplicate_game_' . $post->ID
        );

        $actions['duplicate'] = '<a href="' . $url . '" title="' .
            esc_attr__('Duplica questa partita', 'scacchitrack') . '">' .
            __('Duplica', 'scacchitrack') . '</a>';

        return $actions;
    }

    /**
     * Duplica una partita
     */
    public function duplicate_game() {
        if (empty($_GET['post'])) {
            wp_die(__('Nessuna partita da duplicare!', 'scacchitrack'));
        }

        $post_id = absint($_GET['post']);

        check_admin_referer('duplicate_game_' . $post_id);

        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'scacchipartita') {
            wp_die(__('Partita non valida!', 'scacchitrack'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(__('Non hai i permessi per duplicare partite.', 'scacchitrack'));
        }

        // Crea nuovo post
        $new_post = array(
            'post_title'   => $post->post_title . ' (Copia)',
            'post_content' => $post->post_content,
            'post_status'  => 'draft',
            'post_type'    => $post->post_type,
            'post_author'  => get_current_user_id()
        );

        $new_post_id = wp_insert_post($new_post);

        if (is_wp_error($new_post_id)) {
            wp_die($new_post_id->get_error_message());
        }

        // Copia i meta
        $meta_keys = array(
            '_giocatore_bianco',
            '_giocatore_nero',
            '_data_partita',
            '_nome_torneo',
            '_round',
            '_risultato',
            '_pgn'
        );

        foreach ($meta_keys as $meta_key) {
            $meta_value = get_post_meta($post_id, $meta_key, true);
            if ($meta_value) {
                update_post_meta($new_post_id, $meta_key, $meta_value);
            }
        }

        // Copia le tassonomie
        $taxonomies = array('apertura_scacchi', 'tipo_partita', 'etichetta_partita');
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
            if (!empty($terms)) {
                wp_set_post_terms($new_post_id, $terms, $taxonomy);
            }
        }

        // Redirect alla nuova partita
        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;
    }
}

// Inizializza
new ScacchiTrack_Admin_Enhancements();
