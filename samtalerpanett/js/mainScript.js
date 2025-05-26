document.addEventListener('DOMContentLoaded', () => {
    const ws = new WebSocket('ws://localhost:8080/chat');

    const currentUsername = window.currentUsername;
    const currentProfilePictureUrl = window.currentProfilePictureUrl;

    const messagesDiv = document.getElementById('messages');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    ws.onopen = () => {
        console.log('WebSocket-tilkobling åpnet');
    };

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        appendMessage(data);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    };

    ws.onclose = () => {
        console.log('WebSocket-tilkobling lukket');
        const msgElem = document.createElement('div');
        msgElem.textContent = '[System] Tilkoblingen ble lukket.';
        msgElem.style.color = 'red';
        messagesDiv.appendChild(msgElem);
    };

    sendButton.onclick = () => {
        sendMessage();
    };

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    function sendMessage() {
        const text = input.value.trim();
        if (text === '') return;

        const messageData = {
            username: currentUsername,
            message: text,
            profilePictureUrl: currentProfilePictureUrl
        };

        ws.send(JSON.stringify(messageData));
        appendMessage(messageData, true);
        input.value = '';
    }

    // styler den nydelige meldinger til bruker
    function appendMessage(data) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('message');

        const avatar = document.createElement('img');
        avatar.classList.add('avatar');
        avatar.src = data.profilePictureUrl || 'default.jpg';

        const content = document.createElement('div');
        content.classList.add('message-content');

        const username = document.createElement('span');
        username.classList.add('username');
        username.textContent = data.username || 'Ukjent';

        const text = document.createElement('div');
        text.classList.add('text');
        text.textContent = data.message;

        content.appendChild(username);
        content.appendChild(text);
        wrapper.appendChild(avatar);
        wrapper.appendChild(content);

        messagesDiv.prepend(wrapper);
    }
});
