$(document).ready(function() {
    $("#limit_per_page").val(10);
    $('a[data-toggle="tab"]').on('show', function (e) {
        if(e.target.id == "addedit_tab_link") {
            $("#refresh_data_button").addClass("hidden");
        } else {
            $("#refresh_data_button").removeClass("hidden");
        }
    });
});

function loadCompList(sort_by, sort_type, limit, offset, filter) {
    sort_by = typeof sort_by !== 'undefined' ? sort_by : 'name';
    sort_type = typeof sort_type !== 'undefined' ? sort_type : 'asc';
    limit = typeof limit !== 'undefined' ? limit : 10;
    offset = typeof offset !== 'undefined' ? offset : 0;
    filter = typeof filter != 'undefined' ? filter: '';

    //alert("Offset = " + offset + ", limit: " + limit);
    $.ajax({
        url: "admin_data.php",
        type: "GET",
        data: {type: "comp", op: "list", filter: filter, sort_by: sort_by, 
               sort_type: sort_type, limit: limit, offset: offset},
        success: function(data) {
            var obj = jQuery.parseJSON(data);
            
            $("#comp_list_tbody").html(obj.html);
            $("#showing-text").text(obj.table_text);
            $("div#sort_by").text(sort_by);
            $("div#sort_type").text(sort_type);
            buildPagination(obj.total, limit, offset);
            obj = null;
        }
    });
    return false;
}

function refreshTable() {
    var limit = $("#limit_per_page").val();
    var offset = limit*($("div#current_page").text()-1);
    
    loadCompList($("div#sort_by").text(),
                 $("div#sort_type").text(),
                 limit,
                 offset,
                 $("#filter_by_lab").val()
                );    
    limit = null;
    offset = null;
    return false;
}

function changeLimit(selectBox) {
    var newLimit = $(selectBox).val();
    var newOffset = newLimit*($("div#current_page").text()-1);
    
    loadCompList($("div#sort_by").text(),
                 $("div#sort_type").text(),
                 newLimit,
                 newOffset,
                 $("#filter_by_lab").val()
                );
                    
    newOffset = null;
    newLimit = null;
    return true;
}

function filterLab(selectBox) {
    var newLab = $(selectBox).val();
    var limit = $("#limit_per_page").val();
    var offset = limit*($("div#current_page").text()-1);
    
    loadCompList($("div#sort_by").text(),
                 $("div#sort_type").text(),
                 limit,
                 offset,
                 newLab
                );
                    
    offset = null;
    limit = null;
    newLab = null;
    return true;
}

function buildPagination(total, limit, offset) {
    var totalPages = Math.ceil(total/limit);    
    var currentPage = (offset/limit) + 1;
    var range = 7;
    var halfrange = Math.round(range/2);    
    var lower = 1;
    var higher = range;
    
    if(currentPage > halfrange) {
        lower = lower + (currentPage - halfrange);
        higher = lower + range - 1;
        if(higher > totalPages)
            higher = totalPages;
        if(lower > totalPages - range) {
            lower = totalPages - range + 1;
        }
    }
    
    var html = "<ul>";
    
    if(currentPage == 1) {
        html += "<li class=\"disabled\"><a href=\"#\">First</a></li>";
    } else {
        html += "<li><a href=\"#\" onclick=\"return loadCompList('" + $('div#sort_by').text() + "', " +
                                        "'" + $('div#sort_type').text() + "', " + 
                                        limit + ", " + 
                                        "0, " + 
                                        "'" + $("#filter_by_lab").val() + "');\">First</a></li>";
    }
    $("div#current_page").text(currentPage);
    
    for(var x = lower; x <= higher; x++) {
        if(x > 0 && x <= totalPages) {
            if(x == currentPage) {
                html += "<li class=\"active\"><a href=\"#\">" + x + "</a></li>";
            } else {
                
                html += "<li><a href=\"#\" onclick=\"return loadCompList('" + $('div#sort_by').text() + "', " +
                                        "'" + $('div#sort_type').text() + "', " + 
                                        limit + ", " + 
                                        (limit*(x-1)) + ", " + 
                                        "'" + $("#filter_by_lab").val() + "');\">" + x + "</a></li>";
            }
        }
    }
    
    if(currentPage == totalPages) {
        html += "<li class=\"disabled\"><a href=\"#\">Last</a></li>";
    } else {
        html += "<li><a href=\"#\" onclick=\"return loadCompList('" + $('div#sort_by').text() + "', " +
                                        "'" + $('div#sort_type').text() + "', " + 
                                        limit + ", " + 
                                        (limit*(totalPages-1)) + ", " + 
                                        "'" + $("#filter_by_lab").val() + "');\">Last</a></li>";
    }
    html += "</ul>";
    
    $("div.pagination").html(html);
    
    $("div.pagination ul li.disabled a").click(function() {
        return false;
    });
    $("div.pagination ul li.active a").click(function() {
        return false;
    });
    
    html = null;
    totalPages = null;
    currentPage = null;
    range = null;
    halfrange = null;
    lower = null;
    higher = null;
}

