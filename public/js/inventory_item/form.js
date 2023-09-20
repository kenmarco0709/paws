var inventory_item = {
    settings: {
        itemAutocompleteUrl: '',
    },
    init: function(){

        inventory_item.autoComplete();
    },
    autoComplete: function(){

        global.autocomplete.bind(this.settings.itemAutocompleteUrl,'#inventory_item_form_item_desc','#inventory_item_form_item');
       
    },
}


