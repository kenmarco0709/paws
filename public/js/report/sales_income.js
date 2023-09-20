var sales_income = {
    settings: {
        ajaxUrl: '',
        breakdownUrl: '',
        dt: null
    },
    init: function() {
        sales_income.initDataTable();

        $.each($('.btn-report'),function(){
            var _this = $(this);
            _this.unbind('click').bind('click',function(){
                window.open(_this.data('url') + '?start_date=' + $('#startDate').val() + "&end_date=" + $('#endDate').val(), '_blank');
            });
        });
    },
    initDataTable: function() {

        var callBack = function() {
            sales_income.bindModal();
        };

        sales_income.dataList = $('#sales-income-datalist').DataTable({
            'processing': true,
            'searching': false,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': sales_income.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.endDate = $('#endDate').val();
                    d.startDate = $('#startDate').val();
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': [0,3,4] },
                { 'searchable': false, 'targets': [0,3,4] }
            ],
            'order': [[1, 'desc']],
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

        $('#startDate, #endDate').unbind('change').bind('change',function(){
            sales_income.dataList.draw();
        });
    },
    bindModal: function (){
        $('.href-modal').unbind('click').bind('click',function(){

            var _this = $(this);
                     
            $.ajax({
              url: sales_income.settings.breakdownUrl,
              type: 'POST',
              data: {  id: _this.data('id')},
              beforeSend: function(){
                $('.modal').addClass('modal-fullscreen');
                $(".modal-content").html('');
              },
              success: function(r){
                if(r.success){
                    
                  $(".modal-content").html(r.html);
                  $('#modal').modal('show');
                } else {
                    $.toaster({ message : r.msg, title : '', priority : 'danger' });

                }
              }
            });
        });
    }
};