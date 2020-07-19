// CUSTOM SELECT

/**
** @author JINTO PAUL
** @date 19/nov/2019
** @param   dropdownSelector  =>  eg '.wrapper-dropdown'
** @param index               =>  starts from 0 -> Li index
**/
function clickOnDropdownLi(dropdownSelector,index) {
  if($(dropdownSelector+" li").length>index){
    $(dropdownSelector).addClass("active");
    $(dropdownSelector+' li:nth-child('+(index+1)+')').trigger("click");
  }
  
}

function DropDown(el) {
     this.dd = el;
     this.placeholder = this.dd.children('span');
     this.opts = this.dd.find('ul.dropdown > li');
     this.val = '';
     this.index = -1;
     this.initEvents();
  }
  DropDown.prototype = {
      removeItem: function(position) {
        var liList = [];
        this.opts.each(function( index, item ) {
          if(position!=index) {
            liList.push(item);
          }
        });
        this.opts = liList;
      },
      clickOnIndex: function(index,indexStartsFrom = 1) {
        //The click function will toggle the $('.wrapper-dropdown')'s active class.
        //$(this).addClass('active');
        //nth-child index is starts from 1   
        $(this.opts[index-indexStartsFrom] ).trigger("click");
      },
      initEvents: function(unbind = false) {
          var obj = this;
          var list = obj.dd;
          if(unbind) {
            list = obj.dd.unbind();
          }
          list.on('click', function(event) {
              var hasClass = $(this).hasClass('active');
              $('.wrapper-dropdown').removeClass('active');
              if (hasClass === false) {
                $(this).addClass('active');
              }
                  
              return false;
          });
          obj.opts.on('click', function() {
            
              var opt = $(this);
              obj.val = opt.html();
              obj.index = opt.index();
              obj.placeholder.html(obj.val);
          });
      },
      getValue: function() {
          return this.val;
      },
      getIndex: function() {
          return this.index;
      }
  }
function dropDownOfParent(parent,unbindPrevious = false){
    let dropdowns = [];
    if(parent==null || !parent)
        parent = $(document);
    // F O C U S
    parent.find('input[value!=""]').parents('.form-group').addClass('focused-file');
    parent.find('.custom-round label').click(function() {
        $(this).parent(".form-group").find('input[attr="in"]').focus();
        $(this).parents(".form-group").find('input[attr="in"]').focus();
    });
    parent.find('.custom-round input[attr="in"]').focus(function() {
        $(this).closest('.form-group').addClass('focused');
        $(this).parents('.form-group').removeClass('focused-file');
    });
    parent.find('.custom-round input[attr="in"]').blur(function() {
        var inputValue = $(this).val();
        if (inputValue == "") {
            $(this).parents('.form-group').removeClass('focused');
        } else {
            $(this).parents('.form-group').removeClass('focused');
            $(this).parents('.form-group').addClass('focused-file');
        }
    });
    parent.find('.wrapper-dropdown').each(function(){
        var dropDown = new DropDown($(this));
        if(unbindPrevious) {
          dropDown.initEvents(true);
        }
        dropdowns.push(dropDown);
    })
    // parent.find(".wrapper-dropdown li").click(function() {
        // $(this).parents('.wrapper-dropdown').addClass("active-wrap");
    // });
    $(document).click(function() {
        // all dropdowns
        $('.wrapper-dropdown').removeClass('active');
    });
    return dropdowns;
}