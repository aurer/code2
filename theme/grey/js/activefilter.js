/*
    activeFilter Plugin
    V 2.0
*/
;(function($) {
     
    $.activeFilter = function(o){
         
        // plugin defaults        
        o = $.extend({
        	needle: '#in-keyword',
        	haystack: '.item h2',
        	hideEle: '.item',
        	fxHide: 'hide',
        	fxShow: 'show',
        	hideSpeed: 0,
        	showSpeed: 0,
        	onSearch: searchFunction = function(searchData){ return searchData },
        	spanClass: "hlt"
        }, o)
         
        // bind search function to search input
        $(o.needle).bind('keyup',function(){                   
            var needle = $(this).val().toLowerCase();
            var haystack  = $(o.haystack);
            filterRows(haystack, needle);
            o.onSearch(needle);
        })
         
  
        function filterRows(element,search){
        	element.each(function(){
             
             var regex = new RegExp(search, "ig");
             var string = $(this).text();
             var matched;
             
             var replacement = string.replace(regex, function(match, index){
	           	matched = index;
	           	return "<span class='"+o.spanClass+"'>"+match+"</span>";
	         });
             
             
             if(search.length > 0){
             	$(this).html(replacement);
             } else {
             	$(this).html(string);
             }
             
             if(matched == undefined){
             	$(this).parents(o.hideEle)[o.fxHide](o.hideSpeed, 'linear');
             } else {
             	$(this).parents(o.hideEle)[o.fxShow](o.showSpeed, 'linear');
             }
               
            });
        }
    }
})(jQuery);
