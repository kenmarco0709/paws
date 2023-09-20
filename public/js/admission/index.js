var admission = {
    settings: {
        ajaxUrl: '',
        type: ''
    },
    init: function() {
        admission.initDataTable();
    },
    initDataTable: function() {

        var callBack = function() {
        };

        admission.dataList = $('#admission-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': admission.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.type = admission.settings.type;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': null },
                { 'searchable': false, 'targets': null }
            ],
            'order': [[0, 'desc']],
            drawCallback: function() {
                callBack();
            },
            responsive: {
                details: {
                    renderer: function( api,rowIdx ) {
                        return global.dataTableResponsiveCallBack(api, rowIdx, callBack);
                    }
                }
            }
        });

        $('.content-container').removeClass('has-loading');
        $('.content-container-content').removeClass('hide');
    }
};