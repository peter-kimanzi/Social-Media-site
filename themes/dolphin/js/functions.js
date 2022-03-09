function autosize() {
	// auto adjust the height of
	$('body').on('keyup', 'textarea', function() {
		$(this).height(0);
		$(this).height((this.scrollHeight-10));
	});
}
function showButton(id) {
	$('#comment_box_'+id).append($('#comments-controls'));
	$('#comments-controls'+id).show();
}
function loadComments(id, cid, start) {
	$('#more_comments_'+id).html('<div class="preloader preloader-center"></div>');
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_comments.php",
		data: "id="+id+"&start="+start+"&cid="+cid+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			// Remove the loader animation
			$('#more_comments_'+id).remove();
			
			// Append the new comment to the div id
			$('#comments-list'+id).prepend(html);
		
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
		}
	});
}
function loadFeed(start, filter) {
	$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	if(filter == '') {
		q = '';
	} else {
		q = '&filter='+filter;
	}
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_feed.php",
		data: "start="+start+q+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_messages').remove();
			
			// Append the new comment to the div id
			$('#messages').append(html);
		
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function loadPage(start, page) {
	$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');

	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_page.php",
		data: "start="+start+"&page="+page+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_messages').remove();
			
			// Append the new comment to the div id
			$('#messages').append(html);
		
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function loadGroup(start, group) {
	$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');

	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_group.php",
		data: "start="+start+"&group="+group+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_messages').remove();
			
			// Append the new comment to the div id
			$('#messages').append(html);
		
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function loadPeople(start, value, filter, age) {
	$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	if(filter == '') {
		filter = '';
	} else {
		filter = '&filter='+filter;
	}
	if(age == '') {
		age = ''
	} else {
		age = '&age='+age;
	}
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_people.php",
		data: "start="+start+'&q='+encodeURIComponent(value)+filter+age+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_messages').remove();
			
			// Append the new comment to the div id
			$('#messages').append(html);
		
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function loadProfile(start, filter, profile) {
	$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	if(filter == '') {
		q = '';
	} else {
		q = '&filter='+filter;
	}
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_profile.php",
		data: "profile="+profile+"&start="+start+q+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_messages').remove();
			
			// Append the new comment to the div id
			$('#messages').append(html);
		
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function loadHashtags(start, value, filter) {
	$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	if(filter == null) {
		var filter = '';
	}
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_tags.php",
		data: "start="+start+'&q='+value+'&filter='+filter+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_messages').remove();
			
			// Append the new comment to the div id
			$('#messages').append(html);
		
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function loadSubs(start, type, profile) {
	$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_friends.php",
		data: "id="+profile+"&start="+start+"&type="+type+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_messages').remove();
			
			// Append the new comment to the div id
			$('#messages').append(html);
		}
	});
}
function loadBlocked(start) {
	$('#more_users').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_blocked.php",
		data: "&start="+start+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_users').remove();
			
			// Append the new comment to the div id
			$('#users').append(html);
		}
	});
}
function postComment(id) {
	var comment = $('#comment-form'+id).val();
	
	$('#post_comment_'+id).html('<div class="preloader preloader-center"></div>');
	
	// Remove the post button
	$('#comments-controls'+id).hide();
	
	// Show the loading animation
	$('#action-loader'+id).html('<div class="privacy_loader"></div>');
	
	var formData = new FormData();
	
	// Build the form
	formData.append("id", id);
	formData.append("comment", comment);
	formData.append("token_id", token_id);
	
	if(typeof($('#commentimage'+id)[0].files[0]) !== "undefined") {

		formData.append("type", "picture");
		formData.append("value", $('#commentimage'+id)[0].files[0]);
	}
	
	// Send the request
	var ajax = new XMLHttpRequest;
	ajax.open('POST', baseUrl+"/requests/post_comment.php", true);
	ajax.send(formData);
	
	ajax.onreadystatechange = function() {
		if(ajax.readyState == XMLHttpRequest.DONE) {
			try {
				var result = jQuery.parseJSON(ajax.responseText);
				
				// Append the new comment to the div id
				$('#comments-list'+id).append(result.content);
				$('#message-action'+id).html(result.actions);
				
				// Fade In the style="display: none" class
				$('.message-reply-container').fadeIn(500);
				
				// Reload the timeago plugin
				jQuery(".timeago").timeago('updateFromDOM');
				
				// Empty the text area
				$('#comment-form'+id).val('');
			} catch(e) {
				
			}
			
			// Remove the loader animation
			$('#post_comment_'+id).html('');
			$('#action-loader'+id).html('');
			$('#mentions-container').remove();
		}
	}
	
	// Reset the form's height
	$('#comment-form'+id).height(0);
	
	// Reset the image input
	$('#commentimage'+id).val('');
	
	// Clear the queue preview
	$('#queued-comment-files'+id).html('');
}
function share(id) {
	$('#share').fadeIn();
	$('.modal-background').fadeIn();
	
	$('#share-close').hide();
	$('#share-btn').show();
	$('#share-cancel').show();
	
	$('#share-btn').attr('onclick', 'doShare('+id+', 1)');
}
function doShare(id, type) {
	if(type) {
		$('#share-btn').hide();
		$('#share-cancel').hide();
		$('#share-close').show();
		$('#share-desc').hide();
		$('#share-result').show();
		$('#share-result').html('<div class="preloader preloader-center"></div>');
		$('#action-loader'+id).html('<div class="privacy_loader"></div>');
		
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/share.php",
			data: "id="+id+"&token_id="+token_id, 
			cache: false,
			success: function(html) {
				var result = jQuery.parseJSON(html);
				$('#share-result').html(result.content);
				$('#message-action'+id).html(result.actions);
			}
		});
	} else {
		hideModal();
	}
}
function deleteModal(id, type, parent) {
	if(type == 999) {
		hideModal();
	} else {
		$('#delete0, #delete1, #delete2').hide();
		$('#delete'+type).show();
		
		$('#delete').fadeIn();
		$('.modal-background').fadeIn();
		
		$('#delete-btn').show();
		$('#delete-cancel').show();
		
		if(type == 0) {
			$('#delete-btn').attr('onclick', 'delete_the('+id+', '+type+', '+parent+')');
		} else {
			$('#delete-btn').attr('onclick', 'delete_the('+id+', '+type+')');
		}
	}
}
function cameraModal(type) {
	// Type 0: Display the camera modal along with the video stream
	// Type 1: Close the modal
	// Type 2: Take a screenshot of the camera stream
	if(type == 1) {
		hideModal();
	} else if(type == 2) {
		// Elements for taking the snapshot
		var canvas = document.getElementById('camera-canvas');
		cameraCapture = canvas.getContext('2d');
		
		cameraCapture.drawImage(videoStream, 0, 0, 380, 285);
		$('#camera-video, #camera-btn').hide();
		$('#camera-canvas').fadeIn(); // Adds in the snap photo effect (opacity from 1 to 0)
		$('#camera-send-btn').show();
	} else {
		$('#camera').fadeIn();
		$('.modal-background').fadeIn();
		$('#camera-btn').show();
		$('#camera-cancel').show();
		
		// Get and set the video element
		videoStream = document.getElementById('camera-video')

		// Require access to the camera
		if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
			// The camera options
			var options = {
				video: true,
				audio: false
			}
			
			navigator.mediaDevices.getUserMedia(options).then(function(stream) {
				cameraStream = stream;
				
				// Set the content to the camera stream
				try {
					videoStream.srcObject = stream;
				} catch (error) {
					videoStream.src = window.URL.createObjectURL(stream);
				}
				
				// Start playing the camera stream
				videoStream.play();
			}).catch(function(err) {
				// Display the error
				var canvas = document.getElementById('camera-canvas');
				cameraCapture = canvas.getContext('2d');
				cameraCapture.font = "16px sans-serif";
				cameraCapture.fillStyle = "#AAA";
				cameraCapture.fillText(err.name + ": " + err.message, 10, 20);
				$('#camera-video, #camera-btn, #camera-send-btn').hide();
				$('#camera-canvas').show();
			});
		} else {
			console.log('This browser does not support WebRTC');
		}
	}
}
function likesModal(id, type, close) {
	// Type 0: Message, Type 1: Comment
	if(close) {
		hideModal();
	} else {
		$('#likes').fadeIn();
		$('.modal-background').fadeIn();
		$('#likes-result').html('<div class="modal-listing-load-more"><div class="preloader preloader-center"></div></div>');
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/load_likes.php",
			data: "id="+id+"&extra="+type+"&token_id="+token_id, 
			cache: false,
			success: function(html) {
				$('#likes-result').html(html);
			}
		});
	}
}
function sharesModal(id, close) {
	// Type 0: Message, Type 1: Comment
	if(close) {
		hideModal();
	} else {
		$('#shares').fadeIn();
		$('.modal-background').fadeIn();
		$('#shares-result').html('<div class="modal-listing-load-more"><div class="preloader preloader-center"></div></div>');
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/load_shares.php",
			data: "id="+id+"&token_id="+token_id, 
			cache: false,
			success: function(html) {
				$('#shares-result').html(html);
			}
		});
	}
}
function hideModal() {
	$('#share').fadeOut(400, function() {
		$('#share-desc').show();
		$('#share-result').hide();
	});
	$('#delete, #likes, #camera, #shares, .modal-background').fadeOut();
	
	// Stop the camera stream
	if(typeof cameraStream != 'undefined') {
		cameraStream.getTracks()[0].stop();
	}
	$('#camera-canvas, #camera-send-btn').hide();
	$('#camera-video, #camera-btn').show();
}
function loadLikes(start, id, type, extra) {
	
	var query = "start="+start+"&id="+id+"&extra="+extra;
	$('#more-likes').html('<div class="modal-listing-load-more"><div class="preloader preloader-center"></div></div>');

	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_likes.php",
		data: query+"&token_id="+token_id,
		cache: false,
		success: function(html) {
			// Append the new comment to the div i
			$('#likes-result').append(html);
			$('#more-likes').remove();
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function loadShares(start, id) {
	$('#more-shares').html('<div class="modal-listing-load-more"><div class="preloader preloader-center"></div></div>');

	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_shares.php",
		data: "start="+start+"&id="+id+"&token_id="+token_id,
		cache: false,
		success: function(html) {
			// Append the new comment to the div i
			$('#shares-result').append(html);
			$('#more-shares').remove();
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
			
			autosize();
		}
	});
}
function hideSearch() {
	$(".search-container").hide();
	$(".search-content").remove();
}
function delete_the(id, type, parent) {
	// id = unique id of the message/comment/chat
	// type = type of post: message/comment/chat
	hideModal();
	
	if(type == 0) {
		$('#comment'+id).fadeOut(500, function() { $('#comment'+id).remove(); });
		$('#action-loader'+parent).html('<div class="privacy_loader"></div>');
	} else if(type == 1) {
		$('#message'+id).fadeOut(500, function() { $('#message'+id).remove(); });
	} else if(type == 2) {
		$('div[data-chat-id="'+id+'"]').fadeOut(500, function() { $('div[data-chat-id="'+id+'"]').remove(); });
	}
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/delete.php",
		data: "message="+id+"&type="+type+"&parent="+parent+"&token_id="+token_id,
		cache: false,
		success: function(html) {
			if(type == 0) {
				var result = jQuery.parseJSON(html);
				$('#message-action'+parent).html(result.actions);
			}
		}
	});
}
function edit_message(id, type) {
	if(type) {
		$('#message_loader'+id).html('<div class="preloader preloader-center"></div>');
		
		// Store the new message
		var message = $('#edited_message'+id).val();
		
		// Remove the inputs
		$('#edit-message'+id).remove();
		
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/post_edit.php",
			data: "message="+encodeURIComponent(message)+"&id="+id+"&token_id="+token_id,
			cache: false,
			success: function(html) {
				$('#message_loader'+id).empty();
				if(html) {
					// Update the message's content
					var content = html
					$('#message_text'+id).html(html);
					$('#message_text'+id).show();
				} else {
					// If the message wasn't updated, show the old one
					$('#message_text'+id).show();
				}
			}
		});
	} else {
		// Show the edit box
		$('#edit-message'+id).remove();
		
		var text = $('#message_text'+id).text();
		var button = '<div class="edit-btn-container"><div type="button" name="edit" class="edit-btn button-active" value="'+$('#edit_text'+id).html()+'"><a onclick="edit_message('+id+', 1)">'+$('#edit_text'+id).html()+'</a></div></div>';
		$('#message_text'+id).hide();
		$('#message_text'+id).after('<div class="message-message" id="edit-message'+id+'"><div class="message-editing"><textarea id="edited_message'+id+'">'+$.trim(text)+'</textarea>'+button+'</div></div>');
		
		$('#edited_message'+id).focus();
	}
}
function edit_comment(id, type, pid) {
	if(type) {
		$('#action-loader'+pid).html('<div class="privacy_loader"></div>');
		
		// Store the new message
		var message = $('#edited_comment'+id).val();
		
		// Remove the inputs
		$('#edit-comment'+id).remove();
		
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/post_edit.php",
			data: "message="+encodeURIComponent(message)+"&id="+id+"&type=1&token_id="+token_id,
			cache: false,
			success: function(html) {
				$('#action-loader'+pid).empty();
				if(html) {
					// Update the message's content
					var content = html
					$('#comment_text'+id).html(html);
					$('#comment_text'+id).show();
				} else {
					// If the message wasn't updated, show the old one
					$('#comment_text'+id).show();
				}
			}
		});
	} else {
		// Show the edit box
		$('#edit-comment'+id).remove();
		
		var text = $('#comment_text'+id).text();
		var button = '<div class="edit-btn-container"><div type="button" name="edit" class="edit-btn button-active" value="'+$('#edit_text_c'+id).html()+'"><a onclick="edit_comment('+id+', 1, '+pid+')">'+$('#edit_text_c'+id).html()+'</a></div></div>';
		$('#comment_text'+id).hide();
		$('#comment_text'+id).after('<div class="message-message" id="edit-comment'+id+'"><textarea id="edited_comment'+id+'" class="comment-reply-textarea">'+$.trim(text)+'</textarea>'+button+'</div>');
		
		$('#edited_comment'+id).focus();
	}
}
function report_the(id, type) {
	// id = unique id of the message/comment
	// type = type of post: message/comment
	
	if(type == 0) {
		$('#comment'+id).html('<div class="message-reported"><div class="preloader"></div></div>');
	} if(type == 1) {
		$('#message_loader'+id).html('<div class="preloader preloader-center"></div>');
	}
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/report.php",
		data: "id="+id+"&type="+type+"&token_id="+token_id,
		cache: false,
		success: function(html) {
			if(type == 0) {
				$('#comment'+id).html('<div class="message-reported">'+html+'</div>');
			} if(type == 1) {
				$('#message'+id).html('<div class="message-content"><div class="message-inner">'+html+'</div></div>');
			}
		}
	});
}
function friend(id, type, z) {
	// id = unique id of the viewed profile
	// type 1: send, approve or cancel a friend request
	// type 2: approve a friend request from the notifications widget
	// type 3: decline a friend request from the notifications widget
	// z if on, activate the sublist class which sets another margin (friends dedicated profile page)
	
	if(type == 2 || type == 3) {
		$('#notification-buttons'+z).empty();
	} else {
		if(z == 1) {
			$('#friend'+id).html('<div class="sub-loading subslist"></div>');
		} else {
			$('#friend'+id).html('<div class="sub-loading"></div>');
			$('#friend-card-'+id).html('<div class="sub-loading"></div>');
		}
	}
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/friend.php",
		data: "id="+id+"&type="+type+"&z="+z+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			if(type == 2 || type == 3) {
				$('#notification-buttons'+z).html(html);
			} else {
				$('#friend-card-'+id).html(html);
				$('#friend'+id).html(html);
			}
		}
	});
}
function loadNotifications(start, ln, cn, sn, fn, dn, bn, gn, pn, xn, mn) {
	if(ln) {
		var filter = 'likes';
	} else if(cn) {
		var filter = 'comments';
	} else if(sn) {
		var filter = 'shared';
	} else if(fn) {
		var filter = 'friendships';
	} else if(dn) {
		var filter = 'chats';
	} else if(bn) {
		var filter = 'birthdays';
	} else if(gn) {
		var filter = 'groups';
	} else if(pn) {
		var filter = 'pokes';
	} else if(xn) {
		var filter = 'pages';
	} else if(mn) {
		var filter = 'mentions';
	}
	
	$('#more_users').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_notifications.php",
		data: "start="+start+"&filter="+filter+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_users').remove();
			$('#notifications-page').append(html);
			jQuery(".timeago").timeago('updateFromDOM');
		}
	});
}
function page(type, user, id, profile) {
	// Type: Search Results
	// User: User ID
	// ID: Page ID
	// Profile: Profile ID
	
	if(type == 1 || profile) {
		$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	} else if(type == 2) {
		$('#more_users').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	} else {
		$('#page-invite-'+user).fadeOut(500, function() { $('#page-invite-'+user).remove(); });
	}
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/page.php",
		data: "page="+id+"&user="+user+"&type="+type+"&profile="+profile+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			if(type == 1 || profile) {
				$('#more_messages').remove();
				$('#messages').append(html);
			} else if(type == 2) {
				$('#more_users').remove();
				$('#pages').append(html);
			}
		}
	});
}


