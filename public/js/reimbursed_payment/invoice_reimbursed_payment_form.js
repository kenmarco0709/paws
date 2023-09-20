var invoice_reimbursed_payment_form  = {
    settings: {
        reimbursedPaymentProcessFormAjaxUrl: '',
    },
    init: function(){

        invoice_reimbursed_payment_form.processForm();

        $('.close-modal').unbind('click').bind('click',function(){
            $('.modal').modal('hide');
        });
    },


    processForm: function(){
        
        $('#reimbursed_paymentForm').submit(function(e){
            e.preventDefault();
            _this = $(this);
            _this.find(':input[type=submit]').prop('disabled', true);
            var formData = $(this).serialize();

            $.ajax({
                url: invoice_reimbursed_payment_form.settings.reimbursedPaymentProcessFormAjaxUrl,
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


