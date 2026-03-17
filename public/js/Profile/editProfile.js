document.querySelector('.btn-save').addEventListener('click', function() {
    let formData = new FormData();
    formData.append('username', document.querySelector('.edit-name').value);
    formData.append('bio', document.querySelector('.bio-text').value);
    
    // បញ្ចូលរូបភាព ប្រសិនបើអ្នកប្រើបានជ្រើសរើសរូបថ្មី
    let fileInput = document.getElementById('upload-photo');
    if (fileInput.files.length > 0) {
        formData.append('profile_pic', fileInput.files[0]);
    }

    // ក្នុង public/js/Profile/editProfile.js
    fetch('../../../app/Controllers/Profile/editProfileController.php', { 
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert("កែប្រែបានជោគជ័យ!");
            window.location.reload();
        } else {
            alert("មិនអាចកែប្រែបានទេ!");
        }
    });
});

