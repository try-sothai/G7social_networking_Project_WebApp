// explore.js
let currentCategory = 'All';
let isLoading = false;
let exploreVideoObserver = null;

// ធានាថាអថេរពណ៌ (CSS Variables) សម្រាប់ Dark Mode ត្រូវបានកំណត់
if (!document.getElementById('social-dark-mode-vars')) {
    const style = document.createElement('style');
    style.id = 'social-dark-mode-vars';
    style.innerHTML = `
        body.dark-mode {
            --bg-secondary: #111827 !important;
            --bg-hover: #1e293b !important;
            --border-color: rgba(148, 163, 184, 0.22) !important;
            --text-primary: #f1f5f9 !important;
            --text-secondary: #94a3b8 !important;
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

    // Notifications
    const notificationBtn = document.getElementById('notificationBtn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', toggleNotifications);
    }

    // Close notifications when clicking outside
    document.addEventListener('click', (e) => {
        const notificationBtn = document.getElementById('notificationBtn');
        const dropdown = document.getElementById('notificationsDropdown');
        if (notificationBtn && dropdown && !notificationBtn.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
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

    // បើកដំណើរការ Auto-play សម្រាប់វីដេអូទាំងអស់
    setupVideoAutoplay();

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
    const postId = post.PostID || post.id; // ចាប់យក ID ឱ្យត្រូវនឹងទិន្នន័យពី Database

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
        rawImgPath = String(rawImgPath).replace(/\\/g, '/'); // ប្តូរ \ ទៅជា / ទាំងអស់ការពារ Error Path
        if (rawImgPath.startsWith('http') || rawImgPath.startsWith('data:') || rawImgPath.startsWith('/')) {
            MediaPath = rawImgPath;
        } else {
            // ចាប់យកតែឈ្មោះ File ខាងចុង (ឧ. post_123.mp4) ដើម្បីការពារការជាន់ Path គ្នា
            let fileName = rawImgPath.split('/').pop();
            // ប្រើ Path ធៀបទៅនឹងទីតាំង explore.php
            MediaPath = `../../../public/uploads/posts/${fileName}`;
        }
    }

    // Function សម្រាប់ឆែកមើលថាជាវីដេអូឬអត់
    const isVideo = (path) => {
        if (!path) return false;
        const clean = path.toLowerCase().split('?')[0];
        return clean.endsWith('.mp4') || clean.endsWith('.webm') || clean.endsWith('.mov') || clean.endsWith('.ogg');
    };

    const profilePic = post.ProfilePic ? `${window.APP_CONFIG.imagePath}${post.ProfilePic}` : 'https://via.placeholder.com/50';
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
            const fallbackImg = "data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23e2e8f0'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='16' fill='%2364748b'%3EImage Not Found%3C/text%3E%3C/svg%3E";
            mediaHtml = `<img src="${escapeHtml(MediaPath)}" class="explore-post-image" alt="Post preview" style="cursor: pointer;" onclick="openExploreLightbox(this.src)" onerror="this.onerror=null; this.src='${fallbackImg}';">`;
        }
    } else {
        mediaHtml = `<div class="explore-text-thumbnail" style="background: ${randomGradient};">
                        <h3>${escapeHtml(post.Content).substring(0, 100)}${post.Content.length > 100 ? '...' : ''}</h3>
                     </div>`;
    }

    postDiv.innerHTML = `
        <div class="explore-thumbnail-wrapper">
            ${mediaHtml}
        </div>
        <div class="post-header explore-post-header">
            <a href="${escapeHtml(profileUrl)}" class="post-user-link" style="display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit;">
                <img src="${escapeHtml(profilePic)}" alt="${escapeHtml(post.Username)}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;" onerror="this.src='https://via.placeholder.com/50'">
                <div class="post-user-info" style="line-height: 1.2;">
                    <h4 style="margin: 0; font-size: 14px;">${escapeHtml(post.Username)}</h4>
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: #64748b;">${postDate}</p>
                </div>
            </a>
        </div>
        <div class="post-content explore-post-content">
            <p>${escapeHtml(post.Content)}</p>
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
            <button class="share-btn" onclick="shareExplorePost(${postId})" style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; color: #64748b; font-weight: 600; margin-left: auto; transition: color 0.2s;">
                <i class="fa-solid fa-share-nodes"></i>
                <span>Share</span>
            </button>
        </div>
        <div class="comments-section" id="comments-${postId}" style="display: none; padding: 15px; background: var(--bg-secondary, #f8fafc); border-top: 1px solid var(--border-color, #e2e8f0); border-radius: 0 0 16px 16px;">
            <div class="comments-list" style="max-height: 200px; overflow-y: auto; margin-bottom: 12px;">
                ${renderComments(post.comments || [])}
            </div>
            <div class="comment-input" style="display: flex; gap: 10px;">
                <input type="text" placeholder="Write a comment..." id="comment-input-${postId}" style="flex: 1; padding: 8px 15px; border-radius: 20px; border: 1px solid var(--border-color, #cbd5e1); outline: none; font-family: inherit; background: var(--bg-hover, #ffffff); color: var(--text-primary, #1e293b);">
                <button onclick="addComment(${postId})" style="background: #6366f1; color: white; border: none; padding: 8px 18px; border-radius: 20px; cursor: pointer; font-weight: 600;">Submit</button>
            </div>
        </div>
    `;
    
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
                        <img src="${comment.ProfilePic ? `${window.APP_CONFIG.imagePath}${comment.ProfilePic}` : 'https://via.placeholder.com/32'}" alt="${escapeHtml(comment.Username)}" onerror="this.src='https://via.placeholder.com/32'" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    </a>
                    <div class="comment-content" style="max-width: calc(100% - 45px);">
                        <div style="background: var(--bg-hover, #f1f5f9); padding: 8px 14px; border-radius: 18px; font-size: 13.5px; display: inline-block;">
                            <a href="${escapeHtml(getProfileUrl(comment.UserID || 0))}" style="text-decoration:none;">
                                <strong style="display: block; margin-bottom: 2px; color: var(--text-primary, #1e293b);">${escapeHtml(comment.Username)}</strong>
                            </a>
                            <span style="color: var(--text-secondary, #334155); word-break: break-word; line-height: 1.4;">${escapeHtml(comment.CommentText || comment.Comment)}</span>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px; margin-left: 12px;" title="${escapeHtml(exactDate)}">${escapeHtml(timeDisplay)}</div>
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
            let currentCount = parseInt(countSpan.textContent);
            
            if (data.action === 'liked') {
                icon.className = 'fa-solid fa-heart';
                button.classList.add('liked');
                countSpan.textContent = currentCount + 1;
            } else {
                icon.className = 'fa-regular fa-heart';
                button.classList.remove('liked');
                countSpan.textContent = currentCount - 1;
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

function toggleNotifications() {
    const dropdown = document.getElementById('notificationsDropdown');
    dropdown.classList.toggle('show');
    
    if (dropdown.classList.contains('show')) {
        loadNotifications();
    }
}

function loadNotifications() {
    fetch('../../../app/Controllers/Explore/explore_controller.php?action=get_notifications')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notificationsList');
    
    if (!notifications || notifications.length === 0) {
        container.innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">មិនទាន់មានការជូនដំណឹង</div>';
        return;
    }
    
    container.innerHTML = notifications.map(notif => `
        <div class="notification-item ${notif.IsRead == 0 ? 'unread' : ''}" data-id="${notif.NotificationID}">
            <a href="${escapeHtml(getProfileUrl(notif.TriggerID || notif.UserID || 0))}" class="post-user-link" style="display: flex; gap: 10px; align-items: center; text-decoration:none; color:inherit;">
                <img src="${window.APP_CONFIG.imagePath}${notif.ProfilePic}" style="width: 30px; height: 30px; border-radius: 50%;" onerror="this.src='https://via.placeholder.com/30'">
                <div>
                    <p style="margin: 0; font-size: 13px;"><strong>${escapeHtml(notif.Username)}</strong> បាន ${notif.Type} លើផុសរបស់អ្នក</p>
                    <small style="color: #6b7280;">${new Date(notif.CreatedAt).toLocaleString()}</small>
                </div>
            </a>
        </div>
    `).join('');
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
