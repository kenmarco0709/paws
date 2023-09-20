var pet = {
    settings: {
        ajaxUrl: '',
        ajaxRemoveUrl: '',
        clientPetAjaxMergeForm: ''
    },
    init: function() {
        pet.initDataTable();
    },
    initDataTable: function() {

        var callBack = function() {
            pet.removePet();
            pet.bindModal();
        };

        pet.dataList = $('#pet-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': pet.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 5 },
                { 'searchable': false, 'targets': 5 }
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
    },

    removePet: function(){

        $.each($('.href-delete'), function(){

            var _this = $(this);

            _this.unbind('click').bind('click',function(){
                if(!confirm(_this.data('message'))) {
                    e.preventDefault();
                } else {
                  

                    $.ajax({
                        url: pet.settings.ajaxRemoveUrl,
                        data: { id: _this.data('id')},
                        type: "POST",
                        success: function(r){

                            if(r.success){
                                $.toaster({ message : r.msg, title : '', priority : 'success' });
                                pet.dataList.draw();
                            } else {
                                $.toaster({ message : r.msg, title : '', priority : 'danger' });

                            }
                        }
                    })
                }
            });
        });
    },
    bindModal: function(){
      
        $('.href-modal').unbind('click').bind('click',function(){
            
            var url = '';
            switch($(this).data('type')){
                case 'merge-form' :
                    url = pet.settings.clientPetAjaxMergeForm;
                break;
            }

            $.ajax({
              url: url,
              type: 'POST',
              data: { petId: $(this).data('id') },
              beforeSend: function(){
                $(".modal-content").html('');
                  
              },
              success: function(r){
                if(r.success){
          
                  $(".modal-content").html(r.html);
                  $('#modal').modal('show');
                }
              }
            });
        });
    }
};