$(document).ready( function () {
        $("#dateRange").daterangepicker({
            presetRanges: [/*{
                text: 'Today1',
                dateStart: function () {
                    return moment()
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Tomorrow2',
                dateStart: function () {
                    return moment().add('days', 1)
                },
                dateEnd: function () {
                    return moment().add('days', 1)
                }
            }, {
                text: 'Next 7 Days',
                dateStart: function () {
                    return moment()
                },
                dateEnd: function () {
                    return moment().add('days', 6)
                }
            }, {
                text: 'Next Week',
                dateStart: function () {
                    return moment().add('weeks', 1).startOf('week')
                },
                dateEnd: function () {
                    return moment().add('weeks', 1).endOf('week')
                }
            },*/ {
                text: 'Mois en cours',
                dateStart: function () {
                    return moment().startOf('month')
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Les 2 derniers mois',
                dateStart: function () {
                    return moment().subtract('month', 1).startOf('month')
                },
                dateEnd: function () {
                    return moment().endOf('month')
                }
            }, {
                text: 'Année en cours',
                dateStart: function () {
                    return moment().startOf('year')
                },
                dateEnd: function () {
                    return moment()
                }
            }],
            applyButtonText: 'Valider',
            clearButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            initialText: 'Filtrer par date...',
        }, $.datepicker.regional["fr"]);

})
