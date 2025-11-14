# ScacchiTrack - Schema Database

## Panoramica

ScacchiTrack utilizza lo schema standard di WordPress estendendolo con Custom Post Types, Taxonomies e Post Meta. Non crea tabelle custom, ma sfrutta le tabelle esistenti di WordPress per garantire compatibilità e manutenibilità.

## Tabelle WordPress Utilizzate

### 1. wp_posts

Contiene le partite di scacchi come custom post type `scacchipartita`.

**Campi Rilevanti**:
```sql
ID                  BIGINT(20)      # ID univoco partita
post_author         BIGINT(20)      # Utente che ha creato la partita
post_date           DATETIME        # Data creazione record
post_title          TEXT            # Titolo auto-generato: "{Torneo} R.{Round}: {Bianco}-{Nero}"
post_content        LONGTEXT        # Non utilizzato (tutto in meta)
post_status         VARCHAR(20)     # publish, draft, private, trash
post_type           VARCHAR(20)     # 'scacchipartita'
post_modified       DATETIME        # Data ultima modifica
```

**Esempio Record**:
```sql
ID: 123
post_title: "Campionato Provinciale R.3: Mario Rossi-Luigi Bianchi"
post_type: "scacchipartita"
post_status: "publish"
```

**Query Tipiche**:
```sql
-- Conta partite pubblicate
SELECT COUNT(*) FROM wp_posts WHERE post_type = 'scacchipartita' AND post_status = 'publish';

-- Lista partite recenti
SELECT * FROM wp_posts
WHERE post_type = 'scacchipartita'
AND post_status = 'publish'
ORDER BY post_date DESC
LIMIT 10;
```

### 2. wp_postmeta

Contiene tutti i metadati delle partite. Ogni partita ha multipli record in questa tabella.

**Struttura**:
```sql
meta_id             BIGINT(20)      # ID univoco metadata
post_id             BIGINT(20)      # FK verso wp_posts.ID
meta_key            VARCHAR(255)    # Nome campo metadata
meta_value          LONGTEXT        # Valore metadata
```

**Meta Keys Utilizzati**:

| Meta Key | Tipo | Descrizione | Esempio |
|----------|------|-------------|---------|
| `_giocatore_bianco` | TEXT | Nome giocatore con pezzi bianchi | "Mario Rossi" |
| `_giocatore_nero` | TEXT | Nome giocatore con pezzi neri | "Luigi Bianchi" |
| `_data_partita` | DATE | Data partita (formato: YYYY-MM-DD) | "2025-03-15" |
| `_nome_torneo` | TEXT | Nome del torneo | "Campionato Provinciale 2025" |
| `_round` | TEXT | Numero turno/round | "3" |
| `_risultato` | TEXT | Risultato partita | "1-0", "0-1", "½-½", "*" |
| `_pgn` | LONGTEXT | Notazione PGN completa partita | "[Event "..."]\n1.e4 e5..." |

**Esempio Record**:
```sql
-- Partita ID 123
meta_id: 501, post_id: 123, meta_key: '_giocatore_bianco', meta_value: 'Mario Rossi'
meta_id: 502, post_id: 123, meta_key: '_giocatore_nero', meta_value: 'Luigi Bianchi'
meta_id: 503, post_id: 123, meta_key: '_data_partita', meta_value: '2025-03-15'
meta_id: 504, post_id: 123, meta_key: '_nome_torneo', meta_value: 'Campionato Provinciale 2025'
meta_id: 505, post_id: 123, meta_key: '_round', meta_value: '3'
meta_id: 506, post_id: 123, meta_key: '_risultato', meta_value: '1-0'
meta_id: 507, post_id: 123, meta_key: '_pgn', meta_value: '[Event "Campionato"]\n1.e4 e5 2.Nf3...'
```

**Query Tipiche**:

