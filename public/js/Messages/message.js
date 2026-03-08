document.addEventListener('DOMContentLoaded', () => {
    const userListContainer = document.getElementById('user-list-container');
    const chatActive = document.getElementById('chat-active');
    const chatWelcome = document.getElementById('chat-welcome');
    let activeReceiverID = null;
    const API_PATH = '../../../app/Controllers/Messages/ChatController.php';
    function fetchUsers() {
        fetch(`${API_PATH}?action=fetch_users`)
            .then(res => res.json())
            .then(users => {
                if (!Array.isArray(users)) {
                    if (users.error) {
                        userListContainer.innerHTML = `<p style="padding:20px; color:#f59e0b;">${users.error}</p>`;
                    }
                    return;
                }
                
                userListContainer.innerHTML = users.map(user => `
                    <div class="contact-item" data-id="${user.UserID}" data-username="${user.Username}">
                        <img src="${user.ProfilePic || 'https://via.placeholder.com/50'}" class="contact-avatar">
                        <div class="contact-info">
                            <span class="contact-name">${user.Username}</span>
                            
                        </div>
                    </div>
                `).join('');
                
                attachEvents();
            })
            .catch(err => console.error('Error fetching users:', err));
    }

    function attachEvents() {
        document.querySelectorAll('.contact-item').forEach(item => {
            item.onclick = function() {
                activeReceiverID = this.getAttribute('data-id');
                const userName = this.getAttribute('data-username');
                const userImg = this.querySelector('img').src;
                
                chatWelcome.style.display = 'none';
                chatActive.style.display = 'flex';

                chatActive.innerHTML = `
                    <div class="chat-header">
                        <div class="header-info" style="display: flex; align-items: center; gap: 12px;">
                            <div style="position: relative;">
                                <img src="${userImg}" class="header-avatar" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                                <span style="position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; background: #22c55e; border: 2px solid white; border-radius: 50%;"></span>
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: center;">
                                <span class="header-name" style="font-weight: 700; font-size: 16px; line-height: 1.2;">${userName}</span>
                                <span class="header-status" style="font-size: 13px; color: #22c55e;">Active now</span>
                            </div>
                        </div>
                        <div class="header-actions" style="display: flex; align-items: center; gap: 20px;">
                            <button class="call-btn"><i class="fa-solid fa-phone"></i></button>
                            <button class="call-btn"><i class="fa-solid fa-video"></i></button>
                            <button class="call-btn"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        </div>
                    </div>

                    <div class="chat-messages" id="chat-box"></div>

                    <div class="chat-input-area">
                        <button class="action-btn"><i class="fa-regular fa-image"></i></button>
                        <div class="input-wrapper">
                            <textarea id="msg-input" rows="1" placeholder="Type a message..."></textarea>
                        </div>
                        <button class="action-btn"><i class="fa-solid fa-microphone"></i></button>
                        <button id="send-btn" class="send-btn"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                `;
                
                const msgInput = document.getElementById('msg-input');
                document.getElementById('send-btn').onclick = sendMessage;
                
                msgInput.onkeydown = function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                };

                loadMessages();
            };
        });
    }

    function loadMessages() {
        if (!activeReceiverID) return;

        fetch(`${API_PATH}?action=fetch&receiver_id=${activeReceiverID}`)
            .then(res => res.json())
            .then(messages => {
                const box = document.getElementById("chat-box");
                if (!box) return;

                const receiverAvatar = document.querySelector(".header-avatar").src;

                box.innerHTML = messages.map(m => {
                    const isMe = m.SenderID == CURRENT_USER_ID; 

                    return `
                        <div class="message-wrapper ${isMe ? 'me' : 'them'}">
                            ${!isMe ? `<img src="${receiverAvatar}" class="msg-avatar">` : ''}
                            <div class="msg-content">
                                <div class="msg-bubble">${m.MessageBody}</div>
                                <span class="msg-time">
                                    ${new Date(m.SentAt).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}
                                </span>
                            </div>
                            ${isMe ? `<img src="https://i.pravatar.cc/150?u=me" class="msg-avatar">` : ''}
                        </div>
                    `;
                }).join("");

                box.scrollTop = box.scrollHeight;
            });
    }

    function sendMessage() {
        const input = document.getElementById('msg-input');
        const message = input.value.trim();
        if (!message || !activeReceiverID) return;

        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('message', message);
        formData.append('receiver_id', activeReceiverID);

        fetch(API_PATH, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    input.value = '';
                    loadMessages();
                }
            });
    }

    fetchUsers();
    setInterval(loadMessages, 3000);
});