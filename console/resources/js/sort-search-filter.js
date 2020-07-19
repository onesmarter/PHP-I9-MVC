

jQuery.expr[':'].contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};

function getValueFromTrForSort(tr,childIndex) {
	
    var content = $(tr).find('td:nth-child('+childIndex+')').find('[search-content]');

    
    if($(content).length==0){ //If td does not have children
        return $(tr).find('td:nth-child('+childIndex+')').html();
    }
    var val = $(content).val();
    if(val=='' || typeof val == typeof undefined)
        val = $(content).html();
    return typeof val == typeof undefined?'':val;
}

/** @author JINTO PAUL
** @date 28/nov/2019
**
** @param tableSelector 							-> must provide a valid selector
** @param searchInputSelector 						-> Provide a valid selector if you have search input. Or set as null
** @param addEmptyTrIfDataNotFound					-> true : will set a provided row or default row if the search or filter result count is 0
** @param filterButtonSelector						-> OPTIONAL. To set filter button event..  Should call "addFilterStrings" after creating this function's instance to set the filter options.
** @param filterDivSelector                        -> OPTIONAL. The div to set the filter li options
** @param sortButtonSelector						-> OPTIONAL. To set sort button event..  Should call "addSortStrings" after creating this function's instance to set the sort options.
** @param sortDivSelector							-> OPTIONAL. The div to set the sort li options
** @param blockSeperatorSelector					-> OPTIONAL. Set a valid selector if the table tr's separated by blocks
** @param emptyTrData 								-> OPTIONAL. Replace the default empty search / filter result data if addEmptyTrIfDataNotFound is true
**
**
** Options to set  columns are sortable on column name click 
   1. Set table's "sort-table" attribute to true to set click on all columns .  eg. <table name="table" sort-table="true"></table>
   2. To set selected columns as sortable :
		Set table's "sort-table" attribute to false OR remove "sort-table" attribute.  eg. <table name="table" sort-table="false"></table>
		Add sort class to th

** SEARCH AND SORT
		1. Should provide search-content attribute to all tds OR td's inner content 
			eg: <td class="assigned-company">
                    <p search-content>Created By ABCDEFGH</p>
                </td>

** FILTER
		1. Should provide search-filter attribute to all tds OR td's inner content 
			eg: <td class="assigned-company">
                    <p search-content search-filter> Created By ABCDEFGH</p>
                </td>                
**/
function SortSearchFilter({tableSelector='#tabledesign',searchInputSelector='#topbar_search_input',addEmptyTrIfDataNotFound=true,filterButtonSelector='.filter-option',filterDivSelector='.filter-option-block',sortButtonSelector='.sort-option',sortDivSelector='.sort-option-block',blockSeperatorSelector=null,emptyTrData=null}={}) {
	this.searchInputSelector = searchInputSelector;
	this.tableSelector = tableSelector;
	this.addEmptyTrIfDataNotFound = addEmptyTrIfDataNotFound == null ? false : addEmptyTrIfDataNotFound;
	this.blockSeperatorSelector = blockSeperatorSelector;
	this.filterButtonSelector = filterButtonSelector;
	this.filterDivSelector = filterDivSelector;
	this.sortDivSelector = sortDivSelector;
	this.sortButtonSelector = sortButtonSelector;
	this.currentSortingColumnIndex = -1;
	this.currentSortingTypeAsc = true;
	this.currentFilterIndex = -1;
	this.thCount = $(tableSelector+' thead').find('th').length;
	this.emptyTrData = this.addEmptyTrIfDataNotFound && emptyTrData==null ? '<tr class="locationSearchableList_searchEmptyTemp" style=""><td colspan="'+this.thCount+'" class="text-center">No matching records found</td></tr>':emptyTrData;
	this.searchInputValue = searchInputSelector==null ? '' : $(searchInputSelector).val();

	if(blockSeperatorSelector != null && blockSeperatorSelector!='') {//To identify all blocks 
		$(tableSelector+' '+blockSeperatorSelector).attr('search-block','true');
	}

	this.trList = $(tableSelector+" tbody tr");
	this.blockList = blockSeperatorSelector == null || blockSeperatorSelector=='' ? null : $(tableSelector+' '+blockSeperatorSelector);
	this.trListCount = $(this.trList).length;
	this.thClickIndexes = [];
	var instance = this;
	if(searchInputSelector!=null) {
		$(searchInputSelector).keyup(function(){
        	instance.searchInputValue = $(this).val();
     		instance.triggerFilter();
        	instance.triggerSearch(instance.searchInputValue);	
        	//instance.triggerSort();
    	});
	}
	this.setupThSortByClick();

	

}

