<?php
if ($query->have_posts()) : 
    while ($query->have_posts()) : $query->the_post();
        include SCACCHITRACK_DIR . 'templates/partita-item.php';
    endwhile;
else : ?>
    <tr>
        <td colspan="7"><?php _e('Nessuna partita trovata.', 'scacchitrack'); ?></td>
    </tr>
<?php 
endif;
wp_reset_postdata();
?>