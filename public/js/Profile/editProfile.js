document.querySelector('.btn-save').addEventListener('click', function() {
    let formData = new FormData();
    formData.append('username', document.querySelector('.edit-name').value);
    formData.append('bio', document.querySelector('.bio-text').value);
    
    // បញ្ចូលរូបភាព ប្រសិនបើអ្នកប្រើបានជ្រើសរើសរូបថ្មី
    let fileInput = document.getElementById('upload-photo');
    if (fileInput.files.length > 0) {
        formData.append('profile_pic', fileInput.files[0]);
    }

    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert("Profile updated successfully!");
            if(data.image_url) {
                document.getElementById('profile-img').src = data.image_url;
            }
        } else {
            alert("Update failed!");
        }
    });
});