document.addEventListener('DOMContentLoaded', () => {

    const messagesDiv = document.getElementById('messages');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    function loadChatLog() {
        fetch('/projects/samtalerpanett/global_chat/get_global_logs.php')
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    data.forEach(message => appendMessage(message, true));
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            })
            .catch(error => {
                console.error("Failed to load chat log:", error);
            });
    }

    const ws = new WebSocket('ws://localhost:8080/chat');

    const currentUsername = window.currentUsername;
    const currentProfilePictureUrl = window.currentProfilePictureUrl;



    ws.onclose = () => {
        console.log('WebSocket-tilkobling lukket');
        const msgElem = document.createElement('div');
        msgElem.textContent = '[System] Tilkoblingen ble lukket.';
        msgElem.style.color = 'red';
        messagesDiv.appendChild(msgElem);
    };

    ws.onopen = () => {
        console.log('WebSocket-Connection Opened');
        loadChatLog();
    };

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        appendMessage(data, false);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    };

    sendButton.onclick = () => {
        sendMessage();
    };

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault;
            sendMessage();
        }
    });


    let sending = false;

    function sendMessage() {
        if (sending) return;
        sending = true;

        const text = input.value.trim();
        if (text === '') {
            sending = false;
            return;
        }

        const messageData = {
            username: currentUsername,
            message: text,
            profilePictureUrl: currentProfilePictureUrl
        };
        if(ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        }
        else {
            console.warn('WebSocket is not connected. Message not sent.');
            const systemMessage = {
                username: "System",
                message: "Melding kunne ikke sendes, kobling er stengt",
                profilePictureUrl: "uploads/default.png"
            }
            appendMessage(systemMessage);
            sending = false;
            return;
        }

        input.value = '';
        setTimeout(() => {sending = false;}, 100);
    }

    // styler den nydelige meldinger til bruker
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

        if(data.username === "System") {
            text.style.color = "red";
            username.style.color = "darkred";
        }
        content.appendChild(username);
        content.appendChild(text);
        wrapper.appendChild(avatar);
        wrapper.appendChild(content);

        messagesDiv.prepend(wrapper);
    }


});