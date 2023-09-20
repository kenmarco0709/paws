var vet_schedule = {
    settings: {
        ajaxUrl: '',
        scheduleDataAjaxUrl: ''

    },
    init: function() {
        vet_schedule.initDataTable();
        vet_schedule.getScheduleData();
    },

    getScheduleData: function(){

        $.ajax({
            url : vet_schedule.settings.scheduleDataAjaxUrl,
            success: function(s){

                var events = [];
                $.each(s, function( index, value ) {
                    var data = { 
                        id: value.id,
                        title: value.vet,
                        backgroundColor: '#0073b7', 
                        borderColor    : '#0073b7'
                    };


                    if(value.scheduleType == 'Leave Schedule'){
                        data.backgroundColor =  '#eb7134';
                        data.borderColor     =  '#eb7134';
                    }

                    if(value.scheduleFromYear != null && value.scheduleToYear != null && value.scheduleFromTime != null && value.scheduleToTime != null){
                        data.allDay = false;
                        data.start = value.scheduleFromYear + '-' +  value.scheduleFromMonth +'-' +value.scheduleFromDay + 'T' + moment(value.scheduleFromTime, ["h:mm A"]).format("HH:mm");
                        data.end = value.scheduleToYear + '-' +  value.scheduleToMonth +'-' +value.scheduleToDay + 'T' + moment(value.scheduleToTime, ["h:mm A"]).format("HH:mm");
                        data.displayEventEnd  = true;
                    } else {
                       
                        data.start = value.scheduleFromYear+'-'+value.scheduleFromMonth+'-'+value.scheduleFromDay;
                        data.end = value.scheduleToYear+'-'+value.scheduleToMonth +'-'+value.scheduleToDay;
                        data.allDay = true;
                    }
                    
                    events.push(data);
                });
                vet_schedule.initCalendar(events);                
            }
        });
    },
    convertTo25Hrs: function(time){
        console.log(time);
        var d = new Date("1/1/2022 " + time); 
        return d.getHours() + ':' + d.getMinutes(); 
    },
    initDataTable: function() {

        var callBack = function() {
        };

        vet_schedule.dataList = $('#vet_schedule-datalist').DataTable({
            'order': [[4, 'ASC']],
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': vet_schedule.settings.ajaxUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.startDate = $('#startDate').val();
                    d.endDate = $('#endDate').val();
                }
            },
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 5 },
                { 'searchable': false, 'targets': 5 }
            ],
            createdRow: function( row, data, dataIndex ) {
                // Set the data-status attribute, and add a class
               var dataType =  $( row ).find('td:eq(5)').find('.table-btn').attr('data-type');

               if(dataType == 'follow-up'){
                
                    $( row ).css('background-color' , '#7bc234');
               } else {

               }
            },
            drawCallback: function() {
                callBack();
            },
            responsive: {
                details: {
                    renderer: function( api,rowIdx ) {
                        return global.dataTableResponsiveCallBack(api, rowIdx, callBack);
                    }
                }
            }
        });

        $('.content-container').removeClass('has-loading');
        $('.content-container-content').removeClass('hide');
    },
    initCalendar : function(events){

        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById('calendar');
        var calendar = new Calendar(calendarEl, {
            dateClick: function(info) {
                window.location = '/vet_schedule/form/n/0?date=' + info.dateStr;
            },
            headerToolbar: {
                left  : 'prev,next today new',
                center: 'title',
                right : 'dayGridMonth'
            },
            themeSystem: 'bootstrap',
            events: events,
            eventStartEditable: false,
            droppable : false, 
            eventClick: function(event) {
                    window.location = '/vet_schedule/form/u/' + event.event.id;
            },
            eventContent: function (arg) {

                var event = arg.event;
                console.log(event.end);
                var customHtml = "<div>" + event.title + ': '+ (event.start != null ? moment(event.start, ["h:mm A"]).format("hh:mm A") : '')  + (event.end != null ? '-'  +  moment(event.end, ["h:mm A"]).format("hh:mm A") : '') +"</div>";

                              
    
                return { html: customHtml }
            }
        });

        calendar.render();
        $('.fc-daygrid-event ').css('cursor', 'pointer');
        $('.fc-new-button').html('New Schedule');
        $('.fc-re-vet_schedule-button').html('Re-vet_schedule');
        $('.fc-re-vet_schedule-button').addClass('btn-success');

        $('.fc-new-button').unbind('click').bind('click',function(){
            window.location = '/vet_schedule/form';
        });
    }
};