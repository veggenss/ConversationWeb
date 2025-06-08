<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

require __DIR__ . '/vendor/autoload.php';


class Chat implements MessageComponentInterface {
    //definerer $clients
    protected $clients;

    //idfk
    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    //åpner connection til chatroom
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "({$conn->resourceId}) has Connected!\n";
    }

    //DM melding lagring hvis client sender i dms
    private function storeDirectMessage($fromUsername, $toUserId, $message){
        require_once  __DIR__ . '/include/db.inc.php';
        $conn = getDBconnection();
        if(!$conn) return;

        //Finner hvem som sender melding
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $fromUsername);
        $stmt->execute();
        $stmt->bind_result($fromUserId);
        if (!$stmt->fetch()){
            echo "Fant ikke bruker med brukernavn: $fromUsername\n";
            $stmt->close();
            return;
        }
        $stmt->close();

        if(!$fromUserId){
            return;
        }

        //finner DMen
        $stmt = $conn->prepare("SELECT id FROM dm_conversations WHERE (user1_id = ? AND user2_id = ?) OR (user2_id = ? AND user1_id = ?)");
        $stmt->bind_param("ssss", $fromUserId, $toUserId, $toUserId, $fromUserId);
        $stmt->execute();
        $stmt->bind_result($conversationId);
        if (!$stmt->fetch()){
            echo "Fant ikke samtaler mellom $fromUserId og $toUserId\n";
            $stmt->close();
            return;
        }
        $stmt->close();

        //lagrere melding i DB
        $stmt = $conn->prepare("INSERT INTO dm_messages (conversation_id, user_id, to_user_id, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $conversationId, $fromUserId, $toUserId, $message);
        $stmt->execute();
        $stmt->close();
    }

    //Når melding blir sent:
    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);

        if (!isset($data['username'], $data['profilePictureUrl'], $data['message'])) {
            return; // ignorer misdannet data
        }

        // base url, path til profilbilde, og full profilbilde link
        $baseUrl = 'http://localhost/projects/samtalerpanett';
        $profilePictureFile = basename($data['profilePictureUrl']);
        $fullProfilePictureUrl = $baseUrl . '/uploads/' . $profilePictureFile;

        //Ser om meldingen er fra DM eller global chat
        $type = isset($data['to_user_id']) ? 'dm' : 'global';

        //binder sender info
        $messageData = [
            'type' => $type,
            'username' => $data['username'],
            'profilePictureUrl' => $fullProfilePictureUrl,
            'message' => $data['message']
        ];

        // Ser om meldingen ble sent i DMs eller global, vi vil jo ikke at DMs blir leaket i global chat sånn at alle kan se de!
        if($type === 'dm'){
            $this->storeDirectMessage($data['username'], $data['to_user_id'], $data['message']);
            return;
        }
        else{
            file_put_contents(__DIR__ . '/global_chat/global_chat_log.txt', json_encode($messageData) . PHP_EOL, FILE_APPEND);
        }

        //Sender meldingen ;)
        $encodedMessage = json_encode($messageData);
        foreach ($this->clients as $clientConn) {
            $clientConn->send($encodedMessage);
        }
    }

    //sender melding hvis bruker disconnecter fra chatroom
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "({$conn->resourceId}) has disconnected\n";
    }

    //Error melding
    public function onError(ConnectionInterface $conn, \Exception $e) {
        file_put_contents(__DIR__ . '/WebSocket_error.log' . "\n" . date('c') . " Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
        $conn->close();
    }
}
//etablerer connection til websocket
$server = new App('localhost', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();