<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recupera i nomi unici dei tornei dal database
 *
 * @return array Array di nomi dei tornei
 */
function get_unique_tournament_names() {
    global $wpdb;
    
    $results = $wpdb->get_col(
        "SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_nome_torneo' 
        AND meta_value != '' 
        ORDER BY meta_value ASC"
    );
    
    return array_filter($results);
}

/**
 * Genera il titolo automatico per una partita
 * 
 * @param string $torneo Nome del torneo
 * @param string $round Numero del turno
 * @param string $bianco Nome giocatore bianco
 * @param string $nero Nome giocatore nero
 * @return string Titolo generato
 */
function scacchitrack_generate_game_title($torneo, $round, $bianco, $nero) {
    $torneo = trim($torneo);
    $round = trim($round);
    $bianco = trim($bianco);
    $nero = trim($nero);
    
    if (empty($torneo)) {
        $torneo = __('Partita', 'scacchitrack');
    }
    
    if (empty($round)) {
        return sprintf('%s: %s-%s', $torneo, $bianco, $nero);
    }
    
    return sprintf('%s R.%s: %s-%s', $torneo, $round, $bianco, $nero);
}

/**
 * Recupera le statistiche generali
 *
 * @return array Array di statistiche
 */
function get_scacchitrack_statistics() {
    global $wpdb;
    
    $stats = array();
    
    // Totale tornei
    $stats['total_tournaments'] = count(get_unique_tournament_names());
    
    // Giocatori unici
    $white_players = $wpdb->get_col(
        "SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_giocatore_bianco' 
        AND meta_value != ''"
    );
    
    $black_players = $wpdb->get_col(
        "SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_giocatore_nero' 
        AND meta_value != ''"
    );
    
    $unique_players = array_unique(array_merge($white_players, $black_players));
    $stats['unique_players'] = count($unique_players);
    
    // Risultati
    $results = $wpdb->get_results(
        "SELECT meta_value as risultato, COUNT(*) as count 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_risultato' 
        GROUP BY meta_value"
    );
    
    $total_games = 0;
    $white_wins = 0;
    $black_wins = 0;
    $draws = 0;
    
    foreach ($results as $result) {
        $total_games += $result->count;
        switch ($result->risultato) {
            case '1-0':
                $white_wins = $result->count;
                break;
            case '0-1':
                $black_wins = $result->count;
                break;
            case '½-½':
                $draws = $result->count;
                break;
        }
    }
    
    $stats['white_win_percentage'] = $total_games ? ($white_wins / $total_games) * 100 : 0;
    $stats['black_win_percentage'] = $total_games ? ($black_wins / $total_games) * 100 : 0;
    $stats['draw_percentage'] = $total_games ? ($draws / $total_games) * 100 : 0;
    
    // Timeline data
    $timeline = $wpdb->get_results(
        "SELECT 
            DATE_FORMAT(meta_value, '%Y-%m') as month,
            COUNT(*) as count
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_data_partita'
        GROUP BY month
        ORDER BY month ASC
        LIMIT 12"
    );
    
    $stats['timeline_labels'] = array_column($timeline, 'month');
    $stats['timeline_data'] = array_column($timeline, 'count');
    
    // Top players
    $stats['top_players'] = get_top_players();
    
    // Tournament stats
    $stats['tournament_stats'] = get_tournament_statistics();
    
    // Opening stats
    $stats['openings'] = get_opening_statistics();
    
    return $stats;
}

/**
 * Recupera le statistiche dei migliori giocatori
 *
 * @return array Array di statistiche dei giocatori
 */
function get_top_players() {
    global $wpdb;
    
    $players = array();
    
    // Query per le partite con il bianco
    $white_games = $wpdb->get_results(
        "SELECT 
            pm1.meta_value as player,
            pm2.meta_value as result
        FROM {$wpdb->postmeta} pm1
        JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
        WHERE pm1.meta_key = '_giocatore_bianco'
        AND pm2.meta_key = '_risultato'"
    );
    
    // Query per le partite con il nero
    $black_games = $wpdb->get_results(
        "SELECT 
            pm1.meta_value as player,
            pm2.meta_value as result
        FROM {$wpdb->postmeta} pm1
        JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
        WHERE pm1.meta_key = '_giocatore_nero'
        AND pm2.meta_key = '_risultato'"
    );
    
    // Elabora i risultati
    foreach ($white_games as $game) {
        if (!isset($players[$game->player])) {
            $players[$game->player] = array(
                'name' => $game->player,
                'total_games' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0
            );
        }
        
        $players[$game->player]['total_games']++;
        switch ($game->result) {
            case '1-0':
                $players[$game->player]['wins']++;
                break;
            case '0-1':
                $players[$game->player]['losses']++;
                break;
            case '½-½':
                $players[$game->player]['draws']++;
                break;
        }
    }
    
    foreach ($black_games as $game) {
        if (!isset($players[$game->player])) {
            $players[$game->player] = array(
                'name' => $game->player,
                'total_games' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0
            );
        }
        
        $players[$game->player]['total_games']++;
        switch ($game->result) {
            case '1-0':
                $players[$game->player]['losses']++;
                break;
            case '0-1':
                $players[$game->player]['wins']++;
                break;
            case '½-½':
                $players[$game->player]['draws']++;
                break;
        }
    }
    
    // Calcola le percentuali di vittoria
    foreach ($players as &$player) {
        $player['win_percentage'] = $player['total_games'] ? 
            ($player['wins'] / $player['total_games']) * 100 : 0;
    }
    
    // Ordina per percentuale di vittoria
    uasort($players, function($a, $b) {
        return $b['win_percentage'] <=> $a['win_percentage'];
    });
    
    return array_slice($players, 0, 10);
}

