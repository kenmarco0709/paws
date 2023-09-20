var admission_form = {
    settings: {
        clientAjaxAutocompleteUrl: '',
        clientAjaxForm: '',
        clientPetAjaxForm: '',
        clientPetListUrl: '',
        admissionTypeAjaxAutocompleteUrl: '',
        inventoryItemAutompleteUrl: '',
        vetAutocompleteUrl: '',
        petContainer:  $('.client-pet-container'),
        formAction: '',
        serviceAutcompleteUrl: '',
        petMedicalHistory: '',
        petAdmissionHistory:'',
        itemListIds : {},
        serviceListIds: {},
        prescriptionItemListIds: {}
    },
    init: function(){

        admission_form.autoComplete();
        admission_form.bindModal();

        if(admission_form.settings.formAction != 'u'){
            admission_form.get_client_pets();
        }

        if($('.href-clone').length){
            
            $.each($('.href-clone'),function(){

                var _this = $(this);

                _this.unbind('click').bind('click',function(){
                    var cloneIndex = $('.clone').length;
                    var scheduleInputClone = $("." + _this.data('target') + "").first().clone();
                    
                    jQuery(scheduleInputClone.find('input.datepicker')).each(function() {
                         
                        $(this).removeAttr('id').removeClass('hasDatepicker').val('');
                    });
                    scheduleInputClone.appendTo($('.' + _this.data('target') + '').parent())
                        .find("input")
                        .each(function() {
                            var name = '';
                            if(this.name.indexOf("[returned_date]") != -1){
                              name = this.name.slice(0, this.name.indexOf("[schedule]")) +  '[schedule]' + '[' + cloneIndex + ']' + '[returned_date]';
                            } else {
                              name = this.name.slice(0, this.name.indexOf("[schedule]")) + '[schedule]' + '[' + cloneIndex + ']'  + '[remarks]';

                            }
                            
                            this.value = '';
                            this.name = name;
                            global.mask();

                        });
                });
            });
        }
        //push services to array
        if($('.services').length){
         
          $.each($('.services'), function(){

            var _this = $(this);
            if(admission_form.settings.serviceListIds[_this.data('key')] == undefined){
               admission_form.settings.serviceListIds[_this.data('key')] = [];
            }

            admission_form.settings.serviceListIds[_this.data('key')].push($(this).val());
          
          });

        }

         //push items to array
         if($('.items').length){
          $.each($('.items'), function(){
            
            var _this = $(this);
            if(admission_form.settings.itemListIds[_this.data('key')] == undefined){
               admission_form.settings.itemListIds[_this.data('key')] = [];
            }

            admission_form.settings.itemListIds[_this.data('key')].push($(this).val());
          });
        }

        if($('.prescription-items').length){
          $.each($('.prescription-items'), function(){

            var _this = $(this);
            if(admission_form.settings.prescriptionItemListIds[_this.data('key')] == undefined){
               admission_form.settings.prescriptionItemListIds[_this.data('key')] = [];
            }
            admission_form.settings.prescriptionItemListIds[_this.data('key')].push($(this).val());
          });
        }
    },
    addPrescriptionItem: function(){
        $.each($('.href-add'),function(){
          var _this = $(this);
            _this.unbind('click').bind('click',function(){

              if(admission_form.settings.prescriptionItemListIds[_this.data('key')] == undefined){
                admission_form.settings.prescriptionItemListIds[_this.data('key')] = [];
              }

              if($('#' + _this.data('id')).val() != '' ){
                var _html = '';   
                if(jQuery.inArray($('#' + _this.data('id')).val(), admission_form.settings.prescriptionItemListIds[_this.data('key')]) === -1) {
                  
                    admission_form.settings.prescriptionItemListIds[_this.data('key')].push($('#' + _this.data('id')).val());
                    _html+= '<tr><td>'+$('#' + _this.data('desc')).val()+'<input type="hidden" name="admission[medical_record]['+_this.data('key')+'][prescription_item]['+$('#' +_this.data('id')).val()+'][id]" value="'+$('#' +_this.data('id')).val()+'" /></td>';
                    _html+= '<td><input class="amt form-control" type="text" name="admission[medical_record]['+_this.data('key')+'][prescription_item]['+$('#' +_this.data('id')).val()+'][quantity]" value="" required /></td>';
                    _html+= '<td><input  class="form-control" type="text" name="admission[medical_record]['+_this.data('key')+'][prescription_item]['+$('#' +_this.data('id')).val()+'][remarks]" value="" /></td></tr>'
                    $('#' + _this.data('target')).find('tbody').append(_html);
                    global.mask();
                    //clear data 
                    $('#' + _this.data('id')).val('');
                    $('#' + _this.data('desc')).val('');
                }
              }
          });
        });
    },
    addItem: function(){
      
        $.each($('.href-add'),function(){
            var _this = $(this);
              _this.unbind('click').bind('click',function(){
                if(admission_form.settings.itemListIds[_this.data('key')] == undefined){
                  admission_form.settings.itemListIds[_this.data('key')] = [];
                }

                if($('#' + _this.data('id')).val() != '' ){
                  var _html = '';   
                  if(jQuery.inArray($('#' + _this.data('id')).val(), admission_form.settings.itemListIds[_this.data('key')]) === -1) {
                     
                      admission_form.settings.itemListIds[_this.data('key')].push($('#' + _this.data('id')).val());
                      _html+= '<tr><td>'+$('#' + _this.data('desc')).val()+'<input type="hidden" name="admission[medical_record]['+_this.data('key')+'][inventory_item]['+$('#' +_this.data('id')).val()+'][id]" value="'+$('#' +_this.data('id')).val()+'" /></td>';
                      _html+= '<td><input class="amt form-control" type="text" name="admission[medical_record]['+_this.data('key')+'][inventory_item]['+$('#' +_this.data('id')).val()+'][quantity]" value="" required /></td>';
                      _html+= '<td><input  class="form-control" type="text" name="admission[medical_record]['+_this.data('key')+'][inventory_item]['+$('#' +_this.data('id')).val()+'][remarks]" value="" /></td></tr>'
                      $('#' + _this.data('target')).find('tbody').append(_html);
                      global.mask();
                      //clear data 
                      $('#' + _this.data('id')).val('');
                      $('#' + _this.data('desc')).val('');
                  }
                }
            });
        });
    },
    addTreatment: function(){
      
      $.each($('.href-add-treatment'),function(){
          var _this = $(this);
            _this.unbind('click').bind('click',function(){

              if(admission_form.settings.serviceListIds[_this.data('key')] == undefined){
                admission_form.settings.serviceListIds[_this.data('key')] = [];
              }
              if($('#' + _this.data('id')).val() != '' ){
                var _html = '';   
                if(jQuery.inArray($('#' + _this.data('id')).val(), admission_form.settings.serviceListIds[_this.data('key')]) === -1) {
                  admission_form.settings.serviceListIds[_this.data('key')].push($('#' + _this.data('id')).val());  
                  _html+= '<tr><td>'+$('#' + _this.data('desc')).val()+'<input type="hidden" name="admission[medical_record]['+_this.data('key')+'][service]['+$('#' +_this.data('id')).val()+'][id]" value="'+$('#' +_this.data('id')).val()+'" /></td>';
                  _html+= '<td><input class="form-control" type="text" name="admission[medical_record]['+_this.data('key')+'][service]['+$('#' +_this.data('id')).val()+'][remarks]" value="" /></td></tr>'                
                  $('#' + _this.data('target')).find('tbody').append(_html);
                  global.mask();
                  //clear data 
                  $('#' + _this.data('id')).val('');
                  $('#' + _this.data('desc')).val('');
                }
              }
          });
      });
  },
    autoComplete: function(){

        if($('.treatment_id').length){
            $.each($('.treatment_desc'),function (i ,v){
                global.autocomplete.bind(admission_form.settings.serviceAutcompleteUrl,'#treatment_desc_' +i,'#treatment_id_' +i,{
                  onSelect: function(){
                    admission_form.addTreatment();
                  }
                });

                $('#treatment_desc_' + i).devbridgeAutocomplete('setOptions', {params: {'serviceType': 'treatment'}});
            });
        }

        if($('.item_id').length){
            $.each($('.item_id'),function (i ,v){
              global.autocomplete.bind(admission_form.settings.inventoryItemAutompleteUrl,'#item_desc_'+i,'#item_id_'+i,{
                onSelect: function(){
                  admission_form.addItem();
                }
              });
            });
        }

        if($('.prescription_item_id').length){
            $.each($('.prescription_item_id'),function (i ,v){
              global.autocomplete.bind(admission_form.settings.inventoryItemAutompleteUrl,'#prescription_item_desc_'+i,'#prescription_item_id_'+i,{
                onSelect: function(){
                  admission_form.addPrescriptionItem();
                }
              });
            });
        }

  
        global.autocomplete.bind(this.settings.vetAutocompleteUrl,'#admission_form_attending_vet_desc','#admission_form_attending_vet');
        global.autocomplete.bind(this.settings.admissionTypeAjaxAutocompleteUrl,'#admission_form_admission_type_desc','#admission_form_admission_type');
        global.autocomplete.bind(this.settings.clientAjaxAutocompleteUrl,'#admission_form_client_desc','#admission_form_client',{
          onSelect: function(){
            admission_form.get_client_pets();
          },
          onSearchStart: function(){
            admission_form.settings.petContainer.html('');
          }
        });
    },
    get_client_pets: function(){

        if($('#admission_form_client').val() != ''){
            $.ajax({
              url: admission_form.settings.clientPetListUrl,
              type: 'POST',
              beforeSend: function(){
                  admission_form.settings.petContainer.html('');
                  admission_form.settings.petContainer.addClass('loader');
              },
              data: { clientId: $('#admission_form_client').val()},
              success: function(r){
    
                if(r.success){
                  
                    admission_form.settings.petContainer.removeClass('loader');
                    var html = '';
                    var petList = JSON.parse(r.list);
    
                    $.each(petList,function(k, i){
                      html += '<div class="icheck-primary">';
                      html += '<input type="checkbox" id="pet_'+k+'" name="admission_form[admission_pets]['+k+'][pet]" value="'+i.id+'">';
                      html += '<label for="pet_'+k+'">';
                      html += i.name;
                      html += "</label></div>"; 
                    });
                    
                    admission_form.settings.petContainer.html(html);
                }
              }
            });
        }
       
    },
    bindModal: function (){
        
        $('.href-modal').unbind('click').bind('click',function(){
            var _this = $(this);
            var url = '';
            
            $('.modal').removeClass('modal-fullscreen');

            switch(_this.data('type')){
              case 'pet-form':
                  if($('#admission_form_client').val() == ''){
                    
                    alert('Please select a client first.');
                    return false;
                  }

                  url = admission_form.settings.clientPetAjaxForm;
                break;
              case 'client-form':
                
                 url = admission_form.settings.clientAjaxForm;
                 break;
              case 'pet-medical-history':
                
                  url = admission_form.settings.petMedicalHistory;
                  $('.modal').addClass('modal-fullscreen');
                break;  
              case 'pet-admission-history':
                
                  url = admission_form.settings.petAdmissionHistory;
                  $('.modal').addClass('modal-fullscreen');
                break;     
            }
            
           
            $.ajax({
              url: url,
              type: 'POST',
              data: { clientId: $('#admission_form_client').val(), id: _this.data('id')},
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

}


