document.addEventListener('DOMContentLoaded', () => {

    const messagesDiv = document.getElementById('messages');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    const conversationDiv = document.getElementById('DMlist');

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

    function loadConversations() {
        fetch('/projects/samtalerpanett/direct_messages/fetch_conversation.php')
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    data.forEach(conversation => appendConversation(conversation, true));
                    conversationDiv.scrollTop = conversationDiv.scrollHeight;
                }
            })
            .catch(error => {
                console.error("Failed to load conversation:", error);
            });
    }


    // lager websocket
    const ws = new WebSocket('ws://localhost:8080/chat');

    const currentUsername = window.currentUsername;
    const currentProfilePictureUrl = window.currentProfilePictureUrl;


    ws.onclose = () => {
        console.log('Websocket-tilkobling lukket :(');
        const msgElem = document.createElement('div');
        msgElem.textContent = '[System] Tilkoblingen ble lukket.';
        msgElem.style.color = 'red';
        messagesDiv.appendChild(msgElem);
    };

    ws.onopen = () => {
        console.log('Websocket-tilkobling åpnet');
        loadChatLog();
        loadConversations();
    };

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data); // parser eventen som JSON
        appendMessage(data);

        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        if (data.type === 'dm' && (data.username === currentUsername || data.to_username === currentUsername)) {
            // Oppdater samtalelisten i sidepanelet
            loadConversations();

            // Hvis aktiv chat er med denne brukeren, append meldingen i chatvinduet
            if (window.activeChatUserId &&
                (data.username === currentUsername && data.to_user_id === window.activeChatUserId) ||
                (data.to_username === currentUsername && data.username === window.activeChatUsername)) {
                appendMessage(data);
            }
        }
    };

    sendButton.onclick = () => {
        sendMessage();
    };

    // legger til hotkey for send - du kan trykke på enter (deilig)
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
            console.log("Du skrev ingenting bro")
        }

        // sjekker hvis meldingen er over 1000 tegn, og hvis den er det så blir Big Brother sur og fucker deg opp
        if(text.length > 1000) {
            sending = false;
            console.error("Meldingen din er for lang, bro, lock in. (over 1000 tegn)");

            // json er så jævlig nice bro - isak
            const errorMessage = {
                username: "System",
                message: "Meldingen er for lang. Maks 1000 tegn.",
                profilePictureUrl: "uploads/default.png"
            }
            appendMessage(errorMessage);

            return;
        }

        const messageData = {
            username: currentUsername,
            message: text,
            profilePictureUrl: currentProfilePictureUrl,
        };

        if (window.activeChatUserId) {
            // Hvis vi har en aktiv DM chat, legg til to_user_id og to_username
            messageData.to_user_id = window.activeChatUserId;
            messageData.to_username = window.activeChatUsername;
        }

        if(ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        } else {
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
            text.style.color = "#E30713";
            username.style.color = "#B5050E";
            wrapper.style.backgroundColor = "#E0E0FF";
            text.style.backgroundcolor = "#E0E0FF";
        }

        // hvis brukernavnet til brukeren du er logget inn som er det samme som den som sendte meldingen
        if(data.username === window.currentUsername) {
            wrapper.style.backgroundColor = "#E9E9FF";
            username.style.backgroundColor = "#E9E9FF"
            text.style.backgroundColor = "#E9E9FF";
            wrapper.style.flexDirection = "row-reverse";
            wrapper.style.textAlign = "right";
            wrapper.style.marginLeft = "auto";
        }
        else {
            wrapper.style.backgroundColor = "#F1F1F1";
        }

        content.appendChild(username);
        content.appendChild(text);
        wrapper.appendChild(avatar);
        wrapper.appendChild(content);

        messagesDiv.prepend(wrapper);
    }

    function appendConversation(convo) {
        // unngå duplikater ved å sjekke om element allerede finnes
        if (document.getElementById('convo-' + convo.other_user_id)) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('conversation');
        wrapper.id = 'convo-' + convo.other_user_id;

        const name = document.createElement('span');
        name.classList.add('conversation-name');
        name.textContent = convo.other_username;

        const preview = document.createElement('div');
        preview.classList.add('conversation-preview');
        preview.textContent = convo.last_message;

        wrapper.appendChild(name);
        wrapper.appendChild(preview);

        // Når du klikker på denne samtalen, last inn chat med denne brukeren
        wrapper.addEventListener('click', () => {
            openChatWith(convo.other_user_id, convo.other_username);
        });

        document.getElementById('DMList').appendChild(wrapper);
    }

    function openChatWith(userId, username) {
    // Oppdater header med brukernavn
    document.getElementById('header').textContent = "Samtale med " + username;

    // Tøm meldinger-delen før ny lasting
    messagesDiv.innerHTML = '';

    // Last meldinger for denne samtalen
    fetch('/projects/samtalerpanett/direct_messages/fetch_messages.php?user_id=' + userId)
        .then(res => res.json())
        .then(messages => {
            messages.forEach(msg => appendMessage(msg));
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        })
        .catch(console.error);

    // Sett en global variabel for aktiv chat (kan brukes når sender melding)
    window.activeChatUserId = userId;
    window.activeChatUsername = username;

    }




});