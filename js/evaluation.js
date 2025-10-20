/* ScacchiTrack - Modulo di valutazione della posizione */

(function($) {
    'use strict';

    class PositionEvaluator {
        constructor(mode = 'simple') {
            this.mode = mode; // 'simple' o 'advanced'
            this.currentEvaluation = 0;
            this.stockfish = null;
            this.isReady = false;
            this.evaluationCallbacks = [];

            if (mode === 'advanced') {
                this.initStockfish();
            }
        }

        /**
         * Inizializza Stockfish engine
         */
        initStockfish() {
            try {
                // Carica Stockfish da CDN
                if (typeof STOCKFISH === 'undefined') {
                    console.error('Stockfish non disponibile. Modalità semplice attivata.');
                    this.mode = 'simple';
                    return;
                }

                this.stockfish = new Worker(scacchitrackData.stockfishUrl);

                this.stockfish.onmessage = (event) => {
                    this.handleStockfishMessage(event.data);
                };

                this.stockfish.onerror = (error) => {
                    console.error('Errore Stockfish:', error);
                    this.mode = 'simple';
                };

                // Inizializza UCI
                this.stockfish.postMessage('uci');

            } catch (e) {
                console.error('Impossibile inizializzare Stockfish:', e);
                this.mode = 'simple';
            }
        }

        /**
         * Gestisce i messaggi da Stockfish
         */
        handleStockfishMessage(line) {
            // Controlla se Stockfish è pronto
            if (line === 'uciok') {
                this.stockfish.postMessage('isready');
            }

            if (line === 'readyok') {
                this.isReady = true;
                console.log('Stockfish pronto');
            }

            // Estrai il punteggio
            if (line.indexOf('score cp') > -1) {
                const match = line.match(/score cp (-?\d+)/);
                if (match) {
                    this.currentEvaluation = parseInt(match[1]) / 100;
                    this.notifyCallbacks();
                }
            }

            // Gestisci scacco matto
            if (line.indexOf('score mate') > -1) {
                const match = line.match(/score mate (-?\d+)/);
                if (match) {
                    const mateIn = parseInt(match[1]);
                    this.currentEvaluation = mateIn > 0 ? 999 : -999;
                    this.notifyCallbacks();
                }
            }
        }

        /**
         * Valuta una posizione (FEN)
         */
        evaluate(fen, callback) {
            if (callback) {
                this.evaluationCallbacks.push(callback);
            }

            if (this.mode === 'advanced' && this.stockfish && this.isReady) {
                this.evaluateWithStockfish(fen);
            } else {
                this.evaluateSimple(fen);
            }
        }

        /**
         * Valutazione con Stockfish
         */
        evaluateWithStockfish(fen) {
            if (!this.isReady) {
                setTimeout(() => this.evaluateWithStockfish(fen), 100);
                return;
            }

            const depth = scacchitrackData.evaluationDepth || 15;

            this.stockfish.postMessage('position fen ' + fen);
            this.stockfish.postMessage('go depth ' + depth);
        }

        /**
         * Valutazione semplice basata sul materiale
         */
        evaluateSimple(fen) {
            const pieceValues = {
                'p': 1, 'n': 3, 'b': 3, 'r': 5, 'q': 9, 'k': 0
            };

            const positionBonuses = {
                'p': [
                    [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                    [0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5, 0.5],
                    [0.1, 0.1, 0.2, 0.3, 0.3, 0.2, 0.1, 0.1],
                    [0.0, 0.0, 0.1, 0.2, 0.2, 0.1, 0.0, 0.0],
                    [0.0, 0.0, 0.0, 0.2, 0.2, 0.0, 0.0, 0.0],
                    [0.0, 0.0, -0.1, 0.0, 0.0, -0.1, 0.0, 0.0],
                    [0.0, 0.0, 0.0, -0.2, -0.2, 0.0, 0.0, 0.0],
                    [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0]
                ],
                'n': [
                    [-0.5, -0.4, -0.3, -0.3, -0.3, -0.3, -0.4, -0.5],
                    [-0.4, -0.2, 0.0, 0.0, 0.0, 0.0, -0.2, -0.4],
                    [-0.3, 0.0, 0.1, 0.15, 0.15, 0.1, 0.0, -0.3],
                    [-0.3, 0.05, 0.15, 0.2, 0.2, 0.15, 0.05, -0.3],
                    [-0.3, 0.0, 0.15, 0.2, 0.2, 0.15, 0.0, -0.3],
                    [-0.3, 0.05, 0.1, 0.15, 0.15, 0.1, 0.05, -0.3],
                    [-0.4, -0.2, 0.0, 0.05, 0.05, 0.0, -0.2, -0.4],
                    [-0.5, -0.4, -0.3, -0.3, -0.3, -0.3, -0.4, -0.5]
                ]
            };

            let whiteScore = 0;
            let blackScore = 0;

            // Parse FEN
            const parts = fen.split(' ');
            const position = parts[0];
            const rows = position.split('/');

            rows.forEach((row, rowIndex) => {
                let colIndex = 0;
                for (let char of row) {
                    if (/\d/.test(char)) {
                        colIndex += parseInt(char);
                    } else {
                        const piece = char.toLowerCase();
                        const isWhite = char === char.toUpperCase();
                        const value = pieceValues[piece] || 0;

                        // Bonus posizionale per pedoni e cavalli
                        let bonus = 0;
                        if (positionBonuses[piece]) {
                            const bonusRow = isWhite ? rowIndex : 7 - rowIndex;
                            bonus = positionBonuses[piece][bonusRow][colIndex] || 0;
                        }

                        if (isWhite) {
                            whiteScore += value + bonus;
                        } else {
                            blackScore += value + bonus;
                        }

                        colIndex++;
                    }
                }
            });

            // Bonus per controllo del centro
            const centerControl = this.evaluateCenterControl(fen);
            whiteScore += centerControl;

            this.currentEvaluation = whiteScore - blackScore;
            this.notifyCallbacks();
        }

        /**
         * Valuta il controllo del centro (semplificato)
         */
        evaluateCenterControl(fen) {
            // Implementazione semplificata
            return 0;
        }

        /**
         * Notifica i callback registrati
         */
        notifyCallbacks() {
            this.evaluationCallbacks.forEach(callback => {
                callback(this.currentEvaluation);
            });
            this.evaluationCallbacks = [];
        }

        /**
         * Converte la valutazione in percentuale per la barra
         */
        evaluationToPercentage(evaluation) {
            // Usa funzione tangente per convertire
            // valori estremi (-10, +10) in percentuali (0, 100)
            if (Math.abs(evaluation) >= 999) {
                return evaluation > 0 ? 100 : 0;
            }
            return 50 + (Math.atan(evaluation / 4) / Math.PI * 100);
        }

        /**
         * Formatta la valutazione per la visualizzazione
         */
        formatEvaluation(evaluation) {
            if (Math.abs(evaluation) >= 999) {
                const mateIn = evaluation > 0 ? 'M+' : 'M-';
                return mateIn;
            }

            if (evaluation > 0) {
                return '+' + evaluation.toFixed(2);
            }
            return evaluation.toFixed(2);
        }

        /**
         * Cambia modalità di valutazione
         */
        setMode(mode) {
            if (this.mode !== mode) {
                this.mode = mode;
                if (mode === 'advanced' && !this.stockfish) {
                    this.initStockfish();
                }
            }
        }

        /**
         * Pulisce le risorse
         */
        destroy() {
            if (this.stockfish) {
                this.stockfish.postMessage('quit');
                this.stockfish.terminate();
                this.stockfish = null;
            }
        }
    }

    // Esporta la classe
    window.PositionEvaluator = PositionEvaluator;

})(jQuery);
