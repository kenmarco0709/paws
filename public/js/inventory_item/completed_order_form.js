var inventory_item_completed_order_form  = {
    settings: {
        supplierAutocompleteUrl: '',
    },
    init: function(){

        inventory_item_completed_order_form.autoComplete();
    },
    autoComplete: function(){

        global.autocomplete.bind(this.settings.supplierAutocompleteUrl,'#inventory_item_completed_order_form_supplier_desc','#inventory_item_completed_order_form_supplier');
       
    },
}


