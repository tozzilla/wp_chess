<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupero dei metadati della partita
$post_id = get_the_ID();
$giocatore_bianco = get_post_meta($post_id, '_giocatore_bianco', true);
$giocatore_nero = get_post_meta($post_id, '_giocatore_nero', true);
$data_partita = get_post_meta($post_id, '_data_partita', true);
$nome_torneo = get_post_meta($post_id, '_nome_torneo', true);
$pgn = get_post_meta($post_id, '_pgn', true);
$risultato = get_post_meta($post_id, '_risultato', true);
?>

<div class="scacchitrack-single-partita">
    <!-- Intestazione della partita -->
    <header class="partita-header">
        <h2 class="partita-title">
            <?php echo esc_html($giocatore_bianco); ?> 
            <span class="vs">vs</span> 
            <?php echo esc_html($giocatore_nero); ?>
        </h2>
        
        <div class="partita-meta">
            <?php if ($data_partita) : ?>
            <div class="meta-item">
                <span class="meta-label"><?php _e('Data:', 'scacchitrack'); ?></span>
                <span class="meta-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data_partita))); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($nome_torneo) : ?>
            <div class="meta-item">
                <span class="meta-label"><?php _e('Torneo:', 'scacchitrack'); ?></span>
                <span class="meta-value"><?php echo esc_html($nome_torneo); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($risultato) : ?>
            <div class="meta-item">
                <span class="meta-label"><?php _e('Risultato:', 'scacchitrack'); ?></span>
                <span class="meta-value risultato-<?php echo sanitize_html_class($risultato); ?>">
                    <?php echo esc_html($risultato); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Contenitore principale della scacchiera e dei controlli -->
    <div class="partita-container">
        <!-- Pannello di sinistra con la scacchiera -->
        <div class="scacchiera-container">
            <div id="scacchiera" class="scacchiera"></div>
            
            <!-- Controlli della scacchiera -->
            <div class="scacchiera-controlli">
                <button class="button control-button" id="startBtn" title="<?php esc_attr_e('Vai all\'inizio', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-skipback"></span>
                </button>
                <button class="button control-button" id="prevBtn" title="<?php esc_attr_e('Mossa precedente', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-back"></span>
                </button>
                <button class="button control-button" id="playBtn" title="<?php esc_attr_e('Riproduci/Pausa', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-play"></span>
                </button>
                <button class="button control-button" id="nextBtn" title="<?php esc_attr_e('Mossa successiva', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-forward"></span>
                </button>
                <button class="button control-button" id="endBtn" title="<?php esc_attr_e('Vai alla fine', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-controls-skipforward"></span>
                </button>
                
                <div class="velocita-controllo">
                    <label for="velocitaRange"><?php _e('Velocità:', 'scacchitrack'); ?></label>
                    <input type="range" id="velocitaRange" min="1" max="5" value="3">
                </div>
                
                <button class="button" id="flipBtn" title="<?php esc_attr_e('Ruota scacchiera', 'scacchitrack'); ?>">
                    <span class="dashicons dashicons-image-rotate"></span>
                </button>
            </div>
        </div>

        <!-- Pannello di destra con la notazione -->
        <div class="notazione-container">
            <h3><?php _e('Mosse della Partita', 'scacchitrack'); ?></h3>
            <div id="pgn-viewer" class="pgn-viewer"></div>
            
            <!-- Visualizzazione PGN grezzo -->
            <div class="pgn-raw-container">
                <button class="button toggle-pgn" id="togglePgn">
                    <?php _e('Mostra PGN', 'scacchitrack'); ?>
                </button>
                <div class="pgn-raw" style="display: none;">
                    <pre><?php echo esc_html($pgn); ?></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezione per i commenti se abilitati -->
    <?php if (comments_open() || get_comments_number()) : ?>
    <div class="partita-commenti">
        <?php comments_template(); ?>
    </div>
    <?php endif; ?>
    
    <!-- Script necessari per la partita -->
    <script type="text/javascript">
        // Dati della partita per JavaScript
        var scacchitrackPartita = {
            pgn: <?php echo json_encode($pgn); ?>,
            giocatoreBianco: <?php echo json_encode($giocatore_bianco); ?>,
            giocatoreNero: <?php echo json_encode($giocatore_nero); ?>,
            risultato: <?php echo json_encode($risultato); ?>,
            postId: <?php echo json_encode($post_id); ?>
        };
    </script>
</div>

<!-- Template per la visualizzazione dei commenti -->
<div id="commento-template" style="display: none;">
    <div class="commento-mossa">
        <div class="commento-header">
            <span class="commento-autore"></span>
            <span class="commento-data"></span>
        </div>
        <div class="commento-testo"></div>
        <div class="commento-azioni">
            <button class="button button-small risposta-btn">
                <?php _e('Rispondi', 'scacchitrack'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Stili inline per garantire una visualizzazione base anche senza CSS esterno */
.scacchitrack-single-partita {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.partita-header {
    margin-bottom: 30px;
    text-align: center;
}

.partita-title {
    font-size: 24px;
    margin-bottom: 15px;
}

.partita-title .vs {
    color: #666;
    margin: 0 10px;
}

.partita-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.meta-label {
    font-weight: bold;
    color: #666;
}

.partita-container {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .partita-container {
        grid-template-columns: 1fr;
    }
}

.scacchiera-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.scacchiera {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.scacchiera-controlli {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.control-button {
    padding: 8px 12px;
}

.velocita-controllo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notazione-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pgn-viewer {
    height: 400px;
    overflow-y: auto;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pgn-raw {
    margin-top: 10px;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 4px;
}

.pgn-raw pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
}

.risultato-1-0 { color: #2e7d32; }
.risultato-0-1 { color: #c2185b; }
.risultato-½-½ { color: #1565c0; }
.risultato-* { color: #616161; }

/* Stili per i commenti */
.commento-mossa {
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.commento-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 0.9em;
    color: #666;
}

.commento-azioni {
    margin-top: 10px;
}
</style>