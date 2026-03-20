document.addEventListener('DOMContentLoaded', () => {
    const storyContainer = document.getElementById('stories-container');
    const openAddStoryBtn = document.getElementById('open-add-story');
    const addStoryModal = document.getElementById('add-story-modal');
    const closeAddStoryBtn = document.getElementById('close-add-story');
    const addStoryForm = document.getElementById('add-story-form');
    const storyMediaInput = document.getElementById('story-media-input');
    const selectedFileName = document.getElementById('selected-file-name');
    const storyModalMessage = document.getElementById('story-modal-message');

    const STORY_API = '../../../app/Controllers/Home/StoryController.php';
    const FALLBACK_AVATAR = 'https://i.pravatar.cc/150?u=default-user';
    const FALLBACK_STORY = 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=300&q=60';

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isVideo(mediaPath) {
        const lower = (mediaPath || '').toLowerCase();
        return lower.endsWith('.mp4') || lower.endsWith('.webm') || lower.endsWith('.ogg');
    }

    function resolveMediaUrl(mediaPath) {
        const raw = String(mediaPath || '').replace(/\\/g, '/').trim();
        if (!raw) return FALLBACK_STORY;
        if (/^(https?:)?\/\//i.test(raw) || raw.startsWith('data:')) return raw;
        try {
            return new URL(raw, window.location.href).href;
        } catch (error) {
            return raw;
        }
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
        const avatar = story.ProfilePic || FALLBACK_AVATAR;
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

    loadStories();
    setInterval(loadStories, 15000);
});







