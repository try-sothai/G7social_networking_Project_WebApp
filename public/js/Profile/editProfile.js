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
        interests.push(tag.textContent.trim());
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
        newTag.type = 'button';
        newTag.className = 'interest-tag';
        newTag.innerHTML = `${interest} <span class="remove-interest"><i class="fa-solid fa-xmark"></i></span>`;
        
        const interestsContainer = document.querySelector('.interests');
        const wrapper = document.querySelector('.add-interest-wrapper');
        interestsContainer.insertBefore(newTag, wrapper);
        input.value = '';
    }
});

// Support Enter key to add interest quickly
document.getElementById('interest-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.querySelector('.add-interest').click();
    }
});

document.querySelector('.interests').addEventListener('click', function(e) {
    const removeBtn = e.target.closest('.remove-interest');
    if (removeBtn) {
        removeBtn.parentElement.remove();
    }
});
