 function getSeriesReservations(seriesID) {

	$.ajax({
	  type: "POST",
	  url: "lab_reservation_get_series.php",
	  data: { seriesID: seriesID }
	}).done(function(msg) {
	  alert(msg);
	});    

}