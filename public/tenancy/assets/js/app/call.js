jQuery(document).ready(function(){
function resize(){	
		jQuery(".fi-main").css("height",jQuery(window).height())
		var navbarHeight = jQuery('.top-header').outerHeight();
		var height = jQuery('.top-header').height();
		var mainHeight = jQuery('.visitor-calls').height();
		var mainRowHeight = jQuery('#call-footer').height();
		
		
		var mainContentHeight = jQuery('.fi-main').height();
		var height = jQuery('.fi-main').height();
		jQuery('.fi-main').css('height',mainContentHeight);
		
		jQuery(".visitor-calls").css("height",jQuery(window).height() - navbarHeight - mainRowHeight - 130)
		 
}
resize();
window.onresize = function() {
    resize();
};
})