function sortTable(th) {
    $(th).siblings().each( function(index) {
        $(this).find('i').removeClass("icon-chevron-down");
        $(this).find('i').removeClass("icon-chevron-up");
    });
    
    var sort_type = "asc";
    
    if( $(th).find('i').hasClass("icon-chevron-up") || 
        $(th).find('i').hasClass("icon-chevron-down") ) {
        
        if( $(th).find('i').hasClass("icon-chevron-down") ) {
            $(th).find('i').removeClass("icon-chevron-down");
            $(th).find('i').addClass("icon-chevron-up");
            sort_type = "asc"
        } else {
            $(th).find('i').removeClass("icon-chevron-up");
            $(th).find('i').addClass("icon-chevron-down");
            sort_type = "desc"
        }
    } else {
        $(th).find('i').addClass("icon-chevron-up");
        sort_type = "asc"
    }
    
    var sort_by = "name";
    if($(th).text() == "Name") {
        sort_by = "name";
    } else if($(th).text() == "Current Status") {
        sort_by = "mstatus";
    } else if($(th).text() == "Lab") {
        sort_by = "lab";
    }

    $("div#sort_by").text(sort_by);
    $("div#sort_type").text(sort_type);    
    refreshTable();
    
}

function editComp(compid) {
    $("#msg_hldr").empty().removeClass("alert").removeClass("alert-error").removeClass("alert-success").addClass("hidden");
    $('#comp_form input#name').val($("tr#c_" + compid + " > td").eq(1).text());
    var status = $("tr#c_" + compid + " > td").eq(2).text();
    $("select#mstatus option").each(function(index) {
        if($(this).text() == status) {
            $(this).attr("selected", "selected");
        }
    });
    status = null;
    var lab = $("tr#c_" + compid + " > td").eq(3).text();
    $("select#lab option").each(function(index) {
        if($(this).text() == lab) {
            $(this).attr("selected", "selected");
        }
    });
    
    $('#comp_form input#compid').val(compid);
    $('#addedit_tab_link').tab('show');
    return false;
}

function resetCompForm() {
    $("#msg_hldr").empty().removeClass("alert").removeClass("alert-error").removeClass("alert-success").addClass("hidden");
    $("#comp_form input#name").val("");
    $("select#mstatus").val("");
    $("select#lab").val("");
    $("#comp_form input#compid").val("");
    return true;
}