// function verifycodeinvite(groupinvitecode) {
//     var inputgroupinvitecode = document.getElementById('inputgroupinvitecode').value;
//       if(groupinvitecode == inputgroupinvitecode){
// 		  alert(inputgroupinvitecode);
// 	  }else{

// 		  alert(groupinvitecode);
// 	  }
// }

function group(type, value, group, user, id) {
	// Type: The type of the request
	// Value: Values container
	// Group: Group ID
	// User: User ID
	
	// Type 0: Manage Users
	// Type 1: Load Users
	// Type 6: Join, Leave group

	if(type == 1) {
		$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	} else if(type == 2) {
		$('#message_loader'+value).html('<div class="preloader preloader-center"></div>');
		$('#message'+value).fadeOut(500, function() { $('#message'+value).remove(); });
	} else if(type == 3) {
		$('#more_messages').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	} else if(type == 5) {
		$('#more_users').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	} else if(type == 6) {
		$('#group-btn-'+group).html('<div class="sub-loading"></div>');
	} else if(type == 7) {
		$('#group-invite-'+value).fadeOut(500, function() { $('#group-invite-'+value).remove(); });
	} else {
		$('#group-request-'+id).fadeOut(500, function() { $('#group-request-'+id).remove(); });
	}
	if(value == 0 && type == 0) {
		$('.message-container[data-userid="'+user+'"').fadeOut(500, function() { $('.message-container[data-userid="'+user+'"').remove() });
	}
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/group.php",
		data: "type="+type+"&value="+value+"&group="+group+"&user="+user+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			if(type == 1) {
				$('#more_messages').remove();
			
				// Append the new comment to the div id
				$('#messages').append(html);
			} else if(type == 3) {
				$('#more_messages').remove();
				
				// Append the new comment to the div id
				$('#messages').append(html);
			} else if(type == 5) {
				if(user) {
                    $('#more_messages').remove();
					$('#messages').append(html);
				} else {
                    $('#more_users').remove();
					$('#groups').append(html);
				}
			} else if(type == 6 ) {
				$('#group-btn-'+group).html(html);
				// Hide the post message form when leaving the group
				if(html.search('join-button') > 0) {
					$('#postForm').unwrap();
					$('#postForm').remove();
				}
			}
		}
	});
}

