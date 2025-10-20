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

// Registrazione Tassonomie
function scacchitrack_register_taxonomies() {
    // Categoria: Aperture
    $labels_aperture = array(
        'name'              => __('Aperture', 'scacchitrack'),
        'singular_name'     => __('Apertura', 'scacchitrack'),
        'search_items'      => __('Cerca Aperture', 'scacchitrack'),
        'all_items'         => __('Tutte le Aperture', 'scacchitrack'),
        'parent_item'       => __('Apertura Genitore', 'scacchitrack'),
        'parent_item_colon' => __('Apertura Genitore:', 'scacchitrack'),
        'edit_item'         => __('Modifica Apertura', 'scacchitrack'),
        'update_item'       => __('Aggiorna Apertura', 'scacchitrack'),
        'add_new_item'      => __('Aggiungi Nuova Apertura', 'scacchitrack'),
        'new_item_name'     => __('Nome Nuova Apertura', 'scacchitrack'),
        'menu_name'         => __('Aperture', 'scacchitrack'),
    );

    $args_aperture = array(
        'hierarchical'      => true,
        'labels'            => $labels_aperture,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'apertura'),
        'show_in_rest'      => true,
    );

    register_taxonomy('apertura_scacchi', 'scacchipartita', $args_aperture);

    // Categoria: Tipo Partita
    $labels_tipo = array(
        'name'              => __('Tipi di Partita', 'scacchitrack'),
        'singular_name'     => __('Tipo di Partita', 'scacchitrack'),
        'search_items'      => __('Cerca Tipi', 'scacchitrack'),
        'all_items'         => __('Tutti i Tipi', 'scacchitrack'),
        'parent_item'       => __('Tipo Genitore', 'scacchitrack'),
        'parent_item_colon' => __('Tipo Genitore:', 'scacchitrack'),
        'edit_item'         => __('Modifica Tipo', 'scacchitrack'),
        'update_item'       => __('Aggiorna Tipo', 'scacchitrack'),
        'add_new_item'      => __('Aggiungi Nuovo Tipo', 'scacchitrack'),
        'new_item_name'     => __('Nome Nuovo Tipo', 'scacchitrack'),
        'menu_name'         => __('Tipi di Partita', 'scacchitrack'),
    );

    $args_tipo = array(
        'hierarchical'      => true,
        'labels'            => $labels_tipo,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'tipo-partita'),
        'show_in_rest'      => true,
    );

    register_taxonomy('tipo_partita', 'scacchipartita', $args_tipo);

    // Tag
    $labels_tags = array(
        'name'                       => __('Etichette Partite', 'scacchitrack'),
        'singular_name'              => __('Etichetta', 'scacchitrack'),
        'search_items'               => __('Cerca Etichette', 'scacchitrack'),
        'popular_items'              => __('Etichette Popolari', 'scacchitrack'),
        'all_items'                  => __('Tutte le Etichette', 'scacchitrack'),
        'edit_item'                  => __('Modifica Etichetta', 'scacchitrack'),
        'update_item'                => __('Aggiorna Etichetta', 'scacchitrack'),
        'add_new_item'               => __('Aggiungi Nuova Etichetta', 'scacchitrack'),
        'new_item_name'              => __('Nome Nuova Etichetta', 'scacchitrack'),
        'separate_items_with_commas' => __('Separa le etichette con virgole', 'scacchitrack'),
        'add_or_remove_items'        => __('Aggiungi o rimuovi etichette', 'scacchitrack'),
        'choose_from_most_used'      => __('Scegli dalle più usate', 'scacchitrack'),
        'menu_name'                  => __('Etichette', 'scacchitrack'),
    );

    $args_tags = array(
        'hierarchical'          => false,
        'labels'                => $labels_tags,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array('slug' => 'etichetta-partita'),
        'show_in_rest'          => true,
    );

    register_taxonomy('etichetta_partita', 'scacchipartita', $args_tags);
}
add_action('init', 'scacchitrack_register_taxonomies');