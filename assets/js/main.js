jQuery(document).ready(function($) {
    let selectedOptions = {};

    // Voting functionality
    $('.option_answer').on('click', function() {
        var poll_id = $(this).data('poll-id');
        var option_index = $(this).data('option-index');

        if (selectedOptions[poll_id] !== undefined) {
            // De-select previously selected option
            $('.option_answer[data-poll-id="' + poll_id + '"][data-option-index="' + selectedOptions[poll_id] + '"]')
                .removeClass('selected');
        }

        // Mark the new option as selected
        $(this).addClass('selected');
        selectedOptions[poll_id] = option_index;

        $.ajax({
            url: mpp_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'mpp_vote',
                poll_id: poll_id,
                option_index: option_index,
                mpp_vote_nonce: mpp_vars.mpp_vote_nonce  // Pass the nonce for security
            },
            success: function(response) {
                if (response.success) {
                    // Update the UI with vote percentages and total votes
                    $('.option_answer[data-poll-id="' + poll_id + '"]').each(function() {
                        var $optionAnswer = $(this);
                        var $progressBar = $optionAnswer.find('.progress');
                        var $totalVote = $optionAnswer.find('.total_vote span');

                        var index = $optionAnswer.data('option-index');
                        var percent = response.data.votes_percent[index]; // Use percent from server
                        percent = Math.round(percent * 100) / 100; // Round to 2 decimal places

                        $progressBar.attr('data-percent', percent);
                        $progressBar.css('width', percent + '%');
                        $progressBar.find('span').css('width', percent + '%').text(Math.round(percent) + '%');

                        $totalVote.text(response.data.votes[index]);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error: ' + status + ' - ' + error);
                alert('An error occurred while submitting your vote.');
            }
        });
    });

    // Animate bar heights
    $("#bars li .bar").each(function(key, bar){
        var percentage = $(this).data('percentage');
        $(this).animate({
            'height': percentage + '%'
        }, 1000);
    });
});
