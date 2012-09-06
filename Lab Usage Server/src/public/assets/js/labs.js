$(document).ready(function() {
    $('a[data-toggle="tab"]').on('show', function (e) {
        if(e.target.id == "addedit_tab_link") {
            $("#refresh_data_button").addClass("hidden");
        } else {
            $("#refresh_data_button").removeClass("hidden");
        }
    });
});

function loadList() {
    $.ajax({
        url: "admin_data.php?type=lab&op=list",
        success: function(data) {
            $('#lab_list_tbody').html(data);
        }
    });
}

function editLab(labid) {
    $("#msg_hldr").empty().removeClass("alert").removeClass("alert-error").removeClass("alert-success").addClass("hidden");
    $('#lab_form input#name').val($("tr#rl_" + labid + " > td").eq(1).text());
    $('#lab_form input#title').val($("tr#rl_" + labid + " > td").eq(2).text());
    $('#lab_form textarea#desc').val($("tr#rl_" + labid + " > td").eq(3).text());
    $('#lab_form input#labid').val(labid);
    $('#addedit_tab_link').tab('show');
    return false;
}

function resetLabForm() {
    $("#msg_hldr").empty().removeClass("alert").removeClass("alert-error").removeClass("alert-success").addClass("hidden");
    $('#lab_form input#name').val('');
    $('#lab_form input#title').val('');
    $('#lab_form textarea#desc').val('');
    $('#lab_form input#labid').val('');
    return true;
}

function saveLab() {    
    // Error checking    
    if( $('#lab_form input#name').val() == "" ) {
        $('#lab_form input#name').parents("div.control-group").addClass("error");
    } else {
        var name = $('#lab_form input#name').val();
        var title = $('#lab_form input#title').val();
        var desc = $('#lab_form textarea#desc').val();
        var labid = $('#lab_form input#labid').val();
        
        var bNameExists = false;
        if(labid == '') {
            $('#lab_list_tbody tr td:nth-child(2)').each(function() {
                if(name == $(this).text()) {
                    bNameExists = true;
                    return;
                }
            });
        }
        
        if(bNameExists) {
            $('#lab_form input#name').parents("div.control-group").removeClass("error");
            $('#lab_form input#name ~ span').empty();            
            $('#lab_form input#name').after("<span class=\"margin_left_5\" style=\"font-size: 0.9em; color: #B94A48\">Lab is present with this name. Please choose a different name.</span>");            
            $('#lab_form input#name').parents("div.control-group").addClass("error");
            bNameExists = null;
            return false;
        }
        
        $.ajax({
            url: "admin_data.php?type=lab&op=save",
            data: {name: name, title: title, desc: desc, labid: labid},
            type: "POST",
            beforeSend: function() {
                if($("#msg_hldr").length == 0) {
                    $("#lab_form").prepend('<div id="msg_hldr" class="controls hidden"></div>');
                }
                $("#msg_hldr").empty()
                              .addClass("alert").addClass("alert-info")
                              .removeClass("hidden")
                              .html("<p>Please wait while submitting your request.</p><div class=\"progress progress-striped active\"><div class=\"bar\" style=\"width: 100%;\"></div></div>");
                 $('#lab_form input#name').parents("div.control-group").removeClass("error");
                 $('#lab_form input#name ~ span').empty();
            },
            success: function(data) { 
                if(data != null) {                    
                    var obj = jQuery.parseJSON(data);
                    if(obj.msgtype == "success") {
                        if($("#msg_hldr").length == 0) {
                            $("#lab_form").prepend('<div id="msg_hldr" class="controls hidden"></div>');
                        }
                        $("#msg_hldr").empty()
                                        .removeClass("alert").removeClass("alert-error").removeClass("alert-success").removeClass("hidden")
                                        .addClass("alert").addClass("alert-success")
                                        .html("<a class=\"close\" data-dismiss=\"alert\" href=\"#\">×</a><h4 class=\"alert-heading\">Success !!!</h4><p>" + obj.message + "</p>");
                        $('#lab_form input#labid').val(obj.labid);
                        loadList();
                    } else if(obj.msgtype == "error") {
                        if($("#msg_hldr").length == 0) {
                            $("#lab_form").prepend('<div id="msg_hldr" class="controls hidden"></div>');
                        }
                        $("#msg_hldr").empty()
                                        .removeClass("alert").removeClass("alert-error").removeClass("alert-success").removeClass("hidden")
                                        .addClass("alert").addClass("alert-error")                                        
                                        .html("<a class=\"close\" data-dismiss=\"alert\" href=\"#\">×</a><h4 class=\"alert-heading\">Error !!!</h4><p>" + obj.message + "</p>");
                    }
                    obj = null;
                }
            }
        });
    }
    return false;
}

function confirmDeleteLab(labid) {
    var name = $("tr#rl_" + labid + " > td").eq(1).text();
    
    $("#confirmDelModel div.modal-header h3").html("Are you sure you want to delete lab <em>" + name + "</em> ?");
    $("#confirmDelModel div.modal-body").empty().addClass("alert-danger").append("<h4 class=\"alert-heading\">Warning !!!</h4><p>This action will remove lab from database permanently and it can not be rolled back.</p>");
    $("#confirmDelModel div.modal-footer").empty();
    $("#confirmDelModel div.modal-footer").append("<a href=\"#\" class=\"btn btn-danger\" onclick=\"deleteLab('" + labid + "');\">Delete</a><a href=\"#\" class=\"btn\" data-dismiss=\"modal\">Cancel</a>");
    $('#confirmDelModel').modal({
        keyboard: false,
        show: true
    });
}

function deleteLab(labid) {
    
    var name = $("tr#rl_" + labid + " > td").eq(1).text();
    $.ajax({
        url: "admin_data.php?type=lab&op=del",
        data: {name: name, labid: labid},
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
                    loadList();
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