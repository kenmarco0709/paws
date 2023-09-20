var invoice = {
    settings: {
        ajaxUrl: '',
        popup: null
    },
    init: function() {
        invoice.initDataTable();

        console.log(  invoice.settings.popup );
    },
    initDataTable: function() {

        var callBack = function() {
            invoice.bindModal();
        };

        invoice.dataList = $('#invoice-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': invoice.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 4 },
                { 'searchable': false, 'targets': 4 }
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
    },
    bindModal: function (){
        $('.href-print').unbind('click').bind('click',function(){

            var _this = $(this);
         
            invoice.settings.popup =  window.open(global.settings.url +'invoice/print/' + _this.data('id'),'popUpWindow', 'width=900, height=900');
             

            // $.ajax({
            //   url: invoice.settings.printUrl,
            //   type: 'POST',
            //   data: {  id: _this.data('id')},
            //   beforeSend: function(){
            //     $(".modal-content").html('');
                  
            //   },
            //   success: function(r){
            //     if(r.success){
                    
            //       $(".modal-content").html(r.html);
            //       $('#modal').modal('show');
            //     } else {
            //         $.toaster({ message : r.msg, title : '', priority : 'danger' });

            //     }
            //   }
            // });
        });
    }
};