SortSearchFilter.prototype = {
	sort: function() { 
		//this keyword will not accessible inside click function
		var sortSearchFilterInstance  = this;
		return function(a, b){
			
		    var val = getValueFromTrForSort(a,sortSearchFilterInstance.currentSortingColumnIndex);
		    if(val=='')
		        return 0;
		    var val2 = getValueFromTrForSort(b,sortSearchFilterInstance.currentSortingColumnIndex);
		    if(val2=='')
		        return 0;
		   	try {
		   		if(sortSearchFilterInstance.currentSortingTypeAsc)
		        	return val.localeCompare(val2);    
		    	return val2.localeCompare(val);
		   	} catch(e){}
		    return 0; 
		}
	},
	setupSort: function() {
		//this keyword will not accessible inside click function
		var sortSearchFilterInstance  = this;
		if(this.sortButtonSelector!=null && this.sortButtonSelector!='' && this.sortDivSelector!=null && this.sortDivSelector!='') {
			$(this.sortButtonSelector).unbind().click(function() {
		        $(sortSearchFilterInstance.sortDivSelector).toggleClass('active');
		    });
		    $(this.sortDivSelector+' li').unbind().click(function(){
		    	if(sortSearchFilterInstance.currentSortingColumnIndex == $(this).attr("data-index") && sortSearchFilterInstance.currentSortingTypeAsc && $(this).attr("data-sort-type")=="asc") {
		    		return;
		    	}
		    	$(sortSearchFilterInstance.sortDivSelector+' li').removeClass('active');
		    	$(this).addClass('active');
		        sortSearchFilterInstance.currentSortingColumnIndex = $(this).attr("data-index");
		        sortSearchFilterInstance.currentSortingTypeAsc = $(this).attr("data-sort-type")=="asc";
		        
		        if(sortSearchFilterInstance.thClickIndexes.includes(sortSearchFilterInstance.currentSortingColumnIndex)){
		 
		        	sortSearchFilterInstance.onSortSelected($(sortSearchFilterInstance.tableSelector+' thead th:nth-child('+sortSearchFilterInstance.currentSortingColumnIndex+')'));
		        }
		        sortSearchFilterInstance.triggerSort();
		    });
		}
	},

	setupFilter: function() {
		//this keyword will not accessible inside click function
		var sortSearchFilterInstance = this;
		if(this.filterButtonSelector!=null && this.filterButtonSelector!='' && this.filterDivSelector!=null && this.filterDivSelector!='') {
			$(this.filterButtonSelector).unbind().click(function() {
		        $(sortSearchFilterInstance.filterDivSelector).toggleClass('active');
		    });
		    $(this.filterDivSelector+' li').unbind().click(function(){
		    	$(sortSearchFilterInstance.filterDivSelector+' li').removeClass('active');
		    	$(this).addClass('active');
		    	sortSearchFilterInstance.currentFilterIndex = $(this).index();
		        var val = $(this).html();
		        sortSearchFilterInstance.triggerSearch(val.toLowerCase()=='all'?'':val,false);  
		        sortSearchFilterInstance.triggerSearch(sortSearchFilterInstance.searchInputValue);    
			    
		    });
		}
	},
	setupThSortByClick: function() { 
		var sortSearchFilterInstance = this;
		var sortAllColumns  = $(this.tableSelector).attr('sort-all') == 'true';
		$(this.tableSelector+' thead th').each(function(index,th){
			if(sortAllColumns || $(th).hasClass('sort')) {
				$(th).addClass('sort');
				//sortSearchFilterInstance.currentSortingColumnIndex will be a string. so we need string array
				sortSearchFilterInstance.thClickIndexes.push(''+(index+1));
				$(th).click(function(){
					//var sortClass = $(th).hasClass('asc')?'desc':'asc';
					//$(this.tableSelector+' thead th .sort').removeClass('asc desc');
					//$(th).addClass(sortClass);
					sortSearchFilterInstance.currentSortingColumnIndex = ''+(index+1);
					$(sortSearchFilterInstance.sortDivSelector+' li').removeClass('active');
		    		$(sortSearchFilterInstance.sortDivSelector+' li[data-index="'+(index+1)+'"]').addClass('active');
					
					sortSearchFilterInstance.currentSortingTypeAsc = $(th).hasClass('sort-desc');
					sortSearchFilterInstance.onSortSelected(th);
					sortSearchFilterInstance.triggerSort();
				});
			}
			
		});
	},
	onSortSelected: function(th) {
		$(this.tableSelector+' thead th').removeClass('sort-asc sort-desc');
		for (var i = this.thClickIndexes.length - 1; i >= 0; --i) {
			$(this.tableSelector+' thead th:nth-child('+this.thClickIndexes[i]+')').addClass('sort');
		}
		$(th).removeClass('sort');
		$(th).addClass(this.currentSortingTypeAsc?'sort-asc':'sort-desc');
	},
	addSortStrings: function( keyValueMap,addAscDesc=false) {
		var liList = '';
    
	    keyValueMap.forEach(function(item,index){
	        liList += '<li data-index="'+item[0]+'" data-sort-type="asc">'+item[1]+(addAscDesc?' Asc':'')+'</li>';
	        if(addAscDesc)
	            liList += '<li data-index="'+item[0]+'" data-sort-type="desc">'+item[1]+(addAscDesc?' Desc':'')+'</li>';
	    });
	    $(this.sortDivSelector+" ul").html(liList);
	    this.setupSort();
	},
	addFilterStrings: function(arrayOfStrings,setAddAll = true)  {
		var liList = '';
	    if(setAddAll)
	        liList += '<li>All</li>';
	    arrayOfStrings.forEach(function(item,index){
	        liList += '<li>'+item+'</li>';
	    });
	    $(this.filterDivSelector+" ul").html(liList);
	    this.setupFilter();
	},
	triggerSearch: function(searchString,isForSearch = true) {
		this.removeEmptyInfoRows();
		var searchAttr = isForSearch ? "[search-content]":"[search-filter]";
		if(this.currentFilterIndex<0) {
			isForSearch = false;
		}
		
		if(!isForSearch && searchString=='') {
            $(this.trList).show();
        } else {
	        $(this.trList).each(function(td){
	            if($(this).has(searchAttr).length>0) {
	            	if(isForSearch && $(this).is(':hidden')) {
	            		return;
	            	}
	                if(searchString=='' || $(this).find(searchAttr+":contains("+searchString+")").length>0) {
	                    $(this).show();
	                } else {
	                   $(this).hide();
	                }
	            }
	        });
	    }
	    this.addEmptyInfoRows();
	},
	triggerSort: function() {
		if(this.currentSortingColumnIndex>-1) {
			//sortSearchFilterInstance.trList   does not contain empty info rows
	       	var trList = $(this.tableSelector+" tbody tr");
	        if(this.blockList==null) {
	            $(trList).sort(this.sort()).appendTo(this.tableSelector);
	        } else {
	            var previousIndex= -1;
	            var previousBlock=null;
	            var sortSearchFilterInstance = this;//this is not accessible in each function
	            $(this.blockList).each(function(block){
	                if(previousIndex>-1 && previousIndex<$(this).index()) {
	                    $(trList).slice(previousIndex,$(this).index()).sort(sortSearchFilterInstance.sort()).insertAfter($(previousBlock));
	                }
	                previousBlock = $(this);
	                previousIndex = $(this).index();
	            });
	            if(previousBlock!=null) {
	                $(trList).slice(previousIndex,$(trList).length).sort(this.sort()).insertAfter($(previousBlock));
	            }
	            
	        }
		}
	},
	triggerFilter: function() {
		if(this.currentFilterIndex>-1) {
			$(this.filterDivSelector+' li:nth-child('+(this.currentFilterIndex+1)+')').trigger('click');
		}
	},
	removeEmptyInfoRows: function() {
		$(this.tableSelector+' tbody .locationSearchableList_searchEmptyTemp').remove();
	},
	addEmptyInfoRows: function() {
		if(!this.addEmptyTrIfDataNotFound){
			return;
		}
		if(this.trListCount==0 || (this.blockList==null && $(this.tableSelector+" tbody tr:visible").find("[search-content]").length==0)) {
            $(this.tableSelector+" tbody ").append(this.emptyTrData);
        } else if(this.blockList!=null) {
            var isVisible = false;
            var tr;
            for (var i = $(this.trList).length - 1; i >= 0; --i) {
                tr = this.trList[i];
                var attr = $(tr).attr('search-block');
                if(typeof attr !== typeof undefined && attr == 'true' ) {
                    if(!isVisible) {
                        $(tr).after(this.emptyTrData);
                    } 
                    isVisible = false;
                } else if(!isVisible && $(tr).is(':visible') && $(tr).has("[search-content]").length>0) {
                    isVisible = true; 
                }
            }
        }
	}

}