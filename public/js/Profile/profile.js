document.addEventListener('DOMContentLoaded', () => {
    const POST_API = '../../../app/Controllers/Home/PostController.php';
    const MAX_POST_IMAGES = 4;

    const editPostModal = document.getElementById('edit-post-modal');
    const closeEditPostBtn = document.getElementById('close-edit-post');
    const editPostForm = document.getElementById('edit-post-form');
    const editPostIdInput = document.getElementById('edit-post-id');
    const editPostMediaInput = document.getElementById('edit-post-media-input');
    const editPostSelectedFileName = document.getElementById('edit-post-selected-file-name');
    const editPostPreview = document.getElementById('edit-post-preview');
    const editPostContentInput = document.getElementById('edit-post-content-input');
    const editPostModalMessage = document.getElementById('edit-post-modal-message');

    const editPostState = {
        postId: 0,
        keepMedia: [],
        newFiles: []
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function closeAllPostMenus() {
        document.querySelectorAll('.post-menu-wrap.active').forEach((node) => {
            node.classList.remove('active');
        });
    }

    function renderEditPostPreview() {
        if (!editPostPreview) return;

        const existingHtml = editPostState.keepMedia.map((media, idx) => `
            <div class="post-editor-thumb">
                <img src="${escapeHtml(media)}" alt="Current image ${idx + 1}">
                <button type="button" class="post-thumb-remove" data-role="remove-existing-image" data-index="${idx}" title="Remove image">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `);

        const newHtml = editPostState.newFiles.map((file, idx) => {
            const objectUrl = URL.createObjectURL(file);
            return `
                <div class="post-editor-thumb">
                    <img src="${escapeHtml(objectUrl)}" alt="New image ${idx + 1}">
                    <button type="button" class="post-thumb-remove" data-role="remove-new-image" data-index="${idx}" title="Remove new image">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
        });

        const all = existingHtml.concat(newHtml);
        editPostPreview.innerHTML = all.length > 0
            ? `<div class="post-editor-preview-grid">${all.join('')}</div>`
            : '<div class="empty-post-preview">No image selected.</div>';
    }

    function openEditPostModal(postId, content, mediaList) {
        if (!editPostModal) return;
        editPostState.postId = Number(postId || 0);
        editPostState.keepMedia = Array.isArray(mediaList) ? [...mediaList] : [];
        editPostState.newFiles = [];

        if (editPostIdInput) editPostIdInput.value = String(editPostState.postId);
        if (editPostContentInput) editPostContentInput.value = content || '';
        if (editPostSelectedFileName) editPostSelectedFileName.textContent = 'No new image selected';
        if (editPostModalMessage) editPostModalMessage.textContent = '';
        if (editPostMediaInput) editPostMediaInput.value = '';

        renderEditPostPreview();
        editPostModal.classList.add('active');
    }

    function closeEditPostModal() {
        if (!editPostModal) return;
        editPostModal.classList.remove('active');
        editPostState.postId = 0;
        editPostState.keepMedia = [];
        editPostState.newFiles = [];
        if (editPostForm) editPostForm.reset();
        if (editPostModalMessage) editPostModalMessage.textContent = '';
        if (editPostPreview) editPostPreview.innerHTML = '';
    }

    document.querySelectorAll('[data-role="toggle-post-menu"]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.stopPropagation();
            const wrap = btn.closest('.post-menu-wrap');
            if (!wrap) return;
            const willOpen = !wrap.classList.contains('active');
            closeAllPostMenus();
            if (willOpen) wrap.classList.add('active');
        });
    });

    document.querySelectorAll('[data-role="edit-post"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            closeAllPostMenus();
            const postId = Number(btn.getAttribute('data-post-id') || 0);
            const content = btn.getAttribute('data-content') || '';
            const mediaJson = btn.getAttribute('data-media') || '[]';
            let mediaList = [];
            try {
                const parsed = JSON.parse(mediaJson);
                mediaList = Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                mediaList = [];
            }
            openEditPostModal(postId, content, mediaList);
        });
    });

    document.querySelectorAll('[data-role="delete-post"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            closeAllPostMenus();
            const postId = Number(btn.getAttribute('data-post-id') || 0);
            if (!postId) return;
            if (!window.confirm('Delete this post?')) return;

            const payload = new FormData();
            payload.append('action', 'delete');
            payload.append('post_id', String(postId));

            fetch(POST_API, { method: 'POST', body: payload })
                .then((res) => res.json())
                .then((data) => {
                    if (data && data.status === 'success') {
                        window.location.reload();
                    } else {
                        window.alert((data && data.message) ? data.message : 'Unable to delete post.');
                    }
                })
                .catch(() => window.alert('Unable to delete post.'));
        });
    });

    if (editPostModal) {
        editPostModal.addEventListener('click', (event) => {
            if (event.target === editPostModal) closeEditPostModal();
        });
    }

    if (closeEditPostBtn) {
        closeEditPostBtn.addEventListener('click', closeEditPostModal);
    }

    if (editPostMediaInput) {
        editPostMediaInput.addEventListener('change', () => {
            const selected = Array.from(editPostMediaInput.files || []);
            const allowedSlots = Math.max(0, MAX_POST_IMAGES - editPostState.keepMedia.length);
            editPostState.newFiles = selected.slice(0, allowedSlots);

            if (editPostSelectedFileName) {
                editPostSelectedFileName.textContent = editPostState.newFiles.length > 0
                    ? `${editPostState.newFiles.length} new image${editPostState.newFiles.length > 1 ? 's' : ''} selected`
                    : 'No new image selected';
            }
            if (editPostModalMessage) {
                editPostModalMessage.textContent = selected.length > allowedSlots
                    ? `Only ${MAX_POST_IMAGES} images are allowed per post.`
                    : '';
            }
            renderEditPostPreview();
        });
    }

    if (editPostPreview) {
        editPostPreview.addEventListener('click', (event) => {
            const btn = event.target.closest('button[data-role]');
            if (!btn) return;

            const role = btn.getAttribute('data-role');
            const idx = Number(btn.getAttribute('data-index') || -1);
            if (idx < 0) return;

            if (role === 'remove-existing-image') {
                editPostState.keepMedia = editPostState.keepMedia.filter((_, i) => i !== idx);
                const allowedSlots = Math.max(0, MAX_POST_IMAGES - editPostState.keepMedia.length);
                if (editPostState.newFiles.length > allowedSlots) {
                    editPostState.newFiles = editPostState.newFiles.slice(0, allowedSlots);
                }
                renderEditPostPreview();
                return;
            }

            if (role === 'remove-new-image') {
                editPostState.newFiles = editPostState.newFiles.filter((_, i) => i !== idx);
                renderEditPostPreview();
            }
        });
    }

    if (editPostForm) {
        editPostForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const finalCount = editPostState.keepMedia.length + editPostState.newFiles.length;
            if (finalCount === 0) {
                editPostModalMessage.textContent = 'Please keep at least one image.';
                return;
            }
            if (finalCount > MAX_POST_IMAGES) {
                editPostModalMessage.textContent = `You can keep up to ${MAX_POST_IMAGES} images.`;
                return;
            }

            const submitBtn = editPostForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            editPostModalMessage.textContent = 'Saving changes...';

            const payload = new FormData();
            payload.append('action', 'update');
            payload.append('post_id', String(editPostState.postId));
            payload.append('content', (editPostContentInput && editPostContentInput.value) ? editPostContentInput.value : '');
            payload.append('keep_media', JSON.stringify(editPostState.keepMedia));
            editPostState.newFiles.forEach((file) => payload.append('new_media[]', file));

            fetch(POST_API, { method: 'POST', body: payload })
                .then((res) => res.json())
                .then((data) => {
                    if (!data || data.status !== 'success') {
                        editPostModalMessage.textContent = (data && data.message) ? data.message : 'Unable to save changes.';
                        return;
                    }
                    window.location.reload();
                })
                .catch(() => {
                    editPostModalMessage.textContent = 'Unable to save changes.';
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    }

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.post-menu-wrap')) {
            closeAllPostMenus();
        }
    });
});
