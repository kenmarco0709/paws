var schedule = {
    settings: {
        scheduleTypeAjaxAutocompleteUrl: ''
    },
    init: function() {

        schedule.bindAutocomplete();
        $.each($('.btn-report'),function(){
            var _this = $(this);
            _this.unbind('click').bind('click',function(){
                window.open(_this.data('url') + '?start_date=' + ($('#startDate').val() != '' ? $('#startDate').val() : null) + "&end_date=" + ($('#endDate').val() != '' ? $('#endDate').val() : null)+ "&schedule_type=" + ($('#scheduleType').val() != '' ? $('#scheduleType').val() : null)+ "&status=" + $('#status').val(), '_blank');
            });
        });
    },
    bindAutocomplete:function(){
        global.autocomplete.bind(this.settings.scheduleTypeAjaxAutocompleteUrl,'#scheduleTypeDesc','#scheduleType');

    }
};