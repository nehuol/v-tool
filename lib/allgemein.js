$(document).ready(function(){

	// ausgewählte Spalten per Session merken
	$(".checker").click(function(event){
		var temp_td = $(this).attr("name");
		
		$.ajax({
			url: "session.php",
			type: "GET",
			data: "status=" + $(this).attr("checked") + "&checker=" + $(this).attr("name"),
			
			// ind. Auswahl anzeigen
			success: function (reqCode) {
				//alert(reqCode);
			}
		});
	});
});