document.addEventListener('DOMContentLoaded', () => {

    // ==== Global variabler ====
    const messagesDiv = document.getElementById('messages');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const conversationDiv = document.getElementById('DMList');
    const newDMButton = document.getElementById('newDM');

    const currentUserId = window.currentUserId;
    const currentUsername = window.currentUsername;
    const currentProfilePictureUrl = window.currentProfilePictureUrl;

    let sending = false;
    let ws = null;

    // Variabler for å spore akriv chat
    window.activeChatUserId = null;
    window.activeChatUsername = null;



    // ==== Initialisering ====
    function init() {
        setupWebSocket();
        loadChatLog();
        loadConversations();
        setupEventListeners();
    }



    // ==== Event listeners ====
    function setupEventListeners() {
        sendButton.onclick = sendMessage;

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        newDMButton.addEventListener('click', () => {
            startNewConversation();
        });
    }



    // ==== WebSocket håndtering ====
    function setupWebSocket() {
        ws = new WebSocket('ws://localhost:8080/chat');
        
        ws.onopen = () => {
            console.log('Websocket-tilkobling åpnet');
            ws.send(JSON.stringify({ type: 'register', user_id: currentUserId }));
        };

        ws.onclose = () => {
            console.log('Websocket-tilkobling lukket :(');
            appendSystemMessage('Tilkoblingen ble lukket.');
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            handleIncomingMessage(data);
        };
    }



    // ==== Fetching og lasting av data ====
    function loadChatLog() {
        fetch('/projects/samtalerpanett/global_chat/get_global_logs.php')
            .then(res => res.json())
            .then(data => {
                if (Array.isArray(data)) {
                    messagesDiv.innerHTML = ''; // Clear før lasting
                    data.forEach(message => appendMessage(message, true));
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            })
            .catch(console.error);
    }

    function loadConversations() {
        fetch('/projects/samtalerpanett/direct_messages/fetch_conversation.php')
            .then(res => res.json())
            .then(data => {
                if (Array.isArray(data)) {
                    conversationDiv.innerHTML = ''; // Clear gamle listen
                    data.forEach(conversation => appendConversation(conversation));
                    conversationDiv.scrollTop = conversationDiv.scrollHeight;
                }
            })
            .catch(console.error);
    }



    // ==== Sende meldinger ====
    function sendMessage() {
        if (sending) return;
        sending = true;

        const text = input.value.trim();
        if (text === '') {
            sending = false;
            console.log("Du skrev ingenting bro");
            return;
        }

        if (text.length > 600) {
            sending = false;
            appendSystemMessage("Meldingen er for lang. Maks 600 tegn.");
            return;
        }

        const messageData = {
            username: currentUsername,
            message: text,
            profilePictureUrl: currentProfilePictureUrl,
        };

        if(window.activeChatUserId) {
            messageData.type = 'dm';
            messageData.to_user_id = window.activeChatUserId;

            appendMessage({
                username: currentUsername,
                message: text,
                profilePictureUrl: currentProfilePictureUrl,
                type: 'dm',
                to_user_id: window.activeChatUserId
            });
        }
        else{
            messageData.type = 'global';
        }
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        } else {
            appendSystemMessage("Melding kunne ikke sendes, kobling er stengt");
        }

        input.value = '';
        setTimeout(() => { sending = false; }, 100);
    }



    // ==== Håndtering inkommende meldinger ====
    function handleIncomingMessage(data) {
        appendMessage(data);

        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Hvis DM involverer nåværende bruker så oppdater conversations og chat
        if (data.type === 'dm' && (data.username === currentUsername || data.to_username === currentUsername)) {
            loadConversations();

            if (window.activeChatUserId &&
                ((data.username === currentUsername && data.to_user_id === window.activeChatUserId) ||
                (data.to_username === currentUsername && data.username === window.activeChatUsername))) {
                appendMessage(data);
            }
        }
    }



    // ==== UI rendering funksjoner ====
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
        
        // Style system meldinger
        if (data.username === "[System]") {
            text.style.color = "#E30713";
            username.style.color = "#B5050E";
        }

        // Style egene meldinger 
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

    function appendConversation(convo) {
        // Unngå duplikater
        if (document.getElementById('convo-' + convo.other_user_id)) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('conversation');
        wrapper.id = 'convo-' + convo.other_user_id;

        const recipient_wrapper = document.createElement('div');
        recipient_wrapper.classList.add('conversation-user');

        const conv_avatar = document.createElement('img');
        conv_avatar.classList.add('conversation-avatar');
        conv_avatar.src = convo.other_profile_picture;

        const name = document.createElement('span');
        name.classList.add('conversation-name');
        name.textContent = convo.other_username;

        const preview = document.createElement('div');
        preview.classList.add('conversation-preview');
        preview.textContent = convo.last_message;

        recipient_wrapper.appendChild(conv_avatar);
        recipient_wrapper.appendChild(name);
        wrapper.appendChild(recipient_wrapper);
        wrapper.appendChild(preview);

        // On click, åpne den conversationen
        wrapper.addEventListener('click', () => {
            openChatWith(convo.conversation_id, convo.other_user_id, convo.other_username);
        });

        conversationDiv.appendChild(wrapper);
    }



    // ==== Chat bytting ====
    function openChatWith(conversationId, userId, username) {
        document.getElementById('header').textContent = "Samtale med " + username;
        messagesDiv.innerHTML = '';

        fetch('/projects/samtalerpanett/direct_messages/fetch_messages.php?conversation_id=' + conversationId)
            .then(res => res.json())
            .then(messages => {
                if (Array.isArray(messages) && messages.length > 0){
                    messages.forEach(msg => appendMessage(msg));
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                };
            })
            .catch(console.error);

        window.activeChatUserId = userId;
        window.activeChatUsername = username;
        window.activeConversation = conversationId;
    }



    // ==== Ny conversation starter ====
    function startNewConversation() {
        const usernameToDM = prompt("Skriv inn brukernavn for å starte ny samtale:");
        if (!usernameToDM) return;

        //Finner bruker ID
        fetch('/projects/samtalerpanett/direct_messages/find_user_by_username.php?username=' + encodeURIComponent(usernameToDM))
        .then(res => res.json())
        .then (data => {
            if(!data.id){
                alert('Bruker ikke funnet');
                return;
            };
            
            // Hvis den finner bruker id, start conversation
            fetch('/projects/samtalerpanett/direct_messages/start_conversation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: data.id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadConversations();
                    openChatWith(data.other_user_id, usernameToDM);
                } else {
                    alert("Kunne ikke starte samtale: " + data.error);
                }
            })
            .catch(err => {
                console.error('Error starting conversation:', err);
                alert('En feil oppstod ved start av samtale.');
            });
        });
    };



    // ==== Start appen ====
    init();

});
