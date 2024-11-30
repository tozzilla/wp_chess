<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="scacchitrack-container">
    <!-- Filtri -->
    <div class="scacchitrack-filters">
        <form id="scacchitrack-filter-form" class="scacchitrack-filter-form">
            <div class="filter-row">
                <div class="filter-column">
                    <label for="torneo"><?php _e('Torneo:', 'scacchitrack'); ?></label>
                    <select name="torneo" id="torneo">
                        <option value=""><?php _e('Tutti i tornei', 'scacchitrack'); ?></option>
                        <?php
                        $tornei = get_unique_tournament_names();
                        foreach ($tornei as $torneo) {
                            echo '<option value="' . esc_attr($torneo) . '">' . esc_html($torneo) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filter-column">
                    <label for="giocatore"><?php _e('Giocatore:', 'scacchitrack'); ?></label>
                    <input type="text" 
                           name="giocatore" 
                           id="giocatore" 
                           placeholder="<?php esc_attr_e('Nome giocatore', 'scacchitrack'); ?>">
                </div>
                
                <div class="filter-column">
                    <label for="data_da"><?php _e('Data da:', 'scacchitrack'); ?></label>
                    <input type="date" name="data_da" id="data_da">
                </div>
                
                <div class="filter-column">
                    <label for="data_a"><?php _e('Data a:', 'scacchitrack'); ?></label>
                    <input type="date" name="data_a" id="data_a">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="button">
                    <?php _e('Filtra', 'scacchitrack'); ?>
                </button>
                <button type="reset" class="button">
                    <?php _e('Reimposta', 'scacchitrack'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Risultati -->
    <div id="scacchitrack-results">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Data', 'scacchitrack'); ?></th>
                    <th><?php _e('Bianco', 'scacchitrack'); ?></th>
                    <th><?php _e('Nero', 'scacchitrack'); ?></th>
                    <th><?php _e('Risultato', 'scacchitrack'); ?></th>
                    <th><?php _e('Torneo', 'scacchitrack'); ?></th>
                    <th><?php _e('Azioni', 'scacchitrack'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php include SCACCHITRACK_DIR . 'templates/partite-loop.php'; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginazione -->
    <div class="scacchitrack-pagination">
        <?php
        echo paginate_links(array(
            'total' => $query->max_num_pages,
            'current' => max(1, get_query_var('paged')),
            'format' => '?paged=%#%',
            'show_all' => false,
            'type' => 'plain',
            'end_size' => 2,
            'mid_size' => 1,
            'prev_next' => true,
            'prev_text' => __('« Precedente', 'scacchitrack'),
            'next_text' => __('Successiva »', 'scacchitrack'),
        ));
        ?>
    </div>
</div>

<style>
.scacchitrack-container {
    margin: 20px 0;
}

.scacchitrack-filters {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-column label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.filter-column input,
.filter-column select {
    width: 100%;
}

.filter-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}

#scacchitrack-results.loading {
    opacity: 0.5;
    pointer-events: none;
}

.scacchitrack-pagination {
    margin-top: 20px;
    text-align: center;
}

.scacchitrack-pagination .page-numbers {
    padding: 5px 10px;
    margin: 0 5px;
    border: 1px solid #ddd;
    text-decoration: none;
    border-radius: 3px;
}

.scacchitrack-pagination .page-numbers.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}
</style>