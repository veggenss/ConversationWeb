document.addEventListener('DOMContentLoaded', () => {

    // ==== Globale Variabler ====
    const messagesDiv = document.getElementById('messages');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const newDM = document.getElementById('newDM');
    const dmList = document.getElementById('DMList');
    const globalEnable = document.getElementById('global-enable');
    const currentUserId = window.currentUserId;
    const currentUsername = window.currentUsername;
    const currentProfilePictureUrl = window.currentProfilePictureUrl;
    
    let recipientId = window.recipientId;
    let activeChatState = window.activeChatState;
    let sending = false;
    let ws = null;


    // ==== Initializer ====
    function init() {
        setupWebSocket();
        loadChatLog();
        loadConversationDiv();
        setupEventListeners();
    }




    // ==== Event Listeners ====
    function setupEventListeners() {
        sendButton.onclick = sendMessage;

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        newDM.addEventListener('click', () => {
            newConversation();
        })

        globalEnable.addEventListener('click', () => {
            activeChatState = "global";
            recipientId = "all";
            loadChatLog();
            console.log(activeChatState);

        })
    }




    // ==== Kobler til WebSocket ====
    function setupWebSocket() {
        ws = new WebSocket('ws://localhost:8080?userId=' + encodeURIComponent(currentUserId));

        ws.onopen = () => {
            console.log('WebSocket connection opened');
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




    // ==== Laster in Global Chat Logger ====
    function loadChatLog() {
        fetch('/samtalerpanett/global_chat/get_global_logs.php')
            .then(res => res.json())
            .then(data => {
                messagesDiv.innerHTML = '';
                data.forEach(message => appendMessage(message, true));
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .catch(console.error);
    }




    // ==== Melding Behandling og Sending ====
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
            recipientId: recipientId,
            state: activeChatState,
            username: currentUsername,
            userId: currentUserId,
            message: text,
            profilePictureUrl: currentProfilePictureUrl,
        };

        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(messageData));
        } 
        else {
            appendSystemMessage("WebSocket er frakoblet.");
        }

        input.value = '';
        setTimeout(() => { sending = false; }, 100);
    }




    // ==== Styler Meldinger ====
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

        // System melding styling
        if (data.username === "[System]") {
            text.style.color = "#E30713";
            username.style.color = "#B5050E";
        }

        // Unik Style for dinne egene meldinger
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




    // ==== Definerer System Meldinger =====
    function appendSystemMessage(message) {
        appendMessage({
            username: "[System]",
            message,
            profilePictureUrl: "uploads/default.png"
        });
    }


    // ==== Ny samtale ====
    function newConversation(){
        const recipientUser = prompt("Skriv in brukernavn til bruker du vil ha samtale med");
        if(!recipientUser){
            alert("Venligst skriv noe i felte");
            return;
        }
        if(currentUsername === recipientUser){
            alert("Du kan ikke starte samtale med degselv");
            return;
        }
        //omgjør brukernavn til id
        fetch('/samtalerpanett/direct_messages/dm_functions.php?action=get_user_id&recipientUser=' + encodeURIComponent(recipientUser))
        .then(res => res.json())
        .then(data => {
            if(data.success === false){
                alert(data.response);
                return;
            }

            //Lager Conversation row i db
            fetch('/samtalerpanett/direct_messages/dm_functions.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'createConversation', user1_id: currentUserId, user2_id: data.recipientUserId})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success === false){
                    alert(data.response)
                    return;
                }

                alert(data.response);
                loadConversationDiv();
            })
            .catch(err => {
                console.error('Fetch error', err);
            });
        })
        .catch(err => {
            console.error('Fetch error', err);
        });

    }


    // ==== Laster aktive samtaler ====
    function loadConversationDiv(){
        fetch('/samtalerpanett/direct_messages/dm_functions.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'loadConversationDiv', user_id: currentUserId})
        })
        .then(res => res.json())
        .then(data => {
            if(data.success === true && Array.isArray(data.conversations)){
                console.log(data.response);
                data.conversations.forEach(conv => {
                    console.log("Lastet samtale med \"" + conv.recipientUsername + "\"");
                    renderConversation(conv);
                });
            }
            else{
                console.log(data.response);
                return;
            }
        })
        .catch(err => {
            console.error('Fetch Error', err);
        });
    }


    // ==== Styler DM Listen ====
    function renderConversation(conv){

        if(document.getElementById('conversation-' + conv.conversation_id)) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('conversation');
        wrapper.id = 'conversation-' + conv.conversation_id;

        const recipientWrapper = document.createElement('div');
        recipientWrapper.classList.add('conversation-user');

        const recipientAvatar = document.createElement('img');
        recipientAvatar.classList.add('conversation-avatar');
        recipientAvatar.src = conv.recipient_profile_icon;

        const recipientUsername = document.createElement('span');
        recipientUsername.classList.add('conversation-name');
        recipientUsername.textContent = conv.recipientUsername;

        const convPreview = document.createElement('span');
        convPreview.classList.add('conversation-preview');
        convPreview.textContent = conv.last_message;

        recipientWrapper.appendChild(recipientAvatar);
        recipientWrapper.appendChild(recipientUsername);
        wrapper.appendChild(recipientWrapper);
        wrapper.appendChild(convPreview);

        

        wrapper.addEventListener('click', () => {
            console.log("Åpnet nesten chat med", conv.recipientUsername);
            activeChatState = "direct";
            console.log(activeChatState, recipientId);

            loadConvLog(conv);
        });

        dmList.appendChild(wrapper);
    }



    // ==== Laster in messagehistorie mellom user1 og user2
    function loadConvLog(conv){
        messagesDiv.innerHTML = ''; //clearer chat meldinger fra forige chat

        fetch('/samtalerpanett/direct_messages/dm_functions.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'loadConversationLog', conversation_id: conv.conversation_id, user1_id: currentUserId, user1_name: currentUsername, user2_id: conv.recipientId, user2_name: conv.recipientUsername})
        })
        .then(res => res.json())
        .then(data => {
            if(data.success === false){
                alert(data.response);
                return;
            }
            messagesDiv.innerHTML = '';
            data.forEach(message => {
                appendMessage(message, true);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
        })
    }

    init();
});
