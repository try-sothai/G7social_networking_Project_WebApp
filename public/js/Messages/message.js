// Message Chat Application
class MessageChat {
    constructor() {
        this.activeReceiverID = null;
        this.activeReceiverName = '';
        this.activeReceiverAvatar = '';
        this.activeReceiverOnline = false;
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.isRecording = false;
        this.pollingInterval = null;
        this.messages = [];
        this.pendingDeletes = {};
        
        this.initElements();
        this.initEventListeners();
        this.initWebSocket();
        this.fetchFriends();
        this.startPolling();
    }
    
    initElements() {
        this.elements = {
            userListContainer: document.getElementById('user-list-container'),
            chatActive: document.getElementById('chat-active'),
            chatWelcome: document.getElementById('chat-welcome'),
            chatBox: document.getElementById('chat-box'),
            msgInput: document.getElementById('msg-input'),
            sendBtn: document.getElementById('send-btn'),
            searchInput: document.getElementById('search-input'),
            fileInput: document.getElementById('file-input'),
            attachBtn: document.getElementById('attach-btn'),
            audioBtn: document.getElementById('audio-btn'),
            callAudioBtn: document.getElementById('call-audio-btn'),
            callVideoBtn: document.getElementById('call-video-btn'),
            activeUserName: document.getElementById('active-user-name'),
            activeUserImg: document.getElementById('active-user-img'),
            activeUserStatus: document.querySelector('.header-status'),
            activeUserOnlineDot: document.querySelector('.online-status'),
            myAvatar: document.getElementById('my-avatar-img')ist')
        };
    }
    
