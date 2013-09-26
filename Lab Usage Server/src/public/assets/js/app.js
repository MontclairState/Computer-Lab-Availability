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
                
                baseDelay = 500;
                $.each(data, function(index) {
                   lab = $(this);
                   generatePieCharts(index, lab[0], baseDelay);
                   lab = null;
                   baseDelay = baseDelay + 500;
                });
                baseDelay = null;
            } else {
                $("div.error_while_loading").removeClass("hide");
            }
        }
    );
}

function generatePieCharts(id, lab, loadingDelay) {
    if( $("div#" + id).size() > 0 ) {
        updateLabInfo(id, lab, loadingDelay);
        updateLabDetails(id, lab);
    } else {
        $("div#lab-charts").append("<div class=\"span4 lab-info\" id=\"" + id + "\"></div>");
        $("div#" + id).append("<h2 class=\"l_title\">" + lab.title + "</h2>");
        if(lab.label != "") {
            $("div#" + id).append("<h3 class=\"l_subtitle\">" + lab.description + "</h3>");
        }
        $("div#" + id).append("<div id=\"" + id + "_chart\" class=\"chart_container\"></div>");
        $("div#" + id).append("<p class=\"l_avail\">Available: " + lab.available + "</p>");
        $("div#" + id).append("<p class=\"l_occup\">Occupied: " + lab.occupied + "</p>");
        $("div#" + id).append("<p class=\"l_maint\">Maintenance: " + lab.maintenance + "</p>");
        $("div#" + id).append("<p class=\"l_total\">Total: " + lab.total + "</p>");
        $("div#" + id).append("<p class=\"l_button\"><a class=\"btn\" href=\"#" + id + "_details\" data-toggle=\"modal\" data-backdrop=\"true\" data-keyboard=\"true\">Details &raquo;</a></p>");

        setTimeout(function() { getChart(lab, id + "_chart"); }, loadingDelay);
        $("div#" + id).append("<div id=\"" + id + "_details\" class=\"modal hide\"></div>");
        $("div#" + id + "_details").append("<div class=\"modal-header\"><a href=\"#\" class=\"close\" data-dismiss=\"modal\">&times;</a><h3 class=\"m_title\">" + lab.title + "</h3></div>");
        $("div#" + id + "_details").append("<div class=\"modal-body\"></div>");

        // Folloing are custom changes just for that lab.
        // You can use/customize/remove it.
        if(id == "UN5007") {
            $("div#" + id + "_details div.modal-body").append("<div class=\"row\"><div class=\"span7\"><p><strong>Note:</strong> Computers named <em>UN5007-11</em> to <em>UN5007-16</em> and <em>UN5007-51</em> to <em>UN5007-56</em> are MACs.</p></div></div>");
        }
        $("div#" + id + "_details div.modal-body").append("<div class=\"row\"><div class=\"span1 bold_text avail_color\">Available</div><div class=\"span5\">" + lab.data.available + "</div></div>").append("<div class=\"row margin_top_10\"><div class=\"span1 bold_text occup_color\">Occupied</div><div class=\"span5\">" + lab.data.occupied + "</div></div>");
        // End custom changes
        
        if(lab.data.maintenance != "") {
            $("div#" + id + "_details div.modal-body").append("<div class=\"row margin_top_10\"><div class=\"span1 bold_text maint_color\">Maintenance</div><div class=\"span5\">" + lab.data.maintenance + "</div></div>");
        }
    }
}

function updateLabInfo(labname, data, delay) {
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

function updateLabDetails(labname, data) {
    
    $("div#" + labname + "_details div.modal-header h3.m_title").empty()
                                                                .text(data.title);
    $("div#" + labname + "_details div.modal-body").empty();
    // Folloing are custom changes just for that lab.
    // You can use/customize/remove it.
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
