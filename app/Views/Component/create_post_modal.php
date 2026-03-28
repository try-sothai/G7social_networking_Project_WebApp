
<div class="story-modal-overlay" id="create-post-modal">
    <div class="story-modal-card post-modal-card">
        <div class="story-modal-header">
            <h3>Create New Post</h3>
            <button class="story-modal-close" id="close-create-post" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="create-post-form" class="story-upload-form create-post-modern-form" enctype="multipart/form-data">
            <div class="create-post-user-row">
                <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile" class="create-post-user-avatar">
                <div class="create-post-user-meta">
                    <strong><?php echo htmlspecialchars($u_d['Username']); ?></strong>
                    <select name="visibility" class="create-post-privacy">
                        <option value="public">Public</option>
                        <option value="friends">Friends</option>
                        <option value="private">Only me</option>
                    </select>
                </div>
            </div>

            <textarea id="post-content-input" name="content" maxlength="800" placeholder="What's on your mind?"></textarea>

            <input type="file" id="post-media-input" name="post_media[]" accept="image/*,video/*" multiple>
            <div class="story-file-name" id="post-selected-file-name">No file chosen</div>

            <div class="post-editor-area" id="post-editor-area">
                <div class="post-editor-preview" id="post-editor-preview"></div>
            </div>

            <div class="create-post-tool-row">
                <label for="post-media-input" class="create-post-tool-pill">
                    <i class="fa-solid fa-image"></i>
                    <span>Photo/Video</span>
                </label>
                <label for="post-location-input" class="create-post-tool-pill">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Location</span>
                </label>
                <label for="post-feeling-input" class="create-post-tool-pill">
                    <i class="fa-regular fa-face-smile"></i>
                    <span>Feeling</span>
                </label>
            </div>

            <input type="text" id="post-location-input" name="location" class="create-post-extra-input" placeholder="Add location">
            <input type="text" id="post-feeling-input" name="feeling" class="create-post-extra-input" placeholder="How are you feeling?">

            <button type="submit" class="story-upload-btn create-post-submit-btn"><i class="fa-solid fa-paper-plane"></i> Post</button>
            <p class="story-modal-message" id="post-modal-message"></p>
        </form>
    </div>
</div>
