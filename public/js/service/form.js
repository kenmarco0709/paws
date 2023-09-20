var service_form = {
    settings: {
      serviceTypeAutoCompleteUrl: '',
    },
    init: function(){

      service_form.autoComplete();
    },
    autoComplete: function(){

        global.autocomplete.bind(this.settings.serviceTypeAutoCompleteUrl,'#service_form_service_type_desc','#service_form_service_type');
       
    },
}


