<?php
/*
Plugin Name: ScacchiTrack
Plugin URI: https://connecta.app
Description: Plugin per caricare, gestire e visualizzare partite di scacchi con scacchiera interattiva.
Version: 0.9e
Author: Andrea Tozzi per Empoli Scacchi ASD
Author URI: https://connecta.app
License: GPL2
Text Domain: scacchitrack
*/

// Previeni l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Aumenta il limite di memoria se necessario
if (WP_DEBUG) {
    $current = wp_convert_hr_to_bytes( ini_get('memory_limit') );
    $wp_max = wp_convert_hr_to_bytes( WP_MAX_MEMORY_LIMIT );
    $needed = 268435456; // 256MB
    if ($current < $needed && (!$wp_max || $wp_max > $needed)) {
        ini_set('memory_limit', '256M');
    }
}

// Definizione costanti
define('SCACCHITRACK_VERSION', '1.0.0');
define('SCACCHITRACK_DIR', plugin_dir_path(__FILE__));
define('SCACCHITRACK_URL', plugin_dir_url(__FILE__));

// Caricamento dei file necessari
require_once SCACCHITRACK_DIR . 'includes/functions.php';
require_once SCACCHITRACK_DIR . 'includes/cpt.php';
require_once SCACCHITRACK_DIR . 'includes/metaboxes.php';
require_once SCACCHITRACK_DIR . 'includes/shortcodes.php';
require_once SCACCHITRACK_DIR . 'includes/admin-columns.php';
require_once SCACCHITRACK_DIR . 'includes/admin-enhancements.php';
require_once SCACCHITRACK_DIR . 'includes/scripts.php';
require_once SCACCHITRACK_DIR . 'includes/frontend.php';
require_once SCACCHITRACK_DIR . 'includes/ajax.php'; 

// Attivazione del plugin
function scacchitrack_activate() {
    // Aggiungi le capabilities
    scacchitrack_add_capabilities();
    
    // Registra il CPT
    scacchitrack_register_cpt();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'scacchitrack_activate');

// Disattivazione del plugin
function scacchitrack_deactivate() {
    // Rimuovi le capabilities
    scacchitrack_remove_capabilities();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'scacchitrack_deactivate');

// Inizializzazione del plugin
function scacchitrack_init() {
    // Carica il dominio di traduzione
    load_plugin_textdomain('scacchitrack', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'scacchitrack_init');

// Aggiungi menu amministratore
function scacchitrack_admin_menu() {
    add_menu_page(
        __('ScacchiTrack', 'scacchitrack'),
        __('ScacchiTrack', 'scacchitrack'),
        'edit_posts',
        'scacchitrack',
        'scacchitrack_admin_page',
        'dashicons-games',
        20
    );
}
add_action('admin_menu', 'scacchitrack_admin_menu');

// Pagina amministratore
function scacchitrack_admin_page() {
    include SCACCHITRACK_DIR . 'templates/admin-page.php';
}