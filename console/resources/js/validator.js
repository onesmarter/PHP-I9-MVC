let specialCharactersRegex = new RegExp("^[?=.*!@#$%^&*]+$");


/**
** @author JINTO PAUL
**/
function isHavingError(errorArray) {
	return errorArray && Object.keys(errorArray).length>0;
}

/**
** @author JINTO PAUL
**/
function isValidEmail(email) {
    return /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email);
}

/**
** @author JINTO PAUL
**/
function isEmpty(str) {
	return str == null || (typeof str == "undefined") || $.trim(str) == "";
}

/**
** @author JINTO PAUL
**/
function checkAllInputEmpty(inputNamesArray,errorMap={}) {
	$.each(inputNamesArray, function(index,key){

		if(isEmpty($("[name='"+key+"']").val())) {
			if(errorMap[key])
   				errorMap[key].push("Required field");
   			else
   				errorMap[key] = ["Required field"];
		}
 	});
 	return errorMap;
}

/**
** @author JINTO PAUL
**/
function checkAllTextAreaEmpty(textareaNamesArray,errorMap={}) {
	return checkAllInputEmpty(textareaNamesArray,errorMap);
}

// function isValidName(name) {
// 	var regex = /^[a-zA-Z]+ [a-zA-Z]+$/;
// 	return regex.test(name);
// }

// function isValidZipCode(elementValue){
//     var zipCodePattern = /^\d{5}$|^\d{5}-\d{4}$/;
//      return zipCodePattern.test(elementValue);
// }

/**
** @author JINTO PAUL
**/
function validateInput(inputName,functionName,error,errorMap,isForTrue=true) {
	if(isEmpty($("[name='"+inputName+"']").val())){
		return errorMap;
	}
	var result = window[functionName]($("[name='"+inputName+"']").val());
	if(isForTrue != result) {
		if(error && errorMap) {
			if(errorMap[inputName])
   				errorMap[inputName].push(error);
   			else
   				errorMap[inputName] = [error];
		}
	}
	return result;
}

/**
** @author JINTO PAUL
** @date 12/nov/2019
** @param inputName = the name of the input
** @param maxValue  ->   can be null
** @param minValue  ->   can be null
** @param maxDecimal  ->   can be null
**/
function isValidNumber(inputName,maxValue,minValue,maxDecimal=2,errorMap = {}) {
    var number = $("input[name='"+inputName+"']").val();
    if(!errorMap[inputName])
    	errorMap[inputName] = [];
    if(!$.isNumeric(number)) {
    	errorMap[inputName].push("Not a valid number");
    } else if(maxValue != null && typeof maxValue != undefined && number > maxValue) {
    	 errorMap[inputName].push("Maximum value is "+maxValue);
    }  else if(minValue != null && typeof minValue != undefined && number < minValue) {
    	 errorMap[inputName].push("Minimum value is "+minValue);
    } else if(maxDecimal != null && typeof maxDecimal != undefined && number.toString().includes(".")) {
    	var splitArray = number.toString().split(".");
    	if(splitArray.length > 2) {
    		 errorMap[inputName].push("Not a valid number");
    	} else if(splitArray[splitArray.length-1].length > maxDecimal){
    		 errorMap[inputName].push("Maximum "+maxDecimal+" decimal number allowed");
    	}
    }
    return errorMap;
}

/**
** @author JINTO PAUL
** @date 12/nov/2019
**/
function haveMinLength(inputName,minLength,error,errorMap = {}) {
	if(isEmpty($("input[name='"+inputName+"']").val())){
		return errorMap;
	}
	if($("input[name='"+inputName+"']").val().length < minLength) {
		if(errorMap[inputName])
			errorMap[inputName].push(error && error != null ? error : "Atleast "+minLength+" characters ");
		else
			errorMap[inputName] = [error && error != null ? error : "Atleast "+minLength+" characters "];
	}
	return errorMap;
}


function isHavingSpecialChars(inputName,error="Special characters not allowed",inputType="input",errorMap = {}) {
	let value = inputType == "input" ? $("[name='"+inputName+"']").val() : $("[name='"+inputName+"']").html();
	if( specialCharactersRegex.test(value)) {
		if(errorMap[inputName])
			errorMap[inputName].push(error);
		else
			errorMap[inputName] = [error ];
	}
	return errorMap;
}

/**
** @author JINTO PAUL
** @date 18/nov/2019
**/
function isInputDateBefore(name,secondName,key,errorMessage,errorMap = {}) {
	if(!moment($("[name='"+secondName+"']").val()).isAfter($("[name='"+name+"']").val())) {
		if(errorMap[key])
			errorMap[key].push(errorMessage);
		else
			errorMap[key] = [errorMessage ];
	}
	return errorMap;

}



/**
** @author sarath
**/
// checks that an input string is an integer, with an optional +/- sign character
function isIntegerValue (s) {

	var isInteger_re     = /^\s*(\+|-)?\d+\s*$/;
	return String(s).search (isInteger_re) != -1

}

function isUrlValid(userInput) {
    var res = userInput.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g);
    if(res == null)
        return false;
    else
        return true;
}


