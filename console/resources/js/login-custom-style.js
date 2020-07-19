$(document).ready(function() {

    $('input').focus(function() {
        $(this).closest('.input-group').addClass('focused');
    });
    var style = window.getComputedStyle(document.getElementById('loginEmail'))
    console.log(style.backgroundColor);
    if (style && style.backgroundColor != "rgba(0, 0, 0, 0)") {
        $email = $("#loginEmail");
        $email.focus();
        $email.blur();
    }
    style = window.getComputedStyle(document.getElementById('loginPassword'))
    if (style && style.backgroundColor != "rgba(0, 0, 0, 0)") {
        $password = $("#loginPassword");
        $password.focus();
        $password.blur();
    }
    $('input').blur(function() {
        var inputValue = $(this).val();
        if (inputValue == "") {
            $(this).parents('.input-group').addClass('blanksec');
            $(this).removeClass('filled');
            $(this).parents('.input-group').removeClass('focused');
        } else {
            $(this).addClass('filled');
            $(this).parents('.input-group').removeClass('blanksec');
        }
    });
    // $('input').on('input', function() {
    //     console.log($(this).val());
       
    //     if ($(this).val() != '') {
    //         $(this).focus();
    //         $(this).blur();
    //     };
    //      $(this).unbind('input');
    // });
  
    $(".login-alert i").click(function() {
        $(".login-alert").hide();        
    });


});
