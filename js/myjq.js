//jquery stuff

function rerank_disable(how){
    if(how != "fade") {
        $("#rr_details").hide();
    } else {
        $("#rr_details").slideUp(1000);
    }
    $("#rr_fieldset").attr("style", "border:0px;")
    $("#rr_details input, #rr_details select").attr('disabled','disabled');
//$("rerank_cb").click();

}

function rerank_enable(how) {
     if(how != "fade") {
        $("#rr_details").show();
     } else {
        $("#rr_details").slideDown(1000);
     }
     $("#rr_fieldset").removeAttr("style");
    $("#rr_details input, #rr_details select").removeAttr('disabled');
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



$(document).ready(function(){

    //set init;
    if (!$('#rerank_cb').attr("checked")) {
        rerank_disable();
    }
    //all of them
    disable_all_rerank_details();




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


