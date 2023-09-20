var stage_form = {
    settings: {
      stageTypeAutoCompleteUrl: '',
    },
    init: function(){

      stage_form.autoComplete();
    },
    autoComplete: function(){

        global.autocomplete.bind(this.settings.stageTypeAutoCompleteUrl,'#stage_form_stage_type_desc','#stage_form_stage_type');
       
    },
}


