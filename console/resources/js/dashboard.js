$(document).ready(function() {

    function updateStatus(event,element,url) {
        event.preventDefault();
        var userId = $(element).attr('data-id');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                userId : userId
            },
            dataType: 'json',
            success: function(response) {
                try {
                    if (response.status == 1) {
                        showSuccess(response.msg); 
                        $(element).closest('tr').remove();
                    } else {
                        showSuccess(response.msg,true); 
                    }
                } catch (error) {
                    showSuccess("Something went wrong",true);
                }
                
            },
            error: function(er) {
                showSuccess("Something went wrong",true);
                consoleMsg(er);
            }
            
        });
    }
    $(".updateVerified, .update_unverified").on("click", function(e){
        updateStatus(e,this,'api/setUserDataAsVerified');       
    });

    $(".updateDeleted").on("click", function(e){
        updateStatus(e,this,'api/deleteUserData'); 
    });

    $(".verifyView").on("click", function(e){
        var errorJson = $(this).data( "json" );
        $('#errorLising').html("");
        if(errorJson=="") {
            return;
        }
        errorJson.forEach(function(entry) {
        
        $('#errorLising').append('<div class="col-sm-6"><p class="pdf-list-p">'+entry.originalName+'</p></div>');

            
        });
       // alert(errorJson);
    });
});