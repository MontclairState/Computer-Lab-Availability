/* Application Specific Javascripts */
$(document).ready(function() {
    $('#daypicker').datepicker();
    
    $('#weekpicker')
        .datepicker()
        .on('changeDate', function(ev) {
            
            var weekDates = getWeekStartAndEndDates(ev.date);
            
            $("#weekhelptext").text("Week of " + formateDate(weekDates[0]) + " to " + formateDate(weekDates[1]));
            $("#startdate").val(formateDate(weekDates[0]));
            $("#enddate").val(formateDate(weekDates[1]));
            
            $('#weekpicker').datepicker('hide');
            
            weekDates = null;
        });
    
    $('#custom_from').datepicker();
    $('#custom_to').datepicker();
    
    $('#custom_from')
        .datepicker()
        .on('changeDate', function(ev){
            var endDate = Date.parse($('#custom_to').val());
            if (ev.date.valueOf() > endDate.valueOf()){
                $('#cust_range_alert').show().find('strong').text('From date must be before the To date.');
                $('#custom_submit').attr("disabled", "disabled");
                $('#custom_download').attr("disabled", "disabled");
            } else {
                $('#custom_submit').removeAttr("disabled");
                $('#cust_range_alert').hide();
                $('#custom_download').removeAttr("disabled");
            }
            endDate = null;
            $('#custom_from').datepicker('hide');
        });
    $('#custom_to')
        .datepicker()
        .on('changeDate', function(ev){
            var startDate = Date.parse($('#custom_from').val());
            if (ev.date.valueOf() < startDate.valueOf()){
                $('#cust_range_alert').show().find('strong').text('To date must be after From date.');
                $('#custom_submit').attr("disabled", "disabled");
                $('#custom_download').attr("disabled", "disabled");
            } else {
                $('#custom_submit').removeAttr("disabled");
                $('#cust_range_alert').hide();
                $('#custom_download').removeAttr("disabled");
            }
            startDate = null;
            $('#custom_to').datepicker('hide');
        });
    
    $('#comp_usage_date').datepicker();
    $('#comp_usage_from').datepicker();
    $('#comp_usage_to').datepicker();
    enableCUOption('date');
    
    $('a#daily-report-download').click(function(e) {
        e.preventDefault();  //stop the browser from following
        var curdate = $('#daypicker').val();
        if(curdate == "" || curdate == null) {
            showChartError('#day_chart', 'Error!', 'Date cannot be empty.');
            return;
        }
        
        window.location.href = 'report-download.php?type=day&lab=' + lab + '&date=' + curdate;
        curdate = null;
    });
    
    $('a#weekly-report-download').click(function(e) {
        e.preventDefault();  //stop the browser from following
        var startdate = $('input#startdate').val();
        var enddate = $('input#enddate').val();
        
        if(startdate == "" || startdate == null || enddate == "" || enddate == null) {
            showChartError('#week_chart', 'Error!', 'startdate and enddate cannot be empty.');
            return;
        }        
        window.location.href = 'report-download.php?type=week&lab=' + lab + '&startdate=' + startdate + '&enddate=' + enddate;
        startdate = null;
        enddate = null;
    });
    
    $('a#monthly-report-download').click(function(e) {
        e.preventDefault();  //stop the browser from following
        var month = $('select#month').val();
        var year = $('select#year').val();
        
        if(month == "" || month == null || year == "" || year == null) {
            showChartError('#month_chart', 'Error!', 'month and year cannot be empty.');
            return;
        }        
        window.location.href = 'report-download.php?type=month&lab=' + lab + '&month=' + month + '&year=' + year;
        month = null;
        year = null;
    });
    
    $('a#custom-report-download').click(function(e) {
        e.preventDefault();  //stop the browser from following
        var startdate = $('input#custom_from').val();
        var enddate = $('input#custom_to').val();
        
        if(startdate == "" || startdate == null || enddate == "" || enddate == null) {
            showChartError('#custom_chart', 'Error!', 'startdate and enddate cannot be empty.');
            return;
        }        
        window.location.href = 'report-download.php?type=custom&lab=' + lab + '&startdate=' + startdate + '&enddate=' + enddate;
        startdate = null;
        enddate = null;
    });
    
    $('a#compusage-report-download').click(function(e) {
        e.preventDefault();  //stop the browser from following
        var cuoption = $('input:radio[name=cu_option]:checked').val();
        var compname = $('select#comp_name').val();
        
        if(cuoption == "day") {
            var curdate = $('#comp_usage_date').val();
            if(curdate == "" || curdate == null) {
                showChartError('#cu_chart', 'Error!', 'date cannot be empty.');
                return;
            }
            window.location.href = 'report-download.php?type=comp_usage&cuoption=day&compname=' + compname + '&date=' + curdate;
            curdate = null;
        } else if(cuoption == "month") {
            var month = $('#comp_usage_month').val();
            var year = $('#comp_usage_year').val();
            if(month == "" || month == null || year == "" || year == null) {
                showChartError('#cu_chart', 'Error!', 'month and year cannot be empty.');
                return;
            } 
            
            window.location.href = 'report-download.php?type=comp_usage&cuoption=month&compname=' + compname + '&month=' + month + '&year=' + year;
            year = null;
            month = null;
        } else if(cuoption == "range") {
            var cu_from = $('#comp_usage_from').val();
            var cu_to = $('#comp_usage_to').val();
            if(cu_from == "" || cu_from == null || cu_to == "" || cu_to == null) {
                showChartError('#cu_chart', 'Error!', 'from date and to date cannot be empty.');
                return;
            } 
            window.location.href = 'report-download.php?type=comp_usage&cuoption=range&compname=' + compname + '&from=' + cu_from + '&to=' + cu_to;
            cu_from = null;
            cu_to = null;
        }
        cuoption = null;
        compname = null;
    });
    
});

