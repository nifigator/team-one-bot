
// Closes the sidebar menu
$("#menu-close").click(function(e) {
	e.preventDefault();
	$("#sidebar-wrapper").toggleClass("active");
});

// Opens the sidebar menu
$("#menu-toggle").click(function(e) {
	e.preventDefault();
	$("#sidebar-wrapper").toggleClass("active");
});


var mx0;

$('#sidebar-wrapper').on("touchstart", function(e){
	var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
	mx0 = touch.pageX;
	
	//event.stopPropagation();
        //event.preventDefault();
});

$('#sidebar-wrapper').on("touchend mousedown", function(e){
	/*var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
	var mx = touch.pageX;
	
	if (mx - mx0 >= 100)
		$("#menu-close").click();
	
	mx0 = Math.min(mx0, mx);*/
	
	//event.stopPropagation();
        //event.preventDefault();
});