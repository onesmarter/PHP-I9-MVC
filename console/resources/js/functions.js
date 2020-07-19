

function inputCharChecker(inputSelector, characterString,preventEvent=false,uniqueChar = null,callFunction=null) {
    if(characterString==null || inputSelector==null || inputSelector=="" ||  characterString.length==0)
        return;
    let charCodes = [37,38,39,40,13,8];
    $(inputSelector).keydown(function(event){

        var charCode = (event.which) ? event.which : event.keyCode;

        if( charCodes.includes(charCode) || characterString.includes(event.key) || (uniqueChar==event.key && !$(this).val().includes(uniqueChar))) {
            if(callFunction!=null && !charCodes.includes(charCode)) {
                return callFunction(event,$(this));
            }
            return true;
        }
        if(preventEvent) {
            event.preventDefault();
        }

        return false;
    });
    $(inputSelector).keyup(function(){
        var val = $(this).val();
        if(val.length>0) {
            for(var i=0;i<val.length;++i) {
                if(!characterString.includes(val[i])) {
                    val = val.replace(val[i],'');
                    --i;
                }
            }
            if(val != $(this).val()) {
                $(this).val(val);
            }
        }
    });

}

function inputIntNumberChecker(inputSelector,maxValue=null,preventEvent=false, characterString="0123456789") {
    inputCharChecker(inputSelector,characterString,preventEvent,null,maxValue==null?null:function(event,input){
        var val=$(input).val();
        if(val.length>0) {
            try {
                var intValue = parseInt(val.substring(0,event.originalEvent.target.selectionEnd)+event.key+val.substring(event.originalEvent.target.selectionEnd));

                if(intValue>maxValue) {
                    return false;
                }
            } catch(e){
                return false;
            }
        }
        return true;
    });
}

function inputDoubleNumberChecker(inputSelector,maxValue=null,maxDecimalCount = 2,preventEvent=false, characterString="0123456789.") {
    inputCharChecker(inputSelector,characterString,preventEvent,".",function(event,input){
        var val = $(input).val();
        if(maxValue!=null && val.length>0) {
            try {
                if(event.key=='.' && parseFloat(val.substring(0,event.originalEvent.target.selectionEnd)+val.substring(event.originalEvent.target.selectionEnd))>=maxValue) {
                    return false;
                }
                var floatValue = parseFloat(val.substring(0,event.originalEvent.target.selectionEnd)+event.key+val.substring(event.originalEvent.target.selectionEnd));

                if(floatValue>maxValue) {
                    return false;
                }
            } catch(e){
                return false;
            }
        }
        if(event.key == '.' && event.originalEvent.target.selectionEnd+maxDecimalCount<val.length) {
            return false;
        }
        var index = val.indexOf(".");

        if(index<0 || index>event.originalEvent.target.selectionEnd) {
            return true;
        }
        if(index+maxDecimalCount+(event.originalEvent.target.selectionEnd>index?0:1)<val.length) {
            return false;
        }
        return true;
    });
}


