# ScacchiTrack - Improvement Recommendations

**Data**: 2025-11-14
**Versione Analizzata**: 0.9e
**Target**: Post-release 1.0 improvements

## Overview

Questo documento contiene raccomandazioni per miglioramenti futuri di ScacchiTrack, basate sull'analisi statica del codice e best practices WordPress.

**Nota**: Nessuna di queste raccomandazioni è bloccante per la release 1.0.0. Sono tutte ottimizzazioni e miglioramenti da considerare per versioni future.

## High Priority (v1.1)

### 1. Implementare Caching Statistiche

**Priority**: HIGH
**Effort**: MEDIUM
**Impact**: HIGH (performance)

**Problem**:
Le statistiche calcolano dati aggregati al volo ad ogni caricamento della dashboard. Con molte partite (500+), questo può diventare lento.

**Current Code** (functions.php):
```php
function get_scacchitrack_statistics() {
    global $wpdb;

    $stats = array();

    // Calcoli pesanti...
    $stats['total_games'] = wp_count_posts('scacchipartita')->publish;
    $stats['top_players'] = get_top_players(); // Query complessa
    // ...

    return $stats;
}
```

**Recommended Solution**:
```php
function get_scacchitrack_statistics($force_refresh = false) {
    // Prova a recuperare da cache
    $cache_key = 'scacchitrack_stats_global';
    $stats = get_transient($cache_key);

    // Se cache vuota o force refresh
    if (false === $stats || $force_refresh) {
        global $wpdb;

        $stats = array();

        // Calcoli pesanti...
        $stats['total_games'] = wp_count_posts('scacchipartita')->publish;
        $stats['top_players'] = get_top_players();
        // ...

        // Salva in cache per 1 ora
        set_transient($cache_key, $stats, HOUR_IN_SECONDS);
    }

    return $stats;
}

// Invalida cache quando una partita viene salvata/eliminata
function scacchitrack_invalidate_stats_cache() {
    delete_transient('scacchitrack_stats_global');
}
add_action('save_post_scacchipartita', 'scacchitrack_invalidate_stats_cache');
add_action('delete_post', 'scacchitrack_invalidate_stats_cache');
```

**Benefits**:
- Dashboard carica istantaneamente
- Riduzione carico database
- Scalabilità migliorata

**Testing**:
1. Import 500+ partite
2. Misura tempo caricamento dashboard prima/dopo
3. Verifica cache invalidation funziona

---

### 2. Aggiungere Uninstall Hook

**Priority**: HIGH
**Effort**: LOW
**Impact**: MEDIUM (best practice)

**Problem**:
Il plugin non pulisce i propri dati quando viene disinstallato (non solo disattivato).

**Recommended Solution**:
Creare `uninstall.php` nella root del plugin:

```php
<?php
/**
 * Uninstall ScacchiTrack
 *
 * Eseguito quando il plugin viene disinstallato (non disattivato)
 * Rimuove tutti i dati del plugin dal database
 */

// Se uninstall non chiamato da WordPress, esci
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Elimina tutti i post del tipo scacchipartita
$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'scacchipartita'");

// Elimina metadata orfani
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})");

// Elimina relazioni tassonomie
$wpdb->query("DELETE FROM {$wpdb->term_relationships}
              WHERE term_taxonomy_id IN (
                  SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy}
                  WHERE taxonomy IN ('apertura_scacchi', 'tipo_partita', 'etichetta_partita')
              )");

// Elimina tassonomie
$wpdb->query("DELETE FROM {$wpdb->term_taxonomy}
              WHERE taxonomy IN ('apertura_scacchi', 'tipo_partita', 'etichetta_partita')");

// Elimina termini orfani
$wpdb->query("DELETE FROM {$wpdb->terms}
              WHERE term_id NOT IN (SELECT term_id FROM {$wpdb->term_taxonomy})");

// Elimina transients (cache)
delete_transient('scacchitrack_stats_global');

// Elimina opzioni plugin (se ne esistono)
delete_option('scacchitrack_version');
delete_option('scacchitrack_settings');

// Clear any cached data
wp_cache_flush();
```

**Alternative - Opzionale**:
Chiedere conferma utente prima di eliminare dati:

```php
// Aggiungere in admin settings una checkbox
"Conserva dati alla disinstallazione" (default: ON)
```

**Benefits**:
- Database pulito dopo disinstallazione
- Best practice WordPress
- Rispetta privacy utente

---

### 3. Validazione Rigorosa Import Files

**Priority**: MEDIUM
**Effort**: LOW
**Impact**: MEDIUM (security/UX)

**Problem**:
L'import PGN non verifica esplicitamente tipo file, dimensione massima, o numero massimo file batch.

**Current Code** (templates/admin/import.php):
```php
if (isset($_POST['scacchitrack_import']) && isset($_FILES['pgn_file'])) {
    $file = $_FILES['pgn_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = __('Errore nel caricamento del file.', 'scacchitrack');
    } else {
        $importer = new ScacchiTrack_Import_Handler();
        // ... procede con import
    }
}
```

