function video_call_checkCall() {
	// How often there's a check for an incoming call (value in seconds)
	checkCallInterval = 10000;
	
	$.ajax({
		type: "POST",
		url: baseUrl+"/plugins/video_call/requests/check_call.php",
		data: "&token_id="+token_id,
		success: function(html) {
			response = JSON.parse(html);
			
			try {
				if(response.output) {
					video_call_modal(0, response.title, response.buttons);
					
					$('#video-call-decline-btn').attr('onclick', 'video_call_manage(3, '+response.call_id+', '+response.caller_id+')');
					$('#video-call-answer-btn').attr('onclick', 'video_call_manage(1, '+response.call_id+', '+response.type+')');
				} else {
					video_call_modal(1);
				}
			} catch(e) {
				video_call_modal(1);
			}
			plugin_video_stopCheckCall = setTimeout(video_call_checkCall, checkCallInterval);
		}
	});
}

function video_call_manage(type, call_id, caller_id) {
	/*
	@status 0 = Start call
			1 = Answer call
			2 = End Call
			3 = Decline Call
	*/
	
	if(type == 0 || type == 1) {
		$.ajax({
			type: "POST",
			async: false,
			url: baseUrl+"/plugins/video_call/requests/manage_call.php",
			data: "profile_id="+call_id+"&call_type="+caller_id+"&type="+type+"&token_id="+token_id,
			success: function(html) {
				response = JSON.parse(html);
				
				if(response.error) {
					if(type == 1) {
						video_call_modal(1);
					} else {
						$('#bc-friends-chat-'+call_id).append('<div class="message-reply-container"><div class="call-error-chat">'+response.error+' ('+(new Date).toTimeString().slice(0,5)+')</div></div>');
						$('#bc-friends-chat-'+call_id).scrollTop($('#bc-friends-chat-'+call_id+'.bc-friends-chat')[0].scrollHeight);
					}
				} else {
					window.open(baseUrl+'/plugins/video_call/requests/call.php?call_id='+response.call_id, '', 'width=640,height=480');
					if(type == 1) {
						video_call_modal(1);
					}
				}
			}
		});
	} else if(type == 2) {
	} else {
		$('#video-call-sound')[0].pause();
		
		$.ajax({
			type: "POST",
			url: baseUrl+"/plugins/video_call/requests/manage_call.php",
			data: "call_id="+call_id+"&type="+type+"&caller_id="+caller_id+"&token_id="+token_id,
			success: function(html) {
				video_call_modal(1);
			}
		});
	}
}

function video_call_modal(type, title, buttons) {
	// Type 0: Show Modal
	// Type 1: Hide Modal
	if(type) {
		$('#video-call').fadeOut();
		$('#video-call-modal-background').fadeOut();
		
		$('#video-call-sound')[0].pause();
	} else {
		$('#video-call-modal-background').fadeIn();
		$('#video-call').fadeIn();
		
		$('#video-call-type').html(title);
		$('#video-call-content').html(response.output);
		$('#m-v-c-buttons').html(response.buttons);
		
		$('#video-call-sound')[0].play();
	}
}

function video_call_answer() {
	$('#video-call-sound')[0].pause();
}

$(document).ready(function() {
	// Hide the buttons when on a mobile device & don't make a check call request
	if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
		$('.chat-video-call-btn').hide();
	} else {
		video_call_checkCall();
	}
	
	$(document).on('click', '.chat-video-call-btn', function () {
		localStorage.setItem("chat-image-uid", $(this).data("userid"));
	});
	
	$('body').append('<audio id="video-call-sound" style="display: none;" loop="loop"><source src="'+baseUrl+'/plugins/video_call/sounds/call.ogg" type="audio/ogg"><source src="'+baseUrl+'/plugins/video_call/sounds/call.mp3" type="audio/mpeg"><source src="'+baseUrl+'/plugins/video_call/sounds/call.wav" type="audio/wav"></audio>');
	
	$('body').append('<div id="video-call"><div class="modal-container"><div class="modal-inner"><div class="modal-title" id="video-call-type"></div></div><div class="message-divider"></div><div class="modal-inner"><div class="modal-desc" id="video-call-content"></div></div><div class="message-divider"></div><div class="modal-menu" id="m-v-c-buttons"></div></div></div><div id="video-call-modal-background"></div>');
	
	$('#video-call-answer-btn').on("click", function() {
		video_call_modal(1);
	});
});