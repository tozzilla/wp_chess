<?php
if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
$data_partita = get_post_meta($post_id, '_data_partita', true);
$giocatore_bianco = get_post_meta($post_id, '_giocatore_bianco', true);
$giocatore_nero = get_post_meta($post_id, '_giocatore_nero', true);
$risultato = get_post_meta($post_id, '_risultato', true);
$nome_torneo = get_post_meta($post_id, '_nome_torneo', true);

error_log("Rendering partita ID: $post_id");
error_log("Meta values: " . print_r(get_post_meta($post_id), true));
?>
<tr>
    <td><?php echo $data_partita ? esc_html(date_i18n(get_option('date_format'), strtotime($data_partita))) : ''; ?></td>
    <td><?php echo esc_html($giocatore_bianco); ?></td>
    <td><?php echo esc_html($giocatore_nero); ?></td>
    <td><?php echo esc_html($risultato); ?></td>
    <td><?php echo esc_html($nome_torneo); ?></td>
    <td>
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="button">
            <?php _e('Visualizza', 'scacchitrack'); ?>
        </a>
    </td>
</tr>
<?php
error_log("Rendered partita ID: $post_id");
?>