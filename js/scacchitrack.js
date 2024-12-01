/* ScacchiTrack - Script principale per la gestione della scacchiera interattiva */

(function($) {
    'use strict';

    class ScacchiTrack {
        constructor() {
            // Stato iniziale
            this.game = new Chess();
            this.pgnIndex = 0;
            this.moves = [];
            this.isPlaying = false;
            this.playInterval = null;
            this.playSpeed = 2000;
            this.boardOrientation = 'white';
    
            // Configurazione scacchiera
            this.config = {
                position: 'start',
                pieceTheme: (piece) => {
                    return scacchitrackData.pieces[piece];
                },
                showNotation: true,
                draggable: false,
                orientation: 'white'
            };
    
     // Elementi DOM
     this.elements = {
        board: $('#scacchiera'),
        pgnViewer: $('#pgn-viewer'),
        startBtn: $('#startBtn'),
        prevBtn: $('#prevBtn'),
        playBtn: $('#playBtn'),
        nextBtn: $('#nextBtn'),
        endBtn: $('#endBtn'),
        flipBtn: $('#flipBtn'),
        velocitaRange: $('#velocitaRange'),
        togglePgnBtn: $('#togglePgn')
    };
    
            this.init();
        }

        init() {
            // Inizializza la scacchiera
            this.board = Chessboard('scacchiera', this.config);

            // Carica il PGN se disponibile
            if (scacchitrackData.pgn) {
                this.loadPgn(scacchitrackData.pgn);
            }

            // Bind degli eventi
            this.bindEvents();

            // Inizializza il visualizzatore PGN
            this.initPgnViewer();

            // Responsive resize
            $(window).resize(() => {
                this.board.resize();
            });
        }

        loadPgn(pgn) {
            try {
                // Carica il PGN nel gioco
                this.game.load_pgn(pgn);
                
                // Estrai le mosse
                this.moves = this.parseMoves();
                
                // Reset della posizione
                this.pgnIndex = 0;
                this.board.position('start');
                
                // Aggiorna il visualizzatore PGN
                this.updatePgnViewer();
                
                return true;
            } catch (e) {
                console.error('Errore nel caricamento del PGN:', e);
                return false;
            }
        }

        parseMoves() {
            const history = this.game.history({ verbose: true });
            return history.map(move => ({
                from: move.from,
                to: move.to,
                promotion: move.promotion,
                san: move.san
            }));
        }

        bindEvents() {
            // Bottoni di controllo
    this.elements.startBtn.on('click', (e) => {
        e.preventDefault();
        this.goToStart();
    });
    this.elements.prevBtn.on('click', (e) => {
        e.preventDefault();
        this.prevMove();
    });
    this.elements.playBtn.on('click', (e) => {
        e.preventDefault();
        this.togglePlay();
    });
    this.elements.nextBtn.on('click', (e) => {
        e.preventDefault();
        this.nextMove();
    });
    this.elements.endBtn.on('click', (e) => {
        e.preventDefault();
        this.goToEnd();
    });
    this.elements.flipBtn.on('click', (e) => {
        e.preventDefault();
        this.flipBoard();
    });

            // Controllo velocità
            this.elements.velocitaRange.on('input', (e) => {
                this.playSpeed = 3000 / parseInt(e.target.value);
                if (this.isPlaying) {
                    this.togglePlay();
                    this.togglePlay();
                }
            });

            // Toggle PGN
            this.elements.togglePgnBtn.on('click', () => {
                $('.pgn-raw').slideToggle();
                this.elements.togglePgnBtn.text(
                    $('.pgn-raw').is(':visible') ? 
                    scacchitrackData.i18n.hidePgn : 
                    scacchitrackData.i18n.showPgn
                );
            });

            // Click sulle mosse nel visualizzatore PGN
            this.elements.pgnViewer.on('click', '.move', (e) => {
                const moveIndex = $(e.target).data('move-index');
                if (typeof moveIndex !== 'undefined') {
                    this.goToMove(moveIndex);
                }
            });

            // Gestione tasti freccia
            $(document).on('keydown', (e) => {
                if (!this.isPlaying) {
                    switch(e.which) {
                        case 37: // freccia sinistra
                            this.prevMove();
                            break;
                        case 39: // freccia destra
                            this.nextMove();
                            break;
                    }
                }
            });
        }

        initPgnViewer() {
            this.elements.pgnViewer.empty();
            if (this.moves.length === 0) {
                this.elements.pgnViewer.html(`<p>${scacchitrackData.i18n.noMoves}</p>`);
                return;
            }
            this.updatePgnViewer();
        }

        updatePgnViewer() {
            const container = $('<div class="pgn-moves"></div>');
            
            for (let i = 0; i < this.moves.length; i++) {
                if (i % 2 === 0) {
                    container.append(
                        $(`<span class="move-number">${(i/2 + 1)}.</span>`)
                    );
                }
                
                const moveSpan = $(
                    `<span class="move ${i === this.pgnIndex - 1 ? 'current' : ''}" ` +
                    `data-move-index="${i}">${this.moves[i].san}</span>`
                );
                
                container.append(moveSpan);
                container.append(' ');
            }
        
            this.elements.pgnViewer.html(container);
        
            // Modifichiamo questa parte per non scrollare alla posizione se stiamo usando i controlli
            const currentMove = this.elements.pgnViewer.find('.current');
            if (currentMove.length) {
                // Calcoliamo se la mossa è fuori dalla vista
                const viewer = this.elements.pgnViewer[0];
                const move = currentMove[0];
                const viewerRect = viewer.getBoundingClientRect();
                const moveRect = move.getBoundingClientRect();
        
                if (moveRect.top < viewerRect.top || moveRect.bottom > viewerRect.bottom) {
                    move.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }
        }

        goToStart() {
            this.stopPlay();
            this.game.reset();
            this.pgnIndex = 0;
            this.board.position('start');
            this.updateStatus();
        }

        goToEnd() {
            this.stopPlay();
            while (this.pgnIndex < this.moves.length) {
                this.game.move(this.moves[this.pgnIndex]);
                this.pgnIndex++;
            }
            this.board.position(this.game.fen());
            this.updateStatus();
        }

        prevMove() {
            if (this.pgnIndex > 0) {
                this.stopPlay();
                this.game.undo();
                this.pgnIndex--;
                this.board.position(this.game.fen());
                this.updateStatus();
            }
        }

        nextMove() {
            if (this.pgnIndex < this.moves.length) {
                this.stopPlay();
                this.game.move(this.moves[this.pgnIndex]);
                this.pgnIndex++;
                this.board.position(this.game.fen());
                this.updateStatus();
            }
        }

        goToMove(index) {
            this.stopPlay();
            this.game.reset();
            this.board.position('start');
            this.pgnIndex = 0;
            
            for (let i = 0; i <= index; i++) {
                this.game.move(this.moves[i]);
                this.pgnIndex++;
            }
            
            this.board.position(this.game.fen());
            this.updateStatus();
        }

        togglePlay() {
            if (this.isPlaying) {
                this.stopPlay();
            } else {
                this.startPlay();
            }
        }

        startPlay() {
            // Se siamo alla fine, torniamo all'inizio
            if (this.pgnIndex >= this.moves.length) {
                this.goToStart();
                // Importante: ritorniamo qui per dare tempo alla UI di aggiornarsi
                setTimeout(() => this.startPlay(), 100);
                return;
            }
            
            this.isPlaying = true;
            this.elements.playBtn.find('.dashicons')
                .removeClass('dashicons-controls-play')
                .addClass('dashicons-controls-pause');
            
            this.playInterval = setInterval(() => {
                if (this.pgnIndex < this.moves.length) {
                    this.nextMove();
                } else {
                    this.stopPlay();
                }
            }, this.playSpeed);
        }

        stopPlay() {
            this.isPlaying = false;
            this.elements.playBtn.find('.dashicons')
                .removeClass('dashicons-controls-pause')
                .addClass('dashicons-controls-play');
            
            if (this.playInterval) {
                clearInterval(this.playInterval);
                this.playInterval = null;
            }
        }

        flipBoard() {
            this.boardOrientation = this.boardOrientation === 'white' ? 'black' : 'white';
            this.board.flip();
        }

        updateStatus() {
            // Aggiorna il visualizzatore PGN
            this.updatePgnViewer();
            
            // Aggiorna lo stato dei bottoni
            this.elements.prevBtn.prop('disabled', this.pgnIndex === 0);
            this.elements.nextBtn.prop('disabled', this.pgnIndex >= this.moves.length);
            this.elements.startBtn.prop('disabled', this.pgnIndex === 0);
            this.elements.endBtn.prop('disabled', this.pgnIndex >= this.moves.length);
            
            // Emetti evento personalizzato per lo stato
            $(document).trigger('scacchitrack:moveChanged', {
                index: this.pgnIndex,
                totalMoves: this.moves.length,
                position: this.game.fen(),
                move: this.pgnIndex > 0 ? this.moves[this.pgnIndex - 1] : null,
                isCheck: this.game.in_check(),
                isCheckmate: this.game.in_checkmate(),
                isStalemate: this.game.in_stalemate(),
                isDraw: this.game.in_draw()
            });
        }
    }

    // Inizializzazione quando il documento è pronto
    $(document).ready(() => {
        if ($('#scacchiera').length) {
            window.scacchitrack = new ScacchiTrack();
        }
    });

})(jQuery);