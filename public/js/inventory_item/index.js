var inventory_item = {
    settings: {
        inventory_itemItemAjaxUrl: ''
    },
    init: function() {
        inventory_item.initDataTable();
       
        $('#uploadFile').unbind('change').bind('change',function(){
            $('#uploadFileForm').submit();
       
        });
    },
    initDataTable: function() {

        var callBack = function() {
        };

        inventory_item.dataList = $('#inventory_item-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': inventory_item.settings.inventory_itemItemAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 3 },
                { 'searchable': false, 'targets': 3 }
            ],
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