
document.addEventListener('DOMContentLoaded', function() {
    const markAsReadButtons = document.querySelectorAll('.mark-as-read-button');
    markAsReadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const messageId = this.getAttribute('data-message-id');
            fetch('mark_message_as_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'message_id=' + messageId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.style.display = 'none';
                    // You might want to update the UI further here
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        });
    });
});

