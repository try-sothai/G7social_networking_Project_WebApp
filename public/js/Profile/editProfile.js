document.querySelector('.btn-save').addEventListener('click', function() {
    let formData = new FormData();
    formData.append('username', document.querySelector('.edit-name').value);
    formData.append('bio', document.querySelector('.bio-text').value);
    formData.append('Locations', document.querySelector('#location-input').value);
    formData.append('InterestName', document.querySelector('#interest-input').value);

    let fileInput = document.getElementById('upload-photo');
    if (fileInput.files.length > 0) {
        formData.append('profile_pic', fileInput.files[0]);
    }

    const interests = [];
    document.querySelectorAll('.interest-tag').forEach(tag => {
        interests.push(tag.textContent.trim().replace('x', ''));
    });
    formData.append('interests', JSON.stringify(interests));

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
            window.Locations.reload();
        } else {
            alert("Update failed!");
        }
    });
});

document.querySelector('.add-interest').addEventListener('click', function() {
    const input = document.getElementById('interest-input');
    const interest = input.value.trim();
    if (interest) {
        const newTag = document.createElement('button');
        newTag.className = 'interest-tag';
        newTag.textContent = interest;
        const removeSpan = document.createElement('span');
        removeSpan.className = 'remove-interest';
        removeSpan.textContent = 'x';
        newTag.appendChild(removeSpan);
        
        const interestsContainer = document.querySelector('.interests');
        interestsContainer.insertBefore(newTag, input);
        input.value = '';
    }
});

document.querySelector('.interests').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-interest')) {
        e.target.parentElement.remove();
    }
});

