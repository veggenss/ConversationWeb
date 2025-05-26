document.addEventListener('DOMContentLoaded', () =>{
    const ws = new WebSocket('ws://localhost:8080/chat');

    const messagesDiv = document.getElementById('messages');
    const input = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    ws.onopen = () => {
    console.log('WebSocket-tilkobling åpnet');
    };

    ws.onmessage = (event) => {
    // Mottatt melding fra server
    const msg = event.data;
    const msgElem = document.createElement('div');
    msgElem.textContent = msg;
    msgElem.style.padding = '5px 0';
    messagesDiv.appendChild(msgElem);
    messagesDiv.scrollTop = messagesDiv.scrollHeight; // Scroll til bunn
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

    // Her kan du eventuelt legge til brukernavn, timestamp osv.
    ws.send(text);
    input.value = '';

    // Vis egen melding i chatten
    const msgElem = document.createElement('div');
    msgElem.textContent = 'Du: ' + text;
    msgElem.style.fontWeight = 'bold';
    messagesDiv.appendChild(msgElem);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

})
