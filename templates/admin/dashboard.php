<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupera le statistiche complete
$game_stats = get_scacchitrack_statistics();
$post_counts = wp_count_posts('scacchipartita');
$total_games = $post_counts->publish + $post_counts->draft + $post_counts->pending;

$stats = array(
    'total_games' => $post_counts->publish,
    'draft_games' => $post_counts->draft,
    'pending_games' => $post_counts->pending,
    'total_tournaments' => $game_stats['total_tournaments'],
    'unique_players' => $game_stats['unique_players'],
    'recent_games' => get_posts(array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish'
    )),
    'recent_tournaments' => array_slice($game_stats['tournament_stats'], 0, 5),
    'top_players' => array_slice($game_stats['top_players'], 0, 5)
);

// Calcola ultimo aggiornamento
$last_game = get_posts(array(
    'post_type' => 'scacchipartita',
    'posts_per_page' => 1,
    'orderby' => 'modified',
    'order' => 'DESC'
));
$last_update = !empty($last_game) ? human_time_diff(strtotime($last_game[0]->post_modified), current_time('timestamp')) : 'mai';
?>

<div class="dashboard-widgets">
    <!-- Widget Statistiche Rapide -->
    <div class="dashboard-widget">
        <h3><?php _e('Statistiche Rapide', 'scacchitrack'); ?></h3>
        <ul>
            <li>
                <?php printf(
                    __('Partite Pubblicate: %d', 'scacchitrack'),
                    $stats['total_games']
                ); ?>
            </li>
            <li>
                <?php printf(
                    __('Bozze: %d', 'scacchitrack'),
                    $stats['draft_games']
                ); ?>
            </li>
            <li>
                <?php printf(
                    __('In Attesa: %d', 'scacchitrack'),
                    $stats['pending_games']
                ); ?>
            </li>
            <li>
                <?php printf(
                    __('Tornei: %d', 'scacchitrack'),
                    $stats['total_tournaments']
                ); ?>
            </li>
            <li>
                <?php printf(
                    __('Giocatori Unici: %d', 'scacchitrack'),
                    $stats['unique_players']
                ); ?>
            </li>
            <li>
                <?php printf(
                    __('Ultimo Aggiornamento: %s fa', 'scacchitrack'),
                    $last_update
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

    <!-- Widget Top Giocatori -->
    <div class="dashboard-widget">
        <h3><?php _e('Top Giocatori', 'scacchitrack'); ?></h3>
        <?php if (!empty($stats['top_players'])) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Giocatore', 'scacchitrack'); ?></th>
                        <th><?php _e('Partite', 'scacchitrack'); ?></th>
                        <th><?php _e('Vittorie', 'scacchitrack'); ?></th>
                        <th><?php _e('Win %', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['top_players'] as $player) : ?>
                        <tr>
                            <td><?php echo esc_html($player['name']); ?></td>
                            <td><?php echo intval($player['total']); ?></td>
                            <td><?php echo intval($player['wins']); ?></td>
                            <td><?php echo number_format($player['win_percentage'], 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e('Nessun giocatore trovato.', 'scacchitrack'); ?></p>
        <?php endif; ?>
    </div>

    <!-- Widget Tornei Recenti -->
    <div class="dashboard-widget">
        <h3><?php _e('Tornei Recenti', 'scacchitrack'); ?></h3>
        <?php if (!empty($stats['recent_tournaments'])) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Torneo', 'scacchitrack'); ?></th>
                        <th><?php _e('Partite', 'scacchitrack'); ?></th>
                        <th><?php _e('Data', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_tournaments'] as $tournament) : ?>
                        <tr>
                            <td><?php echo esc_html($tournament['name']); ?></td>
                            <td><?php echo intval($tournament['total_games']); ?></td>
                            <td><?php echo esc_html(mysql2date(get_option('date_format'), $tournament['last_game_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e('Nessun torneo trovato.', 'scacchitrack'); ?></p>
        <?php endif; ?>
    </div>
</div>