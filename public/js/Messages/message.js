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
        this.peer = null;
        this.myPeerId = null;
        this.incomingRingtone = null;
        this.isLoadingMessages = false;
        
        this.initElements();
        this.initEventListeners();
        this.initWebSocket();
        this.fetchFriends();
        this.startPolling();
        this.initPeerJS();
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
            activeUserProfileLink: document.getElementById('active-user-profile-link'),
            activeUserStatus: document.querySelector('.header-status'),
            activeUserOnlineDot: document.querySelector('.online-status'),
            myAvatar: document.getElementById('my-avatar-img')
        };
    }

    getProfileUrl(userId) {
        const id = Number(userId || 0);
        if (!id) return '../Profile/profile.php';
        return `../Profile/profile.php?user_id=${encodeURIComponent(id)}`;
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
    
    initPeerJS() {
        // Initialize PeerJS using the current user ID to make it predictable
        if (typeof CURRENT_USER_ID === 'undefined') return;
        
        // Include PeerJS dynamically
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js';
        script.onload = () => {
            // ប្រើប្រាស់ Auto-generated ID ដើម្បីកុំឲ្យជាន់គ្នាពេល Refresh Page
            this.peer = new Peer({ debug: 2 });

            this.peer.on('open', (id) => {
                console.log('PeerJS connected with ID:', id);
                // រក្សាទុក PeerID ចូល Database ស្វ័យប្រវត្តិ
                const formData = new FormData();
                formData.append('action', 'update_peer_id');
                formData.append('peer_id', id);
                fetch(API_PATH, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) console.error("Failed to update peer ID:", data.error);
                    }).catch(console.error);
            });

            this.peer.on('call', (call) => {
                // Incoming call detected!
                const callType = (call.metadata && call.metadata.callType) ? call.metadata.callType : 'video';
                this.showIncomingCallModal(call, callType);
            });
        };
        document.head.appendChild(script);
    }

    showIncomingCallModal(call, callType = 'video') {
        let overlay = document.getElementById('incoming-call-modal');
        if (overlay) overlay.remove();

        // បន្ថែមសំឡេងរោទ៍ (Ringtone) ពេលមានការខលចូល
        this.incomingRingtone = new Audio('https://actions.google.com/sounds/v1/alarms/phone_ringing.ogg');
        this.incomingRingtone.loop = true;
        // ប្រើ catch ដើម្បីការពារ Error ពេល Browser Block Autoplay
        this.incomingRingtone.play().catch(e => console.log("Autoplay blocked:", e));

        overlay = document.createElement('div');
        overlay.id = 'incoming-call-modal';
        overlay.innerHTML = `
            <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999999; display:flex; align-items:center; justify-content:center; font-family:'Plus Jakarta Sans', sans-serif; backdrop-filter: blur(5px);">
                <div style="background:white; padding:30px; border-radius:20px; text-align:center; max-width:320px; width:90%; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                    <div style="width: 80px; height: 80px; background: #ebf4ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #2563eb; font-size: 30px;">
                        <i class="fa-solid fa-phone-volume fa-shake"></i>
                    </div>
                    <h3 style="margin:0 0 10px 0; color:#1e293b; font-size:1.3rem;">Incoming Call</h3>
                    <p style="color:#64748b; margin-bottom:25px;">Someone wants to ${callType} call you...</p>
                    <div style="display:flex; gap:15px; justify-content:center;">
                        <button id="btn-reject-call" style="flex:1; background:#ef4444; color:white; border:none; padding:12px; border-radius:12px; cursor:pointer; font-weight:600; font-size:1rem; transition: 0.3s;"><i class="fa-solid fa-phone-slash"></i> Decline</button>
                        <button id="btn-answer-call" style="flex:1; background:#10b981; color:white; border:none; padding:12px; border-radius:12px; cursor:pointer; font-weight:600; font-size:1rem; transition: 0.3s;"><i class="fa-solid fa-phone"></i> Answer</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        document.getElementById('btn-reject-call').onclick = () => {
            call.close();
            overlay.remove();
            if (this.incomingRingtone) {
                this.incomingRingtone.pause();
                this.incomingRingtone.currentTime = 0;
            }
        };

        document.getElementById('btn-answer-call').onclick = () => {
            overlay.remove();
            
            // បិទសំឡេងរោទ៍វិញពេលចុចទទួល (Answer)
            if (this.incomingRingtone) {
                this.incomingRingtone.pause();
                this.incomingRingtone.currentTime = 0;
            }
            
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("⚠️ ប្រព័ន្ធសុវត្ថិភាព Browser បាន Block កាមេរ៉ា! លុះត្រាតែប្រើ Link HTTPS (Ngrok) ឬ Localhost ទើបអាចខលបាន។");
                this.showToast('Blocked! Please use HTTPS.', 'error');
                return;
            }

            // កំណត់សិទ្ធិបើក Camera/Mic ទៅតាមប្រភេទ Call (Video ឬ Audio)
            const constraints = {
                audio: true,
                video: callType === 'video'
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then((stream) => {
                    call.answer(stream);
                    this.openCallWindow(callType, stream, call);
                })
                .catch((err) => {
                    console.error('Failed to get local stream', err);
                    let errorMsg = 'Camera/Mic Error: ' + err.name;
                    if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                        errorMsg = 'Camera is already in use by another tab.';
                        alert("⚠️ កាមេរ៉ាកំពុងជាប់រវល់! សូមបិទ Tab ផ្សេងៗដែលកំពុងបើកកាមេរ៉ា (ដូចជា videocall_test.html) ជាមុនសិន។");
                    } else if (window.location.protocol === 'http:' && window.location.hostname !== 'localhost') {
                        errorMsg = 'Blocked! Please use HTTPS (Ngrok).';
                        alert("⚠️ សូមប្រើប្រាស់ Link Ngrok (HTTPS) ទើបអាចខលបាន!");
                    }
                    this.showToast(errorMsg, 'error');
                });
        };

        // ប្រសិនបើអ្នកខល ផ្តាច់ការខលទៅវិញមុនពេលយើងទទួលរូចរាល់
        call.on('close', () => {
            if (document.getElementById('incoming-call-modal')) document.getElementById('incoming-call-modal').remove();
            if (this.incomingRingtone) {
                this.incomingRingtone.pause();
                this.incomingRingtone.currentTime = 0;
            }
        });
    }

    startPolling() {
        // Poll only for NEW messages every 3 seconds (append only, no full re-render)
        this.pollingInterval = setInterval(() => {
            if (this.activeReceiverID) this.loadNewMessages();
        }, 3000);
        // Poll friends list every 60 seconds
        this.friendsInterval = setInterval(() => {
            this.fetchFriends();
        }, 60000);
    }

    isNearBottom() {
        const { chatBox } = this.elements;
        return chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 120;
    }

    loadNewMessages() {
        if (this.isLoadingMessages) return;

        const lastId = this.messages.length > 0
            ? Math.max(...this.messages.map(m => parseInt(m.MessageID)))
            : 0;
        const existingIds = new Set(this.messages.map(m => String(m.MessageID)));

        fetch(`${API_PATH}?action=fetch&receiver_id=${this.activeReceiverID}&after_id=${lastId}`)
            .then(res => res.json())
            .then(newMsgs => {
                if (!Array.isArray(newMsgs) || newMsgs.length === 0) return;
                const wasNearBottom = this.isNearBottom();
                newMsgs.forEach(m => {
                    if (existingIds.has(String(m.MessageID))) return;
                    this.messages.push(m);
                    const html = this.buildMessageHTML(m);
                    if (html) this.elements.chatBox.insertAdjacentHTML('beforeend', html);
                });
                if (wasNearBottom) {
                    this.elements.chatBox.scrollTop = this.elements.chatBox.scrollHeight;
                }
            })
            .catch(() => {});
    }

    buildMessageHTML(m) {
        const myAvatar = this.elements.myAvatar.src;
        const receiverAvatar = this.elements.activeUserImg.src;
        const isMe = m.SenderID == CURRENT_USER_ID;
        const avatar = isMe ? myAvatar : (m.ProfilePic ? (m.ProfilePic.startsWith('http') ? m.ProfilePic : `${window.APP_CONFIG.imagePath}${m.ProfilePic}`) : receiverAvatar);
        const messageContent = this.formatMessageContent(m);
        if (!messageContent.trim()) return '';
        const timeStr = this.formatTime(m.SentAt);
        const canEdit = isMe && this.canEditMessage(m);
        const otherUserProfileUrl = this.getProfileUrl(m.SenderID);
        return `
            <div class="message-wrapper ${isMe ? 'me' : 'them'}" id="message-${m.MessageID}">
                ${!isMe ? `<a href="${otherUserProfileUrl}" style="display:inline-flex;" title="View profile"><img src="${avatar}" class="msg-avatar"></a>` : ''}
                <div class="message-content">
                    ${m.MessageType === 'audio'
                        ? `<div style="background:transparent;border:none;padding:0;box-shadow:none;">${messageContent}</div>`
                        : `<div class="msg-bubble">${messageContent}</div>`
                    }
                    <span class="msg-time">${timeStr}</span>
                    <div class="message-actions">
                        ${canEdit ? `<button class="btn-edit" onclick="chatApp.openEditModal(${m.MessageID}, '${this.escapeJs(m.MessageBody)}')" title="Edit"><i class="fa-solid fa-pen"></i></button>` : ''}
                        <button class="btn-delete" onclick="chatApp.confirmDeleteMessage(${m.MessageID})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                ${isMe ? `<img src="${avatar}" class="msg-avatar">` : ''}
            </div>`;
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
            const userImg = friend.ProfilePic ? (friend.ProfilePic.startsWith('http') ? friend.ProfilePic : `${window.APP_CONFIG.imagePath}${friend.ProfilePic}`) : 'https://via.placeholder.com/50';
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
                <div class="contact-item" data-user-id="${friend.UserID}" onclick="chatApp.selectUser('${friend.UserID}', '${this.escapeHtml(friend.Username)}', '${userImg}', this)">
                    <div style="display:flex; align-items:center; gap:12px; flex:1; min-width:0; color:inherit;">
                        <img src="${userImg}" class="contact-avatar">
                        <div class="contact-info">
                            <div class="name-row">
                                <span class="contact-name">${this.escapeHtml(friend.Username)}</span>
                                <small>${this.formatTimeShort(friend.last_message_time)}</small>
                            </div>
                            <p class="last-message">${this.escapeHtml(lastMessage)}</p>
                        </div>
                    </div>
                    ${onlineBadge}
                    ${unreadBadge}
                </div>
            `;
        }).join('');
    }
    
    selectUser(id, name, img, itemElement, event) {
        if (event) {
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
        if (this.elements.activeUserProfileLink) {
            this.elements.activeUserProfileLink.href = this.getProfileUrl(id);
        }
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

        this.isLoadingMessages = true;
        fetch(`${API_PATH}?action=fetch&receiver_id=${this.activeReceiverID}`)
            .then(res => res.json())
            .then(messages => {
                this.messages = Array.isArray(messages) ? messages : [];
                this.renderMessages();
            })
            .catch(err => console.error('Error loading messages:', err))
            .finally(() => { this.isLoadingMessages = false; });
    }

    renderMessages() {
        this.elements.chatBox.innerHTML = this.messages
            .filter(m => !this.pendingDeletes[m.MessageID])
            .map(m => this.buildMessageHTML(m))
            .filter(h => h)
            .join('');
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
                    content = `<audio controls src="${window.APP_CONFIG.chatPath}${message.FileUrl}" style="height:38px;width:240px;border-radius:999px;display:block;"></audio>`;
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
        formData.append('csrf_token', window.APP_CONFIG?.csrfToken || '');

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
                } else {
                    this.showToast(data.message || 'Failed to send message', 'error');
                }
            } catch (e) {
                console.error('Error parsing response from PHP is:', xhr.responseText);
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
                formData.append('csrf_token', window.APP_CONFIG?.csrfToken || '');

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

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert("⚠️ ប្រព័ន្ធសុវត្ថិភាព Browser បាន Block កាមេរ៉ា! លុះត្រាតែប្រើ Link HTTPS (Ngrok) ឬ Localhost ទើបអាចខលបាន។");
            this.showToast('Blocked! Please use HTTPS.', 'error');
            return;
        }

        try {
            // 1. ទាញយក PeerID របស់មិត្តភក្តិពី Database សិន
            const res = await fetch(`${API_PATH}?action=get_peer_id&user_id=${this.activeReceiverID}`);
            const textResponse = await res.text();
            let data;
            try {
                data = JSON.parse(textResponse);
            } catch (jsonErr) {
                console.error('Invalid JSON response from server (PHP Error):', textResponse);
                this.showToast('Server connection error. Please check console.', 'error');
                return;
            }
            
            if (!data.success) {
                console.error("Server Error while getting peer ID:", data.error);
                this.showToast('Database Error! Check console.', 'error');
                return;
            }
            const receiverPeerId = data.peer_id;

            if (!receiverPeerId) {
                this.showToast('Friend is offline or unreachable right now.', 'error');
                return;
            }

            // Request media permissions
            const constraints = {
                audio: true,
                video: callType === 'video'
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);

            // Initialize PeerJS Call
            let outCall = null;
            if (this.peer && receiverPeerId) {
                outCall = this.peer.call(receiverPeerId, stream, { metadata: { callType: callType } });
                if (!outCall) {
                    console.warn("Could not initiate PeerJS call. Friend might be offline.");
                }
            }

            // បើកផ្ទាំង Call ភ្លាមៗ ដើម្បីកុំឲ្យរំលង Event 'stream' ដែលម្ខាងទៀតបញ្ជូនមករហ័សពេក
            this.openCallWindow(callType, stream, outCall);
            this.showToast(`Starting ${callType} call...`, 'success');

            // Send call notification to backend
            const formData = new FormData();
            formData.append('action', 'start_call');
            formData.append('receiver_id', this.activeReceiverID);
            formData.append('call_type', callType);

            // មិនចាំបាច់ await ទេ ព្រោះវាធ្វើឲ្យខាតពេលយឺតយ៉ាវក្នុងការតភ្ជាប់វីដេអូ
            fetch(API_PATH, { method: 'POST', body: formData }).catch(console.error);

        } catch (err) {
            console.error('Error accessing media devices:', err);
            let errorMsg = 'Camera/Mic Error: ' + err.name;
            if (err.name === 'NotAllowedError') {
                errorMsg = 'Camera/Mic access denied (Please allow in browser)';
            } else if (err.name === 'NotFoundError') {
                errorMsg = 'Camera/Mic not found on your device';
            } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                errorMsg = 'Camera is already in use by another tab.';
                alert("⚠️ កាមេរ៉ាកំពុងជាប់រវល់! សូមបិទ Tab ផ្សេងៗដែលកំពុងបើកកាមេរ៉ា (ដូចជា videocall_test.html) ជាមុនសិន។");
            } else {
                if (window.location.protocol === 'http:' && window.location.hostname !== 'localhost') {
                    alert("⚠️ Browser បាន Block កាមេរ៉ា! សូមប្រើប្រាស់ Link Ngrok (HTTPS) ទើបអាចខលបាន។");
                    errorMsg = 'Blocked! Please use HTTPS.';
                }
            }
            this.showToast(errorMsg, 'error');
        }
    }
    
    openCallWindow(callType, stream, existingCall = null) {
        // លុប Call Overlay ចាស់ចោលប្រសិនបើមាន
        let oldOverlay = document.getElementById('lc-call-overlay');
        if (oldOverlay) oldOverlay.remove();

        // បង្កើត Fullscreen Overlay ជំនួសឲ្យការប្រើ window.open()
        const overlay = document.createElement('div');
        overlay.id = 'lc-call-overlay';
        const callWindowHTML = `
            <style>
                #lc-call-overlay {
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    z-index: 999999; display: flex; flex-direction: column;
                    font-family: 'Plus Jakarta Sans', sans-serif;
                }
                .call-container { display: flex; flex-direction: column; height: 100%; }
                .call-header {
                    background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);
                    padding: 15px 20px; display: flex; align-items: center;
                    justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1);
                }
                .caller-info { display: flex; align-items: center; gap: 15px; color: white; }
                .caller-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid white; }
                .caller-details h3 { font-size: 1.1rem; font-weight: 600; margin: 0; }
                .caller-details p { font-size: 0.9rem; margin: 0; opacity: 0.8; }
                .call-status { color: white; font-size: 0.9rem; opacity: 0.8; }
                .video-container { flex: 1; position: relative; background: #0f0f1a; display: flex; align-items: center; justify-content: center; overflow: hidden; }
                .remote-video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; display: none; }
                .local-video {
                    position: absolute; bottom: 20px; right: 20px; width: 120px; height: 160px;
                    border-radius: 12px; border: 2px solid white; object-fit: cover; box-shadow: 0 4px 15px rgba(0,0,0,0.3); background: #222; z-index: 10;
                }
                /* Mobile Responsive Local Video */
                @media (min-width: 768px) { .local-video { width: 240px; height: 135px; } }
                .audio-call-display { text-align: center; color: white; }
                .audio-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid white; margin-bottom: 20px; }
                .call-controls {
                    background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);
                    padding: 20px; display: flex; justify-content: center; gap: 20px;
                }
                .control-btn {
                    width: 60px; height: 60px; border-radius: 50%; border: none; cursor: pointer;
                    display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: 0.3s;
                }
                .mute-btn, .video-btn { background: rgba(255, 255, 255, 0.2); color: white; }
                .mute-btn.muted, .video-btn.off { background: #ef4444; color: white; }
                .end-call-btn { background: #ef4444; color: white; width: 70px; height: 70px; font-size: 1.4rem; }
                .connecting { animation: pulse 2s infinite; }
                @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
            </style>
            
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
        `;

        overlay.innerHTML = callWindowHTML;
        document.body.appendChild(overlay);

        // កំណត់ Video Stream ទៅកាន់ Elements
        const localVideo = document.getElementById('localVideo');
        if (localVideo && stream) {
            localVideo.srcObject = stream;
        }

        if (existingCall) {
            existingCall.on('stream', function(remoteStream) {
                const remoteVideo = document.getElementById('remoteVideo');
                if (remoteVideo) {
                    remoteVideo.style.display = 'block';
                    remoteVideo.srcObject = remoteStream;
                }
                document.querySelectorAll('.connecting').forEach(el => {
                    el.textContent = 'Connected';
                    el.classList.remove('connecting');
                });
            });
            
            // ពេលមិត្តភក្តិទម្លាក់ទូរស័ព្ទ (End Call)
            existingCall.on('close', function() {
                stream.getTracks().forEach(track => track.stop());
                let overlay = document.getElementById('lc-call-overlay');
                if (overlay) overlay.remove();
                if (window.chatApp) window.chatApp.showToast('Call ended', 'info');
            });
        }

        // Control Buttons Logic
        let isMuted = false;
        let isVideoOn = true;

        document.getElementById('muteBtn').onclick = function() {
            const audioTracks = stream.getAudioTracks();
            if (audioTracks.length) {
                isMuted = !isMuted;
                audioTracks[0].enabled = !isMuted;
                this.classList.toggle('muted', isMuted);
                this.querySelector('i').className = isMuted ? 'fas fa-microphone-slash' : 'fas fa-microphone';
            }
        };

        const videoBtn = document.getElementById('videoBtn');
        if (videoBtn) {
            videoBtn.onclick = function() {
                const videoTracks = stream.getVideoTracks();
                if (videoTracks.length) {
                    isVideoOn = !isVideoOn;
                    videoTracks[0].enabled = isVideoOn;
                    this.classList.toggle('off', !isVideoOn);
                    this.querySelector('i').className = isVideoOn ? 'fas fa-video' : 'fas fa-video-slash';
                }
            };
        }

        // មុខងារបិទការខល
        document.getElementById('endCallBtn').onclick = function() {
            stream.getTracks().forEach(track => track.stop());
            if (existingCall) existingCall.close();
            document.getElementById('lc-call-overlay').remove();
        };
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