function getWeekStartAndEndDates(dateOfWeekDay) {
    
    var day = dateOfWeekDay.getDay() - 0;
    var date = dateOfWeekDay.getDate() - day;

    var startDate = new Date(dateOfWeekDay.setDate(date));
    var endDate = new Date(dateOfWeekDay.setDate(date + 6));    
    
    day = null;
    date = null;
    
    return [startDate, endDate];
}

function showChartLoading(chartid) {
    $(chartid).empty();
    $(chartid).append("<p>Please wait while generating report.</p><div class=\"progress progress-success progress-striped active\"><div class=\"bar\" style=\"width: 100%;\"></div></div>");
}

function showChartError(chartid, title, message) {
    $(chartid).empty();
    $(chartid).append("<div class=\"alert alert-error\"><a class=\"close\" data-dismiss=\"alert\" href=\"#\">×</a><h4 class=\"alert-heading\">" + title + "</h4><p>" + message + "</p></div>")
}

function showChartWarning(chartid, title, message) {
    $(chartid).empty();
    $(chartid).append("<div class=\"alert alert-warning\"><a class=\"close\" data-dismiss=\"alert\" href=\"#\">×</a><h4 class=\"alert-heading\">" + title + "</h4><p>" + message + "</p></div>");
}

function generateDayChart(lab, date) {
    if(date == "" || date == null) {
        showChartError('#day_chart', 'Error!', 'Date cannot be empty.');
        return;
    }
    showChartLoading('#day_chart');
    $.getJSON("report_data.php", 
        {type: "day", lab: lab, date: date}, 
        function(data) {
            if(data != null) {
                drawDayChart(data);
            } else {
                showChartWarning('#day_chart', 'No data available.', 'Chart cannot be generated for current input. Please change the input and try again.');
            }
    }); 
}

function generateWeekChart(lab, startdate, enddate) {
    if(startdate == "" || startdate == null || enddate == "" || enddate == null) {
        showChartError('#week_chart', 'Error!', 'startdate and enddate cannot be empty.');
        return;
    }
    showChartLoading('#week_chart');
    $.getJSON("report_data.php", 
        {type: "week", lab: lab, startdate: startdate, enddate: enddate}, 
        function(data) {
            if(data != null) {
                drawWMCChart(data, startdate, 'week_chart');
            } else {
                showChartWarning('#week_chart', 'No data available.', 'Chart cannot be generated for current input. Please change the input and try again.');
            }
    }); 
}

function generateMonthChart(lab, month, year) {
    if(month == "" || month == null || year == "" || year == null) {
        showChartError('#month_chart', 'Error!', 'month and year cannot be empty.');
        return;
    }
    showChartLoading('#month_chart');
    $.getJSON("report_data.php", 
        {type: "month", lab: lab, month: month, year: year}, 
        function(data) {
            if(data != null) {
                var sdate = year + "-";
                if(month < 10) {
                    sdate = sdate + "0" + month;
                } else {
                    sdate = sdate + month;
                }
                sdate = sdate + "-01";
                drawWMCChart(data, sdate, 'month_chart');
            } else {
                showChartWarning('#month_chart', 'No data available.', 'Chart cannot be generated for current input. Please change the input and try again.');
            }
    }); 
}

function generateCustomChart(lab, startdate, enddate) {
    if(startdate == "" || startdate == null || enddate == "" || enddate == null) {
        showChartError('#custom_chart', 'Error!', 'startdate and enddate cannot be empty.');
        return;
    }    
    showChartLoading('#custom_chart');
    $.getJSON("report_data.php", 
        {type: "custom", lab: lab, startdate: startdate, enddate: enddate}, 
        function(data) {
            if(data != null) {
                drawWMCChart(data, startdate, 'custom_chart');
            } else {                    
                showChartWarning('#custom_chart', 'No data available.', 'Chart cannot be generated for current input. Please change the input and try again.');
            }
    });
}

