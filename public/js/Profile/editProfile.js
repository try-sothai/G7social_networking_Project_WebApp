document.querySelector('.btn-save').addEventListener('click', function() {
    let formData = new FormData();
    formData.append('username', document.querySelector('.edit-name').value);
    formData.append('bio', document.querySelector('.bio-text').value);

    let fileInput = document.getElementById('upload-photo');
    if (fileInput.files.length > 0) {
        formData.append('profile_pic', fileInput.files[0]);
    }

    fetch('../../../app/Controllers/Profile/editProfileController.php', { 
        method: 'POST',
        body: formData
    })

    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (err) {
                console.error("Server error response:", text);
                throw new Error("Invalid JSON from server");
            }
        });
    })
    .then(data => {
        if(data.success) {
            alert("Updated successfully!");
            window.location.reload();
        } else {
            alert("Update failed!");
        }
    });
});

