var billingdetails = {
    settings: {
        paymentAjaxListUrl: '',
        billingId:''
    },
    init: function() {
        billingdetails.initDataTable();
    },
    initDataTable: function() {

        var callBack = function() {
        };

        billingdetails.dataList = $('#admission-details-payment').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': billingdetails.settings.paymentAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.billingId = billingdetails.settings.billingId;
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