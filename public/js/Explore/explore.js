// explore.js
let currentCategory = 'All';
let isLoading = false;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing explore page');
    loadPosts();
    setupEventListeners();
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
    
    fetch(`explore_controller.php?action=get_posts&category=${encodeURIComponent(searchTerm)}`)
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
}

function createPostElement(post) {
    const postDiv = document.createElement('div');
    postDiv.className = 'post-card';
    postDiv.dataset.postId = post.id;
    
    const MediaPath = post.ImagePath ? `${window.APP_CONFIG.baseUrl}/${post.ImagePath}` : null;
    const profilePic = post.ProfilePic ? `${window.APP_CONFIG.imagePath}${post.ProfilePic}` : 'https://via.placeholder.com/50';
    const postDate = new Date(post.CreatedAt).toLocaleDateString('km-KH');
    
    postDiv.innerHTML = `
        <div class="post-header">
            <img src="${escapeHtml(profilePic)}" alt="${escapeHtml(post.Username)}" onerror="this.src='https://via.placeholder.com/50'">
            <div class="post-user-info">
                <h4>${escapeHtml(post.Username)}</h4>
                <p>${postDate}</p>
            </div>
        </div>
        <div class="post-content">
            <p>${escapeHtml(post.Content)}</p>
            ${MediaPath ? `<img src="${escapeHtml(MediaPath)}" class="post-image" alt="Post image" onerror="this.style.display='none'">` : ''}
        </div>
        <div class="post-actions">
            <button class="like-btn ${post.liked ? 'liked' : ''}" data-post-id="${post.id}">
                <i class="fa-${post.liked ? 'solid' : 'regular'} fa-heart"></i>
                <span>${post.like_count || 0}</span>
            </button>
            <button class="comment-toggle-btn" data-post-id="${post.id}">
                <i class="fa-regular fa-comment"></i>
                <span>${post.comment_count || 0}</span>
            </button>
        </div>
        <div class="comments-section" id="comments-${post.id}" style="display: none;">
            <div class="comments-list">
                ${renderComments(post.comments || [])}
            </div>
            <div class="comment-input">
                <input type="text" placeholder="សរសេរមតិយោបល់..." id="comment-input-${post.id}">
                <button onclick="addComment(${post.id})">បញ្ជូន</button>
            </div>
        </div>
    `;
    
    // Add event listeners
    const likeBtn = postDiv.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleLike(post.id, likeBtn);
        });
    }
    
    const commentToggleBtn = postDiv.querySelector('.comment-toggle-btn');
    if (commentToggleBtn) {
        commentToggleBtn.addEventListener('click', () => {
            const commentsSection = document.getElementById(`comments-${post.id}`);
            if (commentsSection) {
                commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
            }
        });
    }
    
    return postDiv;
}

function renderComments(comments) {
    if (!comments || comments.length === 0) {
        return '<div style="padding: 12px; color: #6b7280; text-align: center;">មិនទាន់មានមតិយោបល់</div>';
    }
    
    return comments.map(comment => `
        <div class="comment">
            <img src="${comment.ProfilePic ? `${window.APP_CONFIG.imagePath}${comment.ProfilePic}` : 'https://via.placeholder.com/32'}" alt="${escapeHtml(comment.Username)}" onerror="this.src='https://via.placeholder.com/32'">
            <div class="comment-content">
                <strong>${escapeHtml(comment.Username)}</strong>
                <p>${escapeHtml(comment.CommentText || comment.Comment)}</p>
                <small style="color: #6b7280;">${new Date(comment.CreatedAt).toLocaleTimeString()}</small>
            </div>
        </div>
    `).join('');
}

function toggleLike(postId, button) {
    const formData = new FormData();
    formData.append('action', 'like');
    formData.append('post_id', postId);
    
    fetch('explore_controller.php', {
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
    
    fetch('explore_controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadPosts(currentCategory);
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
    fetch('explore_controller.php?action=get_notifications')
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
            <div style="display: flex; gap: 10px; align-items: center;">
                <img src="${window.APP_CONFIG.imagePath}${notif.ProfilePic}" style="width: 30px; height: 30px; border-radius: 50%;" onerror="this.src='https://via.placeholder.com/30'">
                <div>
                    <p style="margin: 0; font-size: 13px;"><strong>${escapeHtml(notif.Username)}</strong> បាន ${notif.Type} លើផុសរបស់អ្នក</p>
                    <small style="color: #6b7280;">${new Date(notif.CreatedAt).toLocaleString()}</small>
                </div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}