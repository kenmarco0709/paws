var invoice_payment_form  = {
    settings: {
        paymentTypeAutoSuggestUrl: '',
        paymentProcessFormAjaxUrl: ''
    },
    init: function(){

        global.autocomplete.bind(invoice_payment_form.settings.paymentTypeAutoSuggestUrl,'#payment_form_payment_type_desc','#payment_form_payment_type');
        invoice_payment_form.processForm();
        invoice_payment_form.computeChange();   

        $('.close-modal').unbind('click').bind('click',function(){
            $('.modal').modal('hide');
        });
    },

    computeChange: function(){

        if($('#payment_form_amount').length){

            $('#payment_form_amount').unbind('keyup').bind('keyup',function(){

                var _this = $(this);
                var _remainingBalel = $('#remaining_balance');

                if( parseFloat(_this.val().replace(',', '')) >  parseFloat(_remainingBalel.val().replace(',', ''))){
                    $('#payment_form_amount_change').val(parseFloat(parseFloat(_this.val().replace(',', '')) - _remainingBalel.val().replace(',', '')).toFixed(2));

                } else {
                    $('#payment_form_amount_change').val('0.00');
                }
                
            });
        }
    },

    processForm: function(){
        
        $('#paymentForm').submit(function(e){
            e.preventDefault();
            _this = $(this);
            _this.find(':input[type=submit]').prop('disabled', true);
            var formData = $(this).serialize();

            $.ajax({
                url: invoice_payment_form.settings.paymentProcessFormAjaxUrl,
                data: formData, 
                type: "post",
                dataType: "JSON",
                success: function(r){
                    if(r.success){


                        $.toaster({ message : r.msg, title : '', priority : 'success' });
                        $('.success').removeClass('d-none');
                        $('.modal').modal('hide');

                        $('#totalPayment').val(parseFloat(r.totalPayment).toFixed(2));
                        invoice_form.computeGrandTotal();
                    } else {

                        $('.errors').html(r.msg).removeClass('d-none');
                        setTimeout(function() { 
                                $('.errors').html(r.msg).addClass('d-none');
                                _this.find(':input[type=submit]').prop('disabled', false);
                            }, 2000);

                    }
                }
            });
        });
    }

}


