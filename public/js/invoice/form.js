var invoice_form = {
    settings: {
        invoicePaymentFormAjaxUrl: '',
        clientAjaxAutocompleteUrl: '',
        inventoryItemAutompleteUrl: '',
        formAction: '',
        serviceAutcompleteUrl: '',
        itemListIds : {},
        invoiceReimbursedPaymentFormAjaxUrl: '',
        clientAjaxForm: '',

    },
    init: function(){
      
        invoice_form.doCompute();
        invoice_form.bindModal();
        if( invoice_form.settings.itemListIds['service'] == undefined){
         
          invoice_form.settings.itemListIds['service'] = [];
        }

        if( invoice_form.settings.itemListIds['item'] == undefined){
         
          invoice_form.settings.itemListIds['item'] = [];
        }

        invoice_form.autoComplete();
        invoice_form.bindModal();

        //push services to array
        if($('.services').length){
          $.each($('.services'), function(){
            
            invoice_form.settings.itemListIds['service'].push($(this).val());
            invoice_form.doCompute();

          });
        }

         //push items to array
         if($('.items').length){
          $.each($('.items'), function(){
            invoice_form.settings.itemListIds['item'].push($(this).val());
          });
        }
    },
    doCompute: function(){
      
        invoice_form.compute();
        invoice_form.remove();
        invoice_form.computeGrandTotal();

        $('#disc').unbind('keyup').bind('keyup',function(){
          invoice_form.computeGrandTotal();
        });
    },
    addItem: function(){

      $.each($('.href-add'), function(){

         var _this = $(this);
        _this.unbind('click').bind('click',function(){
          var  _html = '' 

          if(invoice_form.settings.itemListIds[_this.attr('data-type')] == undefined){
             invoice_form.settings.itemListIds[_this.attr('data-type')] = [];
          }
          var dataHolder = $('.data-field');
          if(dataHolder.attr('data-id') != ''){
            if(jQuery.inArray(dataHolder.attr('data-id'), invoice_form.settings.itemListIds[_this.attr('data-type')]) === -1){
                invoice_form.settings.itemListIds[_this.attr('data-type')].push(dataHolder.attr('data-id'));
                var targetTable = $('#' + dataHolder.attr('data-target')); 

                _html += '<tr><td><a data-id="'+dataHolder.attr('data-id')+'" data-type="'+_this.attr('data-type')+'"  class="remove" href="javascript:void(0);"/><i style="color:red;margin-right:5px;" class="fa fa-times" aria-hidden="true"></i></a>'+ dataHolder.attr('data-description')+'<input type="hidden" name="invoice['+_this.attr('data-type')+']['+invoice_form.settings.itemListIds[_this.attr('data-type')].length+'][id]" value="'+ dataHolder.attr('data-id')+'" /></td>';
                _html += '<td><input type="text" name="invoice['+_this.attr('data-type')+']['+invoice_form.settings.itemListIds[_this.attr('data-type')].length+'][price]" value="'+ dataHolder.attr('data-price')+'" class="form-control price" readonly /></td>';
                _html += '<td><input type="text" name="invoice['+_this.attr('data-type')+']['+invoice_form.settings.itemListIds[_this.attr('data-type')].length+'][quantity]"  class="form-control amt quantity has-computation "  required /></td>';
                _html += '<td><input type="text" name="invoice['+_this.attr('data-type')+']['+invoice_form.settings.itemListIds[_this.attr('data-type')].length+'][percent_discount]" value="" class="form-control amt disc has-computation"  /></td>';
                _html += '<td><input type="text" name="invoice['+_this.attr('data-type')+']['+invoice_form.settings.itemListIds[_this.attr('data-type')].length+'][remarks]" value="" class="form-control"  /></td>';
                _html += '<td><input type="text" name="invoice['+_this.attr('data-type')+']['+invoice_form.settings.itemListIds[_this.attr('data-type')].length+'][total_price]" value="0.00" class="form-control total-price" readonly /></td></tr>';

                targetTable.find('tbody').append(_html);
                global.mask();

               invoice_form.doCompute();

                $('#item_desc').val('');
                $('#item_id').val('');
                $('#service_desc').val('');
                $('#service_id').val('');
                dataHolder.attr('data-id','');
                dataHolder.attr('data-description','');
                dataHolder.attr('data-price','');

            }
          }
      }); 
      });
    },  
  
  remove: function(){

    $.each($('.remove'),function(){
        var _this = $(this);
        _this.unbind('click').bind('click',function(){
          invoice_form.settings.itemListIds[_this.attr('data-type')].splice($.inArray(_this.attr('data-id'), invoice_form.settings.itemListIds[_this.attr('data-type')]), 1);

          _this.closest('tr').remove();
          invoice_form.computeGrandTotal();
        });
    });
  },
  compute: function(){

    $.each($('.has-computation'), function(){
        global.mask();
         var _this = $(this);
        _this.unbind('keyup').bind('keyup',function(){
                var price = _this.closest('tr').find('.price').val();
                var disc  = _this.closest('tr').find('.disc').val();
                var quantity  = _this.closest('tr').find('.quantity').val();
                var totalItemPriceInput =  _this.closest('tr').find('.total-price');
                totalItemPrice = 0;
                if(quantity > 0 ){
                    totalItemPrice = quantity * price;
                }
                if(disc > 0 ){
                  
                  totalItemPrice = totalItemPrice - ((totalItemPrice * disc) / 100)
                }
                totalItemPriceInput.val(parseFloat(totalItemPrice).toFixed(2)); 
                invoice_form.computeGrandTotal();

        });
    });
  },

  computeGrandTotal:function(){

      var grandTotal = 0;

      $.each($('.total-price'), function(){
        grandTotal = grandTotal + parseFloat($(this).val());

      });

      $('#amount').val(parseFloat(grandTotal).toFixed(2));
      var remainingBalance = parseFloat(grandTotal).toFixed(2);

      if($('#disc').val() > 0){
          remainingBalance  = grandTotal - ((grandTotal *  $('#disc').val()) / 100)
      }
      if($('#totalPayment').length){
        if(parseFloat($('#totalPayment').val().replace(',','')) > 0){
          remainingBalance  = remainingBalance - parseFloat($('#totalPayment').val().replace(',',''));
          
        }
      }
      
      if($('#grand-total').length){
         $('#grand-total').val(parseFloat(remainingBalance).toFixed(2));
      }
  },
  autoComplete: function(){

        global.autocomplete.bind(invoice_form.settings.serviceAutcompleteUrl,'#service_desc','#service_id',{
          onSelect: function(d){
              $('.data-field').attr('data-id', d.id)
              $('.data-field').attr('data-description', d.value);
              $('.data-field').attr('data-price', d.price);
              invoice_form.addItem();
          },
          onSearchStart: function(){
            $('.data-field').attr('data-id','')
            $('.data-field').attr('data-description', '');
            $('.data-field').attr('data-price', '');
          }
        });

        global.autocomplete.bind(invoice_form.settings.inventoryItemAutompleteUrl,'#item_desc','#item_id',{
          onSelect: function(d){
              $('.data-field').attr('data-id', d.id)
              $('.data-field').attr('data-description', d.value);
              $('.data-field').attr('data-price', d.price);
              invoice_form.addItem();
          },
          onSearchStart: function(){
            $('.data-field').attr('data-id','')
            $('.data-field').attr('data-description', '');
            $('.data-field').attr('data-price', '');
          }
        });
  
        global.autocomplete.bind(this.settings.clientAjaxAutocompleteUrl,'#invoice_form_client_desc','#invoice_form_client');
    },

    bindModal: function (){
        
        $('.href-modal').unbind('click').bind('click',function(){
            var _this = $(this);
            
            $('.modal').removeClass('modal-fullscreen');

            var url = '';
            
            if(_this.data('target') == 'client-form'){
              url = invoice_form.settings.clientAjaxForm;
            }

            if(_this.data('target') == 'payment-form'){
              url = invoice_form.settings.invoicePaymentFormAjaxUrl;
            }

            if(_this.data('target') == 'reimbursed-payment-form'){
              url = invoice_form.settings.invoiceReimbursedPaymentFormAjaxUrl;
            }

            $.ajax({
              url: url,
              type: 'POST',
              data: { invoiceId: _this.data('invoiceid') },
              beforeSend: function(){
                $(".modal-content").html('');
                  
              },
              success: function(r){
                if(r.success){
                  $("#ui-datepicker-div").remove();
                  $(".modal-content").html(r.html);
                  $('#modal').modal('show');
                }
              }
            });
        });
    }

}


