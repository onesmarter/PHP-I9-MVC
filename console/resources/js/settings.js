 
 var nonClickable = ['us_citizen_1','us_citizen_2','us_citizen_3','us_citizen_4','not_using_preparer_translator','preparer_translator_assisted','lista','listb','listc'];
 var inDirectChildren = [{'rule-1':['lawful_uscis_number','alien_expiration_number','alien_uscis_number','form_admission_number','passport_number','country_of_issuance']},
{'lists':['lista_document_title','lista_issuing_authority','lista_document_number','lista_expiration_date','listb_document_title','listb_issuing_authority','listb_document_number','listb_expiration_date','listc_document_title','listc_issuing_authority','listc_document_number','listc_expiration_date']},
{'rule-2':['signature_of_preparer','preparer_todays_date','preparer_last_name','preparer_first_name','preparer_address','preparer_city_or_town','preparer_state','preparer_zip_code']}];
$(document).ready(function() {

    $.each( inDirectChildren, function( index, value ) {
        
        $.each( value, function( key, array ) {
            $.each( array, function( index1, column ) {
                $('[data-clm-name="'+column+'"]').attr('data-parent-name',key+'-indirect');
            });
        });
    });

    $.each( data, function( key, value ) {
        var element = $('[data-clm-name="'+value.clmName+'"]');
        changeRequired(element,parseInt( value.isRequired));
    });

    changeRequired($('[data-clm-name="lists"]'),$('[data-clm-name="rule-4"]').attr( 'data-value'));
    onUsCitizenSectionClick('rule-1',$('[data-clm-name="rule-1"]').attr( 'data-value'));
    onPreparerSectionClick('rule-2',$('[data-clm-name="rule-2"]').attr( 'data-value'));
    onListsSectionClick('lists',$('[data-clm-name="lists"]').attr( 'data-value'));

    /**
     * 
     * @param {*} element 
     * @param {0 or 1} dataRequired  
     */
    function changeRequired(element,dataRequired) {
        // if($(element).attr( 'data-value')!=dataRequired) {
            $(element).attr( 'data-value', dataRequired );
            $(element).find('.fa-check.check-icon').attr('style','');
            $(element).find('.fa-times.times-icon').attr('style','');
            if(dataRequired==1) {
                $(element).find('.fa-check.check-icon').removeClass('d-none');
                $(element).find('.fa-times.times-icon').addClass('d-none');
            } else {
                $(element).find('.fa-times.times-icon').removeClass('d-none');
                $(element).find('.fa-check.check-icon').addClass('d-none');
            }
            
        // }
    }

    function onSectionClick(directChildren,fieldName,dataRequired) {
        var data = [];
        $.each(directChildren,function(key,value){
            changeRequired($('[data-clm-name="'+value+'"]'),dataRequired);
            data.push({fieldName:value,isRequired:dataRequired});
        });
        if(dataRequired==0) {
            $('[data-parent-name="'+fieldName+'-indirect"]').parents('.box-block-sub').addClass('d-none');
            $('p[data-parent-name="'+fieldName+'"]').addClass('d-none');
        } else {
            $('[data-parent-name="'+fieldName+'-indirect"]').parents('.box-block-sub').removeClass('d-none');
            $('p[data-parent-name="'+fieldName+'"]').removeClass('d-none');
        }
        return data;
    }

    function onUsCitizenSectionClick(fieldName,dataRequired) {
        var directChildren = ['us_citizen_1','us_citizen_2','us_citizen_3','us_citizen_4'];
        var data = onSectionClick(directChildren,fieldName,dataRequired);
        data.push({fieldName : fieldName,
            isRequired : dataRequired});
        return data;

    }

    function onPreparerSectionClick(fieldName,dataRequired) {
        var directChildren = ['not_using_preparer_translator','preparer_translator_assisted'];
        var data = onSectionClick(directChildren,fieldName,dataRequired);
        data.push({fieldName : fieldName,
            isRequired : dataRequired});
        return data;    
    }

    function onListsSectionClick(fieldName,dataRequired) {
        var directChildren = ['rule-4','rule-5','rule-6'];
        return onSectionClick(directChildren,fieldName,dataRequired);
    }

    $(".settings-change").on("click", function(e){
        e.preventDefault();
        var element = this;
        var fieldName = $(this).data( "clm-name");
        var dataRequired = $(this).attr( 'data-value');
        if(dataRequired==-1) {
            dataRequired = 0;
        }
        if(nonClickable.includes(fieldName)) {
            changeRequired(element,dataRequired);
            return;
        }
        $(this).attr( 'data-value', 1 ^ dataRequired );
        changeRequired(element,1 ^ dataRequired);
        var data = {
            fieldName : fieldName,
            isRequired : 1 ^ dataRequired
        };
        var url = 'api/updateFieldSetting';
        switch (fieldName) {
            case 'rule-1':
                data = {
                    fields:onUsCitizenSectionClick(fieldName,1 ^ dataRequired)};
                url = 'api/updateMultipleFieldsSetting';
                break;
            case 'rule-2':
                data = {
                    fields:onPreparerSectionClick(fieldName,1 ^ dataRequired)};
                url = 'api/updateMultipleFieldsSetting';
                break;
            case 'lists':
                data = {
                    fields:onListsSectionClick(fieldName,1 ^ dataRequired)};
                url = 'api/updateMultipleFieldsSetting';
                break;
        } 
        if(fieldName.startsWith("lista") || fieldName.startsWith("listb") || fieldName.startsWith("listc")) {
            data = 
            {
                fields:[
                    {
                        fieldName : fieldName,
                        isRequired : 1 ^ dataRequired
                    },
                    {
                        fieldName : fieldName+'1',
                        isRequired : 1 ^ dataRequired
                    },
                    {
                        fieldName : fieldName+'2',
                        isRequired : 1 ^ dataRequired
                    },
                ]
            };
            url = 'api/updateMultipleFieldsSetting';
        }
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(response) {
                try {
                    // if(response.data!=null) {
                    //     if(Array.isArray(response.data)) {
                    //         $.each(response.data,function(key,value){ //need to check children also
                    //             if(value.fieldName == fieldName) {
                    //                 changeRequired(element,value.isRequired);
                    //                 return false;
                    //             }
                    //         });
                    //     } else {
                    //         changeRequired(element,response.data.isRequired);
                    //     }
                        
                    // }
                    if (response.status == 1) {
                        showSuccess(response.msg); 
                    } else {
                        showSuccess(response.msg,true); 
                    }
                } catch (error) {
                    showSuccess("Something went wrong",true);
                    // changeRequired(element,dataRequired);
                }
                
            },
            error: function(er) {
                showSuccess("Something went wrong",true);
                consoleMsg(er);
                // changeRequired(element,dataRequired);
            }
            
        });
        
    });

   /*  $('[data-clm-name="rule-1"]').on("click", function(e) {

        var isRequired = $(this).attr('data-value');

        $(".rule-subset-1").each(function(index) {

            var dataName = $(this).data("clm-name");
            console.log(dataName);
            console.log(rule_set_value);

            if (rule_set_value == 0) {
                $(this).html('<i class="fal fa-check check-icon d-none" ></i><i class="fal fa-times times-icon"></i>');
            }
            else {
                $(this).html('<i class="fal fa-check check-icon"></i><i class="fal fa-times times-icon d-none"></i>');
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'events/index.php',
                data: {
                    dataName: dataName,
                    dataRequired: rule_set_value,
                    type: "UpdateField"
                },
                dataType: 'json',
                success: function(response) {},
                error: function(er) {
                    console.log(er);
                }
            });
        });
    });
    $(".rule-set-2").on("click", function(e) {

        var rule_set_value = $(this).attr('data-value');

        $(".rule-subset-2").each(function(index) {

            var dataName = $(this).data("clm-name");
            console.log(dataName);
            console.log(rule_set_value);

            if (rule_set_value == 0) {
                $(this).html('<i class="fal fa-check check-icon" style="display: none;"></i><i class="fal fa-times times-icon"></i>');
            }
            else {
                $(this).html('<i class="fal fa-check check-icon"></i><i class="fal fa-times times-icon" style="display: none;"></i>');
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'events/index.php',
                data: {
                    dataName: dataName,
                    dataRequired: rule_set_value,
                    type: "UpdateField"
                },
                dataType: 'json',
                success: function(response) {},
                error: function(er) {
                    console.log(er);
                }
            });
        });
    });
    $(".rule-set-3").on("click", function(e) {

        var rule_set_value = $(this).attr('data-value');

        $(".rule-subset-3").each(function(index) {

            var dataName = $(this).data("clm-name");
            console.log(dataName);
            console.log(rule_set_value);

            if (rule_set_value == 0) {
                $(this).html('<i class="fal fa-check check-icon" style="display: none;"></i><i class="fal fa-times times-icon"></i>');
            }
            else {
                $(this).html('<i class="fal fa-check check-icon"></i><i class="fal fa-times times-icon" style="display: none;"></i>');
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'events/index.php',
                data: {
                    dataName: dataName,
                    dataRequired: rule_set_value,
                    type: "UpdateField"
                },
                dataType: 'json',
                success: function(response) {},
                error: function(er) {
                    console.log(er);
                }
            });
        });
    });
    $(".rule-set-4").on("click", function(e) {

        var rule_set_value = $(this).attr('data-value');

        $(".rule-subset-4").each(function(index) {

            var dataName = $(this).data("clm-name");
            console.log(dataName);
            console.log(rule_set_value);

            if (rule_set_value == 0) {
                $(this).html('<i class="fal fa-check check-icon" style="display: none;"></i><i class="fal fa-times times-icon"></i>');
            }
            else {
                $(this).html('<i class="fal fa-check check-icon"></i><i class="fal fa-times times-icon" style="display: none;"></i>');
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'events/index.php',
                data: {
                    dataName: dataName,
                    dataRequired: rule_set_value,
                    type: "UpdateField"
                },
                dataType: 'json',
                success: function(response) {},
                error: function(er) {
                    console.log(er);
                }
            });
        });
    });
    $(".rule-set-5").on("click", function(e) {

        var rule_set_value = $(this).attr('data-value');

        $(".rule-subset-5").each(function(index) {

            var dataName = $(this).data("clm-name");
            console.log(dataName);
            console.log(rule_set_value);

            if (rule_set_value == 0) {
                $(this).html('<i class="fal fa-check check-icon" style="display: none;"></i><i class="fal fa-times times-icon"></i>');
            }
            else {
                $(this).html('<i class="fal fa-check check-icon"></i><i class="fal fa-times times-icon" style="display: none;"></i>');
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'events/index.php',
                data: {
                    dataName: dataName,
                    dataRequired: rule_set_value,
                    type: "UpdateField"
                },
                dataType: 'json',
                success: function(response) {},
                error: function(er) {
                    console.log(er);
                }
            });
        });
    });
    $(".rule-set-6").on("click", function(e) {

        var rule_set_value = $(this).attr('data-value');

        $(".rule-subset-6").each(function(index) {

            var dataName = $(this).data("clm-name");
            console.log(dataName);
            console.log(rule_set_value);

            if (rule_set_value == 0) {
                $(this).html('<i class="fal fa-check check-icon" style="display: none;"></i><i class="fal fa-times times-icon"></i>');
            }
            else {
                $(this).html('<i class="fal fa-check check-icon"></i><i class="fal fa-times times-icon" style="display: none;"></i>');
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'events/index.php',
                data: {
                    dataName: dataName,
                    dataRequired: rule_set_value,
                    type: "UpdateField"
                },
                dataType: 'json',
                success: function(response) {},
                error: function(er) {
                    console.log(er);
                }
            });
        });
    });
    $(".rule-set-7").on("click", function(e) {

        var rule_set_value = $(this).attr('data-value');

        $(".rule-subset-7").each(function(index) {

            var dataName = $(this).data("clm-name");
            console.log(dataName);
            console.log(rule_set_value);

            if (rule_set_value == 0) {
                $(this).html('<i class="fal fa-check check-icon" style="display: none;"></i><i class="fal fa-times times-icon"></i>');
            }
            else {
                $(this).html('<i class="fal fa-check check-icon"></i><i class="fal fa-times times-icon" style="display: none;"></i>');
            }

            $.ajax({
                type: 'POST',
                url: base_url + 'events/index.php',
                data: {
                    dataName: dataName,
                    dataRequired: rule_set_value,
                    type: "UpdateField"
                },
                dataType: 'json',
                success: function(response) {},
                error: function(er) {
                    console.log(er);
                }
            });
        });
    }); */

});