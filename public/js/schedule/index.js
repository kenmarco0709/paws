var schedule = {
    settings: {
        ajaxUrl: '',
        scheduleDataAjaxUrl: ''

    },
    init: function() {
        schedule.initDataTable();
        schedule.getScheduleData();
    },

    getScheduleData: function(){

        $.ajax({
            url : schedule.settings.scheduleDataAjaxUrl,
            success: function(s){

                var events = [];
                $.each(s, function( index, value ) {
                    var data = { 
                        id: value.id,
                        start : new Date(value.scheduleYear, value.scheduleMonth - 1, value.scheduleDay), 
                        title: value.schedules,
                        allDay: true,
                        backgroundColor: value.bgColor, 
                        borderColor    :  value.bgColor
                    };
                   
                    events.push(data);
                });

                schedule.initCalendar(events);                
            }
        });
    },
    initDataTable: function() {

        var callBack = function() {
        };

        schedule.dataList = $('#schedule-datalist').DataTable({
            'order': [[4, 'ASC']],
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': schedule.settings.ajaxUrl,
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
                    window.location = '/schedule/form/n/0?date=' + info.dateStr;
                },
                headerToolbar: {
                    left  : 'prev,next today new re-schedule import',
                    center: 'title',
                    right : 'dayGridMonth'
                },
                themeSystem: 'bootstrap',
                events: events,
                eventStartEditable: false,
                droppable : false, 
                eventClick: function(event) {
                    window.location = '/schedule/form/u/' + event.event.id;
                },
                eventDidMount: function(info) {
                    console.log(info);
                }
            }
        );

        calendar.render();
        $('.fc-daygrid-event ').css('cursor', 'pointer');
        $('.fc-new-button').html('New Schedule');
        $('.fc-re-schedule-button').html('Re-schedule');
        $('.fc-re-schedule-button').addClass('btn-success');
        $('.fc-import-button').html(
            '<form method="POST" action="/schedule/import" enctype="multipart/form-data" id="uploadFileForm">' +
                '<label for="uploadFile" class="btn btn-primary" style="margin:0;font-weight:500;">' +
                   ' Import'+
                '</label>' +
                '<input id="uploadFile" type="file" name="items" style="display:none;" accept=".csv" />' +
            '</form>'
        );

        $('.fc-import-button').css('padding', '0px');
        

        $('.fc-new-button').unbind('click').bind('click',function(){
            window.location = '/schedule/form';
        });

        
        $('.fc-re-schedule-button').unbind('click').bind('click',function(){
            window.location = '/schedule/reschedule';
        });

        $('#uploadFile').unbind('change').bind('change',function(){
            $('#uploadFileForm').submit();
       
        });
    }
};