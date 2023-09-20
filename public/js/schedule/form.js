var schedule_form = {
    settings: {
        clientAjaxAutocompleteUrl: '',
        clientAjaxForm: '',
        clientPetAjaxForm: '',
        clientPetListUrl: '',
        vetAutocompleteUrl: '',
        petContainer:  $('.client-pet-container'),
    },
    init: function(){

        schedule_form.autoComplete();
        schedule_form.bindModal();

        if(schedule_form.settings.formAction != 'u'){
            schedule_form.get_client_pets();
        }

   
    },
    autoComplete: function(){
         global.autocomplete.bind(this.settings.vetAutocompleteUrl,'#schedule_form_attending_vet_desc','#schedule_form_attending_vet');
        global.autocomplete.bind(this.settings.scheduleTypeAjaxAutocompleteUrl,'#schedule_form_admission_type_desc','#schedule_form_admission_type');
        global.autocomplete.bind(this.settings.clientAjaxAutocompleteUrl,'#schedule_form_client_desc','#schedule_form_client',{
          onSelect: function(){
            schedule_form.get_client_pets();
          },
          onSearchStart: function(){
            schedule_form.settings.petContainer.html('');
          }
        });
    },
    get_client_pets: function(){

        if($('#schedule_form_client').val() != ''){
            $.ajax({
              url: schedule_form.settings.clientPetListUrl,
              type: 'POST',
              beforeSend: function(){
                  schedule_form.settings.petContainer.html('');
                  schedule_form.settings.petContainer.addClass('loader');
              },
              data: { clientId: $('#schedule_form_client').val()},
              success: function(r){
    
                if(r.success){
                  
                    schedule_form.settings.petContainer.removeClass('loader');
                    var html = '';
                    var petList = JSON.parse(r.list);
    
                    $.each(petList,function(k, i){
    
    
                      html += '<div class="icheck-primary">';
                      html += '<input type="checkbox" id="pet_'+k+'" name="schedule_form[schedule_pets]['+k+'][pet]" value="'+i.id+'">';
                      html += '<label for="pet_'+k+'">';
                      html += i.name;
                      html += "</label></div>"; 
                          
                    });
                    
                    schedule_form.settings.petContainer.html(html);
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
                  if($('#schedule_form_client').val() == ''){
                    
                    alert('Please select a client first.');
                    return false;
                  }

                  url = schedule_form.settings.clientPetAjaxForm;
                break;
              case 'client-form':
                
                 url = schedule_form.settings.clientAjaxForm;
                 break;
              case 'pet-medical-history':
                
                  url = schedule_form.settings.petMedicalHistory;
                  $('.modal').addClass('modal-fullscreen');
                break;  
              case 'pet-schedule-history':
                
                  url = schedule_form.settings.petScheduleHistory;
                  $('.modal').addClass('modal-fullscreen');
                break;     
            }
            
           
            $.ajax({
              url: url,
              type: 'POST',
              data: { clientId: $('#schedule_form_client').val(), id: _this.data('id')},
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


