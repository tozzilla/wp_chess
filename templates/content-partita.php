<?php
if (!defined('ABSPATH')) {
    exit;
}

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
        <h1 class="partita-title">
            <?php the_title(); ?>
        </h1>
        
        <div class="partita-meta">
            <div class="giocatori">
                <div class="giocatore bianco">
                    <span class="label"><?php _e('Bianco:', 'scacchitrack'); ?></span>
                    <span class="nome"><?php echo esc_html($giocatore_bianco); ?></span>
                </div>
                <div class="risultato">
                    <span class="punteggio"><?php echo esc_html($risultato); ?></span>
                </div>
                <div class="giocatore nero">
                    <span class="label"><?php _e('Nero:', 'scacchitrack'); ?></span>
                    <span class="nome"><?php echo esc_html($giocatore_nero); ?></span>
                </div>
            </div>
            
            <div class="dettagli">
                <?php if ($data_partita) : ?>
                <div class="data">
                    <span class="label"><?php _e('Data:', 'scacchitrack'); ?></span>
                    <span class="valore"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data_partita))); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($nome_torneo) : ?>
                <div class="torneo">
                    <span class="label"><?php _e('Torneo:', 'scacchitrack'); ?></span>
                    <span class="valore"><?php echo esc_html($nome_torneo); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Area principale con scacchiera e controlli -->
    <div class="partita-container">
        <div class="scacchiera-wrapper">
            <!-- Scacchiera -->
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

        <!-- Area notazione -->
        <div class="notazione-container">
            <h3><?php _e('Mosse', 'scacchitrack'); ?></h3>
            <div id="pgn-viewer" class="pgn-viewer"></div>
            
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

    <!-- Contenuto addizionale della partita -->
    <div class="partita-content">
        <?php the_content(); ?>
    </div>

    <?php if (comments_open() || get_comments_number()) : ?>
    <div class="partita-commenti">
        <?php comments_template(); ?>
    </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    // Dati della partita per JavaScript
    var scacchitrackPartita = {
        pgn: <?php echo json_encode($pgn); ?>,
        giocatoreBianco: <?php echo json_encode($giocatore_bianco); ?>,
        giocatoreNero: <?php echo json_encode($giocatore_nero); ?>,
        risultato: <?php echo json_encode($risultato); ?>
    };
</script>

<style>
.scacchitrack-single-partita {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.partita-header {
    margin-bottom: 30px;
    text-align: center;
}

.partita-meta {
    margin: 20px 0;
}

.giocatori {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.giocatore {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.giocatore .nome {
    font-size: 1.2em;
    font-weight: bold;
}

.risultato {
    font-size: 1.5em;
    font-weight: bold;
    padding: 0 20px;
}

.dettagli {
    display: flex;
    justify-content: center;
    gap: 20px;
    color: #666;
}

.partita-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .partita-container {
        grid-template-columns: 1fr;
    }
}

.scacchiera-wrapper {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.scacchiera {
    width: 100%;
    margin: 0 auto;
}

.scacchiera-controlli {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.control-button {
    padding: 8px 12px;
    border: none;
    background: #f0f0f0;
    border-radius: 4px;
    cursor: pointer;
}

.control-button:hover {
    background: #e0e0e0;
}

.velocita-controllo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 15px;
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
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>