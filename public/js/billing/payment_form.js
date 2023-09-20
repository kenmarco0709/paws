var billing_payment_form  = {
    settings: {
        paymentTypeAutoSuggestUrl: ''
    },
    init: function(){

        global.autocomplete.bind(billing_payment_form.settings.paymentTypeAutoSuggestUrl,'#payment_form_payment_type_desc','#payment_form_payment_type');

    }

}