```sql
-- Ottieni tutti i tornei unici
SELECT DISTINCT meta_value
FROM wp_postmeta
WHERE meta_key = '_nome_torneo'
AND meta_value != ''
AND meta_value IS NOT NULL
ORDER BY meta_value ASC;

-- Trova partite per giocatore (bianco o nero)
SELECT DISTINCT post_id
FROM wp_postmeta
WHERE meta_key IN ('_giocatore_bianco', '_giocatore_nero')
AND meta_value LIKE '%Mario Rossi%';

-- Statistiche risultati
SELECT meta_value as risultato, COUNT(*) as count
FROM wp_postmeta
WHERE meta_key = '_risultato'
GROUP BY meta_value;

-- Partite per torneo con dettagli
SELECT
    pm1.post_id,
    pm1.meta_value as torneo,
    pm2.meta_value as round,
    pm3.meta_value as bianco,
    pm4.meta_value as nero,
    pm5.meta_value as risultato
FROM wp_postmeta pm1
LEFT JOIN wp_postmeta pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = '_round'
LEFT JOIN wp_postmeta pm3 ON pm1.post_id = pm3.post_id AND pm3.meta_key = '_giocatore_bianco'
LEFT JOIN wp_postmeta pm4 ON pm1.post_id = pm4.post_id AND pm4.meta_key = '_giocatore_nero'
LEFT JOIN wp_postmeta pm5 ON pm1.post_id = pm5.post_id AND pm5.meta_key = '_risultato'
WHERE pm1.meta_key = '_nome_torneo'
AND pm1.meta_value = 'Campionato Provinciale 2025'
ORDER BY CAST(pm2.meta_value AS UNSIGNED) ASC;

-- Timeline partite (per mese)
SELECT
    DATE_FORMAT(meta_value, '%Y-%m') as month,
    COUNT(*) as count
FROM wp_postmeta
WHERE meta_key = '_data_partita'
AND meta_value != ''
GROUP BY month
ORDER BY month DESC
LIMIT 12;
```

### 3. wp_term_taxonomy

Gestisce le tassonomie custom per categorizzare le partite.

**Taxonomies Registrate**:

1. **apertura_scacchi** (hierarchical)
   - Categorizza partite per apertura (es. "Italiana", "Siciliana", "Francese")
   - Supporta gerarchia (es. "Siciliana" > "Variante Najdorf")

2. **tipo_partita** (hierarchical)
   - Tipo di partita (es. "Blitz", "Rapid", "Standard", "Corrispondenza")

3. **etichetta_partita** (non-hierarchical tags)
   - Etichette libere (es. "finale interessante", "tattica", "endgame")

**Struttura**:
```sql
term_taxonomy_id    BIGINT(20)      # ID tassonomia
term_id             BIGINT(20)      # FK verso wp_terms
taxonomy            VARCHAR(32)     # Nome tassonomia
description         LONGTEXT        # Descrizione categoria
parent              BIGINT(20)      # ID parent (se hierarchical)
count               BIGINT(20)      # Numero post associati
```

### 4. wp_terms

Contiene i termini (valori) delle tassonomie.

```sql
term_id             BIGINT(20)      # ID univoco termine
name                VARCHAR(200)    # Nome termine (es. "Siciliana")
slug                VARCHAR(200)    # Slug URL-friendly
term_group          BIGINT(10)      # Grouping (raramente usato)
```

### 5. wp_term_relationships

Collega le partite (post) ai termini (tassonomie).

```sql
object_id           BIGINT(20)      # FK verso wp_posts.ID
term_taxonomy_id    BIGINT(20)      # FK verso wp_term_taxonomy
term_order          INT(11)         # Ordinamento
```

**Esempio**:
```sql
-- Partita 123 categorizzata come "Difesa Siciliana" e "Blitz"
object_id: 123, term_taxonomy_id: 5  -- apertura_scacchi: "Siciliana"
object_id: 123, term_taxonomy_id: 12 -- tipo_partita: "Blitz"
```

## Custom Capabilities

Le capabilities sono salvate nella tabella `wp_options` come parte dei metadati dei ruoli utente.

