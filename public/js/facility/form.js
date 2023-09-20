var facility_form = {
    settings : {
        speciesAutocompleteUrl: ''
    }, 

    init: function(){
        global.autocomplete.bind(this.settings.speciesAutocompleteUrl, '#facility_form_species_desc', '#facility_form_species');   
    }
}


