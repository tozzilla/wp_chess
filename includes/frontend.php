<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestisce la visualizzazione frontend delle partite
 */
class ScacchiTrack_Frontend_Display {
    
    private $is_processing = false;
    
    public function __construct() {
        add_filter('template_include', array($this, 'load_partita_template'));
        add_filter('the_content', array($this, 'filter_partita_content'));
        add_action('init', array($this, 'handle_login'));
        add_action('wp_logout', array($this, 'clear_scacchitrack_session'));
        
        // Rimozione sidebar
        add_filter('body_class', array($this, 'manage_body_classes'));
        add_action('wp', array($this, 'remove_sidebar_areas'));
    }

    /**
     * Verifica se l'accesso è consentito
     */
    public function is_access_allowed() {
        // Se la protezione non è attiva, consenti sempre
        if (!get_option('scacchitrack_password_protection')) {
            return true;
        }

        // Verifica la sessione
        if (isset($_SESSION['scacchitrack_access']) && $_SESSION['scacchitrack_access'] === true) {
            return true;
        }

        return false;
    }

    /**
     * Gestisce il login
     */
    public function handle_login() {
        if (!session_id()) {
            session_start();
        }

        if (isset($_POST['scacchitrack_login_submit'])) {
            if (!wp_verify_nonce($_POST['scacchitrack_login_nonce'], 'scacchitrack_login')) {
                wp_die('Verifica di sicurezza fallita');
            }

            $submitted_password = $_POST['scacchitrack_password'];
            $stored_password = get_option('scacchitrack_access_password');

            if (wp_check_password($submitted_password, $stored_password)) {
                $_SESSION['scacchitrack_access'] = true;
                wp_redirect(remove_query_arg('login_error'));
                exit;
            } else {
                wp_redirect(add_query_arg('login_error', '1'));
                exit;
            }
        }
    }

    /**
     * Pulisce la sessione al logout
     */
    public function clear_scacchitrack_session() {
        if (session_id()) {
            unset($_SESSION['scacchitrack_access']);
        }
    }

    /**
     * Carica il template personalizzato per le partite
     */
    public function load_partita_template($template) {
        if (is_singular('scacchipartita')) {
            // Se è richiesta la password e non siamo autenticati, mostra il form di login
            if (!$this->is_access_allowed()) {
                return SCACCHITRACK_DIR . 'templates/login-form.php';
            }
            
            $custom_template = SCACCHITRACK_DIR . 'templates/single-scacchipartita.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    /**
     * Filtra il contenuto per aggiungere i dettagli della partita
     */
    public function filter_partita_content($content) {
        if ($this->is_processing) {
            return $content;
        }

        // Verifica se è una partita o contiene lo shortcode
        if ((is_singular('scacchipartita') || has_shortcode($content, 'scacchitrack_partite')) && !$this->is_access_allowed()) {
            ob_start();
            include SCACCHITRACK_DIR . 'templates/login-form.php';
            return ob_get_clean();
        }

        if (is_singular('scacchipartita') && in_the_loop() && is_main_query()) {
            $this->is_processing = true;
            
            $template_path = SCACCHITRACK_DIR . 'templates/content-partita.php';
            
            if (file_exists($template_path)) {
                ob_start();
                include $template_path;
                $new_content = ob_get_clean();
                $this->is_processing = false;
                return $new_content;
            }
            
            $this->is_processing = false;
        }
        
        return $content;
    }

    /**
     * Gestisce le classi del body per il layout a larghezza piena
     */
    public function manage_body_classes($classes) {
        if (is_singular('scacchipartita')) {
            // Aggiungi classi per layout a larghezza piena
            $classes[] = 'no-sidebar';
            $classes[] = 'full-width';
            
            // Rimuovi classi relative alle sidebar
            $classes = array_diff($classes, array(
                'has-sidebar',
                'has-right-sidebar',
                'has-left-sidebar'
            ));
        }
        return $classes;
    }

    /**
     * Rimuove le sidebar e forza il layout a larghezza piena
     */
    public function remove_sidebar_areas() {
        if (is_singular('scacchipartita')) {
            // Rimuovi le sidebar
            remove_all_actions('get_sidebar');
            remove_all_actions('get_sidebar_alt');
            
            // Disattiva tutte le aree widget delle sidebar
            add_filter('is_active_sidebar', '__return_false');
        }
    }
}

// Inizializza la classe
global $scacchitrack_frontend;
$scacchitrack_frontend = new ScacchiTrack_Frontend_Display();