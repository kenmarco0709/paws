var shelter_admission = {
    settings: {
        ajaxUrl: ''
    },
    init: function() {
        shelter_admission.initDataTable();
        $('#uploadFile').unbind('change').bind('change',function(){
            $('#uploadFileForm').submit();
       
        });
    },
    initDataTable: function() {

        var callBack = function() {
        };

        shelter_admission.dataList = $('#shelter_admission-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': shelter_admission.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.type = shelter_admission.settings.type;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 6 },
                { 'searchable': false, 'targets': 6 }
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