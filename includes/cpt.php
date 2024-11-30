<?php
if (!defined('ABSPATH')) {
    exit;
}

// Registrazione Custom Post Type
function scacchitrack_register_cpt() {
    $labels = array(
        'name'               => __('Partite di Scacchi', 'scacchitrack'),
        'singular_name'      => __('Partita di Scacchi', 'scacchitrack'),
        'menu_name'          => __('ScacchiTrack', 'scacchitrack'),
        'add_new'            => __('Aggiungi Nuova', 'scacchitrack'),
        'add_new_item'       => __('Aggiungi Nuova Partita', 'scacchitrack'),
        'edit_item'          => __('Modifica Partita', 'scacchitrack'),
        'new_item'           => __('Nuova Partita', 'scacchitrack'),
        'view_item'          => __('Visualizza Partita', 'scacchitrack'),
        'search_items'       => __('Cerca Partite', 'scacchitrack'),
        'not_found'          => __('Nessuna partita trovata', 'scacchitrack'),
        'not_found_in_trash' => __('Nessuna partita nel cestino', 'scacchitrack')
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'partita-scacchi'),
        'capability_type'     => array('partita', 'partite'),
        'map_meta_cap'        => true,
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 20,
        'supports'            => array('title'),
        'show_in_rest'        => true
    );

    register_post_type('scacchipartita', $args);
}
add_action('init', 'scacchitrack_register_cpt');

// Gestione delle capabilities
function scacchitrack_add_capabilities() {
    $roles = array('administrator', 'editor');
    
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if (!$role) continue;

        // Capabilities per il post type
        $role->add_cap('read_partita');
        $role->add_cap('read_private_partite');
        $role->add_cap('edit_partita');
        $role->add_cap('edit_partite');
        $role->add_cap('edit_others_partite');
        $role->add_cap('edit_published_partite');
        $role->add_cap('publish_partite');
        $role->add_cap('delete_partite');
        $role->add_cap('delete_others_partite');
        $role->add_cap('delete_private_partite');
        $role->add_cap('delete_published_partite');
    }
}

function scacchitrack_remove_capabilities() {
    $roles = array('administrator', 'editor');
    
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if (!$role) continue;

        // Rimuovi capabilities
        $role->remove_cap('read_partita');
        $role->remove_cap('read_private_partite');
        $role->remove_cap('edit_partita');
        $role->remove_cap('edit_partite');
        $role->remove_cap('edit_others_partite');
        $role->remove_cap('edit_published_partite');
        $role->remove_cap('publish_partite');
        $role->remove_cap('delete_partite');
        $role->remove_cap('delete_others_partite');
        $role->remove_cap('delete_private_partite');
        $role->remove_cap('delete_published_partite');
    }
}