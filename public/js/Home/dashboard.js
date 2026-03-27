document.addEventListener('DOMContentLoaded', () => {
    const storyContainer = document.getElementById('stories-container');
    const openAddStoryBtn = document.getElementById('open-add-story');
    const addStoryModal = document.getElementById('add-story-modal');
    const closeAddStoryBtn = document.getElementById('close-add-story');
    const addStoryForm = document.getElementById('add-story-form');
    const storyMediaInput = document.getElementById('story-media-input');
    const selectedFileName = document.getElementById('selected-file-name');
    const storyModalMessage = document.getElementById('story-modal-message');

    const openCreatePostBtn = document.getElementById('open-create-post');
    const createPostModal = document.getElementById('create-post-modal');
    const closeCreatePostBtn = document.getElementById('close-create-post');
    const createPostForm = document.getElementById('create-post-form');
    const postMediaInput = document.getElementById('post-media-input');
    const postSelectedFileName = document.getElementById('post-selected-file-name');
    const postEditorArea = document.getElementById('post-editor-area');
    const postEditorPreview = document.getElementById('post-editor-preview');
    const postContentInput = document.getElementById('post-content-input');
    const postModalMessage = document.getElementById('post-modal-message');
    const postFeedList = document.getElementById('post-feed-list');
    const editPostModal = document.getElementById('edit-post-modal');
    const closeEditPostBtn = document.getElementById('close-edit-post');
    const editPostForm = document.getElementById('edit-post-form');
    const editPostIdInput = document.getElementById('edit-post-id');
    const editPostMediaInput = document.getElementById('edit-post-media-input');
    const editPostSelectedFileName = document.getElementById('edit-post-selected-file-name');
    const editPostPreview = document.getElementById('edit-post-preview');
    const editPostContentInput = document.getElementById('edit-post-content-input');
    const editPostModalMessage = document.getElementById('edit-post-modal-message');
    const quickStatusForm = document.getElementById('quick-status-form');
    const quickStatusInput = document.getElementById('quick-status-input');
    const quickStatusMessage = document.getElementById('quick-status-message');
    const quickStatusAddPhotoBtn = document.getElementById('quick-status-add-photo');

    const STORY_API = '../../../app/Controllers/Home/StoryController.php';
    const POST_API = '../../../app/Controllers/Home/PostController.php';
    const FALLBACK_AVATAR = 'https://i.pravatar.cc/150?u=default-user';
    const FALLBACK_STORY = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=300&q=60';
    const MAX_POST_IMAGES = 4;

    const editPostState = {
        postId: 0,
        keepMedia: [],
        newFiles: []
    };
    const viewedPostIds = new Set();
    let postViewObserver = null;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isVideo(mediaPath) {
        const raw = String(mediaPath || '').trim().toLowerCase();
        if (!raw) return false;
        if (raw.startsWith('data:video/')) return true;

        const clean = raw.split('?')[0].split('#')[0];
        return clean.endsWith('.mp4')
            || clean.endsWith('.webm')
            || clean.endsWith('.ogg')
            || clean.endsWith('.mov')
            || clean.endsWith('.m4v');
    }

    function resolveMediaUrl(mediaPath) {
        const raw = String(mediaPath || '').replace(/\\/g, '/').trim();
        if (!raw) return FALLBACK_STORY;
        if (/^(https?:)?\/\//i.test(raw) || raw.startsWith('data:')) return raw;

        // Normalize legacy DB paths like "../../../public/uploads/posts/file.mp4"
        if (raw.startsWith('../../../public/')) {
            const rootPrefix = window.location.pathname.includes('/app/')
                ? window.location.pathname.split('/app/')[0]
                : '';
            return `${rootPrefix}/public/${raw.replace('../../../public/', '')}`;
        }

        if (raw.startsWith('/public/')) {
            const rootPrefix = window.location.pathname.includes('/app/')
                ? window.location.pathname.split('/app/')[0]
                : '';
            return `${rootPrefix}${raw}`;
        }

        try {
            return new URL(raw, window.location.href).href;
        } catch (error) {
            return raw;
        }
    }

    function resolveAvatarUrl(avatarPath) {
        const raw = String(avatarPath || '').replace(/\\/g, '/').trim();
        if (!raw) return FALLBACK_AVATAR;
        if (/^(https?:)?\/\//i.test(raw) || raw.startsWith('data:')) return raw;

        if (raw.startsWith('../../../public/img/') || raw.startsWith('/')) {
            return resolveMediaUrl(raw);
        }

        return resolveMediaUrl(`../../../public/img/${raw}`);
    }

    function getPostMediaList(imagePathValue) {
        if (Array.isArray(imagePathValue)) {
            return imagePathValue.filter(Boolean).map(resolveMediaUrl);
        }

        const raw = String(imagePathValue || '').trim();
        if (!raw) return [];

        if (raw.startsWith('[') && raw.endsWith(']')) {
            try {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    return parsed.filter(Boolean).map(resolveMediaUrl);
                }
            } catch (error) {
                // fall back to single path
            }
        }

        return [resolveMediaUrl(raw)];
    }

    function formatTimeAgo(dateLike) {
        const date = new Date(dateLike);
        if (Number.isNaN(date.getTime())) return 'Just now';

        const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
        if (seconds < 60) return 'Just now';

        const mins = Math.floor(seconds / 60);
        if (mins < 60) return `${mins}m ago`;

        const hours = Math.floor(mins / 60);
        if (hours < 24) return `${hours}h ago`;

        const days = Math.floor(hours / 24);
        if (days < 7) return `${days}d ago`;

        return date.toLocaleDateString();
    }

    function addStoryCardTemplate() {
        return `
            <div class="story-card add-story" id="open-add-story">
                <div class="story-add-top">
                    <img src="${escapeHtml((typeof CURRENT_USER_AVATAR !== 'undefined' ? CURRENT_USER_AVATAR : FALLBACK_AVATAR) || FALLBACK_AVATAR)}" alt="You" class="story-add-avatar">
                </div>
                <div class="story-add-bottom">
                    <div class="add-story-icon-wrap">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <span class="story-name story-name-dark">Create Story</span>
                </div>
            </div>
        `;
    }

    function storyCardTemplate(story) {
        const media = resolveMediaUrl(story.StoryMedia || FALLBACK_STORY);
        const avatar = resolveAvatarUrl(story.ProfilePic || FALLBACK_AVATAR);
        const username = story.Username || 'User';
        const displayName = Number(story.UserID) === Number(typeof CURRENT_USER_ID !== 'undefined' ? CURRENT_USER_ID : 0)
            ? 'Your story'
            : username;
        const video = isVideo(media);

        return `
            <div class="story-card story-item"
                data-story-id="${escapeHtml(story.StoryID || '')}"
                data-media="${escapeHtml(media)}"
                data-video="${video ? '1' : '0'}"
                data-username="${escapeHtml(username)}"
                data-avatar="${escapeHtml(avatar)}"
                data-time="${escapeHtml(story.CreatedAt || '')}">
                ${video
                    ? `<video class="story-media-video" muted playsinline preload="metadata"><source src="${escapeHtml(media)}"></video>`
                    : `<img class="story-media-img" src="${escapeHtml(media)}" alt="${escapeHtml(username)} story" onerror="this.src='${FALLBACK_STORY}'">`}
                <img src="${escapeHtml(avatar)}" class="story-avatar" alt="${escapeHtml(username)}" onerror="this.src='${FALLBACK_AVATAR}'">
                <span class="story-name story-name-light">${escapeHtml(displayName)}</span>
            </div>
        `;
    }

    function bindStoryEvents() {
        const addStoryCard = document.getElementById('open-add-story');
        if (addStoryCard) {
            addStoryCard.onclick = () => {
                addStoryModal.classList.add('active');
                storyModalMessage.textContent = '';
            };
        }

        document.querySelectorAll('.story-item').forEach((item) => {
            item.onclick = () => {
                const storyId = item.dataset.storyId || '';
                if (storyId) {
                    window.location.href = `story_dashboard.php?story_id=${encodeURIComponent(storyId)}`;
                }
            };
        });
    }

    function sortStoriesForDisplay(stories) {
        const currentUserId = Number(typeof CURRENT_USER_ID !== 'undefined' ? CURRENT_USER_ID : 0);
        return [...stories].sort((a, b) => {
            const aMine = Number(a.UserID) === currentUserId ? 1 : 0;
            const bMine = Number(b.UserID) === currentUserId ? 1 : 0;
            if (aMine !== bMine) return bMine - aMine;
            return new Date(b.CreatedAt) - new Date(a.CreatedAt);
        });
    }

    function latestStoryPerUser(stories) {
        const seen = new Set();
        const newestFirst = [...stories].sort((a, b) => new Date(b.CreatedAt) - new Date(a.CreatedAt));
        return newestFirst.filter((story) => {
            const uid = Number(story.UserID || 0);
            if (seen.has(uid)) return false;
            seen.add(uid);
            return true;
        });
    }

    function renderStories(stories) {
        const uniqueLatestStories = latestStoryPerUser(Array.isArray(stories) ? stories : []);
        const orderedStories = sortStoriesForDisplay(uniqueLatestStories);
        const cards = [addStoryCardTemplate()];
        if (orderedStories.length > 0) {
            orderedStories.forEach((story) => cards.push(storyCardTemplate(story)));
        }
        storyContainer.innerHTML = cards.join('');
        bindStoryEvents();
    }

    function loadStories() {
        fetch(`${STORY_API}?action=fetch`)
            .then((res) => res.json())
            .then((data) => {
                if (data.status === 'success') {
                    renderStories(data.stories || []);
                }
            })
            .catch(() => {
                renderStories([]);
            });
    }

    function postCardTemplate(post) {
        const likesCount = post.LikesCount || 0;
        const commentsCount = post.CommentsCount || 0;
        const sharesCount = post.SharesCount || 0;
        const viewsCount = post.ViewsCount || 0;
        const isLiked = post.IsLiked ? 'liked' : '';
        const likeIcon = post.IsLiked ? 'fa-solid fa-thumbs-up' : 'fa-regular fa-thumbs-up';
        const likeColor = post.IsLiked ? 'color: var(--primary-color);' : '';
        const commentsHtml = (post.Comments && post.Comments.length > 0)
            ? post.Comments.map(c => `<div><strong>${escapeHtml(c.Username)}</strong> ${escapeHtml(c.CommentText)}</div>`).join('')
            : 'No comments yet.';

        const mediaList = getPostMediaList(post.ImagePath || '');
        const avatar = resolveAvatarUrl(post.ProfilePic || FALLBACK_AVATAR);
        const username = post.Username || 'User';
        const content = post.Content || '';
        const createdAt = formatTimeAgo(post.CreatedAt || '');
        const isOwner = Number(post.UserID || 0) === Number(typeof CURRENT_USER_ID !== 'undefined' ? CURRENT_USER_ID : 0);
        const mediaHtml = mediaList.length > 1
            ? `<div class="post-media-grid">${mediaList.map((media, index) => (
                isVideo(media)
                    ? `<video class="post-image post-image-grid" controls preload="metadata"><source src="${escapeHtml(media)}"></video>`
                    : `<img src="${escapeHtml(media)}" class="post-image post-image-grid" alt="Post media ${index + 1}">`
            )).join('')}</div>`
            : (() => {
                const media = mediaList[0] || '';
                if (!media) return '';
                return isVideo(media)
                    ? `<video class="post-image" controls preload="metadata"><source src="${escapeHtml(media)}"></video>`
                    : `<img src="${escapeHtml(media)}" class="post-image" alt="Post media">`;
            })();

        return `
            <div class="post-card" data-post-id="${escapeHtml(post.PostID || '')}">
                <div class="post-header">
                    <div class="post-user">
                        <a href="../Profile/profile.php" class="post-user-link" aria-label="Open profile">
                            <img src="${escapeHtml(avatar)}" alt="${escapeHtml(username)}" onerror="this.src='${FALLBACK_AVATAR}'">
                        </a>
                        <div>
                            <a href="../Profile/profile.php" class="post-user-link">
                                <h4 style="font-weight: 800;">${escapeHtml(username)}</h4>
                            </a>
                            <span class="post-time">${escapeHtml(createdAt)}</span>
                        </div>
                    </div>
                    ${isOwner ? `
                    <div class="post-menu-wrap">
                        <button class="post-menu-btn" type="button" data-role="toggle-post-menu" aria-label="Post options">
                            <i class="fa-solid fa-ellipsis-h text"></i>
                        </button>
                        <div class="post-menu-dropdown">
                            <button type="button" class="post-menu-item" data-role="edit-post" data-post-id="${escapeHtml(post.PostID || '')}" data-content="${escapeHtml(content)}" data-media='${escapeHtml(JSON.stringify(mediaList))}'>Edit Post</button>
                            <button type="button" class="post-menu-item danger" data-role="delete-post" data-post-id="${escapeHtml(post.PostID || '')}">Delete Post</button>
                        </div>
                    </div>
                    ` : ''}
                </div>
                ${content ? `<div class="post-content">${escapeHtml(content)}</div>` : ''}
                ${mediaHtml}
                <div class="post-actions-stats" style="display:flex; justify-content:space-between; color:var(--text-muted, #65676b); font-size:13px; margin-bottom:10px; padding: 0 5px;">
                    <div>${likesCount} Likes</div>
                    <div>${viewsCount} Views &nbsp;&bull;&nbsp; ${commentsCount} Comments &nbsp;&bull;&nbsp; ${sharesCount} Shares</div>
                </div>
                <div class="post-actions">
                    <div class="action-item ${isLiked}" data-role="like-post" data-post-id="${escapeHtml(post.PostID || '')}" style="${likeColor}"><i class="${likeIcon}"></i> Like</div>
                    <div class="action-item" data-role="comment-post" data-post-id="${escapeHtml(post.PostID || '')}"><i class="fa-regular fa-comment"></i> Comment</div>
                    <div class="action-item" data-role="share-post" data-post-id="${escapeHtml(post.PostID || '')}"><i class="fa-regular fa-share-from-square"></i> Share</div>
                </div>
                <div class="post-comments-area" id="comments-area-${escapeHtml(post.PostID || '')}" style="display:none; margin-top: 15px; border-top: 1px solid #f0f2f5; padding-top: 15px;">
                    <div class="comments-list" style="max-height: 250px; overflow-y: auto; margin-bottom: 15px;">${commentsHtml}</div>
                    <div style="display:flex; gap: 10px;">
                        <img src="${escapeHtml(typeof CURRENT_USER_AVATAR !== 'undefined' ? CURRENT_USER_AVATAR : FALLBACK_AVATAR)}" style="width: 32px; height: 32px; border-radius: 50%;">
                        <input type="text" id="comment-input-${escapeHtml(post.PostID || '')}" placeholder="Write a comment..." style="flex:1; border-radius: 20px; border: 1px solid #ccd0d5; padding: 8px 15px; outline: none; background: #f0f2f5;">
                        <button type="button" data-role="submit-comment" data-post-id="${escapeHtml(post.PostID || '')}" style="background: transparent; border: none; color: #1877f2; font-weight: bold; cursor: pointer;">Post</button>
                    </div>
                </div>
            </div>
        `;
    }

    function renderPosts(posts) {
        if (!postFeedList) return;
        if (!Array.isArray(posts) || posts.length === 0) {
            postFeedList.innerHTML = '<div class="post-card empty-posts">No posts yet. Create your first photo or video post.</div>';
            return;
        }
        postFeedList.innerHTML = posts.map(postCardTemplate).join('');
        bindPostEvents();
        bindPostViewTracking();
    }

    function sendPostView(postId) {
        const id = Number(postId || 0);
        if (!id || viewedPostIds.has(id)) return;
        viewedPostIds.add(id);

        const payload = new FormData();
        payload.append('action', 'view');
        payload.append('post_id', String(id));

        fetch(POST_API, {
            method: 'POST',
            body: payload
        }).catch(() => {});
    }

    function bindPostViewTracking() {
        const cards = document.querySelectorAll('.post-card[data-post-id]');
        if (cards.length === 0) return;

        if (postViewObserver) {
            postViewObserver.disconnect();
        }

        postViewObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting || entry.intersectionRatio < 0.55) return;
                const postId = entry.target.getAttribute('data-post-id');
                sendPostView(postId);
            });
        }, { threshold: [0.55] });

        cards.forEach((card) => postViewObserver.observe(card));
    }

    function loadPosts() {
        fetch(`${POST_API}?action=fetch`)
            .then((res) => res.json())
            .then((data) => {
                if (data.status === 'success') {
                    renderPosts(data.posts || []);
                } else {
                console.error("Server Error:", data.message);
                if (postFeedList) postFeedList.innerHTML = `<div class="post-card empty-posts" style="color: red;">Error: ${data.message}</div>`;
                }
            })
        .catch((err) => {
            console.error("Fetch Error:", err);
            if (postFeedList) postFeedList.innerHTML = `<div class="post-card empty-posts" style="color: red;">Failed to load posts. Please check console.</div>`;
        });
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
                ${isVideo(media)
                    ? `<video controls preload="metadata"><source src="${escapeHtml(media)}"></video>`
                    : `<img src="${escapeHtml(media)}" alt="Current media ${idx + 1}">`
                }
                <button type="button" class="post-thumb-remove" data-role="remove-existing-image" data-index="${idx}" title="Remove image">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `);

        const newHtml = editPostState.newFiles.map((file, idx) => {
            const objectUrl = URL.createObjectURL(file);
            return `
                <div class="post-editor-thumb">
                    ${file.type.startsWith('video/')
                        ? `<video controls preload="metadata"><source src="${escapeHtml(objectUrl)}"></video>`
                        : `<img src="${escapeHtml(objectUrl)}" alt="New media ${idx + 1}">`
                    }
                    <button type="button" class="post-thumb-remove" data-role="remove-new-image" data-index="${idx}" title="Remove new image">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
        });

        const all = existingHtml.concat(newHtml);
        editPostPreview.innerHTML = all.length > 0
            ? `<div class="post-editor-preview-grid">${all.join('')}</div>`
            : '<div class="empty-post-preview">No media selected.</div>';
    }

    function openEditPostModal(postId, content, mediaList) {
        if (!editPostModal) return;
        editPostState.postId = Number(postId || 0);
        editPostState.keepMedia = Array.isArray(mediaList) ? [...mediaList] : [];
        editPostState.newFiles = [];

        if (editPostIdInput) editPostIdInput.value = String(editPostState.postId);
        if (editPostContentInput) editPostContentInput.value = content || '';
        if (editPostSelectedFileName) editPostSelectedFileName.textContent = 'No new media selected';
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

    function bindPostEvents() {
        document.querySelectorAll('[data-role="toggle-post-menu"]').forEach((btn) => {
            btn.onclick = (event) => {
                event.stopPropagation();
                const wrap = btn.closest('.post-menu-wrap');
                if (!wrap) return;
                const willOpen = !wrap.classList.contains('active');
                closeAllPostMenus();
                if (willOpen) {
                    wrap.classList.add('active');
                }
            };
        });

        document.querySelectorAll('[data-role="edit-post"]').forEach((btn) => {
            btn.onclick = () => {
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
            };
        });

        document.querySelectorAll('[data-role="delete-post"]').forEach((btn) => {
            btn.onclick = () => {
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
                            loadPosts();
                        } else {
                            window.alert((data && data.message) ? data.message : 'Unable to delete post.');
                        }
                    })
                    .catch(() => window.alert('Unable to delete post.'));
            };
        });

        document.querySelectorAll('[data-role="like-post"]').forEach((btn) => {
            btn.onclick = () => {
                const postId = btn.getAttribute('data-post-id');
                const payload = new FormData();
                payload.append('action', 'like');
                payload.append('post_id', postId);
                fetch(POST_API, { method: 'POST', body: payload }).then(res=>res.json()).then(data => {
                    if(data.status === 'success') loadPosts();
                });
            };
        });

        document.querySelectorAll('[data-role="comment-post"]').forEach((btn) => {
            btn.onclick = () => {
                const postId = btn.getAttribute('data-post-id');
                const area = document.getElementById('comments-area-' + postId);
                if(area) area.style.display = area.style.display === 'none' ? 'block' : 'none';
            };
        });

        document.querySelectorAll('[data-role="submit-comment"]').forEach((btn) => {
            btn.onclick = () => {
                const postId = btn.getAttribute('data-post-id');
                const input = document.getElementById('comment-input-' + postId);
                if(!input || !input.value.trim()) return;
                
                const payload = new FormData();
                payload.append('action', 'comment');
                payload.append('post_id', postId);
                payload.append('comment', input.value);
                fetch(POST_API, { method: 'POST', body: payload }).then(res=>res.json()).then(data => {
                    if(data.status === 'success') loadPosts();
                });
            };
        });

        document.querySelectorAll('[data-role="share-post"]').forEach((btn) => {
            btn.onclick = () => {
                if(!window.confirm('Share this post to your profile?')) return;
                const postId = btn.getAttribute('data-post-id');
                const payload = new FormData();
                payload.append('action', 'share');
                payload.append('post_id', postId);
                fetch(POST_API, { method: 'POST', body: payload }).then(res=>res.json()).then(data => {
                    if(data.status === 'success') loadPosts();
                });
            };
        });
    }

    function resetPostComposer() {
        if (!createPostForm) return;
        createPostForm.reset();
        if (postSelectedFileName) postSelectedFileName.textContent = 'No file chosen';
        if (postModalMessage) postModalMessage.textContent = '';
        if (postEditorPreview) postEditorPreview.innerHTML = '';
        if (postEditorArea) postEditorArea.classList.remove('active');
    }

    function openPostComposer() {
        if (!createPostModal) return;
        resetPostComposer();
        createPostModal.classList.add('active');
    }

    function closePostComposer() {
        if (!createPostModal) return;
        createPostModal.classList.remove('active');
        resetPostComposer();
    }

    closeAddStoryBtn.onclick = () => addStoryModal.classList.remove('active');

    addStoryModal.onclick = (event) => {
        if (event.target === addStoryModal) addStoryModal.classList.remove('active');
    };

    storyMediaInput.addEventListener('change', () => {
        const file = storyMediaInput.files && storyMediaInput.files[0];
        selectedFileName.textContent = file ? file.name : 'No file chosen';
    });

    addStoryForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const file = storyMediaInput.files && storyMediaInput.files[0];
        if (!file) {
            storyModalMessage.textContent = 'Please choose a file first.';
            return;
        }

        storyModalMessage.textContent = '';
        addStoryForm.setAttribute('action', 'story_editor.php');
        addStoryForm.setAttribute('method', 'post');
        addStoryForm.submit();
    });

    if (openAddStoryBtn) {
        openAddStoryBtn.onclick = () => addStoryModal.classList.add('active');
    }

    if (openCreatePostBtn) {
        openCreatePostBtn.addEventListener('click', openPostComposer);
    }

    if (quickStatusAddPhotoBtn) {
        quickStatusAddPhotoBtn.addEventListener('click', openPostComposer);
    }

    if (closeCreatePostBtn) {
        closeCreatePostBtn.addEventListener('click', closePostComposer);
    }

    if (createPostModal) {
        createPostModal.addEventListener('click', (event) => {
            if (event.target === createPostModal) closePostComposer();
        });
    }

    if (postMediaInput) {
        postMediaInput.addEventListener('change', () => {
            const files = Array.from(postMediaInput.files || []);
            postSelectedFileName.textContent = files.length > 0
                ? `${files.length} media file${files.length > 1 ? 's' : ''} selected`
                : 'No file chosen';
            postModalMessage.textContent = '';
            postEditorPreview.innerHTML = '';

            if (files.length === 0) {
                postEditorArea.classList.remove('active');
                return;
            }

            const previews = [];
            files.forEach((file, idx) => {
                const objectUrl = URL.createObjectURL(file);
                previews.push(`
                    <div class="post-editor-thumb">
                        ${file.type.startsWith('video/')
                            ? `<video controls preload="metadata"><source src="${escapeHtml(objectUrl)}"></video>`
                            : `<img src="${escapeHtml(objectUrl)}" alt="Preview ${idx + 1}">`
                        }
                    </div>
                `);
            });

            postEditorPreview.innerHTML = `<div class="post-editor-preview-grid">${previews.join('')}</div>`;
            if (files.length > 4) {
                postModalMessage.textContent = 'You can upload up to 4 media files per post.';
            }
            postEditorArea.classList.add('active');
        });
    }

    if (createPostForm) {
        createPostForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const files = Array.from(postMediaInput.files || []);
            if (files.length === 0) {
                postModalMessage.textContent = 'Please choose at least one media file first.';
                return;
            }
            if (files.length > 4) {
                postModalMessage.textContent = 'You can upload up to 4 media files per post.';
                return;
            }

            const submitBtn = createPostForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            postModalMessage.textContent = 'Posting...';

            const payload = new FormData();
            payload.append('action', 'create');
            files.forEach((file) => payload.append('post_media[]', file));
            payload.append('content', (postContentInput && postContentInput.value) ? postContentInput.value : '');

            fetch(POST_API, {
                method: 'POST',
                body: payload
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data || data.status !== 'success') {
                        postModalMessage.textContent = (data && data.message) ? data.message : 'Unable to post right now.';
                        return;
                    }

                    postModalMessage.textContent = 'Posted successfully.';
                    closePostComposer();
                    loadPosts();
                })
                .catch(() => {
                    postModalMessage.textContent = 'Unable to post right now.';
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    }

    if (quickStatusForm) {
        quickStatusForm.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!quickStatusInput) return;

            const content = quickStatusInput.value.trim();
            if (!content) {
                if (quickStatusMessage) quickStatusMessage.textContent = 'Please write something first.';
                quickStatusInput.focus();
                return;
            }

            const submitBtn = quickStatusForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            if (quickStatusMessage) quickStatusMessage.textContent = 'Posting...';

            const payload = new FormData();
            payload.append('action', 'create');
            payload.append('content', content);

            fetch(POST_API, {
                method: 'POST',
                body: payload
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data || data.status !== 'success') {
                        if (quickStatusMessage) {
                            quickStatusMessage.textContent = (data && data.message) ? data.message : 'Unable to post status right now.';
                        }
                        return;
                    }

                    quickStatusInput.value = '';
                    if (quickStatusMessage) quickStatusMessage.textContent = 'Status posted successfully.';
                    loadPosts();
                })
                .catch(() => {
                    if (quickStatusMessage) quickStatusMessage.textContent = 'Unable to post status right now.';
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    }

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
                    ? `${editPostState.newFiles.length} new media file${editPostState.newFiles.length > 1 ? 's' : ''} selected`
                    : 'No new media selected';
            }
            if (editPostModalMessage) {
                editPostModalMessage.textContent = selected.length > allowedSlots
                    ? `Only ${MAX_POST_IMAGES} media files are allowed per post.`
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
            const editContent = (editPostContentInput && editPostContentInput.value) ? editPostContentInput.value.trim() : '';
            if (finalCount === 0 && !editContent) {
                editPostModalMessage.textContent = 'Please keep at least one media file or write some text.';
                return;
            }
            if (finalCount > MAX_POST_IMAGES) {
                editPostModalMessage.textContent = `You can keep up to ${MAX_POST_IMAGES} media files.`;
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

            fetch(POST_API, {
                method: 'POST',
                body: payload
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data || data.status !== 'success') {
                        editPostModalMessage.textContent = (data && data.message) ? data.message : 'Unable to save changes.';
                        return;
                    }
                    closeEditPostModal();
                    loadPosts();
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

    loadStories();
    loadPosts();
    setInterval(loadStories, 15000);
    setInterval(loadPosts, 30000);
});
