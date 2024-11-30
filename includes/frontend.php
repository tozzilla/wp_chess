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
    }

    /**
     * Carica il template personalizzato per le partite
     */
    public function load_partita_template($template) {
        if (is_singular('scacchipartita')) {
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
        // Previene la ricorsione infinita
        if ($this->is_processing) {
            return $content;
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
}

// Inizializza la classe
global $scacchitrack_frontend;
$scacchitrack_frontend = new ScacchiTrack_Frontend_Display();