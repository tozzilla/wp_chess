# ScacchiTrack - SOP: Development Tasks

## Panoramica

Questa guida fornisce procedure standardizzate per le operazioni di sviluppo più comuni su ScacchiTrack. Seguire queste procedure garantisce consistenza e riduce errori.

## 1. Aggiungere un Nuovo Meta Field alle Partite

### Quando Usarlo
Quando si vuole salvare un nuovo dato per ogni partita (es. ELO giocatori, luogo partita, colore apertura).

### Procedura

**Step 1: Aggiornare il Metabox** (includes/metaboxes.php)

```php
// In scacchitrack_game_details_callback(), aggiungi il campo
function scacchitrack_game_details_callback($post) {
    // ... codice esistente ...

    // Recupera il valore
    $nuovo_campo = get_post_meta($post->ID, '_nuovo_campo', true);

    // Aggiungi HTML per il campo
    ?>
    <p>
        <label for="nuovo_campo"><?php _e('Nuovo Campo:', 'scacchitrack'); ?></label>
        <input type="text" id="nuovo_campo" name="nuovo_campo"
               value="<?php echo esc_attr($nuovo_campo); ?>" class="widefat">
    </p>
    <?php
}
```

**Step 2: Salvare il Campo** (includes/metaboxes.php)

```php
// In scacchitrack_save_game_details(), aggiungi al $fields array
$fields = array(
    // ... campi esistenti ...
    'nuovo_campo' => 'sanitize_text_field',  // o altra funzione sanitize appropriata
);
```

**Step 3: Aggiungere alla Colonna Admin** (includes/admin-columns.php) - OPZIONALE

```php
// Aggiungi colonna
function scacchitrack_custom_columns($columns) {
    $columns['nuovo_campo'] = __('Nuovo Campo', 'scacchitrack');
    return $columns;
}

// Popola colonna
function scacchitrack_custom_columns_content($column, $post_id) {
    if ($column === 'nuovo_campo') {
        echo esc_html(get_post_meta($post_id, '_nuovo_campo', true));
    }
}
```

**Step 4: Testing**

1. Vai in admin: ScacchiTrack > Aggiungi Nuova
2. Verifica che il campo sia visibile
3. Salva una partita con il nuovo campo compilato
4. Verifica in database: `wp_postmeta` con `meta_key = '_nuovo_campo'`

### Note Importanti

- Prefissa sempre i meta_key con underscore (`_nuovo_campo`) per campi privati
- Usa sempre funzioni di sanitizzazione appropriate
- Aggiungi traduzione con `__()` per internazionalizzazione

## 2. Aggiungere un Filtro all'Admin List

### Quando Usarlo
Per permettere filtraggio delle partite nell'admin (es. per giocatore, per luogo).

### Procedura

**Step 1: Aggiungere il Dropdown** (includes/admin-enhancements.php)

```php
// In ScacchiTrack_Admin_Enhancements::add_admin_filters()
public function add_admin_filters($post_type) {
    if ($post_type !== 'scacchipartita') {
        return;
    }

    // ... filtri esistenti ...

    // Nuovo filtro
    $selected_value = isset($_GET['filter_nuovo']) ? $_GET['filter_nuovo'] : '';
    $values = $this->get_unique_values('_nuovo_campo'); // Helper function

    echo '<select name="filter_nuovo">';
    echo '<option value="">' . __('Tutti', 'scacchitrack') . '</option>';
    foreach ($values as $value) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($selected_value, $value, false) . '>';
        echo esc_html($value);
        echo '</option>';
    }
    echo '</select>';
}

// Helper per ottenere valori unici
private function get_unique_values($meta_key) {
    global $wpdb;
    return $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        AND meta_value != ''
        ORDER BY meta_value ASC",
        $meta_key
    ));
}
```

**Step 2: Applicare il Filtro alla Query** (includes/admin-enhancements.php)

```php
// In ScacchiTrack_Admin_Enhancements::filter_admin_query()
public function filter_admin_query($query) {
    // ... verifiche esistenti ...

    // Applica nuovo filtro
    if (!empty($_GET['filter_nuovo'])) {
        $meta_query[] = array(
            'key' => '_nuovo_campo',
            'value' => sanitize_text_field($_GET['filter_nuovo']),
            'compare' => '='
        );
    }

    // ... resto codice ...
}
```

**Step 3: Testing**

1. Vai in admin: ScacchiTrack > Tutte le Partite
2. Verifica presenza nuovo dropdown
3. Seleziona un valore e clicca "Filtra"
4. Verifica che la lista si aggiorni correttamente

## 3. Modificare l'Import PGN

### Quando Usarlo
Per modificare come vengono importati i dati dal file PGN (es. estrarre nuovi header).

### Procedura

**Step 1: Identificare l'Handler** (includes/admin-enhancements.php)

