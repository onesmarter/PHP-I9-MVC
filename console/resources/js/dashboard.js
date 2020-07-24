$(document).ready(function() {
    // $('.custom-datatable').DataTable();
    
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
                            $('[name="dashboard-table"]').DataTable().ajax.reload();
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
            }
            
        });
    }
    $('[name="dashboard-table"]').on("click",".updateVerified, .update_unverified", function(e){
        $(this).addClass('d-none');
        updateStatus(e,this,'api/setUserDataAsVerified',$('#removeOnVerify').val()==1);       
    });

    $('[name="dashboard-table"]').on("click",".updateDeleted", function(e){
        $(this).addClass('d-none');
        updateStatus(e,this,'api/deleteUserData',true); 
    });

    $('[name="dashboard-table"]').DataTable({
        fixedColumns: true,
        fixedHeader: true,
        scrollX: true,
        language: {
            search: "",
            searchPlaceholder: "Search..."
        },
        aoColumnDefs: [
            { "bSortable": false, "aTargets": [4, 5] }
        ],
        dom: 'Blfrtip',
        buttons: [{
                extend: 'pdf',
                footer: true,
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            },
            {
                extend: 'csv',
                footer: true,
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            },
            {
                extend: 'excel',
                footer: true,
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }
        ],
        'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'ajax': {
          'url':'api/'+$('#route').val()
      },
      'columns': [
         { data: 'id' },
         { data: 'pdfName' },
         { data: 'processStartTime' },
         { data: 'status' },
         { data: 'id' },
         { data: 'id' },
      ],
      "drawCallback": function( settings ) {
        var api = this.api();
        $.each( api.rows( {page:'current'} ).data(),function(entry){
            
            var tr = $('[name="dashboard-table"] tbody tr:nth-child('+(entry+1)+')');
            
            var index = $(tr).find('.sorting_1').index();
            var html = $(tr).find('td:nth-child(1)').html();
            html = html.split('td1').join("td");
            $(tr).html(html);

            if(index>-1) {
                $(tr).find('td:nth-child('+(index+1)+')').addClass('sorting_1');
            }
            // parseModelToHtml(tr,entry);
        });
       

        // Output the data for the visible rows to the browser's console
        //console.log( api.rows( {page:'current'} ).data() );
    },
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
            data.forEach(function(entry) {
                if ($('#section_' + entry.sectionId).length == 0) {
                    $('#errorLising').append('<div class="failed-pdf-section" id="section_' + entry.sectionId + '"><h3>Section ' + entry.sectionId + '</h3></div>');
    
                    $('#section_' + entry.sectionId).append('<div class="col-sm-12"><p class="pdf-list-p">' + entry.originalName + '</p></div>');
                } else {
                    $('#section_' + entry.sectionId).append('<div class="col-sm-12"><p class="pdf-list-p">' + entry.originalName + '</p></div>');
                }   
            });
        }
    });
});