$(document).ready( function () {
        $(".dateRange").daterangepicker({
            presetRanges: [{
                text: 'Mois en cours',
                dateStart: function () {
                    return moment().startOf('month')
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Les 3 derniers mois',
                dateStart: function () {
                    return moment().subtract('month', 3)
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Année en cours',
                dateStart: function () {
                    return moment().startOf('year')
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Année précédente',
                dateStart: function () {
                    return moment().subtract('year', 1).startOf('year')
                },
                dateEnd: function () {
                    return moment().subtract('year', 1).endOf('year')
                }
            }],
            applyButtonText: 'Valider',
            clearButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            initialText: 'Filtrer par date',
        }, $.datepicker.regional["fr"]);

        $("#dateRange").daterangepicker({
            presetRanges: [{
                text: 'Mois en cours',
                dateStart: function () {
                    return moment().startOf('month')
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Les 3 derniers mois',
                dateStart: function () {
                    return moment().subtract('month', 3)
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Année en cours',
                dateStart: function () {
                    return moment().startOf('year')
                },
                dateEnd: function () {
                    return moment()
                }
            }, {
                text: 'Année précédente',
                dateStart: function () {
                    return moment().subtract('year', 1).startOf('year')
                },
                dateEnd: function () {
                    return moment().subtract('year', 1).endOf('year')
                }
            }],
            applyButtonText: 'Valider',
            clearButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            initialText: 'Filtrer par date',
        }, $.datepicker.regional["fr"]);

})
