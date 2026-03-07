document.getElementById('upload-photo').addEventListener('change', function(e) {
    let file = e.target.files[0];
    let formData = new FormData();
    formData.append('profile_pic', file);

    fetch('update_profile_pic.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('profile-img').src = data.new_image_url;
            alert("Updated successfully!");
        } else {
            alert("Error: " + data.message);
        }
    });
});