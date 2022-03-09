$(document).ready(function() {
	$('body').append('<input type="file" name="chat-media-share" id="chat-media-share" style="display: none;">');
	
	$(document).on('click', '.chat-media-share-btn', function () {
		localStorage.setItem("chat-image-uid", $(this).data("userid"));
	});
	
	$(document).on('change', 'input[name="chat-media-share"]', function () {
		postMediaShare();
	});

	$(document).on("click", 'label[for="video"], label[for="music"]', function() {
		// Clean the old selected files
		$('#media-share-list').empty();
		$('#media-share-list').removeAttr('class');
		$('#media-share-files, #my_file').val('');
		
		// Hide all the current plugin divs
		//$('#plugins-forms, #plugins-forms div').hide();
		//$('.message-form-input, .selected-files').hide();
		
		// Place the inputs
		$('#plugins-forms').append($('#media-share-location'));
		$('#media-share-location, #media-share-location div').finish();
		
		// Deselect any other event type if selected
		$('#values label').addClass('selected').siblings().removeClass('selected');
		
		// Add the selected state to the button
		$(this).addClass('selected');
		
		$('#form-value').attr("Placeholder", $(this).attr('title'));
		$('#media-share-location, #media-share-location div').show();
		$('.message-form-input, #plugins-forms').show('fast', function() {
			// Select the form input
			$('#form-value').focus();
		});
	});
	
	$(document).on("change", "#media-share-files", function() {
		// Empty the file lists
		$('#media-share-list').empty();
		
		$('#media-share-list').attr('class', 'media-share-list');
		
		// Read the current files
		var files = $('#media-share-files').prop('files');
		
		// Show the current files
        for (i = 0; i < files.length; i++){
			$('#form-value').val('media:')
			$('.message-form-input').hide();
			$('#media-share-list').append('<div class="media-share-element">'+files[i].name+' <span class="media-share-value">('+file_share_sizeFormat(files[i].size)+')</span></div>');
        }
		// If the user cancels the file selection
		if(i == 0) {
			$('#media-share-list').removeClass('media-share-list');
			$('#form-value').val('');
		}
	});
	
	// Remove the input's content when the user's uploading an image
	$(document).on('click', '#my_file', function() {
		document.getElementById("media-share-files").value = "";
	});
});
function file_share_sizeFormat(bytes,decimals) {
   if(bytes == 0) return '0 Byte';
   var k = 1024;
   var dm = decimals + 1 || 3;
   var sizes = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
   var i = Math.floor(Math.log(bytes) / Math.log(k));
   return (bytes / Math.pow(k, i)).toPrecision(dm) + ' ' + sizes[i];
}
function postMediaShare() {
	// Type 1: Camera stream capture
	var id = localStorage.getItem('chat-image-uid');
	chatInput(0, id);
	
	var formData = new FormData();
	
	// Build the form
	formData.append("id", id);
	formData.append("type", "plugin");
	formData.append("token_id", token_id);
	formData.append("media-share-files", $('input[name="chat-media-share"]')[0].files[0]);
	
	// Check whether when the input has changed has a file selected
	if(typeof($('input[name="chat-media-share"]')[0].files[0]) == "undefined") {
		chatInput(1, id);
		
		return false;
	}
	
	// Send the request
	var ajax = new XMLHttpRequest;
	ajax.open('POST', baseUrl+"/requests/post_chat.php", true);
	ajax.send(formData);
	
	ajax.onreadystatechange = function() {
		if(ajax.readyState == XMLHttpRequest.DONE) {
			// Check if in the mean time any message was sent
			checkChat(1, id);
			
			// Append the new chat to the div chat container
			$('#bc-friends-chat-'+id).append(ajax.responseText);
			$('#chat-container-'+id).append(ajax.responseText);
			
			chatInput(1, id);
			
			if($('#chat-container-'+id).length) {
				$('#chat-container-'+id).scrollTop($('.chat-container')[0].scrollHeight);
			}
			if($('#bc-friends-chat-'+id).length) {
				$('#bc-friends-chat-'+id).scrollTop($('.bc-friends-chat')[0].scrollHeight);
			}
			jQuery("div.timeago").timeago();
			$('.last-online[data-last-online="'+id+'"]').remove();
		}
	}
}