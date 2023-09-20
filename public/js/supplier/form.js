var supplier_form = {
    settings: {
      supplierTypeAutoCompleteUrl: '',
    },
    init: function(){

      supplier_form.autoComplete();
    },
    autoComplete: function(){

        global.autocomplete.bind(this.settings.supplierTypeAutoCompleteUrl,'#supplier_form_supplier_type_desc','#supplier_form_supplier_type');
       
    },
}


