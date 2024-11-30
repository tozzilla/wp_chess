<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php if ($query->have_posts()) : ?>
    <table class="scacchitrack-table">
        <thead>
            <tr>
                <th><?php _e('Data', 'scacchitrack'); ?></th>
                <th><?php _e('Bianco', 'scacchitrack'); ?></th>
                <th><?php _e('Nero', 'scacchitrack'); ?></th>
                <th><?php _e('Risultato', 'scacchitrack'); ?></th>
                <th><?php _e('Torneo', 'scacchitrack'); ?></th>
                <th><?php _e('Azioni', 'scacchitrack'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php
            $data = get_post_meta(get_the_ID(), '_data_partita', true);
            $bianco = get_post_meta(get_the_ID(), '_giocatore_bianco', true);
            $nero = get_post_meta(get_the_ID(), '_giocatore_nero', true);
            $risultato = get_post_meta(get_the_ID(), '_risultato', true);
            $torneo = get_post_meta(get_the_ID(), '_nome_torneo', true);
            ?>
            <tr>
                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data))); ?></td>
                <td><?php echo esc_html($bianco); ?></td>
                <td><?php echo esc_html($nero); ?></td>
                <td class="risultato-<?php echo sanitize_html_class($risultato); ?>">
                    <?php echo esc_html($risultato); ?>
                </td>
                <td><?php echo esc_html($torneo); ?></td>
                <td>
                    <a href="<?php the_permalink(); ?>" class="button button-small">
                        <?php _e('Visualizza', 'scacchitrack'); ?>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else : ?>
    <p class="scacchitrack-no-results">
        <?php _e('Nessuna partita trovata.', 'scacchitrack'); ?>
    </p>
<?php endif; ?>
<?php wp_reset_postdata(); ?>