function generateCUChart(compname, cuoption, date, month, year, cu_from, cu_to) {
    showChartLoading('#cu_chart');
    
    if(cuoption == "day") {
        if(date == "" || date == null) {
            showChartError('#cu_chart', 'Error!', 'date cannot be empty.');
            return;
        } else {
            $.getJSON("report_data.php", 
            {type: "comp_usage", cuoption: "day", compname: compname, date: date}, 
            function(data) {
                if(data != null) {
                    drawCUChart(data);
                } else {
                    showChartWarning('#cu_chart', 'No data available.', 'Chart cannot be generated for current input. Please change the input and try again.');
                }
        });
        }
    } else if(cuoption == "month") {
        if(month == "" || month == null || year == "" || year == null) {
            showChartError('#cu_chart', 'Error!', 'month and year cannot be empty.');
            return;
        } else {
            $.getJSON("report_data.php", 
            {type: "comp_usage", cuoption: "month", compname: compname, month: month, year: year}, 
            function(data) {
                if(data != null) {
                    drawCUChart(data);
                } else {
                    showChartWarning('#cu_chart', 'No data available.', 'Chart cannot be generated for current input. Please change the input and try again.');
                }
        });
        }
    } else if(cuoption == "range") {
        if(cu_from == "" || cu_from == null || cu_to == "" || cu_to == null) {
            showChartError('#cu_chart', 'Error!', 'from date and to date cannot be empty.');
            return;
        } else {
            $.getJSON("report_data.php", 
            {type: "comp_usage", cuoption: "range", compname: compname, from: cu_from, to: cu_to}, 
            function(data) {
                if(data != null) {
                    drawCUChart(data);
                } else {
                    showChartWarning('#cu_chart', 'No data available.', 'Chart cannot be generated for current input. Please change the input and try again.');
                }
        });
        }
    }
}

function formateDate(dateObj) {
  var dd = dateObj.getDate();
  if(dd < 10) 
      dd = '0' + dd;

  var mm = dateObj.getMonth() + 1;
  if(mm < 10) 
      mm = '0' + mm;

  return dateObj.getFullYear() + "-" + mm + "-" + dd;
}

function drawDayChart(obj) {

    var chartObj = new Highcharts.Chart({
            chart: {
                    renderTo: 'day_chart',
                    type: 'column'
            },
            credits: {
                    enabled: false
            },
            colors: [
                    '#89A54E',
                    '#AA4643', 	
                    '#4572A7', 
                    '#80699B', 
                    '#3D96AE', 
                    '#DB843D', 
                    '#92A8CD', 
                    '#A47D7C', 
                    '#B5CA92'
            ],
            title: {
                    text: obj.title,
                    margin: 50
            },
            xAxis: {
                    categories: ['12 AM', '1 AM', '2 AM', '3 AM', '4 AM', '5 AM', '6 AM', '7 AM', '8 AM', '9 AM', '10 AM', '11 AM', 
                                                '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM', '10 PM', '11 PM'],
                    title: {
                            text: 'Hour of the day'
                    }
            },
            yAxis: {
                    min: 0,
                    title: {
                            text: 'No. of lab computers'
                    }
            },
            legend: {
                    align: 'right',
                    x: 0,
                    verticalAlign: 'top',
                    y: 30,
                    floating: true,
                    backgroundColor: '#FFFFFF',
                    borderColor: '#CCC',
                    borderWidth: 1,
                    shadow: false
            },
            tooltip: {
                    formatter: function() {
                            return '<b>'+ this.x +'<br/>'+
                                   this.series.name +': '+ this.y +'</b><br/>'+
                                        'Total: '+ this.point.stackTotal;
                    }
            },
            plotOptions: {
                    column: {
                            stacking: 'normal'
                    }
            },
            series: [{
                        name:'Available',
                        data:obj.series[0].data},
                    {
                        name:'Occupied',
                        data:obj.series[1].data},
                    {
                        name:'Offline',
                        data:obj.series[2].data}]
    });
    chartObj = null;
}

