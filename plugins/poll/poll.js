function pollVote(id) {
	// If the ID has a value
	if(id > 0) {
		// Get the selected value
		var value = $('input[name=poll-answer-'+id+']:checked').val();
		console.log('input[name=poll-answer-'+id+']:checked');
		$.ajax({
			type: "POST",
			url: baseUrl+"/plugins/poll/poll_vote.php",
			data: "id="+id+"&value="+value+"&token_id="+token_id, 
			cache: false,
			success: function(html) {
				// Output the content
				$('.poll-id-'+id).replaceWith(html);
			}
		});
	}
}
function addAnswer(text) {
	// The maximum inputs allowed
	var max_count = 10;
	
	// Get the number of inputs
	var poll_count = $('.polls-input-container').length;
	
	// Verify if the result has hit the maximum number of inputs
	if(poll_count == (max_count)) {
		$('#polls-add-answer').replaceWith("&nbsp;");
	}
	
	// Create the input option
	$('#answers-list').append('<div class="polls-input-container polls-input-remove"><input type="text" class="polls-input" placeholder="'+text+' '+(poll_count)+'" name="poll-answer[]" maxlength="64"></div>');
}
$(document).ready(function() {
	$(document).on("click", "label#polls-button", function() {
		// Hide all the current plugin divs
		$('#plugins-forms, #plugins-forms div').hide();
		$('.message-form-input, .selected-files').hide('fast');
		
		// Place the inputs
		$('#plugins-forms').append($('#answers-list'));
		$('#plugins-forms').append($('#polls-options'));
		
		// Show the inputs
		$('#answers-list, #answers-list div, #polls-options, #polls-options div').show();
		$('#plugins-forms').show('fast');
		
		// Deselect any other event type if selected
		$('#values label').addClass('selected').siblings().removeClass('selected');
		
		// Add the selected state to the button
		$('#polls-button').addClass('selected');
	});
});