    /**
     * @author JINTO PAUL
     * @param {message} message 
     */
    function consoleMsg(message,tag = "TAG") {
      console.log(tag, message);
    }
    
    /** To show the error for enrtire page
    ** @author JINTO PAUL
    **@param message
    **@param type can be danger,success,info and warning
    **@param element
    **@returns shows messgae in element
    */
   function showCommonError(message=null, type=null, element=null){
    if(!message)
        message = 'Invalid request or network problem';
    if(!type)
        type = 'danger';
    if(!element){
        element = '#common-page-error';
    }
    $(element).removeClass('[className^="text-"]').html(message).addClass('text-'+type).removeClass('d-none').show().fadeOut(10000);
}
/** To remove error for enrtire page
** @author JINTO PAUL
**@param message
**@param element
**@returns hide element
*/
function hideCommonError(element=null){
    if(!element){
        let element = '#common-page-error';
    }
    $(element).removeClass('[className^="text-"]').addClass('d-none').html('');
}


/**
** @author JINTO PAUL
** @errors Format  :    {"key":["error1","error2"]}
**/

function showErrors(errors,parent = null,parentDiv='.group-block', isDropDown=false) {
  $('span.error').html('');
  $('.red-border').removeClass('red-border');
  if(errors && Object.keys(errors).length>0) {
    // $('.error').fadeIn();
     $.each(errors, function(key, value){
       
        if($('[name="'+ key +'"]').parents(parentDiv).length>0){
          $('[name="'+ key +'"]').parents(parentDiv).addClass('red-border');
        }
        var span;
        if(parent!=null) {
          if(isDropDown){
            $('input[name="'+ key +'"]').closest(parent).find('span.error').html(value[0]);
          }
          span = $('[name="'+ key +'"]').parents(parent).next('span.error');
        } else {
          if(isDropDown){
            $('input[name="'+ key +'"]').find('span.error').html(value[0]);
          }
          span = $('[name="'+ key +'"]').next('span.error');
        }
        
       // span.fadeIn();
       span.removeClass('d-none');
       span.html(value[0]);
      
       //span.fadeOut(5000);
     });
     // $('.error').fadeOut(3000);
     return true;
  }

  return false;

}

/**
** @author JINTO PAUL
** @date 12/nov/2019
**/
function showErrorsOnTextArea(errors,parent = '.before-err') {
   return showErrors(errors,parent,"textarea");
}

/**
** @author JINTO PAUL
** @date 12/nov/2019
**/
function addFocusedClassToInput() {
  $('input').each(function(){
      if($(this).val()!="") {
        $(this).closest(".form-group").addClass("focused-file");
      } else {
        $(this).closest(".form-group").removeClass("focused-file");
      }
  });
}

/**
** @author JINTO PAUL
** @date 12/nov/2019
**/
function showSuccess(message,isForWarning = false,divResouceFinder = '#showSuccess') {
  var alert = $(divResouceFinder);
  if(alert.length==0) {
    alert = $("#alertSuccess");
  }
  $(alert).fadeIn();
  if(isForWarning) {
    $(alert).removeClass("alert-success");
    $(alert).addClass("alert alert-warning");
  } else {
    $(alert).removeClass("alert-warning");
    $(alert).addClass("alert alert-success");
  }

  $(alert).html(message);
  $(alert).fadeOut(5000);
}


function toasterMessage(message, messageType){
  toastr.options = {
      "timeOut": "20000",
      "hideDuration": "15000",
      "positionClass": "toast-top-center",
      "showEasing": "linear",
      "hideEasing": "linear",
      "showMethod": "slideDown",
      "hideMethod": "slideUp"
  };
  switch (messageType) {
      case 'error':
          toastr.error(message);
          break;
      case 'success':
          toastr.success(message);
          break;
      case 'info':
          toastr.info(message);
          break;
      case 'warning':
          toastr.warning(message);
          break;
      default:
          toastr.warning(message);
          break;
  }
}