Trova la classe `ScacchiTrack_Import_Handler`.

**Step 2: Modificare il Parsing**

```php
// In ScacchiTrack_Import_Handler::parse_single_game()
private function parse_single_game($pgn_text) {
    // ... codice esistente ...

    // Estrai nuovo header dal PGN
    if (preg_match('/\[NuovoHeader "([^"]+)"\]/', $pgn_text, $matches)) {
        $game_data['nuovo_campo'] = $matches[1];
    }

    return $game_data;
}
```

**Step 3: Salvare il Dato**

```php
// In ScacchiTrack_Import_Handler::create_game_post()
private function create_game_post($game_data) {
    // ... codice esistente ...

    // Salva nuovo campo
    if (isset($game_data['nuovo_campo'])) {
        update_post_meta($post_id, '_nuovo_campo', sanitize_text_field($game_data['nuovo_campo']));
    }

    return $post_id;
}
```

**Step 4: Testing**

1. Crea file PGN di test con nuovo header
2. Importa via admin: ScacchiTrack > Importa
3. Verifica che il dato sia stato salvato
4. Controlla log errori se l'import fallisce

### Note

- chess.js ha limitazioni su quali header PGN supporta nativamente
- Per header custom, usa regex parsing manuale
- Aggiungi validazione per dati estratti

## 4. Aggiungere una Nuova Statistica

### Quando Usarlo
Per calcolare e visualizzare una nuova statistica nella dashboard.

### Procedura

**Step 1: Creare Funzione di Calcolo** (includes/functions.php)

```php
/**
 * Calcola nuova statistica
 *
 * @return array Risultati statistica
 */
function get_nuova_statistica() {
    global $wpdb;

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT
                meta_value as campo,
                COUNT(*) as count
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
            GROUP BY meta_value
            ORDER BY count DESC
            LIMIT %d",
            '_campo_da_analizzare',
            10
        )
    );

    return $results;
}
```

**Step 2: Aggiungere alle Statistiche Globali** (includes/functions.php)

```php
// In get_scacchitrack_statistics()
function get_scacchitrack_statistics() {
    // ... statistiche esistenti ...

    $stats['nuova_statistica'] = get_nuova_statistica();

    return $stats;
}
```

**Step 3: Visualizzare nella Dashboard** (templates/admin/dashboard.php)

```php
// Nel template dashboard
<?php
$nuova_stat = $stats['nuova_statistica'];
?>
<div class="scacchitrack-stat-box">
    <h3><?php _e('Nuova Statistica', 'scacchitrack'); ?></h3>
    <ul>
        <?php foreach ($nuova_stat as $item): ?>
            <li>
                <?php echo esc_html($item->campo); ?>:
                <strong><?php echo intval($item->count); ?></strong>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
```

**Step 4: Testing**

1. Vai in admin: ScacchiTrack > Dashboard
2. Verifica visualizzazione statistica
3. Controlla performance query (use Query Monitor plugin)

### Performance Tips

- Considera caching con Transients per statistiche pesanti
- Limita risultati con LIMIT
- Usa indici database appropriati

## 5. Aggiungere un Nuovo Shortcode

### Quando Usarlo
Per esporre nuove funzionalità nel frontend via shortcode.

### Procedura

**Step 1: Registrare Shortcode** (includes/shortcodes.php)

```php
/**
 * Shortcode per nuova funzionalità
 *
 * @param array $atts Attributi shortcode
 * @return string HTML output
 */
function scacchitrack_nuovo_shortcode($atts) {
    // Normalizza attributi
    $atts = shortcode_atts(array(
        'limit' => 10,
        'filter' => ''
    ), $atts, 'scacchitrack_nuovo');

    // Sanitizza input
    $limit = absint($atts['limit']);
    $filter = sanitize_text_field($atts['filter']);

    // Logica shortcode
    $args = array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => $limit,
        // ... altri parametri
    );

    $query = new WP_Query($args);

    // Buffer output
    ob_start();

    // Include template
    include SCACCHITRACK_DIR . 'templates/nuovo-template.php';

    return ob_get_clean();
}
add_shortcode('scacchitrack_nuovo', 'scacchitrack_nuovo_shortcode');
```

**Step 2: Creare Template** (templates/nuovo-template.php)

```php
<?php
if (!defined('ABSPATH')) exit;

if ($query->have_posts()):
    while ($query->have_posts()): $query->the_post();
        // Output HTML
        ?>
        <div class="scacchitrack-item">
            <h3><?php the_title(); ?></h3>
            <!-- ... -->
        </div>
        <?php
    endwhile;
    wp_reset_postdata();
else:
    echo '<p>' . __('Nessun risultato.', 'scacchitrack') . '</p>';
endif;
?>
```

**Step 3: Documentare Shortcode**

