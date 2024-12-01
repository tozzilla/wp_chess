<?php
$post_id = get_the_ID();
$data = get_post_meta($post_id, '_data_partita', true);
$bianco = get_post_meta($post_id, '_giocatore_bianco', true);
$nero = get_post_meta($post_id, '_giocatore_nero', true);
$risultato = get_post_meta($post_id, '_risultato', true);
$torneo = get_post_meta($post_id, '_nome_torneo', true);
$round = get_post_meta($post_id, '_round', true); // Nuovo campo

error_log("Rendering partita ID: $post_id");
?>
<tr>
    <td><?php echo $data ? esc_html(date_i18n(get_option('date_format'), strtotime($data))) : ''; ?></td>
    <td><?php echo esc_html($bianco); ?></td>
    <td><?php echo esc_html($nero); ?></td>
    <td><?php echo esc_html($torneo); ?></td>
    <td><?php echo $round ? esc_html($round) : '-'; ?></td><!-- Nuova colonna -->
    <td class="risultato-<?php echo sanitize_html_class($risultato); ?>">
        <?php echo esc_html($risultato); ?>
    </td>
    <td>
        <a href="<?php the_permalink(); ?>" class="button button-small">
            <?php _e('Visualizza', 'scacchitrack'); ?>
        </a>
    </td>
</tr>