// explore.js
let currentCategory = 'All';
let isLoading = false;
let exploreVideoObserver = null;
let exploreViewObserver = null;
const exploreViewedPostIds = new Set();

const EXPLORE_POST_API = '../../../app/Controllers/Home/PostController.php';

function closeAllExploreMenus() {
    document.querySelectorAll('.explore-post-menu-wrap.active').forEach(w => w.classList.remove('active'));
}


if (!document.getElementById('social-dark-mode-vars')) {
    const style = document.createElement('style');
    style.id = 'social-dark-mode-vars';
    style.innerHTML = `
        body.dark-mode {
            --bg-secondary: #111827;
            --bg-hover: #1e293b;
            --border-color: rgba(148, 163, 184, 0.22);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
        }
        body.dark-mode .post-card {
            background: #111827 !important;
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
            color: #e2e8f0 !important;
        }
        body.dark-mode .post-user-info h4,
        body.dark-mode .post-header h4,
        body.dark-mode .post-content p {
            color: #e2e8f0 !important;
        }
        body.dark-mode .explore-text-thumbnail {
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
        }

        /* ── Comment Section Dark Mode ── */
        body.dark-mode .explore-comments-wrap {
            background: #111827 !important;
            border-top-color: rgba(148, 163, 184, 0.18) !important;
        }
        body.dark-mode .explore-comment-bubble {
            background: #1e293b !important;
        }
        body.dark-mode .explore-comment-bubble strong {
            color: #e2e8f0 !important;
        }
        body.dark-mode .explore-comment-bubble span {
            color: #94a3b8 !important;
        }
        body.dark-mode .explore-comment-time {
            color: #475569 !important;
        }
        body.dark-mode .explore-comment-input {
            background: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: rgba(148, 163, 184, 0.28) !important;
        }
        body.dark-mode .explore-comment-input::placeholder {
            color: #475569 !important;
        }
        body.dark-mode .explore-post-menu-wrap .post-menu-dropdown {
            background: #0f172a !important;
            border-color: rgba(148, 163, 184, 0.3) !important;
        }
        body.dark-mode .explore-post-menu-wrap .post-menu-item {
            color: #e2e8f0 !important;
        }
        body.dark-mode .explore-post-menu-wrap .post-menu-item:hover {
            background: rgba(148, 163, 184, 0.15) !important;
        }
        body.dark-mode .post-menu-btn {
            color: #ffffff;
        }
    `;
    document.head.appendChild(style);
}

function getProfileUrl(userId) {
    const id = Number(userId || 0);
    if (!id) return '../Profile/profile.php';
    return `../Profile/profile.php?user_id=${encodeURIComponent(id)}`;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing explore page');
    setupEventListeners();
    window.onPostEditSuccess = loadPosts;

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.explore-post-menu-wrap')) closeAllExploreMenus();
    });

    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('q');
    const searchInput = document.getElementById('searchInput');

    if (searchQuery) {
        if (searchInput) {
            searchInput.value = searchQuery;
        }
        searchPosts(searchQuery);
    } else {
        loadPosts();
    }

    // Setup Lightbox events
    const lightbox = document.getElementById('exploreLightbox');
    const closeBtn = document.querySelector('.explore-lightbox-close');
    
    if (lightbox && closeBtn) {
        const closeLightbox = () => {
            lightbox.classList.remove('active');
            // បិទ និងលុបវីដេអូចេញពេលបិទផ្ទាំង Lightbox
            const vid = document.getElementById('exploreLightboxVid');
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
});

function setupEventListeners() {
    console.log('Setting up event listeners');
    
    // Category filter - FIXED: Make sure chips are selected correctly
    const chips = document.querySelectorAll('.chip');
    console.log('Found chips:', chips.length);
    
    chips.forEach(chip => {
        // Remove any existing listeners to avoid duplicates
        chip.removeEventListener('click', chip._clickHandler);
        
        // Create and store the handler
        const clickHandler = function(e) {
            e.preventDefault();
            console.log('Chip clicked:', this.dataset.category);
            
            // Remove active class from all chips
            chips.forEach(c => c.classList.remove('active'));
            // Add active class to clicked chip
            this.classList.add('active');
            
            // Update current category and reload posts
            currentCategory = this.dataset.category;
            console.log('Loading posts for category:', currentCategory);
            loadPosts();
        };
        
        // Store handler for potential removal
        chip._clickHandler = clickHandler;
        chip.addEventListener('click', clickHandler);
    });

    // Search functionality
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = e.target.value.trim();
                if (searchTerm) {
                    searchPosts(searchTerm);
                } else {
                    loadPosts(currentCategory);
                }
            }, 500);
        });
    }

}

