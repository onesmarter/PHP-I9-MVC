$(document).ready(function() {
    // $('.custom-datatable').DataTable();
    $('.custom-datatable').bind('page', function () {
        alert("dsfsdfsfsd");
    });
    function updateStatus(event,element,url,removeTrOnSuccess=false) {
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
                        if(removeTrOnSuccess) {
                            $(element).closest('tr').remove();
                        }
                        
                    } else {
                        showSuccess(response.msg,true); 
                        $(element).removeClass('d-none');
                    }
                } catch (error) {
                    showSuccess("Something went wrong",true);
                    $(element).removeClass('d-none');
                }
                
            },
            error: function(er) {
                showSuccess("Something went wrong",true);
                $(element).removeClass('d-none');
                consoleMsg(er);
            }
            
        });
    }
    $(".updateVerified, .update_unverified").on("click", function(e){
        $(this).addClass('d-none');
        updateStatus(e,this,'api/setUserDataAsVerified');       
    });

    $(".updateDeleted").on("click", function(e){
        $(this).addClass('d-none');
        updateStatus(e,this,'api/deleteUserData',true); 
    });

    $('[name="dashboard-table"]').on("click",".verifyView", function(e){
        var errorJson = $(this).data( "json" );
        $('#errorLising').html("");
        if(errorJson=="") {
            return;
        }
        // for (const [key, entry] of Object.entries(errorJson)) {
        //     $('#errorLising').append('<h3>'+key+'</h3><div class="col-sm-6"><p class="pdf-list-p">'+entry.originalName+'</p></div>');
        // }
        for (const [key, data] of Object.entries(errorJson)) {
            $('#errorLising').append('<div><h3>'+key+'</h3><br><br>');
            data.forEach(function(entry) {
        consoleMsg(entry);
            $('#errorLising').append('</p><div class="col-sm-6"><p class="pdf-list-p">'+entry.originalName+'</p></div>');

                
            });
            $('#errorLising').append('</div>');
        }
        // $('#errorLising').html(JSON.stringify(errorJson));
       // alert(errorJson);
    });
});