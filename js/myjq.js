//jquery stuff

function rerank_disable(how){
    if(how != "fade") {
        $("#rr_details").hide();
    } else {
        $("#rr_details").slideUp(1000);
    }
    $("#rr_fieldset").attr("class", "collapsed")
    $("#rr_details input, #rr_details select").attr('disabled','disabled');
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
            //show it
            if(how != "fade") {
                $(detail_name).show();
            } else {
                $(detail_name).fadeIn(1000);
                
            }
            $(detail_name + ' input, ' + detail_name + ' select').removeAttr('disabled');
           
        } else {
            //hide it
            if(how != "fade") {
                $(detail_name).hide();
            } else {
                $(detail_name).fadeOut(1000);
            }
            $(detail_name + ' input, ' + detail_name + ' select').attr('disabled','disabled');
            
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
            if (month.length == 1) month = "0" + month;
            if (dom.length == 1) dom = "0" + dom;
            return  dom + "." + month + "." + date.getFullYear();
        }
    });

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





    //----------functions----------------------
  
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


// -------------------------------------done--------------------
});