function deleteNotification(type, id) {
	if(type == 0) {
		$('#notification'+id).fadeOut(500, function() { $('#notification'+id).remove(); });
	} else if(type == 1) {
		$('#post_comment_'+id).fadeOut(500, function() { $('#post_comment_'+id).remove(); });
	}
}
function privacy(id, value) {
	// id = unique id of the message/comment
	// value = value to set on the post
	$('#privacy'+id).empty();
	$('#message_loader'+id).html('<div class="preloader preloader-center"></div>');
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/privacy.php",
		data: "message="+id+"&value="+value+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#message_loader'+id).empty();
			$('#privacy'+id).html(html);
			if(value == 0) {
				$('#comment_box_'+id).hide('slow');
			} else {
				$('#comment_box_'+id).show('slow');
			}
		}
	});
}
function manage_the(start, type, filter) {
	// filter = a users filter (normal, verified and moderators)
	if(type == 1) {
		type = 'reports';
	} else {
		type = 'users';
	}
	$('#more_users').html('<div class="load_more"><div class="preloader preloader-center"></div></div>');
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/manage_"+type+".php",
		data: "start="+start+"&filter="+filter+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			$('#more_users').remove();
			
			// Append the new comment to the div id
			$('#'+type).append(html);
		}
	});
}
function manage_report(id, type, post, kind) {
	$('#report'+id).html('<div class="preloader"></div>');
	$('#report'+id).fadeOut(500, function() { $('#message'+id).remove(); });
	
	// Subtract from the notifications number
	var notifCount = $('.admin-notifications-number').text();
	$('.admin-notifications-number').text(notifCount-1);
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/manage_reports.php",
		data: "id="+id+"&type="+type+"&post="+post+"&kind="+kind+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
		}
	});
}
function doLike(id, type) {
	// id = unique id of the message
	// type = 0 for messages, 1 for comments, 2 for pages
	if(type == 1) {
		$('#action-c-loader'+id).html('<div class="privacy_loader"></div>');
		// Store the onclick attribute value and temporary remove it
		var attrVal = $('#doLikeC'+id).attr('onclick');
		$('#doLikeC'+id).removeAttr('onclick');
	} else if(type == 2) {
		$('#page-btn-'+id).html('<div class="sub-loading"></div>');
		$('#page-card-'+id).html('<div class="sub-loading"></div>');
	} else {
		$('#action-loader'+id).html('<div class="privacy_loader"></div>');
		// Store the onclick attribute value and temporary remove it
		var attrVal = $('#doLike'+id).attr('onclick');
		$('#doLike'+id).removeAttr('onclick');
	}
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/like.php",
		data: "id="+id+"&type="+type+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			if(type !== 2) {
				var result = jQuery.parseJSON(html);
			}
			if(type == 1) {
				$('#doLikeC'+id).html(result.value);
				// Add the onclick event to the Like button
				$('#doLikeC'+id).attr('onclick', attrVal);
				$('#comment-action'+id).html(result.actions);
				jQuery(".timeago").timeago('updateFromDOM');
			} else if(type == 2) {
				$('#page-btn-'+id).html(html);
				$('#page-card-'+id).html(html);
			} else {
				$('#doLike'+id).html(result.value);
				// Add the onclick event to the Like button
				$('#doLike'+id).attr('onclick', attrVal);
				$('#message-action'+id).html(result.actions);
			}
		}
	});
}
function doBlock(id, type) {
	// id = unique id of the user
	// type 0: do nothing, just display the block, type 1: do/undo block
	$('.blocked-link').html('<div class="privacy_loader"></div>');
	$('#blocked'+id).remove();
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/block.php",
		data: "id="+id+"&type="+type+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			// Display the block on the Messages page
			$('.blocked-link').html(html);
			
			// Display the block on the profile [...] menu 
			$('#block'+id).html(html);
			
			// Reopen the profile [...] menu
			messageMenu('-profile-extra', 1);
		}
	});
}
function poke(id) {
	// id = unique id of the user
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/poke.php",
		data: "id="+id+"&token_id="+token_id, 
		cache: false,
		success: function(html) {
			// Display the block on the profile [...] menu 
			$('#poke'+id).html(html);
			
			// Reopen the profile [...] menu
			messageMenu('-profile-extra', 1);
		}
	});
}
function showNotification(x, y) {
	// Y1: Show the global notifications
	// Y2: Show the messages notifications
	// Y3: Show the friends notifications
	$('#messages_btn').removeClass('menu_btn_hover');
	$('#notifications_btn').removeClass('menu_btn_hover');
	$('#friends_btn').removeClass('menu_btn_hover');
	
	if(x == 'close') {
		$('.notification-container').hide();
		$('#messages_btn').removeClass('menu_btn_hover');
		$('#notifications_btn').removeClass('menu_btn_hover');
		$('#friends_btn').removeClass('menu_btn_hover');
		$('#notifications-content').empty();
		// Check the notification state
		// Prevent from double instance when loadpage
		if(typeof notificationState != 'undefined') {
			if(notificationState == false) {
				checkNewNotifications();
			}
		}
	} else {
		// Stop checking for new notifications while reading them
		clearTimeout(stopNotifications);
		notificationState = false;
		
		$('.notification-container').show();
		if(y == 1) {
			$('#notifications_btn').addClass('menu_btn_hover');
			$('#notifications_btn').html(getNotificationImage());
			
			// Show or hide the divs from the notifications container header
			$('#global_page_url').show();
			$('#chat_page_url').hide();
			$('#friends_page_url').hide();
			var extra = "";
		} else if(y == 2) {
			$('#messages_btn').addClass('menu_btn_hover');
			$('#messages_btn').html(getMessagesImageUrl(1));
			
			// Show or hide the divs from the notifications container header
			$('#global_page_url').hide();
			$('#chat_page_url').show();
			$('#friends_page_url').hide();
			
			var extra = '&for=1';
		} else if(y == 3) {
			$('#friends_btn').addClass('menu_btn_hover');
			$('#friends_btn').html(getFriendsImage());
			
			// Show or hide the divs from the notifications container header
			$('#global_page_url').hide();
			$('#chat_page_url').hide();
			$('#friends_page_url').show();
			
			var extra = '&for=3';
		}
		$('#notifications-content').html('<div class="notification-inner"><div class="preloader"></div></div>');
		
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/check_notifications.php",
			data: "type=1"+extra+"&token_id="+token_id,
			cache: false,
			success: function(html) {
				if(html) {
					$('#notifications-content').html(html);
					jQuery(".timeago").timeago('updateFromDOM');
				}
			}
		});
	}
}
function checkNewMessages() {
	var last = $('.last-message').data("last");
	var filter = $('.last-message').data("filter");
	var profile = $('.last-message').data("username");
	var type = $('.last-message').data("type");
	
	// Check the current page and if there's no extra filter applied
	if( window.location.search.indexOf('a=feed') > -1
	|| (window.location.search.indexOf('a=profile') > -1 && window.location.search.indexOf('&r=') == -1)
	|| (window.location.search.indexOf('page&name=') > -1 && window.location.search.indexOf('&r=') == -1 && window.location.search.indexOf('&friends=') == -1)
	|| (window.location.search.indexOf('group&name=') > -1 && window.location.search.indexOf('&r=') == -1 && window.location.search.indexOf('&search=') == -1 && window.location.search.indexOf('&friends=') == -1)
	|| (window.location.pathname.indexOf('/feed') > -1)
	// Check if the current page is a profile and it is not on a subpage (for example /about)
	// But also make sure the user's username is not About, case in which, it should work if not on the subpage /about
	|| (window.location.pathname.indexOf('/profile/') > -1 && ['about', 'friends', 'groups', 'likes', 'delete'].indexOf(window.location.pathname.split("/").pop()) == -1)
	|| (window.location.pathname.indexOf('/page/') > -1 && ['likes', 'edit', 'delete'].indexOf(window.location.pathname.split("/").pop()) == -1)
	|| (window.location.pathname.indexOf('/group/') > -1 && ['members', 'admins', 'requests', 'blocked', 'edit', 'delete'].indexOf(window.location.pathname.split("/").pop()) == -1)
	) {
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/check_messages.php",
			data: "last="+last+"&filter="+filter+"&profile="+profile+"&type="+type+"&token_id="+token_id, 
			success: function(html) {
				 // html is a string of all output of the server script.
				if(html) {
					$('#load-content').after('<div id="temphide'+last+'">'+html+'</div>');
					
					// Remove the duplicated message if exists
					var last = $('.last-message').data("last");
					$('.last-message[data-last="'+last+'"]:gt(0)').remove();
					
					$('#temphide'+last).hide().fadeIn('slow');
				}
				jQuery(".timeago").timeago('updateFromDOM');
		   }
		});
	}
	stopNewMessages = setTimeout(checkNewMessages, checkMessageTimeout);
}
function postChatImage(type) {
	// Type 1: Camera stream capture
	var id = localStorage.getItem('chat-image-uid');
	$('#c-w-'+id).val('');
	chatInput(0, id);
	
	var formData = new FormData();
	
	// Build the form
	formData.append("id", id);
	formData.append("type", "picture");
	formData.append("token_id", token_id);
	if(type) {
		formData.append("message", document.getElementById('camera-canvas').toDataURL());
		cameraModal(1);
	} else {
		// Check whether when the input has changed has a file selected
		if(typeof($('input[name=chatimage]')[0].files[0]) == "undefined") {
			chatInput(1, id);
			
			return false;
		}
	}
	formData.append("image", $('input[name=chatimage]')[0].files[0]);
	
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
				$('#chat-container-'+id).scrollTop($('#chat-container-'+id+'.chat-container')[0].scrollHeight);
			}
			if($('#bc-friends-chat-'+id).length) {
				$('#bc-friends-chat-'+id).scrollTop($('#bc-friends-chat-'+id+'.bc-friends-chat')[0].scrollHeight);
			}
			$('.last-online[data-last-online="'+id+'"]').remove();
			jQuery(".timeago").timeago('updateFromDOM');
		}
	}
}
function postChat(id, type) {
	// Type 1: Messages page
	// Type 2: Chat window
	if(type == 1) {
		// Store the message into var
		var message = $('input#chat').val();
		var uid = $('#chat').attr('class');
		var id = uid.replace('chat-user', '');		
		document.getElementById("chat").style.height = "25px";
		
		// Show the progress animation
		$('.message-loader').show();
	} else {
		var message = $('input#c-w-'+id).val();
		$('#c-w-'+id).hide();
		$('#c-w-p-'+id).show();
	}
	// Reset the chat input area
	$('#c-w-'+id).val('');
	$('.chat-user'+id).val('');
	
	// Remove chat errors if any
	$('.chat-error').remove();
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/post_chat.php",
		data: 'message='+encodeURIComponent(message)+'&id='+id+'&token_id='+token_id,
		cache: false,
		success: function(html) {
			// Check if in the mean time any message was sent
			checkChat(1, id);
			
			// Append the new chat to the div chat container
			$('#bc-friends-chat-'+id).append(html);
			$('#chat-container-'+id).append(html);
			if(type == 1) {
				$('.message-loader').hide();
			} else {
				$('#c-w-'+id).show();
				$('#c-w-'+id).focus();
				$('#c-w-p-'+id).hide();
			}
			if($('#chat-container-'+id).length) {
				$('#chat-container-'+id).scrollTop($('.chat-container')[0].scrollHeight);
			}
			if($('#bc-friends-chat-'+id).length) {
				$('#bc-friends-chat-'+id).scrollTop($('.bc-friends-chat')[0].scrollHeight);
			}
			$('.last-online[data-last-online="'+id+'"]').remove();
			jQuery(".timeago").timeago('updateFromDOM');
		}
	});
}
function chatInput(type, id) {
	// Type 0: Show the input, hide preloaders
	// Type 1: Hide the input, show preloaders
	if(type) {
		$('#c-w-'+id).show();
		$('#c-w-'+id).focus();
		$('#c-w-p-'+id).hide();
		$('#m-p-'+id).hide();
	} else {
		$('#c-w-'+id).hide();
		$('#c-w-p-'+id).show();
		$('#m-p-'+id).show();
	}
}
function checkChat(x, id) {
	// x 1: Get the last chat message after a chat message has been posted
	// x 2: Get the last messages for every all chat windows
	if(typeof friends_windows === 'undefined') {
		friends_windows = [];
	}
	
	if(x == 1) {
		var freq = "&uid="+id;
	} else {
		var freq = "";
	}
	// Check whether uid is defined or not [prevent from making requests when leaving the chat page]
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_chat.php",
		data: "friends="+friends_windows+"&type="+x+freq+"&token_id="+token_id,
		success: function(html) {
			 // html is a string of all output of the server script.
			if(html) {
				if(x == 2) {
					var result = jQuery.parseJSON(html);
					$('#friends-count').html(result.friends_chat.friends_count);
					
					var searchQuery = $('#search-window').val();
					
					// Prevent replacing the search results
					if(searchQuery == "") {
						$('#friends-list').html(result.friends_chat.friends_list);
					}
					for(var message_id in result.friends_messages) {
						$('#online-status-'+message_id).html(result.friends_messages[message_id].status);
						
						// If a new message is received
						try {
							if(result.friends_messages[message_id].message.length) {
								if(!$('#c-w-'+message_id).is(':focus')) {
									$('#chat-header-'+message_id).addClass('bc-friends-header bc-friends-header-animated');
								}
								$('#chat-container-'+message_id).append(result.friends_messages[message_id].message);
								$('#bc-friends-chat-'+message_id).append(result.friends_messages[message_id].message);

								if($('#chat-container-'+message_id).length) {
									$('#chat-container-'+message_id).scrollTop($('#chat-container-'+message_id+'.chat-container')[0].scrollHeight);
								}
								if($('#bc-friends-chat-'+message_id).length) {
									$('#bc-friends-chat-'+message_id).scrollTop($('#bc-friends-chat-'+message_id+'.bc-friends-chat')[0].scrollHeight);
								}
								$('.last-online[data-last-online="'+message_id+'"]').remove();
								notificationTitle(1);
							}
						} catch(err) {

						}
					}
				} else if(x == 1) {
					$('#chat-container-'+id).append(html);
					$('#bc-friends-chat-'+id).append(html);
					
					if($('#chat-container-'+id).length) {
						$('#chat-container-'+id).scrollTop($('.chat-container')[0].scrollHeight);
					}
					if($('#bc-friends-chat-'+id).length) {
						$('#bc-friends-chat-'+id).scrollTop($('.bc-friends-chat')[0].scrollHeight);
					}
					$('.last-online[data-last-online="'+uid+'"]').remove();
					notificationTitle(1);
				}
				if(x == 2) {
					setTimeout(function() { checkChat(2) }, chatr);
				}
				jQuery(".timeago").timeago('updateFromDOM');
			}
		}
	});
}
function loadChat(uid, cid, start, z) {
	// Z 1: Chat Windows
	// Z 0: Messages Page
	if(z) {
		$('#l-m-c-'+uid).html('<div class="preloader preloader-center"></div>');
	} else {
		$('#l-m-c').html('<div class="preloader preloader-center"></div>');
	}
	$.ajax({
		type: "POST",
		url: baseUrl+"/requests/load_chat.php",
		data: "uid="+uid+"&cid="+cid+"&start="+start+"&for="+z+"&token_id="+token_id,
		cache: false,
		success: function(html) {
			// Append the new comment to the div id
			if(z == 1) {
				$('#l-m-c-'+uid).remove();
				$('#bc-friends-chat-'+uid).prepend(html);
			} else {
				$('#l-m-c').remove();
				$('.chat-container').prepend(html);
			}
			$('.last-online[data-last-online="'+uid+'"]').remove();
			// Reload the timeago plugin
			jQuery(".timeago").timeago('updateFromDOM');
		}
	});
}
function addSmile(value) {
	var id = $('#chat-plugin-container').data('active-window');
	
	var currentInput = $('#c-w-'+id).val();
	
	// Check whether the current input value can be retrieved or not (dedicated message page while chat window is inactive)
	if(typeof(currentInput) == "undefined") {
		var currentInput = $('#chat').val();
	}
	
	// If the input is empty, don't add any new spaces before the smile
	if(currentInput == "") {
		var output = currentInput + value;
	} else {
		var output = currentInput + ' ' + value;
	}
	
	$('#c-w-'+id).val(output);
	$('.chat-user'+id).val(output);
}
function showEmojis(category) {
	$('.emojis-list').hide();
	$('#emojis-'+category).show();
	
	$('.emoji-category').removeClass('emoji-category-active');
	$('#emoji-button-'+category).addClass('emoji-category-active');
}
function chatPluginContainer(id, close, extra) {
	// Extra: Sets the style for the dedicated message page
	
	// Reset any previous styles (prevents adding extra styles over the dedicated message styles)
	$(".chat-plugin-container").removeAttr("style");
	
	if(close) {
		$('#chat-plugin-container').hide();
		return false;
	}
	// Get the position of the parent element
	if(extra) {
		var position = $("#chat").position();
		var container = $('.chat-plugin-container');
		
		// Store the position into an array
		var pos = {
			width: container.width(),
			height: container.height(),
			position: 'fixed',
			top: '50%',
			left: '0',
			bottom: '0',
			right: '0',
			margin: '-' + container.height() + 'px auto 0 auto'
		};
		
	} else {
		var position = $("#c-w-"+id).position();
		var height = 112;
		if(lng_dir == "rtl") {
			var left = 8;
		} else {
			var left = 28;
		}
	
		// Store the position into an array
		var pos = {
			left: (position.left + left) + 'px'
		};
	}
	
	$('#chat-plugin-container').show();
	$('#chat-plugin-container').css(pos);
	$('#chat-plugin-container').data('active-window', id);
}
function openChatWindow(id, username, realname, url, status) {
	var checkWindow = $('#chat-window-'+id).html();
	if(!checkWindow) {
		// Get the html window model
		var window_html = $('#chat-window-model').html();
		
		// Replace the JS variables with the variables
		var window_html = window_html.replace(/\'\+realname\+\'/g, realname).replace(/\'\+id\+\'/g, id).replace(/\'\+username\+\'/g, username).replace(/\'\+url\+\'/g, url).replace(/\'\+status\+\'/g, status).replace(/\<!--/g, '').replace(/\-->/g, '');
		
		$('.bc-container').append(window_html);
		
		addFriendArray(id);
		
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/load_chat.php",
			data: "uid="+id+"&for=1&token_id="+token_id, 
			cache: false,
			success: function(html) {
				// Append the new comment to the div id
				$('#bc-friends-chat-'+id).prepend(html);
				
				// Scroll to the bottom of the content
				$("#bc-friends-chat-"+id).scrollTop($("#bc-friends-chat-"+id+".bc-friends-chat")[0].scrollHeight);
				
				// Reload the timeago plugin
				jQuery(".timeago").timeago('updateFromDOM');
			}
		});
	}
	$('#c-w-'+id).focus();
}
function closeChatWindow(id) {
	$('#chat-window-'+id).remove();
	
	// Get the array element and remove it
	var i = friends_windows.indexOf(id);
	if(i != -1) {
		// If the current ID being closed is not currently opened on the Messages Page
		if(window.location.search.indexOf('a=messages') == -1 && window.location.search.indexOf('id='+id) == -1 && window.location.href.indexOf(baseUrl+'/messages/') == -1 && window.location.href.indexOf('/'+id) == -1) {
			friends_windows.splice(i, 1);
		}
	}
}
function minimizeChatWindow(id, type) {
	if($('#chat-window-'+id).data('state') == 'minimized') {
		$('#chat-window-'+id).removeAttr('style')
		$('#chat-window-'+id).data('state', 'maximized');
		var state = 1;
	} else {
		$('#chat-window-'+id).data('state', 'minimized');
		$('#chat-window-'+id).css({'margin-top' : '315px'});
		var state = 0;
	}
	if(id == 'friends-list') {
		// Set the friends list state
		localStorage.setItem("friends-list-state", state);
	}
}
function disableTitleAlert(id) {
	$('#chat-header-'+id).removeClass('bc-friends-header bc-friends-header-animated').addClass('bc-friends-header');
}
function addFriendArray(id) {
	// If the array key doesn't exist, then add it
	if(friends_windows.indexOf(id) == -1 && id > 0) {
		friends_windows.push(id);
	}
}
function cleanOldFid() {
	// If the user nagivates away from a page, check if the old fid from messages page has been cleaned up
	if(typeof old_fid != 'undefined') {
		// Check if the chat window is not opened
		if($('#bc-friends-chat-'+old_fid).length == 0) {
			var i = friends_windows.indexOf(old_fid);
			if(i != -1) {
				friends_windows.splice(i, 1);
			}
		}
	}
}
function startUpload() {
	document.getElementById("postForm").target = "frameForm";
	document.getElementById("postForm").submit();
	document.getElementById("post-loader9999999999").style.visibility = "visible";
	$('#mentions-container').remove();
}
function stopUpload(success) {
	document.getElementById("post-loader9999999999").style.visibility = "hidden";
	$('#load-content').after(success);
	// Get the last message ID
	var last = $('.last-message').data("last");
	$('#message'+last).hide().fadeIn('slow');

	if (success.indexOf('class="delete_btn"') == -1) {
		document.getElementById("postForm").reset();
		document.getElementById("post9999999999").style.height = "38px";
		$('#queued-files').html('');

		// Reset the selected 'type' option
		$('#values label').addClass('selected').siblings().removeClass('selected');
		$('.message-form-input').hide('fast');
		$('.selected-files').hide('fast');
		$('#plugins-forms').hide('fast');
		jQuery(".timeago").timeago('updateFromDOM');
		autosize();
		// Hide the plugins forms
	}
	return true;   
}
function focus_form(id) {
	document.getElementById('comment-form'+id).focus();
	showButton(id);
}
function resizeGallery() {
	// image-container class
	var maxWidth = 1000;
	var maxHeight = 600;
	
	$('.image-container').css('max-width', maxWidth);
	$('.image-container').css('max-height', maxHeight);
	
	var currentWidth = $(window).width();
	var currentHeight = $(window).height();
	var currentMidWidth = Math.abs(currentWidth - maxWidth);
	var currentMidHeight = Math.abs(currentHeight - maxHeight);
	
	// Calculate the Width
	if(currentMidWidth <= 40 && currentMidWidth >= 0) {
		$('.image-container').css('max-width', currentWidth - 40);
		$('.image-container').css('margin-left', 20);
		$('.image-container').css('margin-right', 20);
	} else if(maxWidth < currentWidth) {
		$('.image-container').css('margin-left', ((currentWidth - maxWidth) / 2));
		$('.image-container').css('margin-right', ((currentWidth - maxWidth) / 2));
	} else {
		$('.image-container').css('max-width', currentWidth - 40);
	}
	
	// Calculate the Height
	if(currentMidHeight <= 40 && currentMidHeight >= 0) {
		$('.image-container').css('max-height', currentHeight - (62 - 20));
		$('.image-container').css('margin-top', 20);
		$('.image-container').css('margin-bottom', 20);
		$('.image-content').css('height', currentHeight - (40 + 62));
		$('#gallery-next, #gallery-prev').css({'height': ($('.image-content').height()-35), 'top': '35px'});
	} else if(maxHeight < currentHeight) {
		$('.image-container').css('margin-top', ((currentHeight - maxHeight) / 2));
		$('.image-container').css('margin-bottom', ((currentHeight - maxHeight) / 2));
		$('.image-content').css('height', maxHeight - 62);
		$('#gallery-next, #gallery-prev').css({'height': ($('.image-content').height()-35), 'top': '35px'});
	} else {
		$('.image-container').css('max-height', currentHeight - 40);
		$('.image-content').css('height', currentHeight - (40 + 62));
		$('#gallery-next, #gallery-prev').css({'height': ($('.image-content').height()-35), 'top': '35px'});
	}
	
	// console.log('Image Width:'+$('img.ri').width());
	// console.log('Image Height:'+$('img.ri').height());
	// console.log('Container Width:'+$('div.image-content').width());
	// console.log('Container Height:'+$('div.image-content').height());
	return false;
	
	//$('.image-container');
}
function manageResults(x) {
	if(x == 0) {
		hideSearch();
	} else {
		if(x == 1) {
			var url = search_users_url+$("#search").val().replace(' ','+');
		} else if(x == 2) {
			var url = search_tags_url+$("#search").val().replace('#','');
		} else if(x == 3) {
			var url = search_groups_url+$("#search").val().replace('!','');
		} else if(x == 4) {
			var url = search_pages_url+$("#search").val().replace('@','');
		}
		liveLoad(url);
	}
}
function chatLiveSearch(type) {
	// Type 1: Window Chat Search
	// Type 0: Messages page search
	
	if(type) {
		var q = $('#search-window').val();
		var type = 1;
		var css = 'bc-friends-content';
		
		if(typeof last_window_search != 'undefined') {
			if(q == last_window_search) {
				return false;
			}
		}
		
		last_window_search = q;
	} else {
		var q = $('#search-list').val();
		var type = 0;
		var css = 'sidebar-chat-list';
		
		if(typeof last_user_search != 'undefined') {
			if(q == last_user_search) {
				return false;
			}
		}
		
		last_user_search = q;
	}
	
	// If the request is empty	
	if(q.length == 0) {
		return false;
	}
	
	$('.'+css).html('<div class="search-content"><div class="message-inner"><div class="preloader preloader-center"></div></div></div>');
	
	// If the text input is 0, remove everything instantly by setting the MS to 1
	var ms = 200;
	
	// Start the delay (to prevent some useless queries)
	setTimeout(function() {
		if(q == $('#search-list').val() || q == $('#search-window').val()) {
			$.ajax({
				type: "POST",
				url: baseUrl+"/requests/load_people.php",
				data: 'q='+q+'&start=1&live=1&list=1&type='+type+"&token_id="+token_id, // start is not used in this particular case, only needs to be set
				cache: false,
				success: function(html) {
					if(type) {
						$('.bc-friends-content').html(html);
					} else {
						$('.sidebar-chat-list').html(html);
					}
				}
			});
		}
	}, ms);
}
function profileCard(id, post, type, delay, page) {
	// ID: Unique user ID
	// Post: Unique Message/Post ID
	// Type: 0 - Message; 1 - Comment;
	// Delay: 0 - on mouse IN; 1 - on mouse OUT;
	if(delay == 1) {
		clearInterval(pcTimer);
	} else {
		pcTimer = setInterval(function(){

		if(type == 1) {
			var classType = 'comment';
			// The position to be increased
			var height = 45;
			var left = 0;
			var right = 20;
		} else {
			var classType = 'message';
			// The position to be increased
			var height = 58;
			var left = 20;
			var right = 20;
		}
		
		// Start displaying the profile card with the preloader
		$('#profile-card').show();
		$('#profile-card').html('<div class="profile-card-padding"><div class="preloader preloader-center"></div></div>');

		// Get the position of the parent element
		var position = $("#"+classType+post).position();

		// Store the position into an array
		if(lng_dir == "rtl") {
			var pos = {
				top: (position.top + height) + 'px',
				right: right + 'px'
			};
		} else {
			var pos = {
				top: (position.top + height) + 'px',
				left: (position.left + left) + 'px'
			};
		}
		
		// Set the position of the profile card
		$('#profile-card').css(pos);
		$.ajax({
			type: "POST",
			url: baseUrl+"/requests/load_profilecard.php",
			data: 'id='+id+"&page="+page+"&token_id="+token_id,
			cache: false,
			success: function(html) {			
				$('#profile-card').html(html);
			},
			error: function() {
				$('#profile-card').hide();
			}
		});
		clearInterval(pcTimer);
		}, 500);
	}
}
function notificationTitle(type) {
	// Type 1: Play the New Chat Message notification
	if(!document.hasFocus()) {
		if(type == 1) {
			$('#soundNewChat')[0].play();
		} else if(type == 2) {
			$("#soundNewNotification")[0].play();
		}
		
		// If the current document title doesn't have a count alert, add one
		var title = document.title;
		if(title.charAt(0) !== "(") {
			if(totalNotifications > 0) {
				document.title = "(" + totalNotifications + ") " + document.title;
			} else {
				document.title = "(!) " + document.title;
			}
		}
	}
}
function dropdownMenu(type) {
	// 1: Reset the menu
	if(type) {
		$('.menu-image').removeClass('menu-image-active');
		$('#menu-dd-container').hide();
	} else {
		// Dropdown Menu Icon
		$('.menu-image').on("click", function() {
			$('.menu-image').toggleClass('menu-image-active');
			$('#menu-dd-container').toggle();
			showNotification('close', 1);
		});
	}
}
function messageMenu(id, type, close) {
	// Close 1: Close the context menu
	// Type 1: Open context for messages
	// Type 2: Open sidebar context for groups
	// Type 3: Open sidebar context for pages
	// Type 4: Open context for comments
	if(close == 1) {
		$('.message-menu-container').removeClass('message-menu-active');
	} else {
		if(type == 1) {
			// If the menu is already opened and is being clicked, close it
			if($('#message-menu'+id).hasClass('message-menu-active')) {
				messageMenu(0, 0, 1);
			}
			// Display the menu
			else {
				$('.message-menu-container').removeClass('message-menu-active');
				$('#message-menu'+id).addClass('message-menu-container message-menu-active');
				$('#message-menu'+id).show();
			}
		} else if(type == 2) {
			// If the menu is already opened and is being clicked, close it
			if($('#group-menu'+id).hasClass('message-menu-active')) {
				messageMenu(0, 0, 1);
			}
			// Display the menu
			else {
				$('.message-menu-container').removeClass('message-menu-active');
				$('#group-menu'+id).addClass('message-menu-container sidebar-menu-container message-menu-active');
				$('#group-menu'+id).show();
			}
		} else if(type == 3) {
			// If the menu is already opened and is being clicked, close it
			if($('#page-menu'+id).hasClass('message-menu-active')) {
				messageMenu(0, 0, 1);
			}
			// Display the menu
			else {
				$('.message-menu-container').removeClass('message-menu-active');
				$('#page-menu'+id).addClass('message-menu-container sidebar-menu-container message-menu-active');
				$('#page-menu'+id).show();
			}
		} else if(type == 4) {
			// If the menu is already opened and is being clicked, close it
			if($('#comment-menu'+id).hasClass('message-menu-active')) {
				messageMenu(0, 0, 1);
			}
			// Display the menu
			else {
				$('.message-menu-container').removeClass('message-menu-active');
				$('#comment-menu'+id).addClass('message-menu-container message-menu-active');
				$('#comment-menu'+id).show();
			}
		}
	}
}
function postPrivacy(type) {
	// Set the input value
	$('#message-privacy').val(type);
	
	// Set the privacy icon on the button
	$('#privacy-btn').removeClass();
	if(type == 1) {
		$('#privacy-btn').addClass('privacy-icons public-icon');
	} else if(type == 2) {
		$('#privacy-btn').addClass('privacy-icons friends-icon');
	} else {
		$('#privacy-btn').addClass('privacy-icons private-icon');
	}
}
function sidebarShow(id) {
	$('#show-more-btn-'+id).remove();
	if(id == 1) {
		$('.sidebar-events').fadeIn(300);
	} else if(id == 2) {
		$('.sidebar-dates').fadeIn(300);
	} else if(id == 3) {
		$('.sidebar-group').fadeIn(300);
	} else if(id == 4) {
		$('.sidebar-page').fadeIn(300);
	}
}