function saveComp() {
    // Clear error css
    $('#comp_form input#name').parents("div.control-group").removeClass("error");
    $('#comp_form select#mstatus').parents("div.control-group").removeClass("error");
    $('#comp_form select#lab').parents("div.control-group").removeClass("error");
    
    // Error checking    
    if( $('#comp_form input#name').val() == "" ) {
        $('#comp_form input#name').parents("div.control-group").addClass("error");
    } else if( $('#comp_form select#mstatus').val() == "" ) {
        $('#comp_form select#mstatus').parents("div.control-group").addClass("error");
    } else if( $('#comp_form select#lab').val() == "" ) {
        $('#comp_form select#lab').parents("div.control-group").addClass("error");
    } else {

        var name = $('#comp_form input#name').val();
        var mstatus = $('#comp_form select#mstatus').val();
        var lab = $('#comp_form select#lab').val();
        var compid = $('#comp_form input#compid').val();
        
        if(compid == "") {
            var bNameExists = false;
            $('#lab_list_tbody tr td:nth-child(2)').each(function() {
                if(name == $(this).text()) {
                    bNameExists = true;
                    return;
                }
            });

            if(bNameExists) {
                $('#comp_form input#name').after("<span class=\"margin_left_5\" style=\"font-size: 0.9em; color: #B94A48\">Computer is present with this name. Please choose a different name.</span>");            
                $('#comp_form input#name').parents("div.control-group").addClass("error");
                bNameExists = null;
                return false;
            }
        }
                
        $.ajax({
            url: "admin_data.php?type=comp&op=save",
            data: {name: name, mstatus: mstatus, labid: lab, compid: compid},
            type: "POST",
            beforeSend: function() {
                if($("#msg_hldr").length == 0) {
                    $("#comp_form").prepend('<div id="msg_hldr" class="controls hidden"></div>');
                }
                $("#msg_hldr").empty()
                              .addClass("alert").addClass("alert-info")
                              .removeClass("hidden")
                              .html("<p>Please wait while submitting your request.</p><div class=\"progress progress-striped active\"><div class=\"bar\" style=\"width: 100%;\"></div></div>");
                 $('#comp_form input#name').parents("div.control-group").removeClass("error");
                 $('#comp_form input#name ~ span').empty();
            },
            success: function(data) {
                if(data != null) {                    
                    var obj = jQuery.parseJSON(data);
                    if(obj.msgtype == "success") {
                        if($("#msg_hldr").length == 0) {
                            $("#comp_form").prepend('<div id="msg_hldr" class="controls hidden"></div>');
                        }
                        $("#msg_hldr").empty()
                                        .removeClass("alert").removeClass("alert-error").removeClass("alert-success")
                                        .addClass("alert").addClass("alert-success")
                                        .removeClass("hidden")
                                        .html("<a class=\"close\" data-dismiss=\"alert\" href=\"#\">×</a><h4 class=\"alert-heading\">Success !!!</h4><p>" + obj.message + "</p>");
                        $('#comp_form input#compid').val(obj.compid);
                        refreshTable();
                    } else if(obj.msgtype == "error") {
                        if($("#msg_hldr").length == 0) {
                            $("#comp_form").prepend('<div id="msg_hldr" class="controls hidden"></div>');
                        }
                        $("#msg_hldr").empty()
                                        .removeClass("alert").removeClass("alert-error").removeClass("alert-success")
                                        .addClass("alert").addClass("alert-error")
                                        .removeClass("hidden")
                                        .html("<a class=\"close\" data-dismiss=\"alert\" href=\"#\">×</a><h4 class=\"alert-heading\">Error !!!</h4><p>" + obj.message + "</p>");
                    }
                    obj = null;
                }
            }
        });
    }
    return false;
}

function confirmDeleteComp(compid) {
    var name = $("tr#c_" + compid + " > td").eq(1).text();
    
    $("#confirmDelModel div.modal-header h3").html("Are you sure you want to delete computer <em>" + name + "</em> ?");
    $("#confirmDelModel div.modal-body").empty().addClass("alert-danger").append("<h4 class=\"alert-heading\">Warning !!!</h4><p>This action will remove computer from database permanently and it can not be rolled back.</p>");
    $("#confirmDelModel div.modal-footer").empty();
    $("#confirmDelModel div.modal-footer").append("<a href=\"#\" class=\"btn btn-danger\" onclick=\"deleteComp('" + compid + "');\">Delete</a><a href=\"#\" class=\"btn\" data-dismiss=\"modal\">Cancel</a>");
    $('#confirmDelModel').modal({
        keyboard: false,
        show: true
    });
}

function deleteComp(compid) {
    
    var name = $("tr#c_" + compid + " > td").eq(1).text();
    $.ajax({
        url: "admin_data.php?type=comp&op=del",
        data: {name: name, compid: compid},
        type: "POST",
        beforeSend: function() {
            $("#confirmDelModel div.modal-header h3").text("Please wait");
            $("#confirmDelModel div.modal-body").empty();
            $("#confirmDelModel div.modal-body").append("<div class=\"progress progress-danger progress-striped active\"><div class=\"bar\" style=\"width: 100%;\"></div>");
            $("#confirmDelModel div.modal-footer").empty();
        },
        success: function(data) {                
            if(data != null) {                    
                var obj = jQuery.parseJSON(data);
                if(obj.msgtype == "success") {
                    $("#confirmDelModel div.modal-header h3").text("Success !!!");
                    $("#confirmDelModel div.modal-body").empty().removeClass("alert-danger").append("<p>" + obj.message + "</p>");
                    refreshTable();
                } else if(obj.msgtype == "error") {
                    $("#confirmDelModel div.modal-header h3").text("Error !!!");
                    $("#confirmDelModel div.modal-body").empty().append("<p>" + obj.message + "</p>");
                }
                $("#confirmDelModel div.modal-footer").empty().html("<a href=\"#\" class=\"btn\" data-dismiss=\"modal\">Close</a>");
                obj = null;
            }
        }
    });    
}