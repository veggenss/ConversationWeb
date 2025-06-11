document.addEventListener('DOMContentLoaded', () => {
    const messagesDiv = document.getElementById('messages');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    const currentUserId = window.currentUserId;
    const currentUsername = window.currentUsername;
    const currentProfilePictureUrl = window.currentProfilePictureUrl;

    let sending = false;
    let ws = null;

    function init() {
        setupWebSocket();
        loadChatLog();
        setupEventListeners();
    }

    function setupEventListeners() {
        sendButton.onclick = sendMessage;

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    function setupWebSocket() {
        ws = new WebSocket('ws://localhost:8080/chat');

        ws.onopen = () => {
            console.log('WebSocket connection opened');
            ws.send(JSON.stringify({ type: 'register', user_id: currentUserId }));
        };

        ws.onclose = () => {
            console.log('WebSocket connection closed');
            appendSystemMessage('Tilkoblingen ble lukket.');
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            appendMessage(data);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        };
    }

    function loadChatLog() {
        fetch('/projects/samtalerpanett/global_chat/get_global_logs.php')
            .then(res => res.json())
            .then(data => {
                messagesDiv.innerHTML = '';
                data.forEach(message => appendMessage(message, true));
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .catch(console.error);
    }

    function sendMessage() {
        if (sending) return;
        sending = true;

        const text = input.value.trim();
        if (text === '') {
            sending = false;
            return;
        }

        if (text.length > 600) {
            sending = false;
            appendSystemMessage("Meldingen er for lang. Maks 600 tegn.");
            return;
        }

        const messageData = {
            type: 'global',
            username: currentUsername,
            message: text,
            profilePictureUrl: currentProfilePictureUrl,
        };

        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        } else {
            appendSystemMessage("WebSocket er frakoblet.");
        }

        input.value = '';
        setTimeout(() => { sending = false; }, 100);
    }

    function appendMessage(data) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('message');

        const avatar = document.createElement('img');
        avatar.classList.add('avatar');
        avatar.src = data.profilePictureUrl || 'default.png';

        const content = document.createElement('div');
        content.classList.add('message-content');

        const username = document.createElement('span');
        username.classList.add('username');
        username.textContent = data.username || 'Ukjent';

        const text = document.createElement('div');
        text.classList.add('text');
        text.textContent = data.message;

        if (data.username === "[System]") {
            text.style.color = "#E30713";
            username.style.color = "#B5050E";
        }

        if (data.username === currentUsername) {
            wrapper.style.backgroundColor = "#E9E9FF";
            wrapper.style.flexDirection = "row-reverse";
            wrapper.style.textAlign = "right";
            wrapper.style.marginLeft = "auto";
        }

        content.appendChild(username);
        content.appendChild(text);
        wrapper.appendChild(avatar);
        wrapper.appendChild(content);

        messagesDiv.prepend(wrapper);
    }

    function appendSystemMessage(message) {
        appendMessage({
            username: "[System]",
            message,
            profilePictureUrl: "uploads/default.png"
        });
    }

    init();
});