    initEventListeners() {
        this.elements.sendBtn.onclick = () => this.sendMessage();
        this.elements.msgInput.onkeydown = (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        };
        
        if (this.elements.attachBtn) {
            this.elements.attachBtn.onclick = () => this.elements.fileInput.click();
        }
        
        if (this.elements.fileInput) {
            this.elements.fileInput.onchange = () => {
                if (this.elements.fileInput.files.length > 0) {
                    this.sendMessage();
                }
            };
        }
        
        if (this.elements.audioBtn) {
            this.elements.audioBtn.onclick = () => this.toggleRecording();
        }
        
        if (this.elements.callAudioBtn) {
            this.elements.callAudioBtn.onclick = () => this.startCall('audio');
        }
        
        if (this.elements.callVideoBtn) {
            this.elements.callVideoBtn.onclick = () => this.startCall('video');
        }
        
        if (this.elements.searchInput) {
            let searchTimeout;
            this.elements.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                const term = this.elements.searchInput.value.trim();
                searchTimeout = setTimeout(() => this.searchFriends(term), 500);
            });
        }
    }
    
    initWebSocket() {
        // WebSocket implementation for real-time messaging
        // For production, implement WebSocket connection here
    }
    
    startPolling() {
        this.pollingInterval = setInterval(() => {
            if (this.activeReceiverID) {
                this.loadMessages();
            }
            this.fetchFriends();
        }, 300000);
    }
    
    fetchFriends() {
        if (!this.elements.userListContainer) return;
        
        this.elements.userListContainer.innerHTML = '<div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i> Loading friends...</div>';
        
        fetch(`${API_PATH}?action=fetch_friends`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.renderFriendsList(data.friends);
                } else {
                    this.elements.userListContainer.innerHTML = `<p class="error-msg">${data.error || 'Error loading friends'}</p>`;
                }
            })
            .catch(err => {
                console.error('Error fetching friends:', err);
                this.elements.userListContainer.innerHTML = '<p class="error-msg">Error loading friends</p>';
            });
    }
    
    searchFriends(searchTerm) {
        if (searchTerm.length < 2) {
            this.fetchFriends();
            return;
        }
        
        fetch(`${API_PATH}?action=search_friends&q=${encodeURIComponent(searchTerm)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.renderFriendsList(data.friends);
                }
            })
            .catch(err => console.error('Search error:', err));
    }
    
    renderFriendsList(friends) {
        if (!friends || friends.length === 0) {
            this.elements.userListContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fa-regular fa-user"></i>
                    <p>No friends yet. Add friends to start chatting!</p>
                    <a href="../Friends/AddFriends.php" class="btn-add-friend">Add Friends</a>
                </div>
            `;
            return;
        }
        
        this.elements.userListContainer.innerHTML = friends.map(friend => {
            const userImg = friend.ProfilePic ? `${window.APP_CONFIG.imagePath}${friend.ProfilePic}` : 'https://via.placeholder.com/50';
            let lastMessage = friend.last_message || 'No messages yet';
            
            if (lastMessage.length > 30) {
                lastMessage = lastMessage.substring(0, 30) + '...';
            }
            
            const messageIcons = {
                'image': '📷 Photo',
                'video': '🎥 Video',
                'audio': '🎵 Voice message',
                'file': '📎 File',
                'call': '📞 Call'
            };
            
            if (friend.last_message_type && messageIcons[friend.last_message_type]) {
                lastMessage = messageIcons[friend.last_message_type];
            }
            
            const unreadBadge = friend.unread_count > 0 ? 
                `<span class="unread-badge">${friend.unread_count}</span>` : '';
            const onlineBadge = Number(friend.IsOnline) === 1
                ? `<span class="contact-online"><i class="fa-solid fa-circle"></i> Online</span>`
                : '';
            
            return `
                <div
                    <div class="contact-info">
                        <div class="name-row">
                            <span class="contact-name">${this.escapeHtml(friend.Username)}</span>
                            <small>${this.formatTimeShort(friend.last_message_time)}</small>
                        </div>
                        <p class="last-message">${this.escapeHtml(lastMessage)}</p>
                        ${onlineBadge}
                    </div>
                    ${unreadBadge}
                </div>
            `;
        }).join('');
    }
    
    s    if (event) {
ptu
            event.stopPropagation();
        }

        // Handle mobile view sliding
        if (window.innerWidth <= 768 && this.elements.chatActive) {
            this.elements.chatActive.classList.add('mobile-active');
        }

        // remove active class from previous selection
        document.querySelectorAll('.contact-item.active').forEach(el => el.classList.remove('active'));

        if (itemElement) {
            itemElement.classList.add('active');
        } else {
            const existing = document.querySelector(`.contact-item[data-user-id="${id}"]`);
            if (existing) existing.classList.add('active');
        }

        this.activeReceiverID = id;
        this.activeReceiverName = name;
        this.activeReceiverAvatar = img;
        this.activeReceiverOnline = !!itemElement?.querySelector('.contact-online');
        
        this.elements.chatWelcome.style.display = 'none';
        this.elements.chatActive.style.display = 'flex';
        
        this.elements.activeUserName.innerText = name;
        this.elements.activeUserImg.src = img;
        if (this.elements.activeUserStatus) {
            this.elements.activeUserStatus.innerText = this.activeReceiverOnline ? 'Online' : 'Offline';
        }
        if (this.elements.activeUserOnlineDot) {
            this.elements.activeUserOnlineDot.style.display = this.activeReceiverOnline ? 'inline-block' : 'none';
        }
        
        this.loadMessages();
        this.markMessagesAsRead(id);
    }
    
    markMessagesAsRead(receiverId) {
        // This would typically be a separate API call
        // Currently handled in fetch action
    }
    
    loadMessages() {
        if (!this.activeReceiverID) return;
        
        fetch(`${API_PATH}?action=fetch&receiver_id=${this.activeReceiverID}`)
            .then(res => res.json())
            .then(messages => {
                this.messages = Array.isArray(messages) ? messages : [];
                this.renderMessages();
            })
            .catch(err => console.error('Error loading messages:', err));
    }

    renderMessages() {
        const myAvatar = this.elements.myAvatar.src;
        const receiverAvatar = this.elements.activeUserImg.src;

        this.elements.chatBox.innerHTML = this.messages.map(m => {
            if (this.pendingDeletes[m.MessageID]) return '';

            const isMe = m.SenderID == CURRENT_USER_ID;
            const avatar = isMe ? myAvatar : (m.ProfilePic ? `${window.APP_CONFIG.imagePath}${m.ProfilePic}` : receiverAvatar);
            let messageContent = this.formatMessageContent(m);
            if (!messageContent.trim()) return '';
            const timeStr = this.formatTime(m.SentAt);

            const canEdit = isMe && this.canEditMessage(m);
            const actionButtons = isMe ? `
                <div class="msg-actions">
                    ${canEdit ? `<button class="msg-action-btn edit-btn" onclick="chatApp.openEditModal(${m.MessageID}, '${this.escapeJs(m.MessageBody)}')" title="Edit"><i class="fa-solid fa-pen"></i></button>` : ''}
                    <button class="msg-action-btn delete-btn" onclick="chatApp.confirmDeleteMessage(${m.MessageID})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </div>
            ` : '';

            return `
                <div class="message-wrapper ${isMe ? 'me' : 'them'}" id="message-${m.MessageID}">
                    ${!isMe ? `<img src="${avatar}" class="msg-avatar">` : ''}
                    <div class="message-content">
                        <div class="msg-bubble ${m.MessageType === 'audio' ? 'msg-bubble-audio' : ''}">${messageContent}</div>
                        <span class="msg-time">${timeStr}</span>
                        <div class="message-actions">
                            ${canEdit ? `<button class="btn-edit" onclick="chatApp.openEditModal(${m.MessageID}, '${this.escapeJs(m.MessageBody)}')" title="Edit"><i class="fa-solid fa-pen"></i></button>` : ''}
                            <button class="btn-delete" onclick="chatApp.confirmDeleteMessage(${m.MessageID})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                    ${isMe ? `<img src="${avatar}" class="msg-avatar">` : ''}
                </div>
            `;
        }).join('');

        this.elements.chatBox.scrollTop = this.elements.chatBox.scrollHeight;
    }
    
    formatMessageContent(message) {
        let content = '';
        
        switch (message.MessageType) {
            case 'image':
                if (message.FileUrl) {
                    content = `
                        <div class="media-preview">
                            <img src="${window.APP_CONFIG.chatPath}${message.FileUrl}" class="msg-image" 
                                 onclick="chatApp.showMediaModal('${window.APP_CONFIG.chatPath}${message.FileUrl}', 'image')">
                            <button class="download-btn" onclick="chatApp.downloadFile('${window.APP_CONFIG.chatPath}${message.FileUrl}')">
                                <i class="fa-solid fa-download"></i>
                            </button>
                        </div>
                    `;
                }
                if (message.MessageBody) {
                    content += `<div class="msg-caption">${this.escapeHtml(message.MessageBody)}</div>`;
                }
                break;
                
            case 'video':
                if (message.FileUrl) {
                    content = `
                        <div class="media-preview">
                            <video controls src="${window.APP_CONFIG.chatPath}${message.FileUrl}" class="msg-video"></video>
                            <button class="download-btn" onclick="chatApp.downloadFile('${window.APP_CONFIG.chatPath}${message.FileUrl}')">
                                <i class="fa-solid fa-download"></i>
                            </button>
                        </div>
                    `;
                }
                if (message.MessageBody) {
                    content += `<div class="msg-caption">${this.escapeHtml(message.MessageBody)}</div>`;
                }
                break;
                
            case 'audio':
                if (message.FileUrl) {
                    content = `
                        <div class="audio-message">
                            <button class="audio-download-btn" onclick="chatApp.downloadFile('${window.APP_CONFIG.chatPath}${message.FileUrl}')" title="Download audio">
                                <i class="fa-solid fa-download"></i>
                            </button>
                            <div class="audio-player-wrap">
                                <audio controls src="${window.APP_CONFIG.chatPath}${message.FileUrl}"></audio>
                            </div>
                        </div>
                    `;
                }
                break;
                
            case 'call':
                content = `
                    <div class="call-message">
                        <i class="fa-solid fa-phone"></i>
                        <span>${this.escapeHtml(message.MessageBody)}</span>
                    </div>
                `;
                break;
                
            case 'file':
                if (message.FileUrl) {
                    content = `
                        <a href="${window.APP_CONFIG.chatPath}${message.FileUrl}" download class="file-message">
                            <i class="fa-solid fa-file-alt"></i>
                            <span>${this.escapeHtml(message.MessageBody || message.FileUrl)}</span>
                            <i class="fa-solid fa-download"></i>
                        </a>
                    `;
                } else {
                    content = this.escapeHtml(message.MessageBody);
                }
                break;
                
            default:
                content = this.escapeHtml(message.MessageBody);
        }
        
        return content;
    }
    
    sendMessage() {
        const message = this.elements.msgInput.value.trim();
        if (!message && !this.elements.fileInput.files.length) return;
        
        if (!this.activeReceiverID) {
            this.showToast('Please select a user to chat with', 'error');
            return;
        }
        
        // Validate file if present
        if (this.elements.fileInput.files.length > 0) {
            const file = this.elements.fileInput.files[0];
            const validationError = this.validateFile(file);
            if (validationError) {
                this.showToast(validationError, 'error');
                return;
            }
        }
        
        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('receiver_id', this.activeReceiverID);
        
        if (message) {
            formData.append('message', message);
        }
        
        if (this.elements.fileInput.files.length > 0) {
            formData.append('chat_file', this.elements.fileInput.files[0]);
        }
        
        this.elements.sendBtn.disabled = true;
        this.elements.sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        
        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();
        
        // Track upload progress
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                this.elements.sendBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${Math.round(percentComplete)}%`;
            }
        });
        
        xhr.addEventListener('load', () => {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.status === 'success') {
                    this.elements.msgInput.value = '';
                    this.elements.fileInput.value = '';
                    this.loadMessages();
                    this.updateLastMessage(this.activeReceiverID, message || this.getFileDescription(data.type));
                    this.showToast('Message sent successfully', 'success');
                } else {
                    this.showToast(data.message || 'Failed to send message', 'error');
                }
            } catch (e) {
                this.showToast('Error processing response', 'error');
            }
            this.elements.sendBtn.disabled = false;
            this.elements.sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
        });
        
        xhr.addEventListener('error', () => {
            this.showToast('Network error while sending message', 'error');
            this.elements.sendBtn.disabled = false;
            this.elements.sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
        });
        
        xhr.addEventListener('abort', () => {
            this.showToast('Upload cancelled', 'info');
            this.elements.sendBtn.disabled = false;
            this.elements.sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
        });
        
        xhr.open('POST', API_PATH);
        xhr.timeout = 600000; // 10 minute timeout for large videos
        xhr.send(formData);
    }
    
    validateFile(file) {
        // File size limits (in bytes)
        const maxImageSize = 50 * 1024 * 1024;   // 50 MB
        const maxVideoSize = 200 * 1024 * 1024;  // 200 MB
        const maxAudioSize = 50 * 1024 * 1024;   // 50 MB
        const maxFileSize = 100 * 1024 * 1024;   // 100 MB
        
        const ext = file.name.split('.').pop().toLowerCase();
        const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        const videoExts = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'flv', '3gp', 'ogv', 'mts', 'ts', 'm2ts'];
        const audioExts = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        
        let maxSize = maxFileSize;
        let type = 'file';
        
        if (imageExts.includes(ext)) {
            maxSize = maxImageSize;
            type = 'image';
        } else if (videoExts.includes(ext)) {
            maxSize = maxVideoSize;
            type = 'video';
        } else if (audioExts.includes(ext)) {
            maxSize = maxAudioSize;
            type = 'audio';
        }
        
        if (file.size > maxSize) {
            const maxSizeMB = (maxSize / (1024 * 1024)).toFixed(1);
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(1);
            return `File size (${fileSizeMB}MB) exceeds ${maxSizeMB}MB limit for ${type}s`;
        }
        
        return null;
    }
    
    getFileDescription(fileType) {
        const descriptions = {
            'image': '📷 Photo',
            'video': '🎥 Video',
            'audio': '🎵 Voice message',
            'file': '📎 File'
        };
        return descriptions[fileType] || '📎 File';
    }
    
    async toggleRecording() {
        if (!this.activeReceiverID) {
            this.showToast('Please select a user first', 'error');
            return;
        }
        
        if (this.isRecording) {
            this.stopRecording();
        } else {
            await this.startRecording();
        }
    }
    
    async startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.mediaRecorder = new MediaRecorder(stream);
            this.audioChunks = [];
            
            this.mediaRecorder.ondataavailable = event => {
                this.audioChunks.push(event.data);
            };
            
            this.mediaRecorder.onstop = async () => {
                const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                const audioFile = new File([audioBlob], 'voice_message.webm', { type: 'audio/webm' });
                
                const formData = new FormData();
                formData.append('action', 'send');
                formData.append('receiver_id', this.activeReceiverID);
                formData.append('type', 'audio');
                formData.append('chat_file', audioFile);
                
                fetch(API_PATH, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.loadMessages();
                        }
                    });
                
                stream.getTracks().forEach(track => track.stop());
                this.elements.audioBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
                this.elements.audioBtn.classList.remove('recording');
                this.isRecording = false;
            };
            
            this.mediaRecorder.start();
            this.isRecording = true;
            this.elements.audioBtn.innerHTML = '<i class="fa-solid fa-stop"></i>';
            this.elements.audioBtn.classList.add('recording');
            this.showToast('Recording... Click again to stop', 'info');
            
        } catch (err) {
            console.error('Error accessing microphone:', err);
            this.showToast('Unable to access microphone', 'error');
        }
    }
    
    stopRecording() {
        if (this.mediaRecorder && this.isRecording) {
            this.mediaRecorder.stop();
        }
    }
    
    async startCall(callType) {
        if (!this.activeReceiverID) {
            this.showToast('Please select a user to call', 'error');
            return;
        }

        try {
            // Request media permissions
            const constraints = {
                audio: true,
                video: callType === 'video'
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);

            // Send call notification to backend
            const formData = new FormData();
            formData.append('action', 'start_call');
            formData.append('receiver_id', this.activeReceiverID);
            formData.append('call_type', callType);

            const response = await fetch(API_PATH, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showToast(`Starting ${callType} call...`, 'success');
                this.openCallWindow(callType, stream);
            } else {
                this.showToast('Failed to start call', 'error');
                // Stop media tracks if call failed
                stream.getTracks().forEach(track => track.stop());
            }

        } catch (err) {
            console.error('Error accessing media devices:', err);
            if (err.name === 'NotAllowedError') {
                this.showToast('Camera/microphone access denied', 'error');
            } else if (err.name === 'NotFoundError') {
                this.showToast('Camera/microphone not found', 'error');
            } else {
                this.showToast('Unable to access camera/microphone', 'error');
            }
        }
    }
    
    openCallWindow(callType, stream) {
        // Create call window HTML with modern design matching the app theme
        const callWindowHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${callType === 'video' ? 'Video' : 'Audio'} Call - ${this.activeReceiverName}</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        font-family: 'Plus Jakarta Sans', sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        height: 100vh;
                        overflow: hidden;
                    }
                    .call-container {
                        display: flex;
                        flex-direction: column;
                        height: 100vh;
                    }
                    .call-header {
                        background: rgba(255, 255, 255, 0.1);
                        backdrop-filter: blur(10px);
                        padding: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                    }
                    .caller-info {
                        display: flex;
                        align-items: center;
                        gap: 15px;
                        color: white;
                    }
                    .caller-avatar {
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                        object-fit: cover;
                        border: 3px solid white;
                    }
                    .caller-details h3 {
                        font-size: 1.1rem;
                        font-weight: 600;
                        margin-bottom: 2px;
                    }
                    .caller-details p {
                        font-size: 0.9rem;
                        opacity: 0.8;
                    }
                    .call-status {
                        color: white;
                        font-size: 0.9rem;
                        opacity: 0.8;
                    }
                    .video-container {
                        flex: 1;
                        position: relative;
                        background: #0f0f1a;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .remote-video {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        display: none;
                    }
                    .local-video {
                        position: absolute;
                        bottom: 30px;
                        right: 30px;
                        width: 200px;
                        height: 150px;
                        border-radius: 12px;
                        border: 3px solid white;
                        object-fit: cover;
                        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
                    }
                    .audio-call-display {
                        text-align: center;
                        color: white;
                    }
                    .audio-avatar {
                        width: 120px;
                        height: 120px;
                        border-radius: 50%;
                        object-fit: cover;
                        border: 4px solid white;
                        margin: 0 auto 20px;
                        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
                    }
                    .audio-name {
                        font-size: 1.5rem;
                        font-weight: 600;
                        margin-bottom: 10px;
                    }
                    .audio-status {
                        font-size: 1rem;
                        opacity: 0.8;
                    }
                    .call-controls {
                        background: rgba(255, 255, 255, 0.1);
                        backdrop-filter: blur(10px);
                        padding: 30px;
                        display: flex;
                        justify-content: center;
                        gap: 20px;
                        border-top: 1px solid rgba(255, 255, 255, 0.1);
                    }
                    .control-btn {
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        border: none;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.2rem;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                    }
                    .control-btn:hover {
                        transform: scale(1.1);
                    }
                    .mute-btn {
                        background: rgba(255, 255, 255, 0.2);
                        color: white;
                    }
                    .mute-btn.muted {
                        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                        color: white;
                    }
                    .video-btn {
                        background: rgba(255, 255, 255, 0.2);
                        color: white;
                    }
                    .video-btn.off {
                        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                        color: white;
                    }
                    .end-call-btn {
                        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                        color: white;
                        width: 70px;
                        height: 70px;
                        font-size: 1.4rem;
                    }
                    .end-call-btn:hover {
                        background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
                        transform: scale(1.1);
                    }
                    .connecting {
                        animation: pulse 2s infinite;
                    }
                    @keyframes pulse {
                        0%, 100% { opacity: 1; }
                        50% { opacity: 0.5; }
                    }
                </style>
            </head>
            <body>
                <div class="call-container">
                    <div class="call-header">
                        <div class="caller-info">
                            <img src="${this.activeReceiverAvatar}" alt="${this.activeReceiverName}" class="caller-avatar">
                            <div class="caller-details">
                                <h3>${this.activeReceiverName}</h3>
                                <p>${callType === 'video' ? 'Video' : 'Audio'} Call</p>
                            </div>
                        </div>
                        <div class="call-status connecting">Connecting...</div>
                    </div>

                    <div class="video-container" id="videoContainer">
                        ${callType === 'video' ?
                            `<video id="remoteVideo" class="remote-video" autoplay playsinline></video>
                             <video id="localVideo" class="local-video" autoplay playsinline muted></video>` :
                            `<div class="audio-call-display">
                                <img src="${this.activeReceiverAvatar}" alt="${this.activeReceiverName}" class="audio-avatar">
                                <div class="audio-name">${this.activeReceiverName}</div>
                                <div class="audio-status connecting">Connecting...</div>
                             </div>`
                        }
                    </div>

                    <div class="call-controls">
                        <button class="control-btn mute-btn" id="muteBtn" title="Toggle mute">
                            <i class="fas fa-microphone"></i>
                        </button>
                        ${callType === 'video' ?
                            `<button class="control-btn video-btn" id="videoBtn" title="Toggle video">
                                <i class="fas fa-video"></i>
                            </button>` : ''
                        }
                        <button class="control-btn end-call-btn" id="endCallBtn" title="End call">
                            <i class="fas fa-phone-slash"></i>
                        </button>
                    </div>
                </div>

                <script>
                    let localStream = null;
                    let isMuted = false;
                    let isVideoOn = true;
                    let callWindow = window;

                    // Initialize media stream
                    try {
                        localStream = arguments[0]; // Passed from parent window
                        const localVideo = document.getElementById('localVideo');
                        if (localVideo && localStream) {
                            localVideo.srcObject = localStream;
                        }

                        // Update status after a short delay
                        setTimeout(() => {
                            document.querySelectorAll('.connecting').forEach(el => {
                                el.textContent = 'Connected';
                                el.classList.remove('connecting');
                            });
                        }, 2000);

                    } catch (e) {
                        console.error('Error setting up media stream:', e);
                    }

                    // Control button handlers
                    document.getElementById('muteBtn').onclick = function() {
                        if (localStream) {
                            const audioTracks = localStream.getAudioTracks();
                            if (audioTracks.length) {
                                isMuted = !isMuted;
                                audioTracks[0].enabled = !isMuted;
                                this.classList.toggle('muted', isMuted);
                                this.querySelector('i').className = isMuted ? 'fas fa-microphone-slash' : 'fas fa-microphone';
                            }
                        }
                    };

                    const videoBtn = document.getElementById('videoBtn');
                    if (videoBtn) {
                        videoBtn.onclick = function() {
                            if (localStream) {
                                const videoTracks = localStream.getVideoTracks();
                                if (videoTracks.length) {
                                    isVideoOn = !isVideoOn;
                                    videoTracks[0].enabled = isVideoOn;
                                    this.classList.toggle('off', !isVideoOn);
                                    this.querySelector('i').className = isVideoOn ? 'fas fa-video' : 'fas fa-video-slash';
                                }
                            }
                        };
                    }

                    document.getElementById('endCallBtn').onclick = function() {
                        endCall();
                    };

                    function endCall() {
                        if (localStream) {
                            localStream.getTracks().forEach(track => track.stop());
                        }
                        if (callWindow && !callWindow.closed) {
                            callWindow.close();
                        }
                    }

                    // Handle window close
                    window.onbeforeunload = function() {
                        if (localStream) {
                            localStream.getTracks().forEach(track => track.stop());
                        }
                    };

                    // Handle page visibility change (when user switches tabs)
                    document.addEventListener('visibilitychange', function() {
                        if (document.hidden && localStream) {
                            // Could pause tracks or show notification
                        }
                    });
                </script>
            </body>
            </html>
        `;

        // Open call window
        const callWindow = window.open('', '_blank', 'width=900,height=700,scrollbars=no,resizable=yes,status=no,toolbar=no,menubar=no');

        if (callWindow) {
            callWindow.document.write(callWindowHTML);
            callWindow.document.close();

            // Pass the stream to the new window
            callWindow.localStream = stream;

            // Handle window close
            const checkClosed = setInterval(() => {
                if (callWindow.closed) {
                    clearInterval(checkClosed);
                    // Stop all tracks when call window is closed
                    stream.getTracks().forEach(track => track.stop());
                }
            }, 1000);
        } else {
            this.showToast('Please allow popups for calls', 'error');
            // Stop media tracks if window couldn't open
            stream.getTracks().forEach(track => track.stop());
        }
    }
    
    showMediaModal(src, type) {
        let modal = document.getElementById('media-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'media-modal';
            modal.className = 'media-modal';
            modal.onclick = () => modal.style.display = 'none';
            document.body.appendChild(modal);
        }
        
        if (type === 'image') {
            modal.innerHTML = `<img src="${src}" class="modal-media">`;
        } else if (type === 'video') {
            modal.innerHTML = `<video src="${src}" class="modal-media" controls autoplay></video>`;
        }
        
        modal.style.display = 'flex';
    }
    
    downloadFile(url) {
        const a = document.createElement('a');
        a.href = url;
        a.download = url.split('/').pop();
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
    
    updateLastMessage(userId, message) {
        const contactItems = document.querySelectorAll('.contact-item');
        for (let item of contactItems) {
            if (item.getAttribute('onclick')?.includes(userId)) {
                const lastMsgSpan = item.querySelector('.last-message');
                if (lastMsgSpan) {
                    lastMsgSpan.textContent = message.length > 30 ? message.substring(0, 30) + '...' : message;
                }
                break;
            }
        }
    }
    
    formatTime(dateStr) {
        return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    formatTimeShort(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return Math.floor(diff / 60000) + 'm';
        if (diff < 86400000) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }
    
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    canEditMessage(message) {
        if (!message || !message.SentAt) return false;

        // Allow editing for messages sent by current user (all allowed types)
        const editableTypes = ['text', 'image', 'video', 'audio', 'file'];
        if (!editableTypes.includes(message.MessageType)) return false;
        if (String(message.SenderID) !== String(CURRENT_USER_ID)) return false;

        // No window limit, user can edit anytime
        return true;
    }
    
    escapeJs(text) {
        if (!text) return '';
        return text.replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n');
    }
    
    openEditModal(messageId, originalText) {
        const modal = document.createElement('div');
        modal.className = 'edit-modal-overlay';
        modal.id = `edit-modal-${messageId}`;
        modal.innerHTML = `
            <div class="edit-modal">
                <div class="modal-header">
                    <h3>Edit Message</h3>
                    <button class="close-btn" onclick="document.getElementById('edit-modal-${messageId}').remove()">&times;</button>
                </div>
                <div class="modal-body">
                    <textarea id="edit-textarea" class="edit-textarea">${this.escapeHtml(originalText)}</textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" onclick="document.getElementById('edit-modal-${messageId}').remove()">Cancel</button>
                    <button class="btn-save" onclick="chatApp.saveEditedMessage(${messageId})">Save</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
        
        setTimeout(() => {
            const textarea = document.getElementById('edit-textarea');
            if (textarea) {
                textarea.focus();
                textarea.select();
            }
        }, 100);
    }
    
    saveEditedMessage(messageId) {
        const textarea = document.getElementById('edit-textarea');
        if (!textarea) return;
        
        const newText = textarea.value.trim();
        if (!newText) {
            this.showToast('Message cannot be empty', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'edit');
        formData.append('message_id', messageId);
        formData.append('message', newText);
        
        fetch(API_PATH, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`edit-modal-${messageId}`).remove();
                    this.loadMessages();
                    this.showToast('Message updated', 'success');
                } else {
                    this.showToast(data.error || 'Failed to update message', 'error');
                }
            })
            .catch(err => {
                console.error('Error editing message:', err);
                this.showToast('Error updating message', 'error');
            });
    }
    
    confirmDeleteMessage(messageId) {
        if (!confirm('Are you sure you want to delete this message?')) {
            return;
        }

        this.scheduleDeleteMessage(messageId);
    }

    scheduleDeleteMessage(messageId) {
        if (this.pendingDeletes[messageId]) return;

        this.pendingDeletes[messageId] = true;
        this.renderMessages();

        const undoTimeout = setTimeout(() => {
            this.performDeleteMessage(messageId);
        }, 6000);

        this.pendingDeletes[messageId] = { timeoutId: undoTimeout };

        this.showToast('Message deleted. Undo?', 'success', 'Undo', () => {
            this.undoDeleteMessage(messageId);
        });
    }

    undoDeleteMessage(messageId) {
        const pending = this.pendingDeletes[messageId];
        if (pending) {
            clearTimeout(pending.timeoutId);
            delete this.pendingDeletes[messageId];
            this.renderMessages();
            this.showToast('Delete cancelled', 'info');
        }
    }

    performDeleteMessage(messageId) {
        delete this.pendingDeletes[messageId];

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('message_id', messageId);

        fetch(API_PATH, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.loadMessages();
                    this.showToast('Message deleted', 'success');
                } else {
                    this.showToast(data.error || 'Failed to delete message', 'error');
                    this.loadMessages();
                }
            })
            .catch(err => {
                console.error('Error deleting message:', err);
                this.showToast('Error deleting message', 'error');
                this.loadMessages();
            });
    }
    
    showToast(message, type, actionText = null, actionCallback = null) {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'toast';
            document.body.appendChild(toast);
        }

        toast.innerHTML = `<span>${message}</span>`;
        if (actionText && actionCallback) {
            const actionBtn = document.createElement('button');
            actionBtn.textContent = actionText;
            actionBtn.className = 'toast-action';
            actionBtn.onclick = (event) => {
                event.stopPropagation();
                actionCallback();
            };
            toast.appendChild(actionBtn);
        }

        toast.className = `toast ${type}`;
        toast.style.display = 'flex';

        clearTimeout(toast.hideTimeout);
        toast.hideTimeout = setTimeout(() => {
            toast.style.display = 'none';
        }, 4500);
    }
    
    cleanup() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }
}

// Initialize chat application
let chatApp;
document.addEventListener('DOMContentLoaded', () => {
    chatApp = new MessageChat();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (chatApp) {
        chatApp.cleanup();
    }
});
