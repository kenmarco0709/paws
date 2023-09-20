var client_details = {
    settings: {
        clientPetAjaxUrl: '',
        branchAjaxUrl: '',
        clientId:'',
        clientPetAjaxForm: '',
        clientPaymentAjaxUrl: '',
        clientReimbursedPaymentAjaxUrl: '',
        clientPetAjaxAddExistingForm: '',
        clientPetTransferForm: '',
        clientPetRemoveAjaxUrl: ''
    },
    init: function() {
        client_details.initPetDataTable();
        client_details.bindModal();
    },
    initPetDataTable: function() {

        var callBack = function() {

        };

        client_details.dataListPet = $('#pet-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_details.settings.clientPetAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientId = client_details.settings.clientId; 
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 4 },
                { 'searchable': false, 'targets': 4 }
            ],
            drawCallback: function() {
                callBack();
                client_details.bindModal();
                client_details.removePet();
            },
            responsive: {
                details: {
                    renderer: function( api,rowIdx ) {
                        return global.dataTableResponsiveCallBack(api, rowIdx, callBack);
                    }
                }
            }
        });

        client_details.dataListPAyments = $('#payment-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_details.settings.clientPaymentAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientId = client_details.settings.clientId; 

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

        client_details.reimbursedPayments = $('#reimbursed-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_details.settings.clientReimbursedPaymentAjaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientId = client_details.settings.clientId; 

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
    },
 
    bindModal: function (){
        
        $('.href-modal').unbind('click').bind('click',function(){
            
            var url = '';
            switch($(this).data('type')){
                case 'pet-form' :
                    url = client_details.settings.clientPetAjaxForm;
                break;
                case 'pet-form-add-existing': 
                     url = client_details.settings.clientPetAjaxAddExistingForm;
                break;
                case 'pet-transfer': 
                    url = client_details.settings.clientPetTransferForm;
                break;
            }

            $.ajax({
              url: url,
              type: 'POST',
              data: { clientId: client_details.settings.clientId, clientPetId: $(this).data('id') },
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
    },
    removePet: function(){

        $.each($('.href-remove'), function(){

            var _this = $(this);

            _this.unbind('click').bind('click',function(){
                if(!confirm(_this.data('message'))) {
                    e.preventDefault();
                } else {
                  
                    $.ajax({
                        url: client_details.settings.clientPetRemoveAjaxUrl,
                        data: { id: _this.data('id')},
                        type: "POST",
                        success: function(r){

                            if(r.success){
                                $.toaster({ message : r.msg, title : '', priority : 'success' });
                                client_details.dataListPet.draw();
                            } else {
                                $.toaster({ message : r.msg, title : '', priority : 'danger' });

                            }
                        }
                    })
                }
            });
        });
    },

};