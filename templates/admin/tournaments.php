<?php
if (!defined('ABSPATH')) {
    exit;
}

// Gestione azioni tornei
if (isset($_POST['action']) && wp_verify_nonce($_POST['_wpnonce'], 'scacchitrack_tournament_action')) {
    switch ($_POST['action']) {
        case 'rename_tournament':
            if (isset($_POST['old_name']) && isset($_POST['new_name'])) {
                $old_name = sanitize_text_field($_POST['old_name']);
                $new_name = sanitize_text_field($_POST['new_name']);
                
                global $wpdb;
                $wpdb->update(
                    $wpdb->postmeta,
                    array('meta_value' => $new_name),
                    array(
                        'meta_key' => '_nome_torneo',
                        'meta_value' => $old_name
                    )
                );
                
                $success_message = __('Torneo rinominato con successo.', 'scacchitrack');
            }
            break;
            
            case 'delete_tournament':
                if (isset($_POST['tournament_name'])) {
                    global $wpdb;
                    $tournament = sanitize_text_field($_POST['tournament_name']);
                    
                    // Recupera tutti i post ID associati a questo torneo
                    $post_ids = $wpdb->get_col($wpdb->prepare(
                        "SELECT post_id 
                        FROM {$wpdb->postmeta} 
                        WHERE meta_key = '_nome_torneo' 
                        AND meta_value = %s",
                        $tournament
                    ));
            
                    // Elimina i post
                    foreach ($post_ids as $post_id) {
                        wp_delete_post($post_id, true);
                    }
                    
                    // Per sicurezza, pulisci anche eventuali meta orfani
                    $wpdb->delete(
                        $wpdb->postmeta,
                        array(
                            'meta_key' => '_nome_torneo',
                            'meta_value' => $tournament
                        ),
                        array('%s', '%s')
                    );
                    
                    $success_message = sprintf(
                        __('Torneo "%s" eliminato con successo. Rimosse %d partite.', 'scacchitrack'),
                        $tournament,
                        count($post_ids)
                    );
                }
                break;
    }
}

// Recupera lista tornei con conteggio partite
$tournaments = array();
$tournament_names = get_unique_tournament_names();

foreach ($tournament_names as $tournament) {
    $count = get_posts(array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => -1,
        'meta_key' => '_nome_torneo',
        'meta_value' => $tournament,
        'fields' => 'ids'
    ));
    
    $tournaments[] = array(
        'name' => $tournament,
        'count' => count($count)
    );
}
?>

<div class="wrap">
    <h2><?php _e('Gestione Tornei', 'scacchitrack'); ?></h2>
    
    <?php if (isset($success_message)) : ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Nome Torneo', 'scacchitrack'); ?></th>
                <th><?php _e('Numero Partite', 'scacchitrack'); ?></th>
                <th><?php _e('Azioni', 'scacchitrack'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tournaments)) : ?>
                <?php foreach ($tournaments as $tournament) : ?>
                    <tr>
                        <td><?php echo esc_html($tournament['name']); ?></td>
                        <td><?php echo number_format_i18n($tournament['count']); ?></td>
                        <td>
                            <button type="button" 
                                    class="button rename-tournament" 
                                    data-tournament="<?php echo esc_attr($tournament['name']); ?>">
                                <?php _e('Rinomina', 'scacchitrack'); ?>
                            </button>
                            
                            <button type="button" 
                                    class="button delete-tournament" 
                                    data-tournament="<?php echo esc_attr($tournament['name']); ?>">
                                <?php _e('Elimina', 'scacchitrack'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3"><?php _e('Nessun torneo trovato.', 'scacchitrack'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal per rinominare il torneo -->
<div id="rename-tournament-modal" class="scacchitrack-modal" style="display: none;">
    <div class="scacchitrack-modal-content">
        <h3><?php _e('Rinomina Torneo', 'scacchitrack'); ?></h3>
        <form method="post">
            <?php wp_nonce_field('scacchitrack_tournament_action'); ?>
            <input type="hidden" name="action" value="rename_tournament">
            <input type="hidden" name="old_name" id="old_tournament_name">
            
            <p>
                <label for="new_tournament_name">
                    <?php _e('Nuovo nome:', 'scacchitrack'); ?>
                </label>
                <input type="text" 
                       id="new_tournament_name" 
                       name="new_name" 
                       class="regular-text" 
                       required>
            </p>
            
            <p>
                <input type="submit" 
                       class="button button-primary" 
                       value="<?php esc_attr_e('Salva', 'scacchitrack'); ?>">
                <button type="button" 
                        class="button modal-close">
                    <?php _e('Annulla', 'scacchitrack'); ?>
                </button>
            </p>
        </form>
    </div>
</div>

<!-- Modal per conferma eliminazione -->
<div id="delete-tournament-modal" class="scacchitrack-modal" style="display: none;">
    <div class="scacchitrack-modal-content">
        <h3><?php _e('Elimina Torneo', 'scacchitrack'); ?></h3>
        <p><?php _e('Sei sicuro di voler eliminare questo torneo e tutte le sue partite?', 'scacchitrack'); ?></p>
        <form method="post">
            <?php wp_nonce_field('scacchitrack_tournament_action'); ?>
            <input type="hidden" name="action" value="delete_tournament">
            <input type="hidden" name="tournament_name" id="delete_tournament_name">
            
            <p>
                <input type="submit" 
                       class="button button-primary" 
                       value="<?php esc_attr_e('Elimina', 'scacchitrack'); ?>">
                <button type="button" 
                        class="button modal-close">
                    <?php _e('Annulla', 'scacchitrack'); ?>
                </button>
            </p>
        </form>
    </div>
</div>

<style>
.scacchitrack-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.scacchitrack-modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    max-width: 500px;
    border-radius: 4px;
}

.button + .button {
    margin-left: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Gestione modal rinomina
    $('.rename-tournament').on('click', function() {
        var tournamentName = $(this).data('tournament');
        $('#old_tournament_name').val(tournamentName);
        $('#new_tournament_name').val(tournamentName);
        $('#rename-tournament-modal').show();
    });
    
    // Gestione modal elimina
    $('.delete-tournament').on('click', function() {
        var tournamentName = $(this).data('tournament');
        $('#delete_tournament_name').val(tournamentName);
        $('#delete-tournament-modal').show();
    });
    
    // Chiusura modals
    $('.modal-close').on('click', function() {
        $('.scacchitrack-modal').hide();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('scacchitrack-modal')) {
            $('.scacchitrack-modal').hide();
        }
    });
});
</script>