Aggiungi alla documentazione utente:
```
[scacchitrack_nuovo limit="10" filter="valore"]
- limit: Numero risultati (default: 10)
- filter: Filtro da applicare (opzionale)
```

**Step 4: Testing**

1. Crea una pagina WordPress
2. Aggiungi shortcode: `[scacchitrack_nuovo limit="5"]`
3. Visualizza la pagina
4. Testa con vari attributi

## 6. Modificare gli Stili CSS

### Quando Usarlo
Per cambiare l'aspetto visuale del plugin.

### Procedura

**Step 1: Identificare File CSS**

- Frontend: `css/scacchitrack.css`
- Admin: inline styles o file separato se necessario

**Step 2: Aggiungere/Modificare Regole**

```css
/* css/scacchitrack.css */

/* Nuovo componente */
.scacchitrack-nuovo-componente {
    background: #f5f5f5;
    padding: 20px;
    border-radius: 4px;
}

/* Modifica esistente */
.scacchiera-container {
    max-width: 100%; /* era 600px */
}

/* Responsive */
@media (max-width: 768px) {
    .scacchitrack-nuovo-componente {
        padding: 10px;
    }
}
```

**Step 3: Versioning**

Incrementa versione in `scacchitrack.php`:
```php
define('SCACCHITRACK_VERSION', '1.0.1'); // era 1.0.0
```

Questo forza browser a ricaricare CSS (cache busting).

**Step 4: Testing**

1. Svuota cache browser (Ctrl+Shift+R)
2. Verifica modifiche in diversi browser
3. Testa responsive (usa DevTools)

### Best Practices CSS

- Usa prefisso `.scacchitrack-` per evitare conflitti
- Mantieni specificità bassa
- Usa variabili CSS quando possibile
- Commenta sezioni complesse

## 7. Debug e Troubleshooting

### Attivare WP_DEBUG

In `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Log salvato in: `wp-content/debug.log`

### Logging Personalizzato

```php
// In qualsiasi file PHP del plugin
if (WP_DEBUG) {
    error_log('ScacchiTrack Debug: ' . print_r($data, true));
}
```

### Query Monitor

Installa plugin "Query Monitor" per:
- Monitorare query database
- Identificare query lente
- Debug AJAX requests
- Vedere hook WordPress

### JavaScript Console

```javascript
// In file JS
console.log('ScacchiTrack:', variable);
console.table(arrayData);
```

### Common Issues

**Issue**: Import PGN fallisce
**Fix**: Controlla formato PGN, verifica chess.js possa parsarlo, log errori

**Issue**: AJAX non funziona
**Fix**: Verifica nonce, controlla `wp_localize_script`, usa DevTools Network tab

**Issue**: Statistiche sbagliate
**Fix**: Controlla query SQL, verifica dati in wp_postmeta, usa Query Monitor

**Issue**: Scacchiera non si carica
**Fix**: Verifica console errori JS, controlla che librerie siano caricate

## 8. Testing Checklist

Prima di ogni commit:

- [ ] Codice passa linting (se configurato)
- [ ] Nessun errore PHP in debug.log
- [ ] Nessun errore JS in console
- [ ] Funzionalità testata in Firefox e Chrome
- [ ] Testato su mobile (responsive)
- [ ] Query database ottimizzate (Query Monitor)
- [ ] Nonce verification presente
- [ ] Input sanitizzati
- [ ] Output escaped

## 9. Git Workflow

### Commit Message Format

```
[Tipo] Breve descrizione

Descrizione dettagliata se necessario.

Fixes #issue_number
```

Tipi: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

Esempio:
```
feat: Aggiungi filtro per giocatore ELO

Permette di filtrare partite per range ELO dei giocatori
nella lista admin.

Fixes #42
```

### Branch Strategy

- `main` - Produzione stabile
- `develop` - Development in corso
- `feature/nome-feature` - Nuove feature
- `fix/nome-bug` - Bug fixes

### Pre-commit Checklist

1. Run tests (se presenti)
2. Check debug.log è vuoto
3. Review modifiche con `git diff`
4. Commit message descrittivo
5. Push to remote

## 10. Deployment

### Pre-deploy Checklist

- [ ] Versione aggiornata in scacchitrack.php
- [ ] CHANGELOG aggiornato
- [ ] README aggiornato
- [ ] Testing completo
- [ ] Backup database
- [ ] Assets minificati (se applicabile)

### Deploy su WordPress.org (se applicabile)

1. Tag release in git: `git tag -a v1.0.0 -m "Release 1.0.0"`
2. Push tag: `git push origin v1.0.0`
3. Crea ZIP escludendo: `.git`, `node_modules`, `.agent`
4. Upload a WordPress.org

### Deploy su Sito Cliente

1. Backup completo sito
2. Disattiva plugin vecchio
3. Upload nuova versione
4. Attiva plugin
5. Test funzionalità critiche
6. Verifica nessun errore in log
