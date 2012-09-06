$(document).ready(function() {
    getLatest();
    setInterval(function() { 
                    getLatest(); 
                }, 
                30000);
});

function getLatest() {
    $.getJSON("get-json.php", 
        function(data) {
            if(data != null && data != "") {
                $("div.error_while_loading").addClass("hide");
                $("div.legend").removeClass("hide");
                fillLabInfo("UN5007", data.UN5007, 0);
                fillLabDetails("UN5007", data.UN5007);
                fillLabInfo("UN5008", data.UN5008, 500);
                fillLabDetails("UN5008", data.UN5008);
                fillLabInfo("SC004D", data.SC004D, 1000);
                fillLabDetails("SC004D", data.SC004D);
                fillLabInfo("UNCART", data.UNCART, 1500);
                fillLabDetails("UNCART", data.UNCART);
                fillLabInfo("CIRC", data.CIRC, 2000);
                fillLabDetails("CIRC", data.CIRC);
            } else {
                $("div.error_while_loading").removeClass("hide");
            }
        }
    );
}

function fillLabInfo(labname, data, delay) {
    $("div#" + labname + " h2.l_title").text(data.title);
    $("div#" + labname + " h3.l_subtitle").text(data.description);
    $("div#" + labname + " p.l_avail").text("Available: " + data.available);
    $("div#" + labname + " p.l_occup").text("Occupied: " + data.occupied);
    $("div#" + labname + " p.l_maint").text("Maintenance: " + data.maintenance);
    $("div#" + labname + " p.l_total").text("Total: " + data.total);
    $("div#" + labname + " p.l_button").empty()
                                       .append("<a class=\"btn\" href=\"#" + labname + "_details\" data-toggle=\"modal\" data-backdrop=\"true\" data-keyboard=\"true\">Details &raquo;</a>");
    setTimeout(function() { getChart(data, labname + "_chart"); }, delay);
}

function fillLabDetails(labname, data) {
    
    $("div#" + labname + "_details div.modal-header h3.m_title").empty()
                                                                .text(data.title);
    $("div#" + labname + "_details div.modal-body").empty()
    if(labname == "UN5007") {
        $("div#" + labname + "_details div.modal-body").append("<div class=\"row\"><div class=\"span7\"><p><strong>Note:</strong> Computers named <em>UN5007-11</em> to <em>UN5007-16</em> and <em>UN5007-51</em> to <em>UN5007-56</em> are MACs.</p></div></div>");
    }
    $("div#" + labname + "_details div.modal-body").append("<div class=\"row\"><div class=\"span1 bold_text avail_color\">Available</div><div class=\"span5\">" + data.data.available + "</div></div>")
                                                   .append("<div class=\"row margin_top_10\"><div class=\"span1 bold_text occup_color\">Occupied</div><div class=\"span5\">" + data.data.occupied + "</div></div>");
    if(data.data.maintenance != "") {
        $("div#" + labname + "_details div.modal-body").append("<div class=\"row margin_top_10\"><div class=\"span1 bold_text maint_color\">Maintenance</div><div class=\"span5\">" + data.data.maintenance + "</div></div>");
    }
}

function getChart(data, div_container) {
    var chart = new Highcharts.Chart({
            chart: {
                renderTo: div_container,
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                borderRadius: 0,
                spacingTop: 0,
                spacingBottom: 0,
                spacingRight: 0,
                spacingLeft: 0
            },
            credits: {
                    enabled: false
            },
            colors: [
                '#009933',
                '#cc0000',
                '#696969'
            ],
            exporting: {
                enabled: false    
            },
            title: {
                text: ''
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: false,
                    shadow: false,
                    size: '90%'
                }
            },
            series: [{
                type: 'pie',
                name: 'Lab Availablity',
                animation: {
                    duration: 300
                }, 
                data: [
                    ['Available', data.available],
                    ['Occupied', data.occupied],
                    ['Maintenance', data.maintenance]
                ]
            }]
        });
    return chart;
}

// From JS Fiddle Playground
/*
var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                borderRadius: 0,
                spacingTop: 0,
                spacingBottom: 0,
                spacingRight: 0,
                spacingLeft: 0
            },
            credits: {
                    enabled: false
            },
            exporting: {
                enabled: false    
            },
            title: {
                text: ''
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: false,
                    shadow: false
                }
            },
            series: [{
                type: 'pie',
                name: 'Browser share',
                data: [
                    ['Firefox',   45.0],
                    ['IE',       26.8],
                    ['Chrome',   12.8],
                    ['Safari',    8.5],
                    ['Opera',     6.2],
                    ['Others',   0.7]
                ]
            }]
        });
*/