**Recommended Solution**:
```php
if (isset($_POST['scacchitrack_import']) && isset($_FILES['pgn_file'])) {
    $file = $_FILES['pgn_file'];

    // Validazioni
    $validation = scacchitrack_validate_upload_file($file);

    if (is_wp_error($validation)) {
        $error_message = $validation->get_error_message();
    } else {
        $importer = new ScacchiTrack_Import_Handler();
        // ... procede con import
    }
}

/**
 * Valida file upload per import PGN
 *
 * @param array $file File from $_FILES
 * @return true|WP_Error True se valido, WP_Error altrimenti
 */
function scacchitrack_validate_upload_file($file) {
    // Check upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', __('Errore nel caricamento del file.', 'scacchitrack'));
    }

    // Check file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        return new WP_Error('file_too_large',
            sprintf(__('File troppo grande. Massimo %s.', 'scacchitrack'),
            size_format($max_size)));
    }

    // Check file extension
    $allowed_extensions = array('pgn', 'txt');
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        return new WP_Error('invalid_file_type',
            __('Tipo file non valido. Usa .pgn o .txt', 'scacchitrack'));
    }

    // Check MIME type (opzionale, .pgn è text/plain)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = array('text/plain', 'application/octet-stream');
    if (!in_array($mime, $allowed_mimes)) {
        return new WP_Error('invalid_mime',
            __('Formato file non riconosciuto.', 'scacchitrack'));
    }

    return true;
}

// Per batch import, limitare numero file
function scacchitrack_validate_batch_upload($files) {
    $max_files = 20; // massimo 20 file alla volta

    if (count($files['name']) > $max_files) {
        return new WP_Error('too_many_files',
            sprintf(__('Massimo %d file per batch.', 'scacchitrack'), $max_files));
    }

    // Valida ogni file
    for ($i = 0; $i < count($files['name']); $i++) {
        $file = array(
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        );

        $validation = scacchitrack_validate_upload_file($file);
        if (is_wp_error($validation)) {
            return $validation;
        }
    }

    return true;
}
```

**Benefits**:
- Previene upload file enormi
- Migliora sicurezza
- UX migliore con messaggi errore chiari

---

## Medium Priority (v1.2)

### 4. Capability Checks nelle Bulk Actions

**Priority**: MEDIUM
**Effort**: LOW
**Impact**: MEDIUM (security)

**Problem**:
Le bulk actions custom non verificano esplicitamente le capabilities dell'utente.

**Current Code** (admin-enhancements.php):
```php
public function handle_bulk_actions($redirect_to, $action, $post_ids) {
    // Nessun capability check esplicito
    // ...
}
```

**Recommended Solution**:
```php
public function handle_bulk_actions($redirect_to, $action, $post_ids) {
    // Verifica capability
    if (!current_user_can('edit_partite')) {
        return $redirect_to;
    }

    // Verifica nonce
    if (!isset($_REQUEST['_wpnonce'])) {
        return $redirect_to;
    }

    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-posts')) {
        return $redirect_to;
    }

    // Procedi con bulk action...
}
```

---

### 5. Migliorare Coverage PHPDoc

**Priority**: MEDIUM
**Effort**: MEDIUM
**Impact**: LOW (maintainability)

**Problem**:
Non tutte le funzioni hanno PHPDoc completo.

**Current Coverage**: ~60%

**Recommended Standard**:
```php
/**
 * Breve descrizione della funzione
 *
 * Descrizione più dettagliata se necessario.
 * Può essere multi-riga.
 *
 * @since 1.0.0
 * @param string $param1 Descrizione parametro
 * @param int    $param2 Descrizione parametro
 * @param array  $options {
 *     Optional. Array di opzioni.
 *
 *     @type string $key1 Descrizione opzione
 *     @type bool   $key2 Descrizione opzione
 * }
 * @return mixed Descrizione return value
 */
function scacchitrack_function_name($param1, $param2, $options = array()) {
    // ...
}
```

**Priority Functions**:
1. Tutte le funzioni pubbliche API
2. Tutte le funzioni in functions.php
3. Metodi delle classi

---

### 6. Wrappare Error Logging con WP_DEBUG

**Priority**: MEDIUM
**Effort**: LOW
**Impact**: LOW (clean logs)

**Problem**:
`error_log()` in ajax.php rimane attivo in produzione.

**Current Code** (ajax.php):
```php
error_log('ScacchiTrack - Inizio filter_games');
error_log('POST data: ' . print_r($_POST, true));
```

**Recommended Solution**:
```php
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('ScacchiTrack - Inizio filter_games');
    error_log('POST data: ' . print_r($_POST, true));
}

// O meglio, creare helper function
function scacchitrack_log($message, $data = null) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $log_message = 'ScacchiTrack: ' . $message;

    if ($data !== null) {
        $log_message .= ' | Data: ' . print_r($data, true);
    }

    error_log($log_message);
}

// Uso
scacchitrack_log('Inizio filter_games', $_POST);
```

---

## Low Priority (v1.3+)