**Option Name**: `wp_user_roles`

**Capabilities Aggiunte**:
```php
'read_partita'
'read_private_partite'
'edit_partita'
'edit_partite'
'edit_others_partite'
'edit_published_partite'
'publish_partite'
'delete_partite'
'delete_others_partite'
'delete_private_partite'
'delete_published_partite'
```

**Ruoli con Capabilities**:
- Administrator (tutte)
- Editor (tutte)

## Relazioni tra Tabelle

```
wp_posts (scacchipartita)
    |
    |-- 1:N --> wp_postmeta (metadata partita)
    |
    |-- N:M --> wp_term_relationships
                    |
                    |--> wp_term_taxonomy
                            |
                            |--> wp_terms
```

## Indici Database

WordPress crea automaticamente indici ottimali:

**wp_posts**:
- PRIMARY KEY (`ID`)
- INDEX `type_status_date` (`post_type`, `post_status`, `post_date`, `ID`)
- INDEX `post_author` (`post_author`)

**wp_postmeta**:
- PRIMARY KEY (`meta_id`)
- INDEX `post_id` (`post_id`)
- INDEX `meta_key` (`meta_key`)
- INDEX `meta_value` (`meta_value`(191)) -- solo primi 191 caratteri

**wp_term_relationships**:
- PRIMARY KEY (`object_id`, `term_taxonomy_id`)
- INDEX `term_taxonomy_id` (`term_taxonomy_id`)

## Query Complesse Comuni

### 1. Top Players (Win Rate)

```sql
-- Calcola statistiche giocatori
SELECT
    player_name,
    COUNT(*) as total_games,
    SUM(CASE WHEN is_win = 1 THEN 1 ELSE 0 END) as wins,
    SUM(CASE WHEN is_draw = 1 THEN 1 ELSE 0 END) as draws,
    (SUM(CASE WHEN is_win = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as win_percentage
FROM (
    -- Partite come bianco
    SELECT
        pm_white.meta_value as player_name,
        CASE WHEN pm_result.meta_value = '1-0' THEN 1 ELSE 0 END as is_win,
        CASE WHEN pm_result.meta_value = '½-½' THEN 1 ELSE 0 END as is_draw
    FROM wp_postmeta pm_white
    JOIN wp_postmeta pm_result ON pm_white.post_id = pm_result.post_id
    WHERE pm_white.meta_key = '_giocatore_bianco'
    AND pm_result.meta_key = '_risultato'

    UNION ALL

    -- Partite come nero
    SELECT
        pm_black.meta_value as player_name,
        CASE WHEN pm_result.meta_value = '0-1' THEN 1 ELSE 0 END as is_win,
        CASE WHEN pm_result.meta_value = '½-½' THEN 1 ELSE 0 END as is_draw
    FROM wp_postmeta pm_black
    JOIN wp_postmeta pm_result ON pm_black.post_id = pm_result.post_id
    WHERE pm_black.meta_key = '_giocatore_nero'
    AND pm_result.meta_key = '_risultato'
) as all_games
GROUP BY player_name
HAVING total_games >= 5  -- Minimo 5 partite
ORDER BY win_percentage DESC
LIMIT 10;
```

### 2. Tournament Statistics

```sql
-- Statistiche per torneo
SELECT
    pm_torneo.meta_value as tournament_name,
    COUNT(DISTINCT pm_torneo.post_id) as total_games,
    COUNT(DISTINCT CASE
        WHEN pm_white.meta_value != '' THEN pm_white.meta_value
        WHEN pm_black.meta_value != '' THEN pm_black.meta_value
    END) as unique_players,
    SUM(CASE WHEN pm_result.meta_value = '1-0' THEN 1 ELSE 0 END) as white_wins,
    SUM(CASE WHEN pm_result.meta_value = '0-1' THEN 1 ELSE 0 END) as black_wins,
    SUM(CASE WHEN pm_result.meta_value = '½-½' THEN 1 ELSE 0 END) as draws
FROM wp_postmeta pm_torneo
LEFT JOIN wp_postmeta pm_white ON pm_torneo.post_id = pm_white.post_id AND pm_white.meta_key = '_giocatore_bianco'
LEFT JOIN wp_postmeta pm_black ON pm_torneo.post_id = pm_black.post_id AND pm_black.meta_key = '_giocatore_nero'
LEFT JOIN wp_postmeta pm_result ON pm_torneo.post_id = pm_result.post_id AND pm_result.meta_key = '_risultato'
WHERE pm_torneo.meta_key = '_nome_torneo'
GROUP BY pm_torneo.meta_value
ORDER BY total_games DESC;
```

