$('.mark-as-read-button').click(function() {
    var messageId = $(this).data('message-id');
    $.ajax({
        type: 'POST',
        url: 'mark_as_read.php',
        data: {message_id: messageId},
        success: function(response) {
            console.log(response);
        }
    });
});
