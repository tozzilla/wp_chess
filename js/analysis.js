/* ScacchiTrack - Modulo di analisi avanzata */

(function($) {
    'use strict';

    class GameAnalyzer {
        constructor(scacchitrack) {
            this.scacchitrack = scacchitrack;
            this.evaluations = [];
            this.bestMoves = [];
            this.moveAnnotations = [];
            this.isAnalyzing = false;
            this.currentMoveIndex = 0;

            // Riferimenti DOM
            this.elements = {
                graph: $('#evaluation-graph'),
                graphCanvas: $('#eval-chart'),
                bestMove: $('#best-move-display'),
                analysisBtn: $('#analyze-game-btn'),
                analysisProgress: $('#analysis-progress'),
                moveAnnotations: $('.move-annotations')
            };

            // Chart.js instance
            this.chart = null;

            // Soglie per classificazione mosse
            this.thresholds = {
                blunder: -2.0,      // Perdita > 2 pedoni = ??
                mistake: -1.0,      // Perdita > 1 pedone = ?
                inaccuracy: -0.5,   // Perdita > 0.5 pedoni = ?!
                good: 0.3,          // Guadagno > 0.3 = !
                brilliant: 1.0      // Guadagno > 1 pedone = !!
            };

            this.initializeChart();
            this.bindEvents();
        }

        /**
         * Inizializza il grafico Chart.js
         */
        initializeChart() {
            if (!this.elements.graphCanvas.length || typeof Chart === 'undefined') {
                console.log('Chart.js non disponibile');
                return;
            }

            const ctx = this.elements.graphCanvas[0].getContext('2d');

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Valutazione',
                        data: [],
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    return 'Mossa ' + items[0].label;
                                },
                                label: (context) => {
                                    const value = context.parsed.y;
                                    if (Math.abs(value) >= 999) {
                                        return value > 0 ? 'Matto Bianco' : 'Matto Nero';
                                    }
                                    return 'Valutazione: ' + (value > 0 ? '+' : '') + value.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            min: -10,
                            max: 10,
                            grid: {
                                color: (context) => {
                                    if (context.tick.value === 0) {
                                        return '#666';
                                    }
                                    return 'rgba(0, 0, 0, 0.1)';
                                }
                            },
                            ticks: {
                                callback: (value) => {
                                    if (Math.abs(value) >= 999) {
                                        return 'M';
                                    }
                                    return value > 0 ? '+' + value : value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    onClick: (event, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            this.scacchitrack.goToMove(index);
                        }
                    }
                }
            });
        }

        /**
         * Bind eventi
         */
        bindEvents() {
            // Pulsante analizza partita
            this.elements.analysisBtn.on('click', () => {
                this.analyzeFullGame();
            });

            // Aggiorna il grafico quando cambia mossa
            $(document).on('scacchitrack:moveChanged', (e, data) => {
                this.currentMoveIndex = data.index;
                this.updateCurrentPosition();
            });
        }

        /**
         * Analizza l'intera partita
         */
        async analyzeFullGame() {
            if (!this.scacchitrack.evaluator || this.isAnalyzing) {
                return;
            }

            this.isAnalyzing = true;
            this.evaluations = [];
            this.bestMoves = [];
            this.moveAnnotations = [];

            const totalMoves = this.scacchitrack.moves.length;

            // Mostra progress bar
            this.elements.analysisBtn.prop('disabled', true).text('Analisi in corso...');
            this.elements.analysisProgress.show();

            // Reset del gioco alla posizione iniziale
            const originalIndex = this.scacchitrack.pgnIndex;
            this.scacchitrack.goToStart();

            // Valuta posizione iniziale
            await this.evaluatePosition(0);

            // Analizza ogni mossa
            for (let i = 0; i < totalMoves; i++) {
                // Esegui la mossa
                this.scacchitrack.game.move(this.scacchitrack.moves[i]);

                // Valuta la nuova posizione
                await this.evaluatePosition(i + 1);

                // Aggiorna progress
                const progress = ((i + 1) / totalMoves) * 100;
                this.elements.analysisProgress.find('.progress-bar').css('width', progress + '%');

                // Piccola pausa per non bloccare l'UI
                await this.sleep(10);
            }

            // Calcola annotazioni (blunders, mistakes, ecc.)
            this.calculateAnnotations();

            // Aggiorna il grafico
            this.updateGraph();

            // Ripristina posizione originale
            this.scacchitrack.goToMove(originalIndex - 1);

            // Nascondi progress bar
            this.elements.analysisProgress.hide();
            this.elements.analysisBtn.prop('disabled', false).text('Analizza Partita');

            this.isAnalyzing = false;

            // Mostra il pannello grafico
            this.elements.graph.show();

            // Trigger evento analisi completata
            $(document).trigger('scacchitrack:analysisComplete', [this]);
        }

        /**
         * Valuta una posizione specifica
         */
        evaluatePosition(moveIndex) {
            return new Promise((resolve) => {
                const fen = this.scacchitrack.game.fen();

                this.scacchitrack.evaluator.evaluate(fen, (evaluation) => {
                    this.evaluations[moveIndex] = evaluation;

                    // Se modalità avanzata, ottieni anche la mossa migliore
                    if (this.scacchitrack.evaluationMode === 'advanced') {
                        this.getBestMove(fen, moveIndex, resolve);
                    } else {
                        resolve();
                    }
                });

                // Timeout di sicurezza per modalità semplice
                if (this.scacchitrack.evaluationMode === 'simple') {
                    setTimeout(resolve, 50);
                }
            });
        }

        /**
         * Ottiene la mossa migliore da Stockfish
         */
        getBestMove(fen, moveIndex, callback) {
            if (!this.scacchitrack.evaluator.stockfish) {
                callback();
                return;
            }

            const messageHandler = (event) => {
                const line = event.data;

                // Cerca la mossa migliore
                if (line.indexOf('bestmove') > -1) {
                    const match = line.match(/bestmove ([a-h][1-8][a-h][1-8][qrbn]?)/);
                    if (match) {
                        this.bestMoves[moveIndex] = match[1];
                    }

                    this.scacchitrack.evaluator.stockfish.removeEventListener('message', messageHandler);
                    callback();
                }
            };

            this.scacchitrack.evaluator.stockfish.addEventListener('message', messageHandler);

            // Timeout di sicurezza
            setTimeout(() => {
                this.scacchitrack.evaluator.stockfish.removeEventListener('message', messageHandler);
                callback();
            }, 2000);
        }

        /**
         * Calcola le annotazioni per ogni mossa
         */
        calculateAnnotations() {
            this.moveAnnotations = [];

            for (let i = 1; i < this.evaluations.length; i++) {
                const prevEval = this.evaluations[i - 1];
                const currentEval = this.evaluations[i];

                // Inverti il segno per il nero
                const isWhiteMove = (i % 2) === 1;
                const evalDiff = isWhiteMove ?
                    (currentEval - prevEval) :
                    (prevEval - currentEval);

                let annotation = '';
                let className = '';

                if (evalDiff <= this.thresholds.blunder) {
                    annotation = '??';
                    className = 'blunder';
                } else if (evalDiff <= this.thresholds.mistake) {
                    annotation = '?';
                    className = 'mistake';
                } else if (evalDiff <= this.thresholds.inaccuracy) {
                    annotation = '?!';
                    className = 'inaccuracy';
                } else if (evalDiff >= this.thresholds.brilliant) {
                    annotation = '!!';
                    className = 'brilliant';
                } else if (evalDiff >= this.thresholds.good) {
                    annotation = '!';
                    className = 'good';
                }

                this.moveAnnotations[i - 1] = {
                    annotation: annotation,
                    className: className,
                    evalDiff: evalDiff,
                    prevEval: prevEval,
                    currentEval: currentEval
                };
            }
        }

        /**
         * Aggiorna il grafico
         */
        updateGraph() {
            if (!this.chart) return;

            const labels = [];
            const data = [];

            for (let i = 0; i < this.evaluations.length; i++) {
                if (i === 0) {
                    labels.push('Inizio');
                } else {
                    const moveNum = Math.ceil(i / 2);
                    const color = (i % 2) === 1 ? 'b' : 'n';
                    labels.push(moveNum + color);
                }

                // Limita i valori per il grafico
                let value = this.evaluations[i];
                if (Math.abs(value) >= 999) {
                    value = value > 0 ? 10 : -10;
                } else {
                    value = Math.max(-10, Math.min(10, value));
                }

                data.push(value);
            }

            this.chart.data.labels = labels;
            this.chart.data.datasets[0].data = data;

            // Colora la linea in base a chi è in vantaggio
            this.chart.data.datasets[0].borderColor = (context) => {
                const value = context.parsed?.y;
                if (value > 1) return '#2E7D32';  // Verde per bianco
                if (value < -1) return '#C62828'; // Rosso per nero
                return '#666';                     // Grigio per pari
            };

            this.chart.update();
        }

        /**
         * Aggiorna la posizione corrente
         */
        updateCurrentPosition() {
            if (!this.chart || this.evaluations.length === 0) return;

            // Evidenzia il punto corrente sul grafico
            this.chart.data.datasets[0].pointBackgroundColor = (context) => {
                return context.dataIndex === this.currentMoveIndex ? '#FF9800' : '#4CAF50';
            };
            this.chart.data.datasets[0].pointRadius = (context) => {
                return context.dataIndex === this.currentMoveIndex ? 6 : 3;
            };
            this.chart.update();

            // Mostra la mossa migliore se disponibile
            this.displayBestMove();

            // Mostra l'annotazione della mossa
            this.displayMoveAnnotation();
        }

        /**
         * Mostra la mossa migliore
         */
        displayBestMove() {
            if (!this.elements.bestMove.length) return;

            const bestMove = this.bestMoves[this.currentMoveIndex];

            if (bestMove) {
                // Converti da UCI a SAN
                const sanMove = this.convertUCItoSAN(bestMove);
                this.elements.bestMove.html(
                    '<strong>Mossa migliore:</strong> ' +
                    '<span class="best-move-text">' + sanMove + '</span>'
                ).show();
            } else {
                this.elements.bestMove.hide();
            }
        }

        /**
         * Mostra l'annotazione della mossa
         */
        displayMoveAnnotation() {
            if (this.currentMoveIndex === 0 || !this.moveAnnotations[this.currentMoveIndex - 1]) {
                return;
            }

            const annotation = this.moveAnnotations[this.currentMoveIndex - 1];

            // Aggiorna l'annotazione nella lista mosse
            const moveElement = $(`.move[data-move-index="${this.currentMoveIndex - 1}"]`);
            if (moveElement.length) {
                moveElement.addClass(annotation.className);

                if (annotation.annotation) {
                    moveElement.append(' <span class="move-annotation">' + annotation.annotation + '</span>');
                }
            }
        }

        /**
         * Converte notazione UCI in SAN
         */
        convertUCItoSAN(uci) {
            // Crea una copia temporanea del gioco
            const tempGame = new Chess(this.scacchitrack.game.fen());

            try {
                const move = tempGame.move({
                    from: uci.substring(0, 2),
                    to: uci.substring(2, 4),
                    promotion: uci.length > 4 ? uci[4] : undefined
                });

                return move ? move.san : uci;
            } catch (e) {
                return uci;
            }
        }

        /**
         * Sleep utility
         */
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        /**
         * Ottiene statistiche dell'analisi
         */
        getAnalysisStats() {
            if (this.moveAnnotations.length === 0) {
                return null;
            }

            const stats = {
                blunders: 0,
                mistakes: 0,
                inaccuracies: 0,
                good: 0,
                brilliant: 0
            };

            this.moveAnnotations.forEach(annotation => {
                if (annotation.className === 'blunder') stats.blunders++;
                else if (annotation.className === 'mistake') stats.mistakes++;
                else if (annotation.className === 'inaccuracy') stats.inaccuracies++;
                else if (annotation.className === 'brilliant') stats.brilliant++;
                else if (annotation.className === 'good') stats.good++;
            });

            return stats;
        }
    }

    // Esporta la classe
    window.GameAnalyzer = GameAnalyzer;

})(jQuery);
