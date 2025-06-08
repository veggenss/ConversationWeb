<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

require __DIR__ . '/vendor/autoload.php';


class Chat implements MessageComponentInterface {
    //definerer $clients
    protected $clients;
    protected $userConnections = [];

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
    private function storeDirectMessage($dbConn, $fromUsername, $toUserId, $message){

        if(!$dbConn) return;

        //Finner hvem som sender melding
        $stmt = $dbConn->prepare("SELECT id FROM users WHERE username = ?");
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
        $stmt = $dbConn->prepare("SELECT id FROM dm_conversations WHERE (user1_id = ? AND user2_id = ?) OR (user2_id = ? AND user1_id = ?)");
        $stmt->bind_param("iiii", $fromUserId, $toUserId, $toUserId, $fromUserId);
        $stmt->execute();
        $stmt->bind_result($conversationId);
        if (!$stmt->fetch()){
            echo "Fant ikke samtaler mellom $fromUserId og $toUserId\n";
            $stmt->close();
            return;
        }
        $stmt->close();

        //lagrere melding i DB
        $stmt = $dbConn->prepare("INSERT INTO dm_messages (conversation_id, user_id, to_user_id, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $conversationId, $fromUserId, $toUserId, $message);
        $stmt->execute();
        $stmt->close();
    }

    private function getUserIdByUsername($dbConn, $username) {
        if(!$dbConn) return;

        //Finner hvem som sender melding
        $stmt = $dbConn->prepare("SELECT id FROM users WHERE username = ?");
        if(!$stmt) return null;

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($userId);
        if ($stmt->fetch()){
            $stmt->close();
            return $userId;
        }
        else{
            $stmt->close();
            return null;
        }
    }

    //Når melding blir sent:
    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);

        //Når vi får en connection som har type: register så lagrer vi den
        if (isset($data['type']) && $data['type'] === 'register' && isset($data['user_id'])){
            $userId = $data['user_id'];
            if (!isset($this->userConnections[$userId])){
                $this->userConnections[$userId] = new \SplObjectStorage();
            }
            $this->userConnections[$userId]->attach($fromConn);
            return;
        }

        require_once  __DIR__ . '/include/db.inc.php';
        $dbConn = getDBconnection();
        if(!$dbConn) {
            echo "Database tilkobling mislykkes";
            return;
        }


        if (!isset($data['username'], $data['profilePictureUrl'], $data['message'])) {
            return; // ignorer misdannet data
        }

        //Ser om meldingen er fra DM eller global chat
        $type = isset($data['to_user_id']) ? 'dm' : 'global';

        // base url, path til profilbilde, og full profilbilde link
        $baseUrl = 'http://localhost/projects/samtalerpanett';
        $profilePictureFile = basename($data['profilePictureUrl']);
        $fullProfilePictureUrl = $baseUrl . '/uploads/' . $profilePictureFile;



        //binder sender info
        $messageData = [
            'type' => $type,
            'username' => $data['username'],
            'profilePictureUrl' => $fullProfilePictureUrl,
            'message' => $data['message']
        ];

        // Ser om meldingen ble sent i DMs eller global, vi vil jo ikke at DMs blir leaket i global chat sånn at alle kan se de!
        if($type === 'dm'){
            $this->storeDirectMessage($dbConn, $data['username'], $data['to_user_id'], $data['message']);

            $senderUserId = $this->getUserIdByUsername($dbConn, $data['username']);
            $recipientUserId = $data['to_user_id'];
            
            $messageToSend = json_encode($messageData);

            if(isset($this->userConnections[$senderUserId])) {
                foreach ($this->userConnections[$senderUserId] as $conn){
                    $conn->send($messageToSend);
                }
            }

            if($recipientUserId !== $senderUserId && isset($this->userConnections[$recipientUserId])){
                foreach($this->userConnections[$recipientUserId] as $conn){
                    $conn->send($messageToSend);
                }
            }
            return;
        }
        else{
            file_put_contents(__DIR__ . '/global_chat/global_chat_log.txt', json_encode($messageData) . PHP_EOL, FILE_APPEND);
        }

        //Sender meldingen ;)
        $encodedMessage = json_encode($messageData);
        foreach ($this->clients as $clientConn) {
            $clientConn->send($encodedMessage);
        }    }


    //sender melding hvis bruker disconnecter fra chatroom
    public function onClose(ConnectionInterface $conn) {
        foreach ($this->userConnections as $userId => $connections) {
            if ($connections->contains($conn)) {
                $connections->detach($conn);
                if (count($connections) === 0) {
                    unset($this->userConnections[$userId]);
                }
                break;
            }
        }
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