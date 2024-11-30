(function($) {
    'use strict';

    class ScacchiTrackFilters {
        constructor() {
            console.log('🔍 ScacchiTrackFilters inizializzato');
            this.form = $('#scacchitrack-filter-form');
            this.resultsContainer = $('#scacchitrack-results tbody');
            this.pagination = $('.scacchitrack-pagination');
            this.currentPage = 1;
            this.debounceTimeout = null;
            
            console.log('🔍 Form trovato:', this.form.length > 0);
            this.bindEvents();
        }

        bindEvents() {
            // Input e select cambiano immediatamente
            this.form.find('input, select').on('change keyup', (e) => {
                console.log('🔍 Campo cambiato:', $(e.target).attr('name'));
                
                // Usa debounce per evitare troppe richieste
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = setTimeout(() => {
                    this.currentPage = 1;
                    this.filterGames();
                }, 500);
            });

            // Reset button
            this.form.find('button[type="reset"]').on('click', (e) => {
                e.preventDefault();
                console.log('🔍 Reset form');
                this.form[0].reset();
                this.currentPage = 1;
                this.filterGames();
            });

            // Previeni il submit del form
            this.form.on('submit', (e) => {
                e.preventDefault();
            });

            // Paginazione
            $(document).on('click', '.scacchitrack-pagination a', (e) => {
                e.preventDefault();
                this.currentPage = parseInt($(e.currentTarget).data('page'));
                this.filterGames();
            });
        }

        filterGames() {
            console.log('🔍 Inizio filterGames');
            // Raccogli solo i valori non vuoti
            let data = {
                action: 'scacchitrack_filter_games',
                nonce: scacchitrackData.filterNonce,
                paged: this.currentPage
            };

            // Aggiungi solo i filtri che hanno un valore
            const torneo = $('#torneo').val();
            if (torneo) data.torneo = torneo;

            const giocatore = $('#giocatore').val();
            if (giocatore) data.giocatore = giocatore;

            const data_da = $('#data_da').val();
            if (data_da) data.data_da = data_da;

            const data_a = $('#data_a').val();
            if (data_a) data.data_a = data_a;

            console.log('🔍 Dati filtro:', data);

            // Aggiungi classe loading
            this.resultsContainer.addClass('loading');

            // Esegui la richiesta AJAX
            $.ajax({
                url: scacchitrackData.ajaxUrl,
                type: 'POST',
                data: data,
                success: (response) => {
                    console.log('🔍 Risposta AJAX ricevuta:', response);
                    if (response.success) {
                        if (!response.data || !response.data.html) {
                            console.error('🔍 HTML vuoto nella risposta');
                            this.resultsContainer.html(
                                '<tr><td colspan="6">' +
                                (scacchitrackData.i18n.noResults || 'Nessun risultato trovato') +
                                '</td></tr>'
                            );
                            this.pagination.hide();
                        } else {
                            console.log('🔍 Contenuto HTML:', response.data.html);
                            console.log('🔍 Numero risultati trovati:', response.data.found);
                            console.log('🔍 Numero pagine totali:', response.data.max_pages);
                            
                            this.resultsContainer.html(response.data.html);
                            if (response.data.max_pages && response.data.max_pages > 1) {
                                this.updatePagination(response.data.max_pages);
                            } else {
                                this.pagination.hide();
                            }
                        }
                    } else {
                        console.error('🔍 Errore nella risposta:', response);
                        this.resultsContainer.html(
                            '<tr><td colspan="6">' + 
                            (scacchitrackData.i18n.errorLoading || 'Errore nel caricamento dei risultati') + 
                            '</td></tr>'
                        );
                        this.pagination.hide();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('🔍 Errore AJAX:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    this.resultsContainer.html(
                        '<tr><td colspan="6">' + 
                        scacchitrackData.i18n.errorLoading + 
                        '</td></tr>'
                    );
                },
                complete: () => {
                    this.resultsContainer.removeClass('loading');
                }
            });
        }

        updatePagination(maxPages) {
            if (maxPages <= 1) {
                this.pagination.hide();
                return;
            }

            let html = '';
            for (let i = 1; i <= maxPages; i++) {
                html += `
                    <a href="#" 
                       class="page-numbers ${i === this.currentPage ? 'current' : ''}"
                       data-page="${i}">${i}</a>
                `;
            }

            this.pagination.html(html).show();
        }
    }

    // Inizializzazione
    $(document).ready(() => {
        if ($('#scacchitrack-filter-form').length) {
            window.scacchitrackFilters = new ScacchiTrackFilters();
        }
    });

})(jQuery);