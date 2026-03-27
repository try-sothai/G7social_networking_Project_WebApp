document.addEventListener('DOMContentLoaded', () => {
    const API = '../../../app/Controllers/Home/StoryController.php';
    const DASHBOARD_URL = 'dashboard.php';
    const DURATION = 10000;

    const mediaWrap = document.getElementById('story-media');
    const avatar = document.getElementById('story-avatar');
    const username = document.getElementById('story-username');
    const time = document.getElementById('story-time');
    const progress = document.getElementById('story-progress-fill');

    const storyPlayPauseBtn = document.getElementById('story-play-pause-btn');
    const storyMenuBtn = document.getElementById('story-menu-btn');
    const storyMenu = document.getElementById('story-menu');
    const copyStoryLinkBtn = document.getElementById('copy-story-link');
    const deleteStoryBtn = document.getElementById('delete-story');
    const prevBtn = document.getElementById('story-prev-btn');
    const nextBtn = document.getElementById('story-next-btn');

    let stories = [];
    let currentIndex = 0;
    let autoTimer = null;
    let remainingMs = DURATION;
    let timerStartedAt = 0;
    let isPaused = false;

    function isVideo(path) {
        const lower = String(path || '').toLowerCase();
        return lower.endsWith('.mp4') || lower.endsWith('.webm') || lower.endsWith('.ogg');
    }

    function resolveProfileImage(profilePic) {
        const raw = String(profilePic || '').replace(/\\/g, '/').trim();
        if (!raw) return 'https://i.pravatar.cc/150?u=story-user';

        if (
            raw.startsWith('http://') ||
            raw.startsWith('https://') ||
            raw.startsWith('../../../') ||
            raw.startsWith('/')
        ) {
            return raw;
        }

        return `../../../public/img/${raw}`;
    }

    function timeAgo(dateString) {
        const d = new Date(dateString);
        const s = Math.floor((Date.now() - d.getTime()) / 1000);
        if (s < 60) return 'Just now';
        const m = Math.floor(s / 60);
        if (m < 60) return `${m}m`;
        const h = Math.floor(m / 60);
        if (h < 24) return `${h}h`;
        const day = Math.floor(h / 24);
        return `${day}d`;
    }

    function clearAutoTimer() {
        if (autoTimer) {
            clearTimeout(autoTimer);
            autoTimer = null;
        }
    }

    function setPlayPauseIcon(paused) {
        const icon = storyPlayPauseBtn.querySelector('i');
        icon.className = paused ? 'fa-solid fa-play' : 'fa-solid fa-pause';
        storyPlayPauseBtn.setAttribute('aria-label', paused ? 'Play story' : 'Pause story');
    }

    function startProgressAnimation(durationMs) {
        const total = DURATION;
        const played = Math.max(0, total - durationMs);
        const startPercent = Math.min(100, (played / total) * 100);

        progress.style.transition = 'none';
        progress.style.width = `${startPercent}%`;
        requestAnimationFrame(() => {
            progress.style.transition = `width ${durationMs}ms linear`;
            progress.style.width = '100%';
        });
    }

    function startAutoAdvance() {
        clearAutoTimer();
        timerStartedAt = Date.now();
        autoTimer = setTimeout(goNext, remainingMs);
        startProgressAnimation(remainingMs);
    }

    function getCurrentVideo() {
        return mediaWrap.querySelector('video');
    }

    function pauseStory() {
        if (isPaused) return;
        clearAutoTimer();
        remainingMs = Math.max(0, remainingMs - (Date.now() - timerStartedAt));

        const fillRect = progress.getBoundingClientRect();
        const barRect = progress.parentElement.getBoundingClientRect();
        const widthPercent = barRect.width > 0 ? (fillRect.width / barRect.width) * 100 : 0;
        progress.style.transition = 'none';
        progress.style.width = `${Math.min(100, Math.max(0, widthPercent))}%`;

        const video = getCurrentVideo();
        if (video) video.pause();

        isPaused = true;
        setPlayPauseIcon(true);
    }

    function resumeStory() {
        if (!isPaused) return;
        if (remainingMs <= 0) {
            goNext();
            return;
        }

        const video = getCurrentVideo();
        if (video) video.play().catch(() => {});

        isPaused = false;
        setPlayPauseIcon(false);
        startAutoAdvance();
    }

    function togglePlayPause() {
        if (isPaused) resumeStory();
        else pauseStory();
    }

    function currentStory() {
        return stories[currentIndex] || null;
    }

    function closeMenu() {
        storyMenu.classList.remove('open');
    }

    function updateMenuState(story) {
        const isOwner = story && Number(story.UserID) === Number(CURRENT_USER_ID || 0);
        deleteStoryBtn.style.display = isOwner ? 'flex' : 'none';
    }

    function updateNavButtons() {
        if (!prevBtn || !nextBtn) return;
        prevBtn.disabled = currentIndex <= 0;
        nextBtn.disabled = currentIndex >= stories.length - 1;
    }

    function renderOverlay(story) {
        const text = String(story.OverlayText || '').trim();
        if (!text) return;

        const position = ['top', 'center', 'bottom'].includes(String(story.OverlayPosition || 'bottom'))
            ? story.OverlayPosition
            : 'bottom';

        const overlay = document.createElement('div');
        overlay.className = `story-overlay-text pos-${position}`;
        overlay.textContent = text;
        mediaWrap.appendChild(overlay);
    }

    function renderStoryAt(index) {
        const story = stories[index];
        if (!story) {
            window.location.href = DASHBOARD_URL;
            return;
        }

        currentIndex = index;
        username.textContent = story.Username || 'User';
        time.textContent = `${timeAgo(story.CreatedAt)} ago`;
        avatar.src = resolveProfileImage(story.ProfilePic);

        const media = story.StoryMedia || '';
        mediaWrap.innerHTML = isVideo(media)
            ? `<video src="${media}" autoplay muted playsinline></video>`
            : `<img src="${media}" alt="Story">`;

        const mediaElement = mediaWrap.querySelector('img, video');
        const zoomScale = Number(story.ZoomScale || 1);
        if (mediaElement && zoomScale > 1) {
            mediaElement.style.transform = `scale(${zoomScale})`;
            mediaElement.style.transformOrigin = 'center center';
        }

        renderOverlay(story);

        if (story.StoryID) {
            const nextUrl = `story_dashboard.php?story_id=${encodeURIComponent(story.StoryID)}`;
            window.history.replaceState({}, '', nextUrl);
        }

        updateMenuState(story);
        updateNavButtons();
        closeMenu();
        remainingMs = DURATION;
        isPaused = false;
        setPlayPauseIcon(false);
        startAutoAdvance();
    }

    function goNext() {
        if (currentIndex < stories.length - 1) {
            renderStoryAt(currentIndex + 1);
        } else {
            window.location.href = DASHBOARD_URL;
        }
    }

    function goPrev() {
        if (currentIndex > 0) {
            renderStoryAt(currentIndex - 1);
        } else {
            renderStoryAt(0);
        }
    }

    function copyCurrentLink() {
        const url = window.location.href;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url)
                .then(() => alert('Story link copied.'))
                .catch(() => alert('Unable to copy link.'));
            return;
        }

        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        alert('Story link copied.');
    }

    function deleteCurrentStory() {
        const story = currentStory();
        if (!story) return;
        if (Number(story.UserID) !== Number(CURRENT_USER_ID || 0)) {
            alert('You can only delete your own story.');
            return;
        }
        if (!confirm('Delete this story?')) return;

        clearAutoTimer();
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('story_id', story.StoryID);

        fetch(API, { method: 'POST', body: formData })
            .then((res) => res.json())
            .then((data) => {
                if (data.status !== 'success') {
                    alert(data.message || 'Unable to delete story.');
                    startAutoAdvance();
                    return;
                }

                stories.splice(currentIndex, 1);
                if (stories.length === 0) {
                    window.location.href = DASHBOARD_URL;
                    return;
                }

                const nextIndex = Math.min(currentIndex, stories.length - 1);
                renderStoryAt(nextIndex);
            })
            .catch(() => {
                alert('Unable to delete story.');
                startAutoAdvance();
            });
    }

    mediaWrap.addEventListener('click', (event) => {
        const rect = mediaWrap.getBoundingClientRect();
        const clickX = event.clientX - rect.left;
        if (clickX > rect.width / 2) {
            goNext();
        } else {
            goPrev();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') goNext();
        if (event.key === 'ArrowLeft') goPrev();
        if (event.key === ' ') {
            event.preventDefault();
            togglePlayPause();
        }
    });

    storyPlayPauseBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        togglePlayPause();
    });

    storyMenuBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        storyMenu.classList.toggle('open');
    });

    copyStoryLinkBtn.addEventListener('click', () => {
        copyCurrentLink();
        closeMenu();
    });

    prevBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        goPrev();
    });

    nextBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        goNext();
    });

    deleteStoryBtn.addEventListener('click', () => {
        closeMenu();
        deleteCurrentStory();
    });

    document.addEventListener('click', (event) => {
        if (!storyMenu.contains(event.target) && event.target !== storyMenuBtn && !storyMenuBtn.contains(event.target)) {
            closeMenu();
        }
    });

    fetch(`${API}?action=fetch`)
        .then((res) => res.json())
        .then((data) => {
            stories = Array.isArray(data.stories) ? data.stories : [];
            if (stories.length === 0) {
                window.location.href = DASHBOARD_URL;
                return;
            }

            const idx = stories.findIndex((s) => Number(s.StoryID) === Number(STORY_ID));
            renderStoryAt(idx >= 0 ? idx : 0);
        })
        .catch(() => {
            window.location.href = DASHBOARD_URL;
        });
});