function adminSubMenu(id) {
	$('#sub-menu'+id).toggleClass('sidebar-link-sub-active');
	$('#sub-menu-content'+id).slideToggle(300);
}
function checkAlert() {
	if(!document.hasFocus()) {
		// If the current document title doesn't have a count alert, add one
		var title = document.title;
		if(title.charAt(0) !== "(") {
			if(totalNotifications > 0) {
				document.title = "(" + totalNotifications + ") " + document.title;
			} else {
				document.title = "(!) " + document.title;
			
			}
		}
		notificationTitle(2);
	}
}
function searchFriends(url, type) {
	if(type == 1) {
		var q = $('#search-group').val();
	} else if(type == 2) {
		var q = $('#invite-page').val();
	} else {
		var q = $('#invite-group').val();
	}
	if(q.length > 0) {
		liveLoad(url+q);
	}
}
$(document).ready(function() {
	if(typeof friends_windows === 'undefined') {
		friends_windows = [];
	}
	dropdownMenu();
	
	// Prevent scrolling the page when scrolling a scrollable div
	$(document).on('mousewheel DOMMouseScroll', '.scrollable', function(e) {
		var e0 = e.originalEvent,
			delta = e0.wheelDelta || -e0.detail;
		
		this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
		e.preventDefault();
	});
	
	$(document).on('change', '#avatarselect', function () {
		document.getElementById("avatar").submit();
	});
	
	$(document).on('change', '#coverselect', function () {
		document.getElementById("cover").submit();
	});
	
	$(document).on('click', '.chat-image-btn, .chat-camera-btn', function () {
		localStorage.setItem("chat-image-uid", $(this).data("userid"));
	});
	
	$(document).on('change', 'input[name="chatimage"]', function () {
		postChatImage();
	});
	
	$(document).mouseup(function(e) {
		// All the divs that needs to be excepted when being clicked (including the buttons itselfs)
		var container = $('.menu-image, .menu-dd-content, .search-content, .notification-content, .message-menu, .sidebar-settings, #privacy-button, #profile-button, #profile-extra, #share, #camera, #delete, #likes, #shares, #chat-plugin-container');

		// If the element clicked isn't the container nor a descendant then hide the menus, dropdowns
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			manageResults(0);
			dropdownMenu(1);
			messageMenu(0, 0, 1);
			showNotification('close', 1);
			hideModal();
			chatPluginContainer(0, 1);
		}
	});
	
	$(document).on('keyup', '.message-form, .comment-reply-textarea', function(e) {
		$('#mentions-container').remove();
		
		var target = $(this);
		
		var textarea = target.val();

		var words = textarea.split(" ");
		
		var word = words[words.length-1];	
		
		var find = word.match(/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i);
		
		try {
			if(find[2].length > 0) {
				$.ajax({
					type: "POST",
					url: baseUrl+"/requests/mentions.php",
					data: "&target="+target.attr('id')+"&value="+find[2]+"&token_id="+token_id,
					cache: false,
					success: function(html) {
						$('#mentions-container').remove();
						if(target.hasClass('message-form')) {
							$('.message-form-inner').after(html);
						} else {
							target.after(html);
							$('#mentions-container').addClass('mentions-container');
						}
					}
				});
			}
		} catch(e) {
			
		}
	});
	
	$("#search").on('keyup', function(e) {
		var q = $('#search').val();
		
		if(typeof last_search != 'undefined') {
			if(q == last_search && e.which != 13) {
				return false;
			}
		}
		
		last_search = q;
		
		// If the query is empty, don't do anything
		if(q.length < 1) {
			hideSearch();
			return false;
		}
		
		// If the query starts with #, do not execute anything
		if(q == '#' || q == '!' || q == '@') {
			hideSearch();
			return false;
		}
		
		// Check the notification state
		if(typeof notificationState != 'undefined') {
			showNotification('close');
		}
		
		// Search
		if(q.substring(0, 1) == '#') {
			var url = 'load_tags';
			var full_url = search_tags_url+q.replace('#','');
			var data = 'q='+q+'&start=1&live=1';
		} else if(q.substring(0, 1) == '!') {
			var url = 'group';
			var full_url = search_groups_url+q.replace('!','');
			var data = 'type=4&value='+q+'&group=1&user=1';
		} else if(q.substring(0, 1) == '@') {
			var url = 'page';
			var full_url = search_pages_url+q.replace('@','');
			var data = 'live=1&value='+q;
		} else {
			var url = 'load_people';
			var full_url = search_users_url+q.replace(' ','+');
			var data = 'q='+q+'&start=1&live=1';
		}

		// If the text input is 0, remove everything instantly by setting the MS to 1
		if(q == 0) {
			var ms = 0;
		} else {
			$('.search-container').show();
			$('.search-container').html('<div class="search-content"><div class="search-results"><div class="retrieving-results"><div class="preloader preloader-left"></div></div></div></div>');
			var ms = 200;
		}
		
		if(e.which == 13) {
			liveLoad(full_url);
			hideSearch();
			return false;
		}
		
		// Start the delay (to prevent some useless queries)
		setTimeout(function() {
			if(q == $('#search').val()) {
				if(q == 0) {
					hideSearch();
				} else {
					$.ajax({
						type: "POST",
						url: baseUrl+"/requests/"+url+".php",
						data: data+"&token_id="+token_id, // start is not used in this particular case, only needs to be set
						cache: false,
						success: function(html) {
							$(".search-container").html(html).show();
						}
					});
				}
			}
		}, ms);
	});
	
	$(document).on('keyup', '#search-list', function() { chatLiveSearch(0); });
	$(document).on('keyup', '#search-window', function() { chatLiveSearch(1); });
	
	$(document).on('click', '.notification-close-error, .notification-close-warning, .notification-close-success, .notification-close-info', function() {
		$(this).parent().fadeOut("slow"); return false;
	});
	
	$(window).resize(function() {
		resizeGallery()
	});
	resizeGallery();
		
	$(document).on('click', '#gallery-close', function() {
		$("#gallery, .overall").fadeOut(300);
		return false;
	});
	
	$(document).on('click', '#values label:not(".plugin-button, #open_images")', function() {
		$('#plugins-forms, #plugins-forms div, .message-form-input').hide('fast');
		$(this).addClass('selected').siblings().removeClass('selected');
		$('#form-value').attr("Placeholder", $(this).attr('title'));
		$('#form-value').val('');
		$('#images').val('');
		$('.message-form-input').show('fast', function() {
			// Select the form input
			$('#form-value').focus();
		});
		$('.selected-files').hide('fast');
	});
	
	$(document).on('click', '#open_images', function() {
		$('#plugins-forms, #plugins-forms div, .message-form-input').hide();
		$('#form-value').val('');
		$('#values label').removeClass('selected');
		$('#images').click();
	});
	
	$(document).on('change', '#images', function() {
		if(this.files.length > 0) {
			// Clear any previous selections
			$('#queued-files').html('');
			$('.selected-files').show();
			
			for(var i = 0; i < this.files.length; i++){
				if(this.files[i]) {
					var reader = new FileReader();
			
					reader.onload = function(e) {
						$('#queued-files').append('<div class="si-box"><div class="si-box-inner" style="background-size: cover;background-image: url('+e.target.result+');"></div></div>');
					}
					
					reader.readAsDataURL(this.files[i]);
				}
			}
		} else {
			// Clear any previous selections
			$('#queued-files').html('');
			$('.selected-files').hide();
		}
	});
	
	// Disable the enter key on messages
	$(document).on('submit', '#postForm', function() {
		return false;
	});
	
	$(document).on('click', '.comment-image-btn', function() {
		activeComment = $(this).data("active-comment");
	});
	
	$(document).on('change', 'input[name="commentimage"]', function() {
		if(this.files.length > 0) {
			// Clear any previous selections
			$('#queued-comment-files'+activeComment).html('');
			
			for(var i = 0; i < this.files.length; i++){
				if(this.files[i]) {
					var reader = new FileReader();
			
					reader.onload = function(e) {
						$('#queued-comment-files'+activeComment).append('<div class="sci-box"><div class="sci-box-inner" style="background-size: cover;background-image: url('+e.target.result+');"></div></div>');
					}
					
					reader.readAsDataURL(this.files[i]);
				}
			}
		} else {
			// Clear any previous selections
			$('#queued-comment-files'+activeComment).html('');
		}
		focus_form(activeComment);
	});
	
	autosize();
	
	$('#profile-card').mouseleave(function() {
		$('#profile-card').hide();
	});
	
	// When the window is focused
	$(window).focus(function() {
		// Get the position numbe to cut from the title
		var cut = document.title.indexOf(')') + 1;
		
		// If the currentTitle has a notification notification, then remove it
		if(document.title.charAt(0) == "(") {
			document.title = document.title.substr(cut);
		}
	});
	
	// Enable infinite scrolling when on desktop
	if(/Mobi/.test(navigator.userAgent) == false) {
		$(window).scroll(function() {
			if($(window).scrollTop() + $(window).height() == $(document).height()) {
				$('#load-more').click();
			}
		});
	}
	
	// Set the friends list state
	if(localStorage.getItem('friends-list-state') == 0) {
		minimizeChatWindow('friends-list');
	}
	
	reload();
});
function reload() {
	jQuery(".timeago").timeago('updateFromDOM');
	autosize();
	// Reset menu, search
	manageResults(0);
	dropdownMenu(1);
	messageMenu(0, 0, 1);
	showNotification('close', 1);

	// Check the notification state
	if(typeof notificationState != 'undefined') {
		showNotification('close');
	}
	
	$('#share, #camera, #delete, #likes, #shares').fadeOut();
	$('.modal-background').fadeOut();

	// Reset the search value
	if(window.location.search.indexOf('a=search') == -1 && window.location.pathname.indexOf('/search/') == -1) {
		$("#search").val('');
	}
	
	// Scroll down the chat window when on the messages page
	if(window.location.search.indexOf('a=messages') > -1 || window.location.pathname.indexOf('/messages/') > -1) {
		$(".chat-container").scrollTop($(".chat-container")[0].scrollHeight);
	}
	
	// Reset the search value
	if(window.location.search.indexOf('a=search') == -1 && window.location.pathname.indexOf('/search/') == -1) {
		$("#search").val('');
	}
	
	// Reload the profile card event
	$('#profile-card').on("mouseleave", function() {
		$('#profile-card').hide();
	});
	
	// On modal background click, hide it
	$('.modal-background').on("click", function() {
		hideModal();
	});
	
	// Modal menu items
	$('.modal-menu-item').click(function() {
		$(this).addClass('modal-menu-item-active').siblings().removeClass('modal-menu-item-active');
		$('.add-class-here').hide();
		$('.'+$(this).attr('id')).show();
	});
	
	// Edit menu items
	$('.edit-menu-item').click(function() {
		$(this).addClass('edit-menu-item-active').siblings().removeClass('edit-menu-item-active');
		$('.edit-general,.edit-other,.edit-privacy,.edit-notifications,.edit-registration,.edit-limits,.edit-emails').hide();
		$('.'+$(this).attr('id')).show();
	});
	
	// If there's a comment #highlighted
	if(window.location.hash) {
		var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
		// If the hashtag is a comment 
		if(hash.indexOf("comment") > -1) {
			$('#'+hash).addClass('comment-active');
		}
		// If the hashtag is a message 
		if(hash.indexOf("message") > -1) {
			$('#'+hash+' .message-content').addClass('message-active');
		}
	}
}
function gallery(id, uid, type, target) {
	/**
	 * Target:	0 Pages, Profiles, Groups
	 *			1 Posts
	 *			2 Comments
	 *			3 Chats
	 */
	 
	if(target == 1) {
		var prefix = '-p-';
	} else if(target == 2) {
		var prefix = '-c-';
	} else if(target == 3) {
		var prefix = '-m-';
	} else {
		var prefix = '';
	}
	// Show the Gallery
	$("#gallery, #gallery-background, .overall").fadeIn(300);
	
	// If the ID is close, close the Gallery
	if(id == 'close') {
		$("#gallery, .overall").fadeOut(300);
		return false;
	}
	
	// Escape the ID (contains dots) http://api.jquery.com/category/selectors/
	var parsedId = id.replace('.', '\\.');
	
	// Decide NEXT / PREV buttons
	var nextImg = ($('#'+parsedId).next('a'));
	var prevImg = ($('#'+parsedId).prev('a'));

	// If the ID attribute is undefined, hide the button
	if(!nextImg.attr('id')) {
		$('#gallery-next').hide();
	} else {
		$('#gallery-next').show();
		$('#gallery-next').attr('onclick', 'getNext(\''+id+'\', 0, '+uid+', '+target+')');
	}
	if(!prevImg.attr('id')) {
		$('#gallery-prev').hide();
	} else {
		$('#gallery-prev').show();
		$('#gallery-prev').attr('onclick', 'getNext(\''+id+'\', 1, '+uid+', '+target+')');
	}
	
	// Put the content
	$('.image-content').html('<img src="'+baseUrl+'/image.php?src='+id+'&t='+type.charAt(0)+'&zc=3" class="ri">').fadeIn(300);
	$('.gallery-footer-container').html('<div class="message-avatar">'+($('#avatar'+prefix+uid).html() ? $('#avatar'+prefix+uid).html() : '')+'</div><div class="message-top"><a onclick="gallery(\'close\')" title="'+lng_close+'"><div class="delete_btn"></div></a><a href="'+baseUrl+'/uploads/'+type+'/'+id+'" title="'+lng_download+'" target="_blank" download><div class="download_btn"></div></a><div class="message-author">'+($('#author'+prefix+uid).html() ? $('#author'+prefix+uid).html() : '')+'</div><div class="message-time">'+($('#time'+prefix+uid).html() ? $('#time'+prefix+uid).html() : '')+'</div></div>');
	jQuery('.timeago').timeago();
	
	resizeGallery();
}
function getNext(currentId, direction, uid, target) {
	if(target == 1) {
		var prefix = '-p-';
	} else if(target == 2) {
		var prefix = '-c-';
	} else if(target == 3) {
		var prefix = '-m-';
	} else {
		var prefix = '';
	}
	
	// Get the next id
	var parsedId = currentId.replace('.', '\\.');
	if(direction == 0) {
		var next = ($('#'+parsedId).next('a'));
	} else {
		var next = ($('#'+parsedId).prev('a'));
	}
	
	// Put the new Image
	$(".image-content").html('<img src="'+baseUrl+'/image.php?src='+next.attr('id')+'&t=m&zc=3" class="ri">');
	$('.gallery-footer-container').html('<div class="message-avatar">'+($('#avatar'+prefix+uid).html() ? $('#avatar'+prefix+uid).html() : '')+'</div><div class="message-top"><a onclick="gallery(\'close\')" title="'+lng_close+'"><div class="delete_btn"></div></a><a href="'+baseUrl+'/uploads/media/'+next.attr('id')+'" title="'+lng_download+'" target="_blank" download><div class="download_btn"></div></a><div class="message-author">'+($('#author'+prefix+uid).html() ? $('#author'+prefix+uid).html() : '')+'</div><div class="message-time">'+($('#time'+prefix+uid).html() ? $('#time'+prefix+uid).html() : '')+'</div></div>');
	jQuery('.timeago').timeago();
	
	var currentId = next.attr('id').replace('.', '\\.');
	
	// Decide NEXT / PREV buttons
	var nextImg = ($('#'+currentId).next('a'));
	var prevImg = ($('#'+currentId).prev('a'));

	// If the ID attribute is undefined, hide the button
	if(!nextImg.attr('id')) {
		$('#gallery-next').hide();
	} else {
		$('#gallery-next').show();
		$('#gallery-next').attr('onclick', 'getNext(\''+next.attr('id')+'\', 0, '+uid+', '+target+')');
	}
	if(!prevImg.attr('id')) {
		$('#gallery-prev').hide();
	} else {
		$('#gallery-prev').show();
		$('#gallery-prev').attr('onclick', 'getNext(\''+next.attr('id')+'\', 1, '+uid+', '+target+')');
	}
	resizeGallery();
}
$(function() {
	$("body").on("click", "a[rel='loadpage']", function(e) {
		
		// Get the link location that was clicked
		liveLoad($(this).attr('href'), 0, null);
		
		return false;
	});
});

