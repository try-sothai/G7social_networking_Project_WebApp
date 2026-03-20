// បន្ថែមក្នុង attachEvents() ឬ កន្លែង Render Chat Input
const imageBtn = document.querySelector('.fa-image').parentElement;
const fileInput = document.createElement('input');
fileInput.type = 'file';
fileInput.accept = 'image/*';
fileInput.style.display = 'none';
document.body.appendChild(fileInput);

imageBtn.onclick = () => fileInput.click();

fileInput.onchange = function() {
    if (this.files && this.files[0]) {
        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('receiver_id', activeReceiverID);
        formData.append('image', this.files[0]);

        fetch(API_PATH, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') loadMessages();
                this.value = ''; // Reset input
            });
    }
};