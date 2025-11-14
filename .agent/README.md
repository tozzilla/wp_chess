# ScacchiTrack - Documentazione Progetto

Benvenuto nella documentazione completa di ScacchiTrack. Questa directory contiene tutta la documentazione necessaria per comprendere, sviluppare e mantenere il progetto.

## Indice Documentazione

### System - Stato Corrente del Sistema

Documentazione dello stato attuale dell'architettura, tecnologie e implementazione.

1. **[project_architecture.md](./System/project_architecture.md)**
   - Obiettivo e target del progetto
   - Struttura completa del progetto
   - Stack tecnologico (Backend PHP, Frontend JS, CSS)
   - Punti di integrazione WordPress
   - Funzionalità core implementate
   - Security e best practices

2. **[database_schema.md](./System/database_schema.md)**
   - Schema database WordPress utilizzato
   - Custom Post Type e Taxonomies
   - Post Meta structure completa
   - Query complesse comuni
   - Indici e performance
   - Procedure backup e migration

### SOP - Standard Operating Procedures

Guide pratiche per operazioni di sviluppo comuni.

1. **[development_tasks.md](./SOP/development_tasks.md)**
   - Aggiungere meta field alle partite
   - Creare filtri admin
   - Modificare import PGN
   - Aggiungere statistiche
   - Creare nuovi shortcodes
   - Modificare CSS
   - Debug e troubleshooting
   - Git workflow e deployment

### Tasks - PRD, Testing e Implementation Plans

Directory per Product Requirements Documents, piani di test e implementazione delle feature.

**Status**: Contiene documentazione testing v1.0

**Contenuti Attuali**:
1. **[testing_plan_v1.0.md](./Tasks/testing_plan_v1.0.md)**
   - Piano di test completo per release 1.0
   - 147 test cases definiti
   - Checklist activation, CPT, import, frontend, security, etc.

2. **[static_code_analysis_report.md](./Tasks/static_code_analysis_report.md)**
   - Report analisi statica del codice
   - Security assessment (95/100)
   - Code quality metrics (90/100)
   - Issue trovate e raccomandazioni

3. **[improvement_recommendations.md](./Tasks/improvement_recommendations.md)**
   - 10 raccomandazioni per versioni future
   - Roadmap v1.1, v1.2, v1.3, v2.0
   - Prioritizzazione e effort estimates

4. **[testing_summary.md](./Tasks/testing_summary.md)**
   - Riepilogo completo testing
   - Verdict finale: READY FOR RELEASE 1.0 ✅
   - Metriche e conclusioni

**Template per Future Feature**:
```
Tasks/
├── feature_001_nome_feature/
│   ├── PRD.md              # Product Requirements
│   └── implementation.md   # Piano implementazione
```

## Quick Start

### Per Nuovi Sviluppatori

1. **Comprendi il Progetto**
   - Leggi [project_architecture.md](./System/project_architecture.md)
   - Obiettivo: plugin WordPress per gestione partite di scacchi
   - Tech stack: WordPress + PHP + JavaScript (chess.js/chessboard.js)

2. **Comprendi i Dati**
   - Leggi [database_schema.md](./System/database_schema.md)
   - Custom Post Type: `scacchipartita`
   - 7 meta fields principali
   - 3 taxonomies personalizzate

3. **Inizia a Sviluppare**
   - Consulta [development_tasks.md](./SOP/development_tasks.md)
   - Segui procedure standardizzate
   - Usa checklist pre-commit

### Per Product Managers

1. **Stato Attuale**: v0.9e (prossima 1.0.0)
2. **Feature Implementate**:
   - Import PGN (singolo/batch/paste)
   - Scacchiera interattiva
   - Sistema statistiche completo
   - Analisi partite con grafici
   - Valutazione posizioni (simple/advanced)
   - Controllo accessi

3. **Roadmap v1.0**:
   - Documentazione completa (DONE)
   - Testing completo
   - Performance optimization

## Struttura Progetto

```
wp_chess/
├── .agent/                     # Questa directory - documentazione
│   ├── README.md              # Questo file
│   ├── System/                # Documentazione architettura
│   ├── Tasks/                 # PRD e implementation plans
│   └── SOP/                   # Procedure operative
├── scacchitrack.php           # Main plugin file
├── includes/                  # PHP backend logic
├── js/                        # JavaScript frontend
├── css/                       # Styles
└── templates/                 # PHP templates
```

