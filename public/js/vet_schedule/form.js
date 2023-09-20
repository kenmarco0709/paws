var vet_schedule_form = {
    settings: {
        vetAutocompleteUrl: '',
    },
    init: function(){
      vet_schedule_form.autoComplete();
   
    },
    autoComplete: function(){
         global.autocomplete.bind(this.settings.vetAutocompleteUrl,'#vet_schedule_form_vet_desc','#vet_schedule_form_vet');
   
    }

}


