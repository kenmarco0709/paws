var shelter_admission_details = {
    settings: {
        shelterAdmissionId: '',
        petId: '',
        shelterAdmissionDetailPartAjax: '',
        status: '',
        behaviorRecordAjaxListUrl: '',
        petFileAjaxListUrl: '',
        medicalRecordAjaxListUrl: '',
        shelterAdmissionHistoryAjax: '',
        petPhotoAjaxListUrl: ''
    },
    init: function(){

      shelter_admission_details.loadDetailsPart();
      shelter_admission_details.bindModal();
      shelter_admission_details.initDatatable();
    },
    initDatatable: function(){
        var callBack = function(){
          shelter_admission_details.bindModal();
        };

        shelter_admission_details.behaviorRecordDataList = $('#behaviorRecord-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': shelter_admission_details.settings.behaviorRecordAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = shelter_admission_details.settings.petId;
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
        
        shelter_admission_details.petFileDataList = $('#petFile-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': shelter_admission_details.settings.petFileAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = shelter_admission_details.settings.petId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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

        shelter_admission_details.petPhotoDataList = $('#petPhoto-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': shelter_admission_details.settings.petPhotoAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = shelter_admission_details.settings.petId;
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

        shelter_admission_details.medicalRecordDataList = $('#medicalRecord-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': shelter_admission_details.settings.medicalRecordAjaxListUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = shelter_admission_details.settings.petId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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

        shelter_admission_details.admissionRecordDataList = $('#admission-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': shelter_admission_details.settings.shelterAdmissionHistoryAjax,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.petId = shelter_admission_details.settings.petId;
                    d.id = shelter_admission_details.settings.shelterAdmissionId;
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'searchable': false, 'targets': 2 }
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
    loadDetailsPart(){

        $.each($('.details-part'),function(){
              var _this = $(this);
              $.ajax({
                url: shelter_admission_details.settings.shelterAdmissionDetailPartAjax,
                type: 'POST',
                data: { id: shelter_admission_details.settings.shelterAdmissionId, part: _this.data('id')},
                success: function(r){
                  if(r.success){
                    _this.html(r.html);
                    _this.removeClass('linear-placeholder');
                  }
                }
              });
        });
      
    },

    bindModal: function (){
        
        $('.href-modal').unbind('click').bind('click',function(){
            var _this = $(this);            
            $('.modal').removeClass('modal-fullscreen');

            if(_this.hasClass("fullscreen")){
                $('.modal').addClass('modal-fullscreen');
            }

            $.ajax({
              url: _this.data('url'),
              type: 'POST',
              data: { id: _this.data('id'), action: _this.data('action'), petId: _this.data('petid'), admissionId: _this.data('admissionid'), admissionType: _this.data('admissiontype')},

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
    
    removeActionBtn: function(){
        // $.each($('.action-btn'),function(){
        //     var _this = $(this);
        //     _this.remove();
        // });
    }

}


