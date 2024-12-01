<?php
if (!defined('ABSPATH')) {
    exit;
}

// Recupera le statistiche
$total_games = wp_count_posts('scacchipartita')->publish;
$stats = get_scacchitrack_statistics();
?>

<div class="scacchitrack-stats">
    <!-- Statistiche Generali -->
    <div class="scacchitrack-stats">
    <!-- Statistiche Generali -->
    <div class="stats-section">
        <h2><?php _e('Statistiche Generali', 'scacchitrack'); ?></h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($stats['total_games']); ?></div>
                <div class="stat-label"><?php _e('Partite Totali', 'scacchitrack'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($stats['total_tournaments']); ?></div>
                <div class="stat-label"><?php _e('Tornei', 'scacchitrack'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($stats['unique_players']); ?></div>
                <div class="stat-label"><?php _e('Giocatori Unici', 'scacchitrack'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format_i18n($stats['white_win_percentage'], 1); ?>%</div>
                <div class="stat-label"><?php _e('Percentuale Vittorie Bianco', 'scacchitrack'); ?></div>
            </div>
        </div>

    <!-- Statistiche Risultati -->
    <div class="stats-section">
        <h2><?php _e('Risultati', 'scacchitrack'); ?></h2>
        <div class="stats-chart">
            <canvas id="resultChart"></canvas>
        </div>
        <div class="stats-legend">
            <div class="legend-item">
                <span class="color-box white-wins"></span>
                <?php printf(
                    __('Vittorie Bianco: %s%%', 'scacchitrack'),
                    number_format_i18n($stats['white_win_percentage'], 1)
                ); ?>
            </div>
            <div class="legend-item">
                <span class="color-box black-wins"></span>
                <?php printf(
                    __('Vittorie Nero: %s%%', 'scacchitrack'),
                    number_format_i18n($stats['black_win_percentage'], 1)
                ); ?>
            </div>
            <div class="legend-item">
                <span class="color-box draws"></span>
                <?php printf(
                    __('Patte: %s%%', 'scacchitrack'),
                    number_format_i18n($stats['draw_percentage'], 1)
                ); ?>
            </div>
        </div>
    </div>

    <!-- Top Giocatori -->
    <div class="stats-section">
        <h2><?php _e('Top Giocatori', 'scacchitrack'); ?></h2>
        <div class="stats-table-wrapper">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Giocatore', 'scacchitrack'); ?></th>
                        <th><?php _e('Partite', 'scacchitrack'); ?></th>
                        <th><?php _e('Vittorie', 'scacchitrack'); ?></th>
                        <th><?php _e('Patte', 'scacchitrack'); ?></th>
                        <th><?php _e('Sconfitte', 'scacchitrack'); ?></th>
                        <th><?php _e('% Vittorie', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['top_players'] as $player) : ?>
                        <tr>
                            <td><?php echo esc_html($player['name']); ?></td>
                            <td><?php echo number_format_i18n($player['total_games']); ?></td>
                            <td><?php echo number_format_i18n($player['wins']); ?></td>
                            <td><?php echo number_format_i18n($player['draws']); ?></td>
                            <td><?php echo number_format_i18n($player['losses']); ?></td>
                            <td><?php echo number_format_i18n($player['win_percentage'], 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grafici Temporali -->
    <div class="stats-section">
        <h2><?php _e('Andamento Temporale', 'scacchitrack'); ?></h2>
        <div class="stats-chart">
            <canvas id="timelineChart"></canvas>
        </div>
    </div>
</div>

<style>
.scacchitrack-stats {
    margin-top: 20px;
}

.stats-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.stat-value {
    font-size: 2em;
    font-weight: bold;
    color: #2271b1;
    margin-bottom: 10px;
}

.stat-label {
    color: #646970;
    font-size: 0.9em;
}

.stats-chart {
    margin: 20px 0;
    position: relative;
    height: 300px;
}

.stats-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 10px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.color-box {
    width: 16px;
    height: 16px;
    border-radius: 3px;
}

.color-box.white-wins { background-color: #4CAF50; }
.color-box.black-wins { background-color: #f44336; }
.color-box.draws { background-color: #2196F3; }

.stats-table-wrapper {
    margin-top: 20px;
    overflow-x: auto;
}

@media (max-width: 782px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-legend {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
// Inizializzazione dei grafici con Chart.js
jQuery(document).ready(function($) {
    // Dati per il grafico dei risultati
    const resultCtx = document.getElementById('resultChart').getContext('2d');
    new Chart(resultCtx, {
        type: 'doughnut',
        data: {
            labels: [
                '<?php _e("Vittorie Bianco", "scacchitrack"); ?>',
                '<?php _e("Vittorie Nero", "scacchitrack"); ?>',
                '<?php _e("Patte", "scacchitrack"); ?>'
            ],
            datasets: [{
                data: [
                    <?php echo $stats['white_win_percentage']; ?>,
                    <?php echo $stats['black_win_percentage']; ?>,
                    <?php echo $stats['draw_percentage']; ?>
                ],
                backgroundColor: [
                    '#4CAF50',  // verde per vittorie bianco
                    '#f44336',  // rosso per vittorie nero
                    '#2196F3'   // blu per patte
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: '<?php _e("Distribuzione Risultati", "scacchitrack"); ?>'
                }
            }
        }
    });

    // Dati per il grafico temporale
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($stats['timeline_labels']); ?>,
            datasets: [{
                label: '<?php _e("Partite per Mese", "scacchitrack"); ?>',
                data: <?php echo json_encode($stats['timeline_data']); ?>,
                borderColor: '#2271b1',
                backgroundColor: 'rgba(34, 113, 177, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: '<?php _e("Andamento Partite nel Tempo", "scacchitrack"); ?>'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>

<!-- Statistiche Dettagliate per Torneo -->
<div class="stats-section">
    <h2><?php _e('Statistiche per Torneo', 'scacchitrack'); ?></h2>
    <?php if (!empty($stats['tournament_stats'])) : ?>
        <div class="tournament-stats">
            <?php foreach ($stats['tournament_stats'] as $tournament) : ?>
                <div class="tournament-card">
                    <h3><?php echo esc_html($tournament['name']); ?></h3>
                    <div class="tournament-info">
                        <div class="info-item">
                            <span class="info-label"><?php _e('Partite:', 'scacchitrack'); ?></span>
                            <span class="info-value"><?php echo number_format_i18n($tournament['total_games']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php _e('Giocatori:', 'scacchitrack'); ?></span>
                            <span class="info-value"><?php echo number_format_i18n($tournament['players']); ?></span>
                        </div>
                        <div class="info-grid">
                            <div class="info-stat">
                                <div class="stat-circle white-wins">
                                    <?php echo number_format_i18n($tournament['white_wins_percentage'], 1); ?>%
                                </div>
                                <span><?php _e('Vittorie Bianco', 'scacchitrack'); ?></span>
                            </div>
                            <div class="info-stat">
                                <div class="stat-circle black-wins">
                                    <?php echo number_format_i18n($tournament['black_wins_percentage'], 1); ?>%
                                </div>
                                <span><?php _e('Vittorie Nero', 'scacchitrack'); ?></span>
                            </div>
                            <div class="info-stat">
                                <div class="stat-circle draws">
                                    <?php echo number_format_i18n($tournament['draws_percentage'], 1); ?>%
                                </div>
                                <span><?php _e('Patte', 'scacchitrack'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p><?php _e('Nessun torneo trovato.', 'scacchitrack'); ?></p>
    <?php endif; ?>
</div>

<!-- Statistiche Aperture -->
<div class="stats-section">
    <h2><?php _e('Aperture Più Comuni', 'scacchitrack'); ?></h2>
    <?php if (!empty($stats['openings'])) : ?>
        <div class="opening-stats">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Apertura', 'scacchitrack'); ?></th>
                        <th><?php _e('Partite', 'scacchitrack'); ?></th>
                        <th><?php _e('Vittorie Bianco', 'scacchitrack'); ?></th>
                        <th><?php _e('Vittorie Nero', 'scacchitrack'); ?></th>
                        <th><?php _e('Patte', 'scacchitrack'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['openings'] as $opening) : ?>
                        <tr>
                            <td><?php echo esc_html($opening['name']); ?></td>
                            <td><?php echo number_format_i18n($opening['count']); ?></td>
                            <td><?php echo number_format_i18n($opening['white_wins']); ?></td>
                            <td><?php echo number_format_i18n($opening['black_wins']); ?></td>
                            <td><?php echo number_format_i18n($opening['draws']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <p><?php _e('Nessuna statistica sulle aperture disponibile.', 'scacchitrack'); ?></p>
    <?php endif; ?>
</div>

<style>
/* Stili aggiuntivi per le statistiche dei tornei */
.tournament-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.tournament-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 20px;
}

.tournament-card h3 {
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

.tournament-info .info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 15px;
}

.info-stat {
    text-align: center;
}

.stat-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 5px;
    color: white;
    font-weight: bold;
}

.stat-circle.white-wins { background-color: #4CAF50; }
.stat-circle.black-wins { background-color: #f44336; }
.stat-circle.draws { background-color: #2196F3; }

/* Stili responsive aggiuntivi */
@media (max-width: 782px) {
    .tournament-card {
        margin-bottom: 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-circle {
        width: 50px;
        height: 50px;
        font-size: 0.9em;
    }
}
</style>