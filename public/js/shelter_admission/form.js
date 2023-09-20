var shelter_admission_form = {
    settings: {
        petAutcompleteUrl: '',
        ajaxBranchPetFormUrl: '',
        admissionType: '',
        facilityAutcompleteUrl: ''
    },
    init: function(){

        shelter_admission_form.autoComplete();
        shelter_admission_form.bindModal();
    },
    autoComplete: function(){

        global.autocomplete.bind(this.settings.facilityAutcompleteUrl,'#shelter_admission_form_facility_desc','#shelter_admission_form_facility'); 
        if(shelter_admission_form.settings.admissionType == 1){
            global.autocomplete.bind(this.settings.petAutcompleteUrl,'#shelter_admission_form_pet_desc','#shelter_admission_form_pet');  
            $('#shelter_admission_form_pet_desc').devbridgeAutocomplete('setOptions', {params: {'isShelterPet': true }});
        }



    },

    bindModal: function (){
        
        $('.href-modal').unbind('click').bind('click',function(){
            var _this = $(this);            
            $('.modal').removeClass('modal-fullscreen');

         

            $.ajax({
              url: shelter_admission_form.settings.ajaxBranchPetFormUrl,
              type: 'POST',
              data: { id: _this.data('id'), action: _this.data('action')},

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


