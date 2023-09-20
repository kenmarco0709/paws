var item_form = {
    settings : {
        itemCategoryAutocompleteUrl: ''
    }, 

    init: function(){
        global.autocomplete.bind(this.settings.itemCategoryAutocompleteUrl, '#item_form_item_category_desc', '#item_form_item_category');   
    }
}


