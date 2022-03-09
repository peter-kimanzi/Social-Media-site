function doDislike(id) {
	// id = unique id of the message
	$('#dislike_btn'+id).html('<div class="privacy_loader"></div>');
	$('#doDislike'+id).removeAttr('onclick');
	$.ajax({
		type: "POST",
		url: baseUrl + "/plugins/dislike/dislike.php",
		data: "id="+id+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#message-action-dislike'+id).empty();
			$('#message-action-dislike'+id).html(html);
		}
	});
}