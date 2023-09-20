var medical_form = {
    settings: {
        inventoryItemAutompleteUrl: '',
        prescriptionItemListIds: []
    },
    init: function(){

        medical_form.autoComplete();
  
         if($('.items').length){
          $.each($('.items'), function(){
            medical_form.settings.prescriptionItemListIds.push($(this).val());
          });
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
    },
    addPrescriptionItem: function(){
        $.each($('.href-add'),function(){
          var _this = $(this);
            _this.unbind('click').bind('click',function(){

              if($('#' + _this.data('id')).val() != '' ){
                var _html = '';   
                if(jQuery.inArray($('#' + _this.data('id')).val(), medical_form.settings.prescriptionItemListIds) === -1) {
                  
                    medical_form.settings.prescriptionItemListIds.push($('#' + _this.data('id')).val());
                    _html+= '<tr><td>'+$('#' + _this.data('desc')).val()+'<input type="hidden" name="medical_record[prescription_item]['+$('#' +_this.data('id')).val()+'][id]" value="'+$('#' +_this.data('id')).val()+'" /></td>';
                    _html+= '<td><input class="amt form-control" type="text" name="medical_record[prescription_item]['+$('#' +_this.data('id')).val()+'][quantity]" value="" required /></td>';
                    _html+= '<td><input  class="form-control" type="text" name="medical_record[prescription_item]['+$('#' +_this.data('id')).val()+'][remarks]" value="" /></td></tr>'
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
      global.autocomplete.bind(medical_form.settings.inventoryItemAutompleteUrl,'#prescription_item_desc','#prescription_item_id',{
        onSelect: function(){
          medical_form.addPrescriptionItem();
        }
      });

    },
   
}