// Override the back button to get the ajax content via the back content */
$(window).on('popstate', function(ev) {
	liveLoad(location.href, 0, null);
});

$.fn.scrollIntoView = function(padding, duration, easing) {	
    $('html,body').animate({
        scrollTop: this.offset().top-padding
    }, duration, easing);
    return this;
};
function startLoadingBar() {
	// only add progress bar if added yet.
	$("#loading-bar").show();
	$("#loading-bar").width((50 + Math.random() * 30) + "%");
}
function stopLoadingBar() {
	//End loading animation
	$("#loading-bar").width("101%").delay(200).fadeOut(400, function() {
		$(this).width("0");
	});
}
function liveLoad(pageurl, type, parameters) {
	// page url = request url
	// type = 1: POST; 0: GET;
	// parameters: serialized params
	cleanOldFid();
	startLoadingBar();
	
	if(type == 1) {
		var type = "POST";
	} else {
		var type = "GET";
	}
	
	// Request the page
	$.ajax({url: pageurl, type: type, data: parameters, success: function(data) {
		var result = jQuery.parseJSON(data);
		// Show the content
		$('#content').html(result.content);
		// Stop the loading bar
		stopLoadingBar();
		// Set the new title tag
		document.title = result.title;
		// Scroll the document at the top of the page
		$(document).scrollTop(0);
		// Reload functions
		reload();
	}});
	
	// Store the url to the last page accessed
	if(pageurl != window.location) {
		window.history.pushState({path:pageurl}, '', pageurl);	
	}
	return false;
}
function doMention(id, find, replace) {
	current = $('#'+id).val();
	$('#'+id).val(current.replace(new RegExp('\@'+find+'\\b'), '@'+replace+' '));
	$('#mentions-container').remove();
	$('#'+id).focus();
}