function drawWMCChart(obj, startdate, chart_div) {
    
    chartObj = new Highcharts.Chart({
        chart: {
            renderTo: chart_div
        },
        credits: {
            enabled: false
        },
        title: {
            text: obj.title,
            margin: 50
        },
        colors: [
            '#AA4643',
            '#89A54E', 	
            '#4572A7', 
            '#80699B', 
            '#3D96AE', 
            '#DB843D', 
            '#92A8CD', 
            '#A47D7C', 
            '#B5CA92'
        ],
        xAxis: {
            type: 'datetime',
            dateTimeLabelFormats: {
                day: '%e %b'   
            },
            minorGridLineWidth: 0,
            minorTickInterval: 3600 * 1000,
            minorTickWidth: 1,
            title: {
                text: 'Day of month'
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'No. of Computers'
            }
        },
        tooltip: {
            formatter: function() {
                return '<b>No. of ' + this.series.name + ' Computers:</b> ' + Highcharts.numberFormat(this.y, 0) + '<br>' + 
                    '<b>At</b> ' + Highcharts.dateFormat('%e %b %Y, %l:00 %P', this.x);
            }
        },
        legend: {
            align: 'right',
            x: 0,
            verticalAlign: 'top',
            y: 30,
            floating: true,
            backgroundColor: '#FFFFFF',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        plotOptions: {
            area: {
                pointStart: 0,
                marker: {
                    enabled: false,
                    symbol: 'circle',
                    radius: 2,
                    states: {
                        hover: {
                            enabled: true
                        }
                    }
                }
            }
        },
        series: [{
                name: 'Occupied',
                type: 'area',
                data: obj.series[1].data,
                pointStart: Date.parse(startdate),
                pointInterval: 3600 * 1000
            }, {
                name: 'Available',
                type: 'spline',
                data: obj.series[0].data,
                pointStart: Date.parse(startdate),
                pointInterval: 3600 * 1000,
                marker: {
                    enabled: false,
                    symbol: 'circle',
                    radius: 2,
                    states: {
                        hover: {
                            enabled: true
                        }
                    }
                }
            }]
    });

    chartObj = null;
}

function drawCUChart(obj) {
    var chartObj = new Highcharts.Chart({
                        chart: {
                                renderTo: 'cu_chart',
                                defaultSeriesType: 'column'
                        },
                        credits: {
                                enabled: false
                        },
                        colors: [
                                '#89A54E',
                                '#AA4643', 	
                                '#4572A7', 
                                '#80699B', 
                                '#3D96AE', 
                                '#DB843D', 
                                '#92A8CD', 
                                '#A47D7C', 
                                '#B5CA92'
                        ],
                        title: {
                                text: obj.title,
                                margin: 50
                        },
                        xAxis: {
                                categories: obj.xaxis,
                                labels: {
                                        rotation: -45,
                                        align: 'right'
                                },
                                title: {
                                        text: 'Day of month'
                                }
                        },
                        yAxis: {
                                min: 0,
                                title: {
                                        text: 'No. of times computer used'
                                }
                        },
                        legend: {
                                align: 'right',
                                x: 0,
                                verticalAlign: 'top',
                                y: 30,
                                floating: true,
                                backgroundColor: '#FFFFFF',
                                borderColor: '#CCC',
                                borderWidth: 1,
                                shadow: false
                        },
                        tooltip: {
                                formatter: function() {
                                        return '<b> On day ' + this.x +'</b><br/>'+
                                                    this.series.name +': '+ this.y;
                                }
                        },
                        plotOptions: {
                                column: {
                                        stacking: 'normal'
                                }
                        },
                        series: [{
                                    name: 'No. of times Computer used',
                                    data: obj.data
                                }]
                    });
    chartObj = null;
}

function enableCUOption(opt) {
    
    if(opt == 'date') {
        disableCUOption('month');
        disableCUOption('range');
        $('#cu_option_date').attr("checked", "checked");
        $('#comp_usage_date').removeAttr("disabled");
        $('#comp_usage_date_addon').attr("onclick", "$('#comp_usage_date').datepicker('show');");
    } else if(opt == 'month') {
        disableCUOption('date');
        disableCUOption('range');
        $('#cu_option_month').attr("checked", "checked");
        $('#comp_usage_month').removeAttr("disabled");
        $('#comp_usage_year').removeAttr("disabled");
    } else if(opt == 'range') {
        disableCUOption('date');
        disableCUOption('month');
        $('#cu_option_range').attr("checked", "checked");
        $('#comp_usage_from').removeAttr("disabled");
        $('#comp_usage_from_addon').attr("onclick", "$('#comp_usage_from').datepicker('show');");
        $('#comp_usage_to').removeAttr("disabled");
        $('#comp_usage_to_addon').attr("onclick", "$('#comp_usage_to').datepicker('show');");        
    }
}

function disableCUOption(opt) {    
    if(opt == 'date') {
        $('#comp_usage_date').attr("disabled", "disabled");
        $('#comp_usage_date_addon').removeAttr("onclick");
    } else if(opt == 'month') {
        $('#comp_usage_month').attr("disabled", "disabled");
        $('#comp_usage_year').attr("disabled", "disabled");
    } else if(opt == 'range') {        
        $('#comp_usage_from').attr("disabled", "disabled");
        $('#comp_usage_from_addon').removeAttr("onclick");
        $('#comp_usage_to').attr("disabled", "disabled");
        $('#comp_usage_to_addon').removeAttr("onclick");         
    }
}