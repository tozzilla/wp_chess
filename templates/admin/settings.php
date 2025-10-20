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
    'commenti_abilitati' => true,
    'evaluation_enabled' => false,
    'evaluation_mode' => 'simple',
    'evaluation_depth' => 15
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
        'commenti_abilitati' => isset($_POST['commenti_abilitati']),
        'evaluation_enabled' => isset($_POST['evaluation_enabled']),
        'evaluation_mode' => sanitize_text_field($_POST['evaluation_mode']),
        'evaluation_depth' => absint($_POST['evaluation_depth'])
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
                <th scope="row"><?php _e('Valutazione Posizione', 'scacchitrack'); ?></th>
                <td>
                    <fieldset>
                        <label for="evaluation_enabled">
                            <input type="checkbox"
                                   id="evaluation_enabled"
                                   name="evaluation_enabled"
                                   <?php checked($settings['evaluation_enabled']); ?>>
                            <?php _e('Abilita valutazione della posizione', 'scacchitrack'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Mostra una barra di valutazione che indica il vantaggio di bianco o nero durante la riproduzione delle partite.', 'scacchitrack'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>

            <tr class="evaluation-settings" style="display: <?php echo $settings['evaluation_enabled'] ? 'table-row' : 'none'; ?>;">
                <th scope="row">
                    <label for="evaluation_mode">
                        <?php _e('Modalità di Valutazione', 'scacchitrack'); ?>
                    </label>
                </th>
                <td>
                    <select id="evaluation_mode" name="evaluation_mode">
                        <option value="simple" <?php selected($settings['evaluation_mode'], 'simple'); ?>>
                            <?php _e('Semplice (Materiale)', 'scacchitrack'); ?>
                        </option>
                        <option value="advanced" <?php selected($settings['evaluation_mode'], 'advanced'); ?>>
                            <?php _e('Avanzata (Stockfish - Richiede più risorse)', 'scacchitrack'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Semplice: valutazione rapida basata sul materiale e posizione. Avanzata: usa il motore Stockfish per valutazioni più accurate (può rallentare su dispositivi meno potenti).', 'scacchitrack'); ?>
                    </p>
                </td>
            </tr>

            <tr class="evaluation-settings evaluation-depth-settings" style="display: <?php echo ($settings['evaluation_enabled'] && $settings['evaluation_mode'] === 'advanced') ? 'table-row' : 'none'; ?>;">
                <th scope="row">
                    <label for="evaluation_depth">
                        <?php _e('Profondità Analisi', 'scacchitrack'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                           id="evaluation_depth"
                           name="evaluation_depth"
                           value="<?php echo esc_attr($settings['evaluation_depth']); ?>"
                           min="5"
                           max="20"
                           class="small-text">
                    <p class="description">
                        <?php _e('Profondità di analisi di Stockfish (5-20). Valori più alti danno valutazioni più precise ma richiedono più tempo. Consigliato: 15.', 'scacchitrack'); ?>
                    </p>
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

    // Mostra/nascondi i campi di valutazione
    $('#evaluation_enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('.evaluation-settings').show();
            updateDepthVisibility();
        } else {
            $('.evaluation-settings').hide();
        }
    });

    // Mostra/nascondi il campo profondità in base alla modalità
    $('#evaluation_mode').on('change', function() {
        updateDepthVisibility();
    });

    function updateDepthVisibility() {
        if ($('#evaluation_enabled').is(':checked') && $('#evaluation_mode').val() === 'advanced') {
            $('.evaluation-depth-settings').show();
        } else {
            $('.evaluation-depth-settings').hide();
        }
    }
});
</script>