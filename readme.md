# ScacchiTrack

ScacchiTrack è un plugin WordPress per la gestione e visualizzazione di partite di scacchi.

## Caratteristiche

- Gestione delle partite di scacchi con notazione PGN
- Scacchiera interattiva per rivedere le partite
- Sistema di filtri per ricercare le partite
- Supporto per tornei e giocatori
- Shortcode per la visualizzazione delle partite

## Requisiti

- WordPress 5.0 o superiore
- PHP 7.4 o superiore
- jQuery (incluso in WordPress)

## Installazione

1. Carica la cartella `scacchitrack` nella directory `/wp-content/plugins/`
2. Attiva il plugin dal menu 'Plugin' in WordPress

## Utilizzo

### Shortcode disponibili

- `[scacchitrack_partite]` - Mostra la lista di tutte le partite con filtri
- `[scacchitrack_partita id="X"]` - Mostra una singola partita

### Esempio di utilizzo

```php
// Per mostrare la lista delle partite
[scacchitrack_partite per_page="10"]

// Per mostrare una partita specifica
[scacchitrack_partita id="123"]
```

## Versione

0.9d

## Changelog

### 0.9d
- Prima release
- Implementazione base della gestione partite
- Visualizzazione scacchiera interattiva
- Sistema di filtri base
- Shortcode per lista partite e singola partita

## Sviluppo

Per contribuire allo sviluppo:
1. Fork del repository
2. Crea un branch per la tua feature
3. Commit delle modifiche
4. Push al branch
5. Nuova Pull Request

## Licenza

GPL v2 o successive