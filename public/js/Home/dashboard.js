document.addEventListener('DOMContentLoaded', () => {
    const storyContainer = document.getElementById('stories-container');
    const openAddStoryBtn = document.getElementById('open-add-story');
    const addStoryModal = document.getElementById('add-story-modal');
    const closeAddStoryBtn = document.getElementById('close-add-story');
    const addStoryForm = document.getElementById('add-story-form');
    const storyMediaInput = document.getElementById('story-media-input');
    const selectedFileName = document.getElementById('selected-file-name');
    const storyModalMessage = document.getElementById('story-modal-message');

    const createPostTriggers = Array.from(document.querySelectorAll('[data-open-create-post], .js-open-create-post'));
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
    const headerSearchInput = document.getElementById('header-search-input');
    const hasStoryRail = Boolean(storyContainer && addStoryModal);
    const hasCreatePostComposer = Boolean(
        createPostModal
        && createPostForm
        && postMediaInput
        && postSelectedFileName
        && postEditorArea
        && postEditorPreview
        && postModalMessage
    );

    const APP_ROOT = window.location.pathname.includes('/app/')
        ? window.location.pathname.split('/app/')[0]
        : '';
    const STORY_API = `${APP_ROOT}/app/Controllers/Home/StoryController.php`;
    const POST_API = `${APP_ROOT}/app/Controllers/Home/PostController.php`;
    const FALLBACK_AVATAR = 'https://ui-avatars.com/api/?background=cbd5e1&color=1e293b&name=User';
    const FALLBACK_STORY = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=300&q=60';
    const MAX_POST_IMAGES = 4;

    const editPostState = {
        postId: 0,
        keepMedia: [],
        newFiles: []
    };
    const viewedPostIds = new Set();
    let postViewObserver = null;
    let postVideoObserver = null;

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
        if (!raw) {
            return FALLBACK_STORY;
        }
        // If it's already a full URL or a data URI, return it as is.
        if (raw.startsWith('http') || raw.startsWith('data:')) {
            return raw;
        }

        // For relative paths like "../../../public/uploads/...",
        // use the URL constructor to resolve it against the current page's location.
        // This is the most reliable way to get the correct full URL.
        try {
            return new URL(raw, window.location.href).href;
        } catch (e) {
            // If constructor fails, return the raw path as a fallback.
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
        if (!hasStoryRail) return;

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
        if (!storyContainer) return;
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
        if (!hasStoryRail) return;
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
        const likeIcon = post.IsLiked ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
        const commentsHtml = (post.Comments && post.Comments.length > 0)
            ? post.Comments.map(c => {
                const cAvatar = resolveAvatarUrl(c.ProfilePic || FALLBACK_AVATAR);
                return `<div style="display: flex; gap: 10px; margin-bottom: 12px;">
                    <a href="../Profile/profile.php?user_id=${escapeHtml(c.UserID || '')}"><img src="${escapeHtml(cAvatar)}" alt="${escapeHtml(c.Username)}" onerror="this.src='${FALLBACK_AVATAR}'" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05);"></a>
                    <div style="background: var(--bg-hover, #f1f5f9); padding: 8px 14px; border-radius: 18px; font-size: 13.5px; max-width: calc(100% - 45px);">
                        <a href="../Profile/profile.php?user_id=${escapeHtml(c.UserID || '')}" style="text-decoration: none;"><strong style="display: block; margin-bottom: 2px; color: var(--text-primary, #1e293b);">${escapeHtml(c.Username)}</strong></a>
                        <span style="color: var(--text-secondary, #334155); word-break: break-word; line-height: 1.4;">${escapeHtml(c.CommentText)}</span>
                    </div>
                </div>`;
            }).join('')
            : '<div style="color: #64748b; font-size: 14px; text-align: center; padding: 10px 0;">No comments yet.</div>';

        const mediaList = getPostMediaList(post.ImagePath || '');
        const avatar = resolveAvatarUrl(post.ProfilePic || FALLBACK_AVATAR);
        const username = post.Username || 'User';
        let content = post.Content || '';
        const createdAt = formatTimeAgo(post.CreatedAt || '');
        const isOwner = Number(post.UserID || 0) === Number(typeof CURRENT_USER_ID !== 'undefined' ? CURRENT_USER_ID : 0);
        
        let isShare = false;
        let shareData = null;

        if (content.startsWith('[SHARED_POST]')) {
            isShare = true;
            try {
                shareData = JSON.parse(content.substring(13));
                content = ''; // លុប Content ខាងក្រៅចេញ
            } catch(e) {
                isShare = false;
            }
        }
        
        let mediaHtml = mediaList.length > 1
            ? `<div class="post-media-grid">${mediaList.map((media, index) => (
                isVideo(media)
                    ? `<video class="post-image post-image-grid" muted playsinline loop preload="metadata" style="cursor: pointer;" onclick="openLightbox('${escapeHtml(media)}', true)"><source src="${escapeHtml(media)}"></video>`
                    : `<img src="${escapeHtml(media)}" class="post-image post-image-grid" alt="Post media ${index + 1}" style="cursor: pointer;" onclick="openLightbox('${escapeHtml(media)}', false)">`
            )).join('')}</div>`
            : (() => {
                const media = mediaList[0] || '';
                if (!media) return '';
                return isVideo(media)
                    ? `<video class="post-image" muted playsinline loop preload="metadata" style="cursor: pointer;" onclick="openLightbox('${escapeHtml(media)}', true)"><source src="${escapeHtml(media)}"></video>`
                    : `<img src="${escapeHtml(media)}" class="post-image" alt="Post media" style="cursor: pointer;" onclick="openLightbox('${escapeHtml(media)}', false)">`;
            })();
            
        let sharedHtml = '';
        if (isShare && shareData) {
            const originalUser = shareData.original_user || 'Original user';
            const origAvatar = resolveAvatarUrl(shareData.original_avatar || '');
            sharedHtml = `
                <div class="shared-post-card" style="margin: 0 20px 15px 20px; border-radius: 16px; border: 1px solid var(--border-color, #e2e8f0); background: var(--background-secondary, #f8fafc); overflow: hidden;">
                    <div class="shared-post-header">
                        <img src="${escapeHtml(origAvatar)}" alt="${escapeHtml(originalUser)}" onerror="this.src='${FALLBACK_AVATAR}'" style="width: 36px; height: 36px; border-radius: 12px; object-fit: cover;">
                        <div>
                            <h4 style="font-weight: 700; font-size: 14px; margin: 0;">${escapeHtml(originalUser)}</h4>
                            <span class="post-time" style="font-size: 11px;">Original Post</span>
                        </div>
                    </div>
                    ${shareData.original_content ? `<div class="post-content" style="padding: 12px 15px; margin-bottom: 0;">${escapeHtml(shareData.original_content)}</div>` : ''}
                    ${mediaHtml}
                </div>
            `;
            mediaHtml = ''; // លុបរូបខាងក្រៅចេញ ដើម្បីឱ្យវាបង្ហាញតែខាងក្នុងកាត Share
        }

        return `
            <div class="post-card" data-post-id="${escapeHtml(post.PostID || '')}" data-post-id-alt="${escapeHtml(post.id || '')}">
                <div class="post-header">
                    <div class="post-user">
                        <a href="../Profile/profile.php?user_id=${escapeHtml(post.UserID || '')}" class="post-user-link" aria-label="Open profile">
                            <img src="${escapeHtml(avatar)}" alt="${escapeHtml(username)}" onerror="this.src='${FALLBACK_AVATAR}'" style="width: 48px; height: 48px; border-radius: 16px; object-fit: cover;">
                        </a>
                        <div>
                            <a href="../Profile/profile.php?user_id=${escapeHtml(post.UserID || '')}" class="post-user-link" style="display: flex; align-items: center; gap: 5px;">
                                <h4 style="font-weight: 800;">${escapeHtml(username)}</h4>
                                ${isShare ? `<span style="color: #64748b; font-weight: 500; font-size: 13px;"><i class="fa-solid fa-share" style="font-size: 11px; margin-right: 4px;"></i>shared a post</span>` : ''}
                            </a>
                            <span class="post-time">${escapeHtml(createdAt)}</span>
                        </div>
                    </div>
                    <div class="post-menu-wrap">
                        <button class="post-menu-btn" type="button" data-role="toggle-post-menu" aria-label="Post options">
                            <i class="fa-solid fa-ellipsis-h text"></i>
                        </button>
                        <div class="post-menu-dropdown">
                            ${isOwner && !isShare ? `<button type="button" class="post-menu-item" data-role="edit-post" data-post-id="${escapeHtml(post.PostID || '')}" data-content="${escapeHtml(content)}" data-media='${escapeHtml(JSON.stringify(mediaList))}'>Edit Post</button>` : ''}
                            ${isOwner ? `<button type="button" class="post-menu-item danger" data-role="delete-post" data-post-id="${escapeHtml(post.PostID || '')}">Delete Post</button>` : ''}
                            ${!isOwner ? `<button type="button" class="post-menu-item" data-role="report-post" data-post-id="${escapeHtml(post.PostID || '')}">Report Post</button>` : ''}
                        </div>
                    </div>
                </div>
                ${content ? `<div class="post-content">${escapeHtml(content)}</div>` : ''}
                ${sharedHtml}
                ${mediaHtml}
                <div class="modern-post-actions">
                    <button class="action-pill ${isLiked}" data-role="like-post" data-post-id="${escapeHtml(post.PostID || '')}">
                        <i class="${likeIcon}"></i> <span>${likesCount}</span>
                    </button>
                    <button class="action-pill" data-role="comment-post" data-post-id="${escapeHtml(post.PostID || '')}">
                        <i class="fa-regular fa-comment"></i> <span>${commentsCount}</span>
                    </button>
                    <button class="action-pill" data-role="share-post" data-post-id="${escapeHtml(post.PostID || '')}">
                        <i class="fa-solid fa-share-nodes"></i> <span>${sharesCount}</span>
                    </button>
                    <div class="post-stats-pill">
                        <i class="fa-regular fa-eye"></i> ${viewsCount}
                    </div>
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
        bindVideoAutoplay();
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

    function bindVideoAutoplay() {
        const videos = document.querySelectorAll('.post-card video');
        if (videos.length === 0) return;

        if (postVideoObserver) {
            postVideoObserver.disconnect();
        }

        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.7
        };

        postVideoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const video = entry.target;
                if (entry.isIntersecting) {
                    const playPromise = video.play();
                    if (playPromise !== undefined) {
                        playPromise.catch(() => {});
                    }
                } else {
                    video.pause();
                }
            });
        }, observerOptions);

        videos.forEach(video => {
            postVideoObserver.observe(video);
            // Add a click listener to toggle mute
            video.addEventListener('click', () => {
                video.muted = !video.muted;
            });
        });
    }

    function loadPosts() {
        if (!postFeedList) return;
        let url = `${POST_API}?action=fetch`;
        if (typeof window.CURRENT_GROUP_ID !== 'undefined' && window.CURRENT_GROUP_ID > 0) {
            url += `&group_id=${window.CURRENT_GROUP_ID}`;
        }
        fetch(url)
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

        document.querySelectorAll('[data-role="report-post"]').forEach((btn) => {
            btn.onclick = () => {
                closeAllPostMenus();
                const postId = Number(btn.getAttribute('data-post-id') || 0);
                if (!postId) return;

                const reason = window.prompt('Why are you reporting this post?', 'Spam or inappropriate content');
                if (reason === null) return;
                if (!reason.trim()) {
                    window.alert('Please enter a report reason.');
                    return;
                }

                const payload = new FormData();
                payload.append('action', 'report');
                payload.append('post_id', String(postId));
                payload.append('reason', reason.trim());

                fetch(POST_API, { method: 'POST', body: payload })
                    .then((res) => res.json())
                    .then((data) => {
                        window.alert((data && data.message) ? data.message : 'Report sent.');
                    })
                    .catch(() => window.alert('Unable to submit report.'));
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
                const postId = btn.getAttribute('data-post-id');
                const payload = new FormData();
                payload.append('action', 'share');
                payload.append('post_id', postId);
                if (typeof window.CURRENT_GROUP_ID !== 'undefined' && window.CURRENT_GROUP_ID > 0) {
                    payload.append('group_id', window.CURRENT_GROUP_ID);
                }
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
        
        const locInput = document.getElementById('post-location-input');
        const feelInput = document.getElementById('post-feeling-input');
        if (locInput) { locInput.value = ''; locInput.classList.remove('active'); }
        if (feelInput) { feelInput.value = ''; feelInput.classList.remove('active'); }
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

    if (closeAddStoryBtn && addStoryModal) {
        closeAddStoryBtn.addEventListener('click', () => addStoryModal.classList.remove('active'));
    }

    if (addStoryModal) {
        addStoryModal.addEventListener('click', (event) => {
            if (event.target === addStoryModal) addStoryModal.classList.remove('active');
        });
    }

    if (storyMediaInput && selectedFileName) {
        storyMediaInput.addEventListener('change', () => {
            const file = storyMediaInput.files && storyMediaInput.files[0];
            selectedFileName.textContent = file ? file.name : 'No file chosen';
        });
    }

    if (addStoryForm && storyMediaInput && storyModalMessage) {
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
    }

    if (openAddStoryBtn && addStoryModal) {
        openAddStoryBtn.onclick = () => addStoryModal.classList.add('active');
    }

    if (hasCreatePostComposer) {
        createPostTriggers.forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                openPostComposer();
            });
        });
    }

    if (closeCreatePostBtn) {
        closeCreatePostBtn.addEventListener('click', closePostComposer);
    }

    if (createPostModal) {
        createPostModal.addEventListener('click', (event) => {
            if (event.target === createPostModal) closePostComposer();
        });
    }

    if (hasCreatePostComposer) {
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

    if (hasCreatePostComposer) {
        const locationPill = document.querySelector('label[for="post-location-input"]');
        const feelingPill = document.querySelector('label[for="post-feeling-input"]');
        const locationInput = document.getElementById('post-location-input');
        const feelingInput = document.getElementById('post-feeling-input');

        if (locationPill && locationInput) {
            locationPill.addEventListener('click', (e) => {
                e.preventDefault();
                locationInput.classList.toggle('active');
                if (locationInput.classList.contains('active')) locationInput.focus();
                else locationInput.value = '';
            });
        }
        if (feelingPill && feelingInput) {
            feelingPill.addEventListener('click', (e) => {
                e.preventDefault();
                feelingInput.classList.toggle('active');
                if (feelingInput.classList.contains('active')) feelingInput.focus();
                else feelingInput.value = '';
            });
        }

        createPostForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const files = Array.from(postMediaInput.files || []);
            const content = (postContentInput && postContentInput.value) ? postContentInput.value.trim() : '';
            const locationVal = locationInput ? locationInput.value.trim() : '';
            const feelingVal = feelingInput ? feelingInput.value.trim() : '';
            const visibilitySelect = createPostForm.querySelector('select[name="visibility"]');
            const visibilityVal = visibilitySelect ? visibilitySelect.value : 'Public';

            if (files.length === 0 && content === '' && !locationVal && !feelingVal) {
                postModalMessage.textContent = 'Please add text, feeling, location, or at least one media file.';
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
            payload.append('content', content);
            payload.append('location', locationVal);
            payload.append('feeling', feelingVal);
            payload.append('privacy', visibilityVal);
            if (typeof window.CURRENT_GROUP_ID !== 'undefined' && window.CURRENT_GROUP_ID > 0) {
                payload.append('group_id', window.CURRENT_GROUP_ID);
            }

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
            const content = quickStatusInput ? quickStatusInput.value.trim() : '';
            const quickStatusFileInput = document.getElementById('quick-status-media-input');
            const files = quickStatusFileInput ? Array.from(quickStatusFileInput.files || []) : [];

            if (!content && files.length === 0) {
                if (quickStatusMessage) quickStatusMessage.textContent = 'Please write something or add a photo/video.';
                if (quickStatusInput) quickStatusInput.focus();
                return;
            }
            
            if (files.length > 4) {
                if (quickStatusMessage) quickStatusMessage.textContent = 'You can upload up to 4 media files per post.';
                return;
            }

            const submitBtn = quickStatusForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            if (quickStatusMessage) quickStatusMessage.textContent = 'Posting...';

            const payload = new FormData();
            payload.append('action', 'create');
            payload.append('content', content);
            files.forEach((file) => payload.append('post_media[]', file));

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

                    if (quickStatusInput) quickStatusInput.value = '';
                    if (quickStatusFileInput) {
                        quickStatusFileInput.value = '';
                        const dt = new DataTransfer();
                        quickStatusFileInput.files = dt.files;
                    }
                    const previewContainer = document.getElementById('quick-status-preview');
                    if (previewContainer) previewContainer.innerHTML = '';

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

    if (headerSearchInput) {
        headerSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = headerSearchInput.value.trim();
                if (searchTerm) {
                    // Redirect to explore page with search query
                    window.location.href = `../Explore/explore.php?q=${encodeURIComponent(searchTerm)}`;
                }
            }
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

    // --- Notifications Logic ---
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    const notificationsList = document.getElementById('notificationsList');

    if (notificationBtn && notificationsDropdown) {
        notificationBtn.addEventListener('click', (e) => {
            if (notificationsDropdown.contains(e.target)) return;
            
            notificationsDropdown.classList.toggle('show');
            if (notificationsDropdown.classList.contains('show')) {
                fetchNotifications();
                // បិទមិនឱ្យ Scroll Background ពេលបើកលើទូរស័ព្ទ
                if (window.innerWidth <= 768) {
                    document.body.style.overflow = 'hidden';
                }
            } else {
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target)) {
                notificationsDropdown.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }

    function getNotificationText(type) {
        switch(type.toLowerCase()) {
            case 'like': return 'liked your post';
            case 'comment': return 'commented on your post';
            case 'share': return 'shared your post';
            case 'friendrequest': return 'sent you a friend request';
            case 'message': return 'sent you a message';
            case 'newevent': return 'created a new event';
            case 'groupinvite': return 'invited you to a group';
            case 'mention': return 'mentioned you in a post';
            default: return 'interacted with your profile';
        }
    }

    function fetchNotifications() {
        if (!notificationsList) return;
        notificationsList.innerHTML = '<div style="padding: 15px; text-align: center;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>';
        
        fetch('../../../app/Controllers/Explore/explore_controller.php?action=get_notifications')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (!data.notifications || data.notifications.length === 0) {
                        notificationsList.innerHTML = '<div style="padding: 20px; text-align: center; color: #64748b;">No new notifications</div>';
                        return;
                    }
                    
                    notificationsList.innerHTML = data.notifications.map(notif => `
                        <div class="notification-item ${notif.IsRead == 0 ? 'unread' : ''}">
                            <img src="${resolveAvatarUrl(notif.ProfilePic)}" class="notification-avatar" onerror="this.src='${FALLBACK_AVATAR}'">
                            <div class="notification-content">
                                <div><strong>${escapeHtml(notif.Username)}</strong> ${getNotificationText(notif.Type)}</div>
                                <div class="notification-time">${formatTimeAgo(notif.CreatedAt)}</div>
                            </div>
                        </div>
                    `).join('');
                }
            })
            .catch(err => {
                notificationsList.innerHTML = '<div style="padding: 15px; text-align: center; color: #ef4444;">Failed to load notifications</div>';
            });
    }

    // --- Setup Lightbox events ---
    const lightbox = document.getElementById('postLightbox');
    const closeBtn = document.querySelector('.post-lightbox-close');
    
    if (lightbox && closeBtn) {
        const closeLightbox = () => {
            lightbox.classList.remove('active');
            const vid = document.getElementById('postLightboxVid');
            if (vid) {
                vid.pause();
                vid.remove();
            }
        };
        
        closeBtn.addEventListener('click', closeLightbox);
        
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    }

    window.openLightbox = function(src, isVideo = false) {
        if (lightbox) {
            const img = document.getElementById('postLightboxImg');
            const existingVid = document.getElementById('postLightboxVid');
            if (existingVid) {
                existingVid.pause();
                existingVid.remove();
            }
            
            if (isVideo) {
                if (img) img.style.display = 'none';
                const vid = document.createElement('video');
                vid.id = 'postLightboxVid';
                vid.className = 'post-lightbox-img';
                vid.src = src;
                vid.controls = true;
                vid.autoplay = true;
                lightbox.insertBefore(vid, img);
            } else {
                if (img) {
                    img.style.display = 'block';
                    img.src = src;
                }
            }
            lightbox.classList.add('active');
        }
    };

    if (hasStoryRail) {
        loadStories();
        setInterval(loadStories, 15000);
    }

    if (postFeedList) {
        loadPosts();
        setInterval(loadPosts, 30000);
    } else {
        // សម្រាប់ទំព័រ Profile ឫ Group ដែល render តាមរយៈ PHP
        // យើងត្រូវ bind events ដោយដៃ ដើម្បីឲ្យប៊ូតុង Like, Comment, Edit លោតដំណើរការ
        bindPostEvents();
        bindPostViewTracking();
        bindVideoAutoplay();
    }
});
