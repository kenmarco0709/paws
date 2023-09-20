var pet_details = {
    settings: {
        petId: '',
        medicalRecordAjaxListUrl: '',
        sendMedicalRecordAjaxUrl: '',
        cabinetFormAjaxListUrl: ''
    },
    init: function() {
        pet_details.initDatatable();
    },
    initDatatable: function() {

        var callBack = function() {
            pet_details.bindModal();
            global.formSubmitted();
        };

        pet_details.dataListVac = $('#vaccination-datalist').DataTable({
            'responsive': true,
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': pet_details.settings.medicalRecordAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = pet_details.settings.petId;
                    d.confinementType = 'vaccination'; 
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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

        pet_details.dataListCon = $('#consultation-datalist').DataTable({
            'responsive': true,
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': pet_details.settings.medicalRecordAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = pet_details.settings.petId;
                    d.confinementType = 'consultation'; 
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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

        pet_details.dataListLab = $('#laboratory-datalist').DataTable({
            'responsive': true,
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': pet_details.settings.medicalRecordAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = pet_details.settings.petId;
                    d.confinementType = 'laboratory'; 
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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

        pet_details.dataListSpay = $('#spayn-datalist').DataTable({
            'responsive': true,
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': pet_details.settings.medicalRecordAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = pet_details.settings.petId;
                    d.confinementType = 'Spay/Neuter Surgery'; 
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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

        pet_details.dataListCb = $('#cabinetForm-datalist').DataTable({
            'responsive': true,
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': pet_details.settings.cabinetFormAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = pet_details.settings.petId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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
                case 'send-medical-record' :
                    url = pet_details.settings.sendMedicalRecordAjaxUrl;
                break;
            }

            $.ajax({
              url: url,
              type: 'POST',
              data: { id: $(this).data('id') },
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

};