### 3. Partite con Filtri (usata dagli AJAX handlers)

```sql
-- Esempio: filtro per torneo, giocatore e range date
SELECT DISTINCT p.ID, p.post_title, p.post_date
FROM wp_posts p
INNER JOIN wp_postmeta pm_torneo ON p.ID = pm_torneo.post_id
INNER JOIN wp_postmeta pm_data ON p.ID = pm_data.post_id
LEFT JOIN wp_postmeta pm_white ON p.ID = pm_white.post_id AND pm_white.meta_key = '_giocatore_bianco'
LEFT JOIN wp_postmeta pm_black ON p.ID = pm_black.post_id AND pm_black.meta_key = '_giocatore_nero'
WHERE p.post_type = 'scacchipartita'
AND p.post_status = 'publish'
AND pm_torneo.meta_key = '_nome_torneo'
AND pm_torneo.meta_value = 'Campionato Provinciale 2025'
AND pm_data.meta_key = '_data_partita'
AND pm_data.meta_value BETWEEN '2025-01-01' AND '2025-12-31'
AND (
    pm_white.meta_value LIKE '%Mario%'
    OR pm_black.meta_value LIKE '%Mario%'
)
ORDER BY pm_data.meta_value DESC;
```

## Data Validation

**A livello applicativo** (non database constraints):

1. **_risultato**: Deve essere uno di: `1-0`, `0-1`, `½-½`, `*`
2. **_data_partita**: Formato DATE `YYYY-MM-DD`
3. **_pgn**: Validato con chess.js prima del salvataggio
4. **_round**: Numerico (salvato come TEXT per flessibilità)

## Performance Considerations

**Ottimizzazioni Implementate**:
1. Meta queries limitate a campi indicizzati
2. JOIN su post_id (sempre indicizzato)
3. DISTINCT solo quando necessario
4. LIMIT nelle query di lista

**Potenziali Miglioramenti**:
1. Implementare caching con Transients API:
   ```php
   set_transient('scacchitrack_stats_global', $stats, 3600);
   ```
2. Considerare tabella custom per statistiche pre-calcolate
3. Indexing custom su meta_value per campi più ricercati

## Data Migration

Non necessaria migrazioni custom. Durante activation/deactivation:

**Activation**:
- Registra CPT
- Flush rewrite rules
- Aggiunge capabilities ai ruoli

**Deactivation**:
- Rimuove capabilities
- Flush rewrite rules
- **NON elimina dati** (partite rimangono nel database)

**Uninstall** (se implementato):
```php
// Da implementare in uninstall.php se necessario
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'scacchipartita'");
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})");
```

## Backup Recommendations

**Dati da Backuppare**:
1. Tabella `wp_posts` (filtro: `post_type = 'scacchipartita'`)
2. Tabella `wp_postmeta` (associata a post_id delle partite)
3. Tassonomie: `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships`
4. Settings in `wp_options` (capabilities nei ruoli)

**Export Query**:
```sql
-- Export tutte le partite con metadata
SELECT
    p.*,
    GROUP_CONCAT(
        CONCAT(pm.meta_key, '=', pm.meta_value)
        SEPARATOR '||'
    ) as all_meta
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = 'scacchipartita'
GROUP BY p.ID;
```
