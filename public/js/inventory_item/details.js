var details = {
    settings : {
        inventoryItemAdjustmentAjaxUrl: '',
        invetoryItemId: "",
        inventoryItemMedicalRecordAjaxUrl: "",
        inventoryItemInvoiceAjaxUrl: "",
        inventoryItemInvoiceVoidAjaxUrl: "",
        inventoryItemCompletedOrderAjaxUrl: ""

    },
    init: function() {
        details.initDataTable();
    },
    initDataTable: function() {

        var callBack = function() {
        };

        details.dataListOrder = $('#inventoryItemCompletedOrders').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': details.settings.inventoryItemCompletedOrderAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.invetoryItemId = details.settings.invetoryItemId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': null },
                { 'searchable': false, 'targets': null }
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

        details.dataList = $('#inventoryItemAdjustments').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': details.settings.inventoryItemAdjustmentAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.invetoryItemId = details.settings.invetoryItemId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': null },
                { 'searchable': false, 'targets': null }
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

        details.dataList = $('#medicalRecordItem').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': details.settings.inventoryItemMedicalRecordAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.invetoryItemId = details.settings.invetoryItemId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': null },
                { 'searchable': false, 'targets': null }
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

        details.dataList = $('#invoiceInventoryItem').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': details.settings.inventoryItemInvoiceAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.invetoryItemId = details.settings.invetoryItemId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': null },
                { 'searchable': false, 'targets': null }
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

        details.dataList = $('#invoiceVoidInventoryItem').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': details.settings.inventoryItemInvoiceVoidAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.invetoryItemId = details.settings.invetoryItemId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': null },
                { 'searchable': false, 'targets': null }
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
}