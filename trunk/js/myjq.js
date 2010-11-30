//jquery stuff

function rerank_disable(how){
    if(how != "fade") {
        $("#rr_details").hide();
        $("#rr_fieldset").attr("class", "collapsed")
        $("#rr_details input, #rr_details select").attr('disabled','disabled');
        
    } else {
        $("#rr_details").slideUp(1000, function(){
            $("#rr_fieldset").attr("class", "collapsed") //this means do it after animation!
            $("#rr_details input, #rr_details select").attr('disabled','disabled');
        });
    }
    
//$("rerank_cb").click();

}

function rerank_enable(how) {
    if(how != "fade") {
        $("#rr_details").show();
    } else {
        $("#rr_details").slideDown(1000);
    }
    $("#rr_fieldset").attr("class", "expanded")
    $("#rr_details input, #rr_details select").removeAttr('disabled');
    disable_all_rerank_details(); //fixes unchanged value from the last time!
//$("rerank_cb").click();

}

function disable_all_rerank_details(how){

    
    for(i=0;i<rerankTypes.length;i++){
        var controller_name ="#rerank_" + rerankTypes[i];
        var detail_name = controller_name  +  "_detail";

        //alert(detail_name);

        
        if ($(controller_name).attr("checked")) {
            
            $(detail_name + ' input, ' + detail_name + ' select').removeAttr('disabled'); //if clicking fast, disable could be last, this fixes it

            //show it
            if(how != "fade") {
                $(detail_name).show();
            } else {
                $(detail_name).fadeIn(1000);
            }

        //alert("disRem:" + detail_name);
           
        } else {
            //hide it
            $(detail_name + ' input, ' + detail_name + ' select').attr('disabled','disabled');

            if(how != "fade") {
                $(detail_name).hide();
            } else {
                $(detail_name).fadeOut(1000);
            }
            
            
        }

    }

}

function my_date_input_extend() {
    
    $.extend(DateInput.DEFAULT_OPTS, {
        stringToDate: function(string) {
            var matches;
            // for czech-alike date: 2.12.2011
            if (matches = string.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4,4})$/)) {
                return new Date(matches[3], matches[2] - 1, matches[1]);
            } else {
                return new Date; //today on error
            };
        },

        dateToString: function(date) {
            var month = (date.getMonth() + 1).toString();
            var dom = date.getDate().toString();
            //if (month.length == 1) month = "0" + month;
            //if (dom.length == 1) dom = "0" + dom;
            return  dom + "." + month + "." + date.getFullYear();
        }
    });

}


function disable_date(which, how) {
    if (which != "from") which = "to";
    var block_id = "input_date_" + which + "_detail"; //as in input_date_from_detail;

    $("#" + block_id + " input").attr('disabled','disabled');
    if(how != "fade") {
        $("#" + block_id).hide();
    } else {
        $("#" + block_id).fadeOut(1000);
    }
}

function enable_date(which, how) {
    if (which != "from") which = "to";
    var block_id = "input_date_" + which + "_detail"; //as in input_date_from_detail;

    $("#" + block_id + " input").removeAttr('disabled');
    if(how != "fade") {
        $("#" + block_id).show();
    } else {
        $("#" + block_id).fadeIn(1000);
    }
}


function hide_show_dates(chosentype, how) {
    // input_date_types form jsHelper php (generated)
    //alert(chosentype);
    switch(chosentype)
    {
        //no dates at al
        default:
        case input_date_types[0]:
            disable_date("from", how);
            disable_date("to", how);
            break;

        case input_date_types[1]: //from_only
            enable_date("from", how);
            disable_date("to", how);
            break;

        case input_date_types[2]: //from_only
            disable_date("from", how);
            enable_date("to", how);
            break;

        case input_date_types[3]: //from_only
            enable_date("from", how);
            enable_date("to", how);
            break;

    }
}

function infoFade(elem){
    $("#"+elem).hide();
}

$(document).ready(function(){

    //set init;
    if (!$('#rerank_cb').attr("checked")) {
        rerank_disable();
    }
    //details hide
    disable_all_rerank_details();

    //setup date input;
    my_date_input_extend();
    $(".date_input").date_input();

    //setup dates init
    var init_date_type =  $('#input_date_type').val();
    hide_show_dates(init_date_type); //fast




    //----------functions----------------------

    $('#input_date_type').change(function(){
        hide_show_dates( $('#input_date_type').val(), "fade");
    //alert("hi");

    });
  
    $('#rerank_cb').change(function () {
        if ($('#rerank_cb').attr("checked")) {
            rerank_enable("fade");
            return;
        }
        rerank_disable("fade");
    //Here do the stuff you want to do when 'unchecked'
    });


    $('.rerankType').change(function () {
       
        //disables and hides everything but actually selected!
        disable_all_rerank_details("fade");

    });

    //jednoduche skryvani
    /*       $('label.popis').click(function(){
        	//infoFade($this.attr("for"));
               var id = $(this).attr("for");
               $('#' + id).toggle();
        });
     */

    //jednoduche skryvani
    $('label.popis').toggle(
        function(){
            //infoFade($this.attr("for"));
            var id = $(this).attr("for");
            $('#' + id).fadeOut(1000)
        },
        
        function(){
            //infoFade($this.attr("for"));
            var id = $(this).attr("for");
            $('#' + id).fadeIn(1000);
        }
        );


// -------------------------------------done--------------------
});


