$(document).ready(function() {
    $("#submitLogin").click(function(e) {
        e.preventDefault();
        var errors = checkAllInputEmpty(["email","password"]);
        validateInput("email","isValidEmail","Invalid email",errors);
        haveMinLength("password",6,null,errors);
        consoleMsg("DDDDD");
        if(isHavingError(errors)) {
            showErrors(errors,null);
            return;
        }
        consoleMsg("cccccc");
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