### 7. Asset Minification

**Priority**: LOW
**Effort**: MEDIUM
**Impact**: LOW (performance)

**Recommendation**:
- Minify CSS e JS per produzione
- Usare build tool (Webpack, Gulp, etc.)
- Mantenere versioni non-minified per development

**Implementation**:
```bash
npm install --save-dev gulp gulp-uglify gulp-clean-css
```

```javascript
// gulpfile.js
const gulp = require('gulp');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');

gulp.task('minify-js', () => {
    return gulp.src('js/*.js')
        .pipe(uglify())
        .pipe(gulp.dest('dist/js'));
});

gulp.task('minify-css', () => {
    return gulp.src('css/*.css')
        .pipe(cleanCSS())
        .pipe(gulp.dest('dist/css'));
});
```

---

### 8. Unit Tests

**Priority**: LOW
**Effort**: HIGH
**Impact**: MEDIUM (long-term quality)

**Recommendation**:
Implementare PHPUnit tests per:
1. Parsing PGN
2. Statistiche calculations
3. Sanitization functions

**Example**:
```php
// tests/test-statistics.php
class Test_ScacchiTrack_Statistics extends WP_UnitTestCase {

    public function test_calculate_win_percentage() {
        $stats = array(
            'wins' => 7,
            'draws' => 2,
            'losses' => 1,
            'total_games' => 10
        );

        $percentage = calculate_win_percentage($stats);

        $this->assertEquals(70, $percentage);
    }
}
```

---

### 9. Internationalization Completa

**Priority**: LOW
**Effort**: MEDIUM
**Impact**: LOW (se solo per IT)

**Current**: Strings tradotte con `__()`, text domain 'scacchitrack'

**Recommendations**:
1. Generare file .pot per traduttori
2. Testare con lingua diversa dall'italiano
3. Verificare format date/numbers per diverse locale

**Commands**:
```bash
# Genera .pot file
wp i18n make-pot . languages/scacchitrack.pot

# Verifica strings
wp i18n check languages/scacchitrack.pot
```

---

### 10. REST API Endpoints

**Priority**: LOW
**Effort**: HIGH
**Impact**: LOW (future-proofing)

**Recommendation**:
Esporre dati via WordPress REST API per:
- Headless WordPress integrations
- Mobile apps
- Third-party integrations

**Example**:
```php
// includes/rest-api.php
add_action('rest_api_init', function() {
    register_rest_route('scacchitrack/v1', '/games', array(
        'methods' => 'GET',
        'callback' => 'scacchitrack_get_games',
        'permission_callback' => function() {
            return current_user_can('read');
        }
    ));
});

function scacchitrack_get_games($request) {
    $args = array(
        'post_type' => 'scacchipartita',
        'posts_per_page' => 10
    );

    $query = new WP_Query($args);

    $games = array();
    while ($query->have_posts()) {
        $query->the_post();
        $games[] = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'white' => get_post_meta(get_the_ID(), '_giocatore_bianco', true),
            // ...
        );
    }

    return new WP_REST_Response($games, 200);
}
```

---

## Summary Table

| # | Recommendation | Priority | Effort | Impact | Version |
|---|----------------|----------|--------|--------|---------|
| 1 | Caching statistiche | HIGH | MED | HIGH | 1.1 |
| 2 | Uninstall hook | HIGH | LOW | MED | 1.1 |
| 3 | Validazione import | MED | LOW | MED | 1.1 |
| 4 | Capability checks bulk | MED | LOW | MED | 1.2 |
| 5 | PHPDoc coverage | MED | MED | LOW | 1.2 |
| 6 | WP_DEBUG logging | MED | LOW | LOW | 1.2 |
| 7 | Asset minification | LOW | MED | LOW | 1.3 |
| 8 | Unit tests | LOW | HIGH | MED | 1.3 |
| 9 | i18n completa | LOW | MED | LOW | 1.3 |
| 10 | REST API | LOW | HIGH | LOW | 2.0 |

## Implementation Roadmap

### Version 1.1 (Post-launch - 1 mese)
- [ ] Implementare caching statistiche
- [ ] Creare uninstall.php
- [ ] Migliorare validazione import

**Effort**: ~2-3 giorni
**Testing**: 1 giorno

### Version 1.2 (3 mesi)
- [ ] Aggiungere capability checks
- [ ] Migliorare PHPDoc
- [ ] Refactor error logging

**Effort**: ~2 giorni
**Testing**: 1 giorno

### Version 1.3 (6 mesi)
- [ ] Setup build process per minification
- [ ] Iniziare unit tests
- [ ] Migliorare i18n

**Effort**: ~1 settimana
**Testing**: 2 giorni

### Version 2.0 (12 mesi)
- [ ] REST API completa
- [ ] Gutenberg blocks (considerare)
- [ ] Advanced features (engine analysis integration, etc.)

**Effort**: TBD
**Testing**: TBD

---

**Document Version**: 1.0
**Last Updated**: 2025-11-14
**Maintained By**: Andrea Tozzi
