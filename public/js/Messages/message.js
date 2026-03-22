document.addEventListener('DOMContentLoaded', () => {
    const userListContainer = document.getElementById('user-list-container');
    const chatActive = document.getElementById('chat-active');
    const chatWelcome = document.getElementById('chat-welcome');
    const chatBox = document.getElementById('chat-box');
    const msgInput = document.getElementById('msg-input');
    const sendBtn = document.getElementById('send-btn');
    
    let activeReceiverID = null;

    function fetchUsers() {
        fetch(`${API_PATH}?action=fetch_users`)
            .then(res => res.json())
            .then(users => {
                if (users.error) {
                    userListContainer.innerHTML = `<p class="error-msg">${users.error}</p>`;
                    return;
                }
                renderUserList(users);
            })
            .catch(err => console.error('Error fetching users:', err));
    }

    function renderUserList(users) {
        userListContainer.innerHTML = users.map(user => {
            const userImg = user.ProfilePic ? `../../../public/img/${user.ProfilePic}` : 'https://via.placeholder.com/50';
            return `
                <div class="contact-item" onclick="selectUser('${user.UserID}', '${user.Username}', '${userImg}')">
                    <img src="${userImg}" class="contact-avatar">
                    <div class="contact-info">
                        <span class="contact-name">${user.Username}</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Window function ងាយស្រួលហៅពី HTML string
    window.selectUser = function(id, name, img) {
        activeReceiverID = id;
        chatWelcome.style.display = 'none';
        chatActive.style.display = 'flex';
        
        document.getElementById('active-user-name').innerText = name;
        document.getElementById('active-user-img').src = img;
        
        loadMessages();
    };

    function loadMessages() {
        if (!activeReceiverID) return;

        fetch(`${API_PATH}?action=fetch&receiver_id=${activeReceiverID}`)
            .then(res => res.json())
            .then(messages => {
                const myAvatar = document.getElementById('my-avatar-img').src;
                const receiverAvatar = document.getElementById('active-user-img').src;

                chatBox.innerHTML = messages.map(m => {
                    const isMe = m.SenderID == CURRENT_USER_ID;
                    const avatar = isMe ? myAvatar : (m.ProfilePic && m.ProfilePic !== 'default.png' ? `../../../public/img/${m.ProfilePic}` : receiverAvatar);
                    
                    return `
                        <div class="message-wrapper ${isMe ? 'me' : 'them'}">
                            ${!isMe ? `<img src="${avatar}" class="msg-avatar">` : ''}
                            <div class="msg-content">
                                <div class="msg-bubble">${m.MessageBody}</div>
                                <span class="msg-time">${formatTime(m.SentAt)}</span>
                            </div>
                            ${isMe ? `<img src="${avatar}" class="msg-avatar">` : ''}
                        </div>
                    `;
                }).join("");
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    function sendMessage() {
        const message = msgInput.value.trim();
        if (!message || !activeReceiverID) return;

        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('message', message);
        formData.append('receiver_id', activeReceiverID);

        fetch(API_PATH, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    msgInput.value = '';
                    loadMessages();
                }
            });
    }

    function formatTime(dateStr) {
        return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Events
    sendBtn.onclick = sendMessage;
    msgInput.onkeydown = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    fetchUsers();
    setInterval(loadMessages, 3000);
});