## Informazioni di Contatto

- **Autore**: Andrea Tozzi
- **Organizzazione**: Empoli Scacchi ASD
- **Repository**: https://github.com/tozzilla/wp_chess
- **Versione**: 0.9e
- **Licenza**: GPL2

## Come Contribuire

### Aggiornare la Documentazione

La documentazione deve rimanere allineata al codice. Dopo ogni modifica significativa:

1. **Nuova Feature Implementata**:
   - Aggiorna `project_architecture.md` sezione "Funzionalità Core"
   - Se modifica schema dati, aggiorna `database_schema.md`
   - Se procedura comune, aggiungi a `development_tasks.md`

2. **Modifica Architettura**:
   - Aggiorna `project_architecture.md`
   - Documenta motivazioni in commit message

3. **Nuova Procedura**:
   - Aggiungi a `development_tasks.md`
   - Segui formato esistente (Quando/Procedura/Note/Testing)

4. **Nuova Feature Complessa**:
   - Crea directory in `Tasks/`
   - Scrivi PRD.md prima di implementare
   - Scrivi implementation.md durante sviluppo

### Workflow Documentazione

```
1. Implementa codice
2. Testa funzionalità
3. Aggiorna documentazione pertinente
4. Commit codice + docs insieme
5. Review: codice e docs devono essere allineati
```

## Riferimenti Rapidi

### File Principali da Conoscere

| File | Scopo | Documentazione |
|------|-------|----------------|
| `includes/cpt.php` | Custom Post Type | [project_architecture.md](./System/project_architecture.md#custom-post-type) |
| `includes/functions.php` | Statistiche e utility | [project_architecture.md](./System/project_architecture.md#sistema-statistiche) |
| `includes/ajax.php` | Handler AJAX | [project_architecture.md](./System/project_architecture.md#ajax-endpoints) |
| `js/scacchitrack.js` | Scacchiera interattiva | [project_architecture.md](./System/project_architecture.md#frontend-javascript) |
| `js/evaluation.js` | Valutazione posizioni | [project_architecture.md](./System/project_architecture.md#valutazione-posizioni) |
| `js/analysis.js` | Analisi partite | [project_architecture.md](./System/project_architecture.md#analisi-completa-partita) |

### Query Database Comuni

Trova query SQL pronte all'uso in [database_schema.md](./System/database_schema.md#query-complesse-comuni):
- Top Players
- Tournament Statistics
- Filtri partite

### Task Comuni

Guide step-by-step in [development_tasks.md](./SOP/development_tasks.md):
- Aggiungere meta field
- Creare filtri
- Modificare import
- Debug problemi

## Note di Manutenzione

### Quando Aggiornare Questa Documentazione

**Sempre**:
- Nuova feature implementata
- Modifica schema database
- Cambio architettura
- Nuova procedura standardizzata

**Mai**:
- Fix bug minori senza impatto architettura
- Refactoring interno senza cambio API
- Modifiche CSS pure

### Review Periodica

Ogni major release (1.0, 2.0, etc.):
- Review completa documentazione
- Aggiorna screenshot se presenti
- Verifica accuracy info tecniche
- Rimuovi riferimenti obsoleti

## Versioning

Questa documentazione segue il versionamento del plugin:

| Doc Version | Plugin Version | Date | Changes |
|-------------|----------------|------|---------|
| 1.0 | 0.9e | 2025-11-14 | Documentazione iniziale completa |

## Changelog Documentazione

### v1.1 - 2025-11-14 (Testing Release)
- Aggiunta documentazione testing completa
- testing_plan_v1.0.md: 147 test cases definiti
- static_code_analysis_report.md: Analisi statica codice (95/100 security)
- improvement_recommendations.md: Roadmap v1.1-v2.0
- testing_summary.md: Verdict READY FOR RELEASE 1.0 ✅
- Aggiornato README.md con riferimenti testing

### v1.0 - 2025-11-14 (Initial Release)
- Creazione documentazione iniziale
- project_architecture.md: Architettura completa
- database_schema.md: Schema database dettagliato
- development_tasks.md: 10 procedure operative
- README.md: Indice e guida navigazione

---

**Ultima modifica**: 2025-11-14
**Manutentore Documentazione**: Andrea Tozzi
**Status**: Completa e testata - READY FOR RELEASE 1.0 ✅