/**
 * Recupera le statistiche dei tornei
 *
 * @return array Array di statistiche dei tornei
 */
function get_tournament_statistics() {
    global $wpdb;
    
    $tournaments = get_unique_tournament_names();
    $stats = array();
    
    foreach ($tournaments as $tournament) {
        $tournament_games = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                pm1.meta_value as result,
                pm2.meta_value as white_player,
                pm3.meta_value as black_player
            FROM {$wpdb->postmeta} pm1
            JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
            JOIN {$wpdb->postmeta} pm3 ON pm1.post_id = pm3.post_id
            JOIN {$wpdb->postmeta} pm4 ON pm1.post_id = pm4.post_id
            WHERE pm1.meta_key = '_risultato'
            AND pm2.meta_key = '_giocatore_bianco'
            AND pm3.meta_key = '_giocatore_nero'
            AND pm4.meta_key = '_nome_torneo'
            AND pm4.meta_value = %s",
            $tournament
        ));
        
        if (!empty($tournament_games)) {
            $tournament_stats = array(
                'name' => $tournament,
                'total_games' => count($tournament_games),
                'white_wins' => 0,
                'black_wins' => 0,
                'draws' => 0,
                'players' => array()
            );
            
            foreach ($tournament_games as $game) {
                $tournament_stats['players'][] = $game->white_player;
                $tournament_stats['players'][] = $game->black_player;
                
                switch ($game->result) {
                    case '1-0':
                        $tournament_stats['white_wins']++;
                        break;
                    case '0-1':
                        $tournament_stats['black_wins']++;
                        break;
                    case '½-½':
                        $tournament_stats['draws']++;
                        break;
                }
            }
            
            $tournament_stats['players'] = count(array_unique($tournament_stats['players']));
            $total = $tournament_stats['total_games'];
            
            $tournament_stats['white_wins_percentage'] = ($tournament_stats['white_wins'] / $total) * 100;
            $tournament_stats['black_wins_percentage'] = ($tournament_stats['black_wins'] / $total) * 100;
            $tournament_stats['draws_percentage'] = ($tournament_stats['draws'] / $total) * 100;
            
            $stats[] = $tournament_stats;
        }
    }
    
    return $stats;
}

/**
 * Recupera le statistiche delle aperture
 *
 * @return array Array di statistiche delle aperture
 */
function get_opening_statistics() {
    global $wpdb;
    
    // Questa è una versione semplificata che estrae le prime mosse dal PGN
    $games = $wpdb->get_results(
        "SELECT 
            post_id,
            meta_value as pgn
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_pgn'"
    );
    
    $openings = array();
    
    foreach ($games as $game) {
        // Estrai le prime mosse (esempio semplificato)
        preg_match('/1\.\s*([A-Za-z0-9]+)/', $game->pgn, $matches);
        
        if (!empty($matches[1])) {
            $opening = $matches[1];
            
            if (!isset($openings[$opening])) {
                $openings[$opening] = array(
                    'name' => $opening,
                    'count' => 0,
                    'white_wins' => 0,
                    'black_wins' => 0,
                    'draws' => 0
                );
            }
            
            $openings[$opening]['count']++;
            
            // Recupera il risultato
            $result = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value 
                FROM {$wpdb->postmeta} 
                WHERE post_id = %d 
                AND meta_key = '_risultato'",
                $game->post_id
            ));
            
            switch ($result) {
                case '1-0':
                    $openings[$opening]['white_wins']++;
                    break;
                case '0-1':
                    $openings[$opening]['black_wins']++;
                    break;
                case '½-½':
                    $openings[$opening]['draws']++;
                    break;
            }
        }
    }
    
    // Ordina per frequenza
    uasort($openings, function($a, $b) {
        return $b['count'] <=> $a['count'];
    });
    
    return array_slice($openings, 0, 10);
}