var species_form = {
    settings: {
      speciesTypeAutoCompleteUrl: '',
    },
    init: function(){

      species_form.autoComplete();
    },
    autoComplete: function(){

        global.autocomplete.bind(this.settings.speciesTypeAutoCompleteUrl,'#species_form_species_type_desc','#species_form_species_type');
       
    },
}


