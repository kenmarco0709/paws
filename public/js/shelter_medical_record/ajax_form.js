var shelterMedicalRecord = {
    settings: {
        processFormUrl: '',
        vetAutocompleteUrl: '',
        inventoryItemAutompleteUrl: '',
        processFormUrl: '',
        serviceAutcompleteUrl:'',
        prescriptionItemListIds: [],
        serviceItemListIds: [],
        treatmentListIds: []


    },
    init: function(){

        global.init();
        $('.close-modal').unbind('click').bind('click',function(){
            $('.modal').modal('hide');
            $('#modal').removeClass('modal-xl');
    
        });
        shelterMedicalRecord.autoComplete();
        shelterMedicalRecord.processForm();
        shelterMedicalRecord.remove();
        shelterMedicalRecord.prePopulateArray();

    },
    autoComplete: function(){
        global.autocomplete.bind(shelterMedicalRecord.settings.vetAutocompleteUrl,'#shelter_medical_record_form_attending_vet_desc','#shelter_medical_record_form_attending_vet');
        global.autocomplete.bind(shelterMedicalRecord.settings.inventoryItemAutompleteUrl,'#prescription_item_desc','#prescription_item_id',{
            onSelect: function(){
                shelterMedicalRecord.addPrescriptionItem();
            }
          });
        global.autocomplete.bind(shelterMedicalRecord.settings.inventoryItemAutompleteUrl,'#service_item_desc','#service_item_id',{
            onSelect: function(){
                shelterMedicalRecord.addServiceItem();
            }
        });  
        global.autocomplete.bind(shelterMedicalRecord.settings.serviceAutcompleteUrl,'#treatment_desc','#treatment_id',{
            onSelect: function(){
                shelterMedicalRecord.addTreatmentItem();
            }
        });  
    },
    addPrescriptionItem: function(){
        $.each($('.href-add'),function(){
          var _this = $(this);
            _this.unbind('click').bind('click',function(){

              if($('#' + _this.data('id')).val() != '' ){
                var _html = '';
                if(jQuery.inArray($('#' + _this.data('id')).val(), shelterMedicalRecord.settings.prescriptionItemListIds) === -1) {
                  
                    shelterMedicalRecord.settings.prescriptionItemListIds.push($('#' + _this.data('id')).val());
                    _html+= '<tr><td><a data-id="'+_this.attr('data-id')+'" data-type="prescriptionItemListIds"  class="remove" href="javascript:void(0);"/><i style="color:red;margin-right:5px;" class="fa fa-times" aria-hidden="true"></i></a>'+$('#' + _this.data('desc')).val()+'<input type="hidden" name="shelter_medical_record_form[prescription_item]['+$('#' +_this.data('id')).val()+'][id]" value="'+$('#' +_this.data('id')).val()+'" /></td>';
                    _html+= '<td><input class="amt form-control" type="text" name="shelter_medical_record_form[prescription_item]['+$('#' +_this.data('id')).val()+'][quantity]" value="" required /></td>';
                    _html+= '<td><input  class="form-control" type="text" name="shelter_medical_record_form[prescription_item]['+$('#' +_this.data('id')).val()+'][remarks]" value="" /></td></tr>'
                    $('#' + _this.data('target')).find('tbody').append(_html);
                    global.mask();
                    //clear data 
                    $('#' + _this.data('id')).val('');
                    $('#' + _this.data('desc')).val('');

                    shelterMedicalRecord.remove();
                }
              }
          });
        });
    },
    addServiceItem: function(){
        $.each($('.href-add'),function(){
          var _this = $(this);
            _this.unbind('click').bind('click',function(){

              if($('#' + _this.data('id')).val() != '' ){
                var _html = '';
                if(jQuery.inArray($('#' + _this.data('id')).val(), shelterMedicalRecord.settings.serviceItemListIds) === -1) {
                  
                    shelterMedicalRecord.settings.serviceItemListIds.push($('#' + _this.data('id')).val());
                    _html+= '<tr><td><a data-id="'+_this.attr('data-id')+'" data-type="serviceItemListIds"  class="remove" href="javascript:void(0);"/><i style="color:red;margin-right:5px;" class="fa fa-times" aria-hidden="true"></i></a>'+$('#' + _this.data('desc')).val()+'<input type="hidden" name="shelter_medical_record_form[service_item]['+$('#' +_this.data('id')).val()+'][id]" value="'+$('#' +_this.data('id')).val()+'" /></td>';
                    _html+= '<td><input class="amt form-control" type="text" name="shelter_medical_record_form[service_item]['+$('#' +_this.data('id')).val()+'][quantity]" value="" required /></td>';
                    _html+= '<td><input  class="form-control" type="text" name="shelter_medical_record_form[service_item]['+$('#' +_this.data('id')).val()+'][remarks]" value="" /></td></tr>'
                    $('#' + _this.data('target')).find('tbody').append(_html);
                    global.mask();
                    //clear data 
                    $('#' + _this.data('id')).val('');
                    $('#' + _this.data('desc')).val('');

                    shelterMedicalRecord.remove();
                }
              }
          });
        });
    },
    addTreatmentItem: function(){
        $.each($('.href-add'),function(){
          var _this = $(this);
            _this.unbind('click').bind('click',function(){
    
              if($('#' + _this.data('id')).val() != '' ){
                var _html = '';
                if(jQuery.inArray($('#' + _this.data('id')).val(), shelterMedicalRecord.settings.treatmentListIds) === -1) {
                  
                    shelterMedicalRecord.settings.treatmentListIds.push($('#' + _this.data('id')).val());
                    _html+= '<tr><td><a data-id="'+_this.attr('data-id')+'" data-type="treatmentListIds"  class="remove" href="javascript:void(0);"/><i style="color:red;margin-right:5px;" class="fa fa-times" aria-hidden="true"></i></a>'+$('#' + _this.data('desc')).val()+'<input type="hidden" name="shelter_medical_record_form[treatment]['+$('#' +_this.data('id')).val()+'][id]" value="'+$('#' +_this.data('id')).val()+'" /></td>';
                    _html+= '<td><input  class="form-control" type="text" name="shelter_medical_record_form[treatment]['+$('#' +_this.data('id')).val()+'][remarks]" value="" /></td></tr>'
                    $('#' + _this.data('target')).find('tbody').append(_html);
                    global.mask();
                    //clear data 
                    $('#' + _this.data('id')).val('');
                    $('#' + _this.data('desc')).val('');
    
                    shelterMedicalRecord.remove();
                }
              }
          });
        });
    },
    

    processForm: function(){
        $('#form').submit(function(e){
    
            e.preventDefault();
            _this = $(this);
            _this.find(':input[type=submit]').prop('disabled', true);
            //var formData = $(this).serialize();
            var formData = new FormData(_this[0]);

            $.ajax({
                url:  shelterMedicalRecord.settings.processFormUrl,
                data: formData, 
                type: "post",
                cache: false,
                processData: false,
                contentType: false, 
                success: function(r){
                    if(r.success){
          
                        $.toaster({ message : r.msg, title : '', priority : 'success' });
                        $('.modal').modal('hide');
    
                        if(typeof shelter_admission_details  != 'undefined'){
                            shelter_admission_details.medicalRecordDataList.draw();
                        }
    
                    } else {
                        $.toaster({ message : r.msg, title : '', priority : 'danger' });
                    }
                }
            });
        });
    },

    remove: function(){
        $.each($('.remove'),function(){
            var _this = $(this);
            _this.unbind('click').bind('click',function(){
               
                var type = _this.data('type');
                switch(type){
                    case 'prescriptionItemListIds' :
                        shelterMedicalRecord.settings.prescriptionItemListIds.splice($.inArray(_this.val(), shelterMedicalRecord.settings.prescriptionItemListIds), 1);
                        break;
                    case 'serviceItemListIds': 
                        shelterMedicalRecord.settings.serviceItemListIds.splice($.inArray(_this.val(), shelterMedicalRecord.settings.serviceItemListIds), 1);
                       
                        break;  
                    case 'serviceListIds': 
                        shelterMedicalRecord.settings.treatmentListIds.splice($.inArray(_this.val(), shelterMedicalRecord.settings.treatmentListIds), 1);
                        break;      
                        
                }
                 _this.closest('tr').remove();
            });
        });
    },
    prePopulateArray:function(){
        //push services to array
        if($('.services').length){
            
            $.each($('.services'), function(){
                var _this = $(this);
                shelterMedicalRecord.settings.treatmentListIds.push(_this.val());
            
            });

        }
  
        //push items to array
        if($('.items').length){
            $.each($('.items'), function(){
                var _this = $(this);
                shelterMedicalRecord.settings.serviceItemListIds.push(_this.val());
            });
        }
  
        if($('.prescription-items').length){
            $.each($('.prescription-items'), function(){
                var _this = $(this);
                shelterMedicalRecord.settings.prescriptionItemListIds.push(_this.val());
            });
        }
    },
}