document.addEventListener('DOMContentLoaded', () => {
    const mediaBtn = document.getElementById('media-btn');
    const mediaInput = document.getElementById('media-input');
    const msgInput = document.getElementById('msg-input');

    if (mediaBtn && mediaInput) {
        mediaBtn.addEventListener('click', () => {
            mediaInput.click();
        });

        mediaInput.addEventListener('change', () => {
            const file = mediaInput.files[0];
            if (file) {
                // We can show a preview here in the future.
                // For now, just send it.
                sendMediaMessage(file);
            }
        });
    }

    function sendMediaMessage(file) {
        const message = msgInput.value.trim();
        // activeReceiverID is a global from message.js, which is not ideal but will work for now
        if (!activeReceiverID) return;

        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('message', message);
        formData.append('receiver_id', activeReceiverID);
        formData.append('media', file);

        fetch(API_PATH, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    msgInput.value = '';
                    // Reset file input
                    mediaInput.value = ''; 
                    // Reload messages to show the new media message
                    if(typeof loadMessages === 'function') {
                        loadMessages();
                    }
                } else {
                    console.error('Error sending media:', data.error);
                    alert('Error sending media: ' + data.error);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('An error occurred while sending your message.');
            });
    }
});
