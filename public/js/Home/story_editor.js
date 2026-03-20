document.addEventListener('DOMContentLoaded', () => {
    const config = window.STORY_EDITOR_CONFIG || {};
    const api = config.api || '';
    const dashboardUrl = config.dashboardUrl || 'dashboard.php';

    const form = document.getElementById('story-editor-form');
    const postBtn = document.getElementById('post-story-btn');
    const statusEl = document.getElementById('editor-status');
    const textInput = document.getElementById('overlay_text');
    const positionInput = document.getElementById('overlay_position');
    const preview = document.getElementById('overlay-preview');
    const mediaAsset = document.querySelector('.editor-media-asset');
    const zoomScaleInput = document.getElementById('zoom_scale');
    const zoomRange = document.getElementById('zoom-range');
    const zoomInBtn = document.getElementById('zoom-in-btn');
    const zoomOutBtn = document.getElementById('zoom-out-btn');
    const zoomValue = document.getElementById('zoom-value');

    if (!form || !postBtn || !textInput || !positionInput || !preview || !zoomScaleInput || !zoomRange || !zoomInBtn || !zoomOutBtn || !zoomValue) {
        return;
    }

    let zoom = 1;
    const MIN_ZOOM = 1;
    const MAX_ZOOM = 3;
    const ZOOM_STEP = 0.05;

    function applyZoom(nextZoom) {
        const clamped = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, Number(nextZoom) || 1));
        zoom = Math.round(clamped * 100) / 100;
        zoomScaleInput.value = String(zoom);
        zoomRange.value = String(zoom);
        zoomValue.textContent = `${Math.round(zoom * 100)}%`;
        if (mediaAsset) {
            mediaAsset.style.transform = `scale(${zoom})`;
        }
    }

    function updatePreview() {
        const text = textInput.value.trim();
        const pos = positionInput.value || 'bottom';

        preview.textContent = text;
        preview.classList.remove('pos-top', 'pos-center', 'pos-bottom');
        preview.classList.add(`pos-${pos}`);
    }

    function setStatus(message, ok = false) {
        statusEl.textContent = message;
        statusEl.classList.toggle('ok', ok);
    }

    textInput.addEventListener('input', updatePreview);
    positionInput.addEventListener('change', updatePreview);

    zoomRange.addEventListener('input', () => applyZoom(zoomRange.value));
    zoomInBtn.addEventListener('click', () => applyZoom(zoom + ZOOM_STEP));
    zoomOutBtn.addEventListener('click', () => applyZoom(zoom - ZOOM_STEP));

    if (!mediaAsset || mediaAsset.tagName !== 'IMG') {
        zoomInBtn.disabled = true;
        zoomOutBtn.disabled = true;
        zoomRange.disabled = true;
        zoomValue.textContent = 'Zoom available for images only';
    } else {
        applyZoom(1);
    }

    updatePreview();

    postBtn.addEventListener('click', () => {
        const tempMedia = (form.querySelector('input[name="temp_media"]') || {}).value || '';
        if (!tempMedia) {
            setStatus('Missing temporary media file.');
            return;
        }

        const payload = new FormData();
        payload.append('action', 'create_from_temp');
        payload.append('temp_media', tempMedia);
        payload.append('overlay_text', textInput.value.trim());
        payload.append('overlay_position', positionInput.value);
        payload.append('zoom_scale', zoomScaleInput.value || '1');

        setStatus('Posting story...');
        postBtn.disabled = true;

        fetch(api, { method: 'POST', body: payload })
            .then((res) => res.json())
            .then((data) => {
                if (data.status !== 'success') {
                    setStatus(data.message || 'Unable to post story.');
                    postBtn.disabled = false;
                    return;
                }

                setStatus('Story posted successfully.', true);
                window.location.href = dashboardUrl;
            })
            .catch(() => {
                setStatus('Unable to post story. Please try again.');
                postBtn.disabled = false;
            });
    });
});
