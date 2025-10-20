<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupera le impostazioni correnti
$settings = get_option('scacchitrack_settings', array(
    'partite_per_pagina' => 10,
    'tema_scacchiera' => 'default',
    'animazioni' => true,
    'notazione_algebrica' => true,
    'commenti_abilitati' => true
));

// Gestione del salvataggio delle impostazioni
if (isset($_POST['scacchitrack_save_settings'])) {
    check_admin_referer('scacchitrack_settings');

    // Salva le impostazioni esistenti
    $settings = array(
        'partite_per_pagina' => absint($_POST['partite_per_pagina']),
        'tema_scacchiera' => sanitize_text_field($_POST['tema_scacchiera']),
        'animazioni' => isset($_POST['animazioni']),
        'notazione_algebrica' => isset($_POST['notazione_algebrica']),
        'commenti_abilitati' => isset($_POST['commenti_abilitati'])
    );
    update_option('scacchitrack_settings', $settings);

    // Salva le nuove impostazioni di protezione
    update_option('scacchitrack_password_protection', isset($_POST['password_protection']));

    if (isset($_POST['access_password']) && !empty($_POST['access_password'])) {
        update_option('scacchitrack_access_password', wp_hash_password($_POST['access_password']));
    }

    add_settings_error(
        'scacchitrack_messages',
        'scacchitrack_message',
        __('Impostazioni salvate con successo.', 'scacchitrack'),
        'updated'
    );
}
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

            <tr>
        <th scope="row"><?php _e('Protezione Contenuti', 'scacchitrack'); ?></th>
        <td>
            <fieldset>
                <label for="password_protection">
                    <input type="checkbox" 
                           id="password_protection" 
                           name="password_protection" 
                           value="1" 
                           <?php checked(get_option('scacchitrack_password_protection')); ?>>
                    <?php _e('Proteggi la lista delle partite con password', 'scacchitrack'); ?>
                </label>
            </fieldset>
        </td>
    </tr>

    <tr class="password-settings" style="display: <?php echo get_option('scacchitrack_password_protection') ? 'table-row' : 'none'; ?>;">
        <th scope="row">
            <label for="access_password">
                <?php _e('Password di Accesso', 'scacchitrack'); ?>
            </label>
        </th>
        <td>
            <input type="password"
                   id="access_password"
                   name="access_password"
                   value=""
                   class="regular-text"
                   placeholder="<?php echo get_option('scacchitrack_access_password') ? esc_attr__('Inserisci nuova password per cambiarla', 'scacchitrack') : esc_attr__('Inserisci password', 'scacchitrack'); ?>">
            <p class="description">
                <?php
                if (get_option('scacchitrack_access_password')) {
                    _e('Lascia vuoto per mantenere la password attuale. Inserisci una nuova password per cambiarla.', 'scacchitrack');
                } else {
                    _e('Imposta la password per accedere alla lista delle partite.', 'scacchitrack');
                }
                ?>
            </p>
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

<script>
jQuery(document).ready(function($) {
    // Mostra/nascondi il campo password in base allo stato del checkbox
    $('#password_protection').on('change', function() {
        if ($(this).is(':checked')) {
            $('.password-settings').show();
        } else {
            $('.password-settings').hide();
        }
    });
});
</script>