function loadPosts() {
    if (isLoading) return;
    isLoading = true;
    
    const container = document.getElementById('postsContainer');
    if (!container) {
        console.error('Posts container not found');
        return;
    }
    
    container.innerHTML = '<div class="loading"><i class="fa-solid fa-spinner fa-spin"></i> Loading posts...</div>';
    
    const url = `../../../app/Controllers/Explore/explore_controller.php?action=get_posts&category=${encodeURIComponent(currentCategory)}`;
    console.log('Fetching posts from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Posts data received:', data);
            if (data.success) {
                displayPosts(data.posts);
            } else {
                container.innerHTML = '<div class="no-posts">No posts to display</div>';
                console.error('Error loading posts:', data.error);
            }
        })
        .catch(error => {
            console.error('Error fetching posts:', error);
            container.innerHTML = '<div class="no-posts">Error loading posts. Please try again later.<br>Error: ' + error.message + '</div>';
        })
        .finally(() => {
            isLoading = false;
        });
}

function searchPosts(searchTerm) {
    if (isLoading) return;
    isLoading = true;
    
    const container = document.getElementById('postsContainer');
    container.innerHTML = '<div class="loading"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</div>';
    
    fetch(`../../../app/Controllers/Explore/explore_controller.php?action=get_posts&category=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPosts(data.posts);
            } else {
                container.innerHTML = '<div class="no-posts">No posts found</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="no-posts">Error searching posts</div>';
        })
        .finally(() => {
            isLoading = false;
        });
}

function displayPosts(posts) {
    const container = document.getElementById('postsContainer');
    
    if (!posts || posts.length === 0) {
        container.innerHTML = '<div class="no-posts">No posts yet. Be the first to create one!</div>';
        return;
    }
    
    container.innerHTML = '<div class="posts-grid"></div>';
    const grid = container.querySelector('.posts-grid');
    
    posts.forEach(post => {
        const postElement = createPostElement(post);
        grid.appendChild(postElement);
    });

    // បើកដំណើរការ Auto-play + View tracking
    setupVideoAutoplay();
    setupExploreViewTracking();

    if (window.activeExploreCommentPostId) {
        const commentsSection = document.getElementById(`comments-${window.activeExploreCommentPostId}`);
        if (commentsSection) {
            commentsSection.style.display = 'block';
            const list = commentsSection.querySelector('.comments-list');
            if (list) {
                list.scrollTop = list.scrollHeight;
            }
            const input = document.getElementById(`comment-input-${window.activeExploreCommentPostId}`);
            if (input) input.focus();
        }
        window.activeExploreCommentPostId = null;
    }
}

function sendExploreView(postId) {
    const id = Number(postId || 0);
    if (!id || exploreViewedPostIds.has(id)) return;
    exploreViewedPostIds.add(id);

    const formData = new FormData();
    formData.append('action', 'view');
    formData.append('post_id', String(id));

    fetch('../../../app/Controllers/Explore/explore_controller.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success && typeof data.views === 'number') {
                const card = document.querySelector(`.post-card[data-post-id="${id}"]`);
                if (card) {
                    const viewNum = card.querySelector('.explore-view-num');
                    if (viewNum) viewNum.textContent = data.views;
                }
            }
        })
        .catch(() => {});
}

function setupExploreViewTracking() {
    if (exploreViewObserver) exploreViewObserver.disconnect();

    exploreViewObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting || entry.intersectionRatio < 0.5) return;
            const postId = entry.target.getAttribute('data-post-id');
            sendExploreView(postId);
        });
    }, { threshold: [0.5] });

    document.querySelectorAll('.post-card[data-post-id]').forEach(card => {
        exploreViewObserver.observe(card);
    });
}

function setupVideoAutoplay() {
    if (exploreVideoObserver) {
        exploreVideoObserver.disconnect();
    }
    
    exploreVideoObserver = new IntersectionObserver((entries) => {
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
    }, { threshold: 0.6 }); // Play នៅពេលអូសមកឃើញ 60% នៃវីដេអូ

    document.querySelectorAll('video.explore-post-image').forEach(video => {
        exploreVideoObserver.observe(video);
    });
}

function createPostElement(post) {
    const postId = post.PostID || post.id;
    const currentUserId = Number(window.currentUser?.id || 0);
    const isOwner = currentUserId && Number(post.UserID || 0) === currentUserId;

    const postDiv = document.createElement('div');
    postDiv.className = 'post-card';
    postDiv.dataset.postId = postId;
    
    // ជួសជុលបញ្ហារូបភាពមិនលោត (Fix Image Path Issue)
    let MediaPath = null;
    let rawImgPath = post.ImagePath || post.Image || post.MediaURL; 

    if (rawImgPath) {
        // ត្រួតពិនិត្យមើលក្រែងលោវាជាប្រភេទ JSON Array (មានរូបច្រើន)
        if (typeof rawImgPath === 'string' && rawImgPath.trim().startsWith('[') && rawImgPath.trim().endsWith(']')) {
            try {
                const parsed = JSON.parse(rawImgPath);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    rawImgPath = parsed[0]; // ទាញយករូបភាពទី១មកបង្ហាញ
                } else {
                    rawImgPath = null;
                }
            } catch (e) {}
        }
    }

    if (rawImgPath) {
        rawImgPath = String(rawImgPath).replace(/\\/g, '/');
        if (rawImgPath.startsWith('data:')) {
            MediaPath = rawImgPath;
        } else if (rawImgPath.startsWith('http')) {
            // If URL contains an uploads path, extract just the filename and serve locally.
            // This handles images stored with old tunnel URLs (loca.lt, cloudflare, etc.).
            if (rawImgPath.includes('/uploads/posts/')) {
                const fileName = rawImgPath.split('/uploads/posts/').pop().split('?')[0];
                MediaPath = `../../../public/uploads/posts/${fileName}`;
            } else if (rawImgPath.includes('/public/uploads/avatars/')) {
                const fileName = rawImgPath.split('/public/uploads/avatars/').pop().split('?')[0];
                MediaPath = `../../../public/uploads/avatars/${fileName}`;
            } else {
                MediaPath = rawImgPath;
            }
        } else if (rawImgPath.startsWith('/')) {
            MediaPath = rawImgPath;
        } else {
            const fileName = rawImgPath.split('/').pop();
            MediaPath = `../../../public/uploads/posts/${fileName}`;
        }
    }

    // Detect [SHARED_POST] content and extract embedded JSON
    let isSharedPost = false;
    let sharedData = null;
    if (post.Content && post.Content.startsWith('[SHARED_POST]')) {
        try {
            sharedData = JSON.parse(post.Content.slice(13));
            isSharedPost = true;
            // If no media of its own, try the original post's image
            if (!MediaPath && sharedData.original_image) {
                const origImg = String(sharedData.original_image).replace(/\\/g, '/');
                if (origImg.startsWith('http')) {
                    if (origImg.includes('/uploads/posts/')) {
                        MediaPath = `../../../public/uploads/posts/${origImg.split('/uploads/posts/').pop().split('?')[0]}`;
                    } else if (origImg.includes('/public/uploads/avatars/')) {
                        MediaPath = `../../../public/uploads/avatars/${origImg.split('/public/uploads/avatars/').pop().split('?')[0]}`;
                    }
                } else if (origImg) {
                    MediaPath = `../../../public/uploads/posts/${origImg.split('/').pop()}`;
                }
            }
        } catch (e) { /* malformed — fall through to plain content display */ }
    }

    // Function សម្រាប់ឆែកមើលថាជាវីដេអូឬអត់
    const isVideo = (path) => {
        if (!path) return false;
        const clean = path.toLowerCase().split('?')[0];
        return clean.endsWith('.mp4') || clean.endsWith('.webm') || clean.endsWith('.mov') || clean.endsWith('.ogg');
    };

    const encodedUsername = encodeURIComponent(post.Username || 'User');
    const profilePic = post.ProfilePic
        ? (post.ProfilePic.startsWith('http') ? post.ProfilePic : `${window.APP_CONFIG.imagePath}${post.ProfilePic}`)
        : `https://ui-avatars.com/api/?name=${encodedUsername}&background=dbeafe&color=1d4ed8`;
    const profileUrl = getProfileUrl(post.UserID || 0);
    const postDate = new Date(post.CreatedAt).toLocaleDateString('km-KH');
    
    // Generate beautiful gradient for text-only posts to act as a thumbnail
    const gradients = [
        'linear-gradient(135deg, #f6d365 0%, #fda085 100%)', 
        'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)', 
        'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)', 
        'linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%)',
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
    ];
    const randomGradient = gradients[postId % gradients.length];
    
    let mediaHtml = '';
    if (MediaPath) {
        if (isVideo(MediaPath)) {
            mediaHtml = `
                <video src="${escapeHtml(MediaPath)}" class="explore-post-image" style="cursor: pointer; background: #000;" 
                       onclick="openExploreLightbox(this.src, true)" 
                       muted playsinline loop></video>
                <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: white; padding: 5px 10px; border-radius: 12px; font-size: 12px; pointer-events: none;">
                    <i class="fa-solid fa-video"></i> Video
                </div>`;
        } else {
            const fallbackImg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22400%22%20height%3D%22300%22%3E%3Crect%20fill%3D%22%23e2e8f0%22%20width%3D%22400%22%20height%3D%22300%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%20font-size%3D%2216%22%20fill%3D%22%2364748b%22%3EImage%20Not%20Found%3C%2Ftext%3E%3C%2Fsvg%3E";
            mediaHtml = `<img src="${escapeHtml(MediaPath)}" class="explore-post-image" alt="Post preview" style="cursor: pointer;" onclick="openExploreLightbox(this.src)" onerror="this.onerror=null; this.src='${fallbackImg}';">`;
        }
    } else if (isSharedPost) {
        mediaHtml = `<div class="explore-text-thumbnail" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h3><i class="fa-solid fa-share-nodes"></i>&nbsp; Shared Post</h3>
                     </div>`;
    } else {
        mediaHtml = `<div class="explore-text-thumbnail" style="background: ${randomGradient};">
                        <h3>${escapeHtml(post.Content).substring(0, 100)}${post.Content.length > 100 ? '...' : ''}</h3>
                     </div>`;
    }

    let contentBodyHtml;
    if (isSharedPost && sharedData) {
        const sharedAvatar = sharedData.original_avatar
            ? (String(sharedData.original_avatar).startsWith('http')
                ? sharedData.original_avatar
                : `../../../public/uploads/avatars/${sharedData.original_avatar}`)
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(sharedData.original_user || 'U')}&background=dbeafe&color=1d4ed8`;
        const origContent = sharedData.original_content ? String(sharedData.original_content) : '';
        contentBodyHtml = `
            <p style="font-size:0.8rem;color:#64748b;margin:0 0 8px;display:flex;align-items:center;gap:5px;">
                <i class="fa-solid fa-share-nodes"></i> Shared a post
            </p>
            <div style="border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;background:#f8fafc;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                    <img src="${escapeHtml(sharedAvatar)}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=U&background=dbeafe&color=1d4ed8'">
                    <strong style="font-size:0.82rem;color:#1e293b;">${escapeHtml(sharedData.original_user || 'Unknown')}</strong>
                </div>
                ${origContent ? `<p style="font-size:0.8rem;color:#475569;margin:0;line-height:1.4;">${escapeHtml(origContent.substring(0, 150))}${origContent.length > 150 ? '...' : ''}</p>` : ''}
            </div>`;
    } else {
        contentBodyHtml = `<p>${escapeHtml(post.Content)}</p>`;
    }

    postDiv.innerHTML = `
        <div class="explore-thumbnail-wrapper">
            ${mediaHtml}
        </div>
        <div class="post-header explore-post-header" style="display:flex; align-items:center; justify-content:space-between;">
            <a href="${escapeHtml(profileUrl)}" class="post-user-link" style="display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit;">
                <img src="${escapeHtml(profilePic)}" alt="${escapeHtml(post.Username)}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=${encodedUsername}&background=dbeafe&color=1d4ed8'">
                <div class="post-user-info" style="line-height: 1.2;">
                    <h4 style="margin: 0; font-size: 14px;">${escapeHtml(post.Username)}</h4>
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: #64748b;">${postDate}</p>
                </div>
            </a>
            <div class="post-menu-wrap explore-post-menu-wrap" style="position:relative;">
                <button class="post-menu-btn" type="button" data-role="explore-toggle-menu" aria-label="Post options">
                    <i class="fa-solid fa-ellipsis-h"></i>
                </button>
                <div class="post-menu-dropdown">
                    ${isOwner ? `<button type="button" class="post-menu-item" data-role="explore-edit-post" data-post-id="${postId}" data-content="${escapeHtml(post.Content || '')}" data-media='${escapeHtml(JSON.stringify(Array.isArray(post.media) ? post.media : []))}'>Edit Post</button>` : ''}
                    ${isOwner ? `<button type="button" class="post-menu-item danger" data-role="explore-delete-post" data-post-id="${postId}">Delete Post</button>` : ''}
                    ${!isOwner ? `<button type="button" class="post-menu-item" data-role="explore-report-post" data-post-id="${postId}">Report Post</button>` : ''}
                </div>
            </div>
        </div>
        <div class="post-content explore-post-content">
            ${contentBodyHtml}
        </div>
        <div class="post-actions explore-post-actions">
            <button class="like-btn ${post.liked ? 'liked' : ''}" data-post-id="${postId}" style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; color: ${post.liked ? '#ef4444' : '#64748b'}; font-weight: 600;">
                <i class="fa-${post.liked ? 'solid' : 'regular'} fa-heart"></i>
                <span>${post.like_count || 0}</span>
            </button>
            <button class="comment-toggle-btn" data-post-id="${postId}" style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; color: #64748b; font-weight: 600;">
                <i class="fa-regular fa-comment"></i>
                <span>${post.comment_count || 0}</span>
            </button>
            <button class="share-btn" onclick="shareExplorePost(${postId})" style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; color: #64748b; font-weight: 600; transition: color 0.2s;">
                <i class="fa-solid fa-share-nodes"></i>
                <span>Share</span>
            </button>
            <div class="explore-view-count" style="margin-left: auto; font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 5px; pointer-events: none;">
                <i class="fa-regular fa-eye"></i>
                <span class="explore-view-num">${post.view_count || 0}</span>
            </div>
        </div>
        <div class="comments-section explore-comments-wrap" id="comments-${postId}" style="display: none; padding: 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 16px 16px;">
            <div class="comments-list" style="max-height: 200px; overflow-y: auto; margin-bottom: 12px;">
                ${renderComments(post.comments || [])}
            </div>
            <div class="comment-input" style="display: flex; gap: 10px;">
                <input type="text" class="explore-comment-input" placeholder="Write a comment..." id="comment-input-${postId}" style="flex: 1; padding: 8px 15px; border-radius: 20px; border: 1px solid #cbd5e1; outline: none; font-family: inherit; background: #ffffff; color: #1e293b;">
                <button onclick="addComment(${postId})" style="background: #6366f1; color: white; border: none; padding: 8px 18px; border-radius: 20px; cursor: pointer; font-weight: 600;">Submit</button>
            </div>
        </div>
    `;
    
    // Three-dot menu events
    const menuWrap = postDiv.querySelector('.explore-post-menu-wrap');
    if (menuWrap) {
        const menuBtn = menuWrap.querySelector('[data-role="explore-toggle-menu"]');
        if (menuBtn) {
            menuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = menuWrap.classList.contains('active');
                closeAllExploreMenus();
                if (!isOpen) menuWrap.classList.add('active');
            });
        }

        menuWrap.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-role]');
            if (!btn || btn.getAttribute('data-role') === 'explore-toggle-menu') return;
            const role = btn.getAttribute('data-role');
            const pid = Number(btn.getAttribute('data-post-id') || 0);
            closeAllExploreMenus();

            if (role === 'explore-delete-post') {
                if (!pid || !window.confirm('Delete this post?')) return;
                const fd = new FormData();
                fd.append('action', 'delete');
                fd.append('post_id', String(pid));
                fd.append('csrf_token', window.APP_CONFIG?.csrfToken || '');
                fetch(EXPLORE_POST_API, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(d => {
                        if (d && d.status === 'success') loadPosts();
                        else window.alert((d && d.message) ? d.message : 'Unable to delete post.');
                    })
                    .catch(() => window.alert('Unable to delete post.'));

            } else if (role === 'explore-edit-post') {
                if (!pid) return;
                const content = btn.getAttribute('data-content') || '';
                let mediaList = [];
                try { mediaList = JSON.parse(btn.getAttribute('data-media') || '[]'); } catch (_) {}
                if (typeof window.openEditPostModal === 'function') {
                    window.openEditPostModal(pid, content, mediaList);
                }

            } else if (role === 'explore-report-post') {
                if (!pid) return;
                const reason = window.prompt('Why are you reporting this post?', 'Spam or inappropriate content');
                if (!reason || !reason.trim()) { if (reason !== null) window.alert('Please enter a report reason.'); return; }
                const fd = new FormData();
                fd.append('action', 'report');
                fd.append('post_id', String(pid));
                fd.append('reason', reason.trim());
                fd.append('csrf_token', window.APP_CONFIG?.csrfToken || '');
                fetch(EXPLORE_POST_API, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(d => window.alert((d && d.message) ? d.message : 'Report sent.'))
                    .catch(() => window.alert('Report sent.'));
            }
        });
    }

    // Add event listeners
    const likeBtn = postDiv.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleLike(postId, likeBtn);
        });
    }
    
    const commentToggleBtn = postDiv.querySelector('.comment-toggle-btn');
    if (commentToggleBtn) {
        commentToggleBtn.addEventListener('click', () => {
            const commentsSection = document.getElementById(`comments-${postId}`);
            if (commentsSection) {
                commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
            }
        });
    }
    
    const commentInput = postDiv.querySelector(`#comment-input-${postId}`);
    if (commentInput) {
        commentInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addComment(postId);
            }
        });
    }

    return postDiv;
}

function renderComments(comments) {
    if (!comments || comments.length === 0) {
        return '<div style="padding: 12px; color: #6b7280; text-align: center;">no comments yet</div>';
    }
    
    return comments.map(comment => {
        const cPic = comment.ProfilePic
            ? (comment.ProfilePic.startsWith('http') ? comment.ProfilePic : `${window.APP_CONFIG.imagePath}${comment.ProfilePic}`)
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.Username || 'U')}&background=dbeafe&color=1d4ed8`;
        const cFallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.Username || 'U')}&background=dbeafe&color=1d4ed8`;
        const dateObj = new Date(comment.CreatedAt || '');
        let exactDate = '';
        let timeDisplay = '';
        
        if (!Number.isNaN(dateObj.getTime())) {
            exactDate = dateObj.toLocaleString('en-US', { day: 'numeric', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
            
            const seconds = Math.floor((Date.now() - dateObj.getTime()) / 1000);
            let timeAgo = 'Just now';
            if (seconds >= 60) {
                const mins = Math.floor(seconds / 60);
                if (mins < 60) timeAgo = `${mins}m ago`;
                else {
                    const hours = Math.floor(mins / 60);
                    if (hours < 24) timeAgo = `${hours}h ago`;
                    else {
                        const days = Math.floor(hours / 24);
                        if (days < 7) timeAgo = `${days}d ago`;
                        else timeAgo = dateObj.toLocaleDateString();
                    }
                }
            }
            timeDisplay = exactDate && timeAgo !== exactDate ? `${timeAgo} • ${exactDate}` : timeAgo;
        }

        return `
            <div class="comment" style="margin-bottom: 12px;">
                <div style="display:flex; align-items:flex-start; gap:10px;">
                    <a href="${escapeHtml(getProfileUrl(comment.UserID || 0))}" style="text-decoration:none; flex-shrink: 0;">
                        <img src="${escapeHtml(cPic)}" alt="${escapeHtml(comment.Username)}" onerror="this.onerror=null; this.src='${cFallback}'" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    </a>
                    <div class="comment-content" style="max-width: calc(100% - 45px);">
                        <div class="explore-comment-bubble" style="background: #f1f5f9; padding: 8px 14px; border-radius: 18px; font-size: 13.5px; display: inline-block;">
                            <a href="${escapeHtml(getProfileUrl(comment.UserID || 0))}" style="text-decoration:none;">
                                <strong style="display: block; margin-bottom: 2px; color: #1e293b;">${escapeHtml(comment.Username)}</strong>
                            </a>
                            <span style="color: #334155; word-break: break-word; line-height: 1.4;">${escapeHtml(comment.CommentText || comment.Comment)}</span>
                        </div>
                        <div class="explore-comment-time" style="font-size: 11px; color: #64748b; margin-top: 4px; margin-left: 12px;" title="${escapeHtml(exactDate)}">${escapeHtml(timeDisplay)}</div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function toggleLike(postId, button) {
    const formData = new FormData();
    formData.append('action', 'like');
    formData.append('post_id', postId);

    fetch('../../../app/Controllers/Explore/explore_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('i');
            const countSpan = button.querySelector('span');
            if (data.action === 'liked') {
                icon.className = 'fa-solid fa-heart';
                button.classList.add('liked');
                button.style.color = '#ef4444';
            } else {
                icon.className = 'fa-regular fa-heart';
                button.classList.remove('liked');
                button.style.color = '#64748b';
            }
            if (countSpan && typeof data.count === 'number') {
                countSpan.textContent = data.count;
            }
        } else {
            console.error('Error toggling like:', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function addComment(postId) {
    const input = document.getElementById(`comment-input-${postId}`);
    const comment = input.value.trim();
    
    if (!comment) return;
    
    const formData = new FormData();
    formData.append('action', 'comment');
    formData.append('post_id', postId);
    formData.append('comment', comment);
    
    fetch('../../../app/Controllers/Explore/explore_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            window.activeExploreCommentPostId = postId;
            
            const searchInput = document.getElementById('searchInput');
            if (searchInput && searchInput.value.trim()) {
                searchPosts(searchInput.value.trim());
            } else {
                loadPosts();
            }
        } else {
            console.error('Error adding comment:', data.error);
            alert('Error adding comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding comment');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Lightbox Open Function
function openExploreLightbox(src, isVideo = false) {
    const lightbox = document.getElementById('exploreLightbox');
    const img = document.getElementById('exploreLightboxImg');
    if (lightbox) {
        const existingVid = document.getElementById('exploreLightboxVid');
        if (existingVid) {
            existingVid.pause();
            existingVid.remove();
        }
        
        if (isVideo) {
            if (img) img.style.display = 'none'; // លាក់រូបភាពធម្មតា
            const vid = document.createElement('video');
            vid.id = 'exploreLightboxVid';
            vid.className = 'explore-lightbox-img'; // ប្រើ CSS ដូចរូបភាពដែរ
            vid.src = src;
            vid.controls = true; // បង្ហាញផ្ទាំងបញ្ជា (Play/Pause/Sound)
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
}

// Share Function
function shareExplorePost(postId) {
    const postUrl = `${window.location.origin}${window.location.pathname.replace('Explore/explore.php', 'Home/post_detail.php')}?id=${postId}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Check out this post on LocalConnect',
            url: postUrl
        }).catch(err => console.log('Error sharing:', err));
    } else {
        navigator.clipboard.writeText(postUrl).then(() => {
            alert('Connect (Link) ត្រូវបានចម្លង! អាចយកទៅចែករំលែកបាន។ (Link Copied!)');
        }).catch(err => {
            console.error('Could not copy text: ', err);
        });
    }
}
