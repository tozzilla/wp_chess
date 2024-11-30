<?php
if (!defined('ABSPATH')) {
    exit;
}

// Salva le impostazioni
if (isset($_POST['scacchitrack_save_settings'])) {
    check_admin_referer('scacchitrack_settings');
    
    $settings = array(
        'partite_per_pagina' => absint($_POST['partite_per_pagina']),
        'tema_scacchiera' => sanitize_text_field($_POST['tema_scacchiera']),
        'animazioni' => isset($_POST['animazioni']),
        'notazione_algebrica' => isset($_POST['notazione_algebrica']),
        'commenti_abilitati' => isset($_POST['commenti_abilitati'])
    );
    
    update_option('scacchitrack_settings', $settings);
    add_settings_error(
        'scacchitrack_messages',
        'scacchitrack_message',
        __('Impostazioni salvate con successo.', 'scacchitrack'),
        'updated'
    );
}

// Recupera le impostazioni correnti
$settings = get_option('scacchitrack_settings', array(
    'partite_per_pagina' => 10,
    'tema_scacchiera' => 'default',
    'animazioni' => true,
    'notazione_algebrica' => true,
    'commenti_abilitati' => true
));
?>

<div class="scacchitrack-settings">
    <?php settings_errors('scacchitrack_messages'); ?>

    <form method="post" action="">
        <?php wp_nonce_field('scacchitrack_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="partite_per_pagina">
                        <?php _e('Partite per Pagina', 'scacchitrack'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" 
                           id="partite_per_pagina" 
                           name="partite_per_pagina" 
                           value="<?php echo esc_attr($settings['partite_per_pagina']); ?>" 
                           min="1" 
                           max="100" 
                           class="small-text">
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="tema_scacchiera">
                        <?php _e('Tema Scacchiera', 'scacchitrack'); ?>
                    </label>
                </th>
                <td>
                    <select id="tema_scacchiera" name="tema_scacchiera">
                        <option value="default" <?php selected($settings['tema_scacchiera'], 'default'); ?>>
                            <?php _e('Default', 'scacchitrack'); ?>
                        </option>
                        <option value="blue" <?php selected($settings['tema_scacchiera'], 'blue'); ?>>
                            <?php _e('Blu', 'scacchitrack'); ?>
                        </option>
                        <option value="green" <?php selected($settings['tema_scacchiera'], 'green'); ?>>
                            <?php _e('Verde', 'scacchitrack'); ?>
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Opzioni Visualizzazione', 'scacchitrack'); ?></th>
                <td>
                    <fieldset>
                        <label for="animazioni">
                            <input type="checkbox" 
                                   id="animazioni" 
                                   name="animazioni" 
                                   <?php checked($settings['animazioni']); ?>>
                            <?php _e('Abilita animazioni pezzi', 'scacchitrack'); ?>
                        </label>
                        <br>
                        <label for="notazione_algebrica">
                            <input type="checkbox" 
                                   id="notazione_algebrica" 
                                   name="notazione_algebrica" 
                                   <?php checked($settings['notazione_algebrica']); ?>>
                            <?php _e('Mostra notazione algebrica', 'scacchitrack'); ?>
                        </label>
                        <br>
                        <label for="commenti_abilitati">
                            <input type="checkbox" 
                                   id="commenti_abilitati" 
                                   name="commenti_abilitati" 
                                   <?php checked($settings['commenti_abilitati']); ?>>
                            <?php _e('Abilita commenti sulle partite', 'scacchitrack'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" 
                   name="scacchitrack_save_settings" 
                   class="button button-primary" 
                   value="<?php esc_attr_e('Salva Impostazioni', 'scacchitrack'); ?>">
        </p>
    </form>
</div>