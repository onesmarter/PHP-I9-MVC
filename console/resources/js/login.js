$(document).ready(function() {

    $('input[name="password"]').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
             $("#submitLogin").click();
        }
    });
    $("#submitLogin").click(function(e) {
        e.preventDefault();
        var errors = checkAllInputEmpty(["email","password"]);
        validateInput("email","isValidEmail","Invalid email",errors);
        haveMinLength("password",6,null,errors);
        if(isHavingError(errors)) {
            showErrors(errors,null);
            return;
        }
        $.ajax({
            type: 'POST',
            url: 'api/login',
            data: {
                email: $("#email").val(),
                password: $("#password").val()
            },
            dataType: 'json',

            success: function(response) {
                try {
                    if (response.status == 1) {
                        window.location = response.data['url'];
                    } else {
                        showErrors({"email" : [response.msg]},null);
                    }
                } catch (error) {
                    consoleMsg(error);
                    showErrors({"email" : ["Something went wrong. Please try again."]},null);
                }
            },
            error: function(er) {
                showErrors({"email" : ["Something went wrong. Please try again."]},null);
                consoleMsg(er);
            }
        });
    });
});