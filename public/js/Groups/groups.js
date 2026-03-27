document.addEventListener('DOMContentLoaded', () => {
    const openCreatePostBtn = document.getElementById('open-create-post');
    const closeCreatePostBtn = document.getElementById('close-create-post');
    const createPostModal = document.getElementById('create-post-modal');
    const createPostForm = document.getElementById('create-post-form');
    const postMediaInput = document.getElementById('post-media-input');
    const postPreviewContainer = document.getElementById('post-editor-preview');
    const postSelectedFileName = document.getElementById('post-selected-file-name');
    const postModalMessage = document.getElementById('post-modal-message');

    // --- Modal Handling ---
    if (openCreatePostBtn && createPostModal) {
        openCreatePostBtn.addEventListener('click', () => {
            createPostModal.classList.add('active');
        });
    }

    if (closeCreatePostBtn && createPostModal) {
        closeCreatePostBtn.addEventListener('click', () => {
            createPostModal.classList.remove('active');
        });
    }

    // Close modal if clicking outside the card
    if (createPostModal) {
        createPostModal.addEventListener('click', (e) => {
            if (e.target === createPostModal) {
                createPostModal.classList.remove('active');
            }
        });
    }
    
    // --- Image Preview ---
    if (postMediaInput && postPreviewContainer && postSelectedFileName) {
        postMediaInput.addEventListener('change', () => {
            const files = postMediaInput.files;
            postPreviewContainer.innerHTML = ''; // Clear existing previews
            
            if (files.length > 0) {
                postSelectedFileName.textContent = `${files.length} file(s) selected`;
                
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('post-preview-image');
                        postPreviewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
                postPreviewContainer.parentElement.classList.add('active');
            } else {
                postSelectedFileName.textContent = 'No file chosen';
                postPreviewContainer.parentElement.classList.remove('active');
            }
        });
    }

    // --- Form Submission ---
    if (createPostForm) {
        createPostForm.addEventListener('submit', (e) => {
            e.preventDefault();
            postModalMessage.textContent = '';
            const formData = new FormData(createPostForm);
            formData.append('action', 'create');

            const submitBtn = createPostForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Posting...';
            
            fetch('../../Controllers/Home/PostController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    postModalMessage.textContent = 'Post created successfully!';
                    postModalMessage.style.color = 'green';
                    setTimeout(() => {
                        createPostModal.classList.remove('active');
                        // Optionally, refresh the feed or add the new post dynamically
                        location.reload(); 
                    }, 1500);
                } else {
                    postModalMessage.textContent = data.message || 'An error occurred.';
                    postModalMessage.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                postModalMessage.textContent = 'A network error occurred. Please try again.';
                postModalMessage.style.color = 'red';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Post to Website';
            });
        });
    }
});
