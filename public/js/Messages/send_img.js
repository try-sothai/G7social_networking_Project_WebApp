// បន្ថែមក្នុង attachEvents() ឬ កន្លែង Render Chat Input
document.addEventListener('DOMContentLoaded', () => {
    const icon = document.querySelector('.fa-image');
    if (icon && icon.parentElement) {
        const imageBtn = icon.parentElement;
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        imageBtn.onclick = () => fileInput.click();

        fileInput.onchange = function() {
            if (this.files && this.files[0]) {
                const receiverId = typeof activeReceiverID !== 'undefined' ? activeReceiverID : (window.chatApp ? window.chatApp.activeReceiverID : null);
                if (!receiverId) return;
                
                const formData = new FormData();
                formData.append('action', 'send');
                formData.append('receiver_id', receiverId);
                formData.append('chat_file', this.files[0]);

                fetch(typeof API_PATH !== 'undefined' ? API_PATH : '../../../app/Controllers/Messages/ChatController.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (typeof loadMessages === 'function') loadMessages();
                            else if (window.chatApp) window.chatApp.loadMessages();
                        }
                        this.value = ''; // Reset input
                    }).catch(err => console.error(err));
            }
        };
    }
});