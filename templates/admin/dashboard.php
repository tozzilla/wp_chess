<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupera le statistiche
$stats = array(
    'total_games' => wp_count_posts('scacchipartita')->publish,
    'recent_games' => get_posts(array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC'
    )),
    'tournaments' => get_unique_tournament_names()
);
?>

<div class="dashboard-widgets">
    <!-- Widget Statistiche Rapide -->
    <div class="dashboard-widget">
        <h3><?php _e('Statistiche Rapide', 'scacchitrack'); ?></h3>
        <ul>
            <li>
                <?php printf(
                    __('Totale Partite: %d', 'scacchitrack'),
                    $stats['total_games']
                ); ?>
            </li>
            <li>
                <?php printf(
                    __('Tornei: %d', 'scacchitrack'),
                    count($stats['tournaments'])
                ); ?>
            </li>
        </ul>
    </div>

    <!-- Widget Ultime Partite -->
    <div class="dashboard-widget">
        <h3><?php _e('Ultime Partite', 'scacchitrack'); ?></h3>
        <?php if (!empty($stats['recent_games'])) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Data', 'scacchitrack'); ?></th>
                        <th><?php _e('Bianco', 'scacchitrack'); ?></th>
                        <th><?php _e('Nero', 'scacchitrack'); ?></th>
                        <th><?php _e('Risultato', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_games'] as $game) : ?>
                        <tr>
                            <td>
                                <?php echo get_the_date('', $game->ID); ?>
                            </td>
                            <td>
                                <?php echo esc_html(get_post_meta($game->ID, '_giocatore_bianco', true)); ?>
                            </td>
                            <td>
                                <?php echo esc_html(get_post_meta($game->ID, '_giocatore_nero', true)); ?>
                            </td>
                            <td>
                                <?php echo esc_html(get_post_meta($game->ID, '_risultato', true)); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e('Nessuna partita trovata.', 'scacchitrack'); ?></p>
        <?php endif; ?>
    </div>

    <!-- Widget Scorciatoie -->
    <div class="dashboard-widget">
        <h3><?php _e('Scorciatoie', 'scacchitrack'); ?></h3>
        <p>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=scacchipartita')); ?>" class="button button-primary">
                <?php _e('Aggiungi Nuova Partita', 'scacchitrack'); ?>
            </a>
        </p>
        <p>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=scacchipartita')); ?>" class="button">
                <?php _e('Gestisci Partite', 'scacchitrack'); ?>
            </a>
        </p>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=scacchitrack&tab=import')); ?>" class="button">
                <?php _e('Importa Partite', 'scacchitrack'); ?>
            </a>
        </p>
    </div>

    <!-- Widget Guida Rapida -->
    <div class="dashboard-widget">
        <h3><?php _e('Guida Rapida', 'scacchitrack'); ?></h3>
        <p><?php _e('Per iniziare:', 'scacchitrack'); ?></p>
        <ol>
            <li><?php _e('Aggiungi una nuova partita dal menu "Aggiungi Nuova Partita"', 'scacchitrack'); ?></li>
            <li><?php _e('Inserisci i dettagli della partita e il PGN', 'scacchitrack'); ?></li>
            <li><?php _e('Usa lo shortcode [scacchitrack_partite] per visualizzare la lista delle partite', 'scacchitrack'); ?></li>
            <li><?php _e('Usa lo shortcode [scacchitrack_partita id="X"] per visualizzare una singola partita', 'scacchitrack'); ?></li>
        </ol>
    </div>
</div>