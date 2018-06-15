let Table = {
    init: function () {
        $('.data-table').each(function () {
            let table = Table.initTable(this, $(this).data('status'));

            $('#dataTable_wrapper').on('keyup', ".column_search", function () {
                table.column($(this).parent().index()).search(this.value).draw();
            });

        });
    },

    initTable: function (dataTable, status) {
        $(dataTable).find('tfoot th').each(function () {
            let title = $(this).text();
            $(this).html('<input type="text" class="form-control column_search" style="width: 100%" placeholder="' + title + '" />');
        });

        return $(dataTable).DataTable({
            "order": [[0, "desc"]],
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "searchDelay": 350,
            "bDeferRender": true,
            "ajax": Routing.generate('data_table', {type: status}, false),
            'language': {
                "sProcessing": "Traitement en cours...",
                "sSearch": "Rechercher selon n'importe quel critère&nbsp;:",
                "sLengthMenu": "Afficher _MENU_ &eacute;l&eacute;ments",
                "sInfo": "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                "sInfoEmpty": "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
                "sInfoFiltered": "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                "sInfoPostFix": "",
                "sLoadingRecords": "Chargement en cours...",
                "sZeroRecords": "Aucun &eacute;l&eacute;ment &agrave; afficher",
                "sEmptyTable": "Aucune donn&eacute;e disponible dans le tableau",
                "oPaginate": {
                    "sFirst": "Premier",
                    "sPrevious": "Pr&eacute;c&eacute;dent",
                    "sNext": "Suivant",
                    "sLast": "Dernier"
                },
                "oAria": {
                    "sSortAscending": ": activer pour trier la colonne par ordre croissant",
                    "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
                }
            },
            "sPaginationType": "full_numbers",
            "aoColumns": [null, null, null, null, null, null, null],
            "columnDefs": [],
        });
    },
};


$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();

    $(".select2").select2({
        language: 'fr'
    });

    $(".select2-tags").select2({
        tags: true,
        language: 'fr'
    });

    $(".datepicker").datepicker({
        format: "dd/mm/yyyy",
        todayBtn: "linked",
        language: "fr",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });

    Table.init();
});