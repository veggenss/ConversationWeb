<?php
// Velkommen til WebSocket.php - Her ligger masse alien kode som ingen kan fortså :)

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/include/db.inc.php';
$mysqli = dbConnection();

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userConnections = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        parse_str($conn->httpRequest->getUri()->getQuery(), $query);
        if(isset($query['userId'])){
            $userId = $query['userId'];
            if(!isset($this->userConnections[$userId])){
                $this->userConnections[$userId] = new \SplObjectStorage();
            }
            $this->userConnections[$userId]->attach($conn);
            echo "User $userId ({$conn->resourceId}) has Connected!\n";
        }
        else{
            echo "Unknown user connected ({$conn->resourceId})\n";
        }
    }

    private function directMessage($mysqli, $messageData){

        //finner conversation Id hvor userid og recipient id matcher
        $conv_query = "SELECT id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user2_id = ? AND user1_id = ?)";
        $conv_stmt = $mysqli->prepare($conv_query);
        $conv_stmt->bind_param("iiii", $messageData['userId'], $messageData['recipientId'], $messageData['recipientId'], $messageData['userId']);
        $conv_result = $conv_stmt->get_result();
        if(!$row = $conv_result->fetch_assoc()){
            echo "Kunne ikke finne samtale mellom " . $messageData['userId'] . "og " . $messageData['recipientId'];
            return;
        }
        else{
            $conversationId = $row['id'];

            //inserter melding og sånn in i messages
            $msg_query = "INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)";
            $msg_stmt = $mysqli->prepare($msg_query);
            $msg_stmt->bind_param("iis", $conversationId, $messageData['userId'], $messageData['message']);
            $msg_stmt->execute();

            $this->sendToUser($messageData['recipientId'], json_encode($messageData));
        }
    }

    private function sendToUser($userId, $message){
        if(isset($this->userConnections[$userId])){
            foreach($this->userConnections[$userId] as $conn){
                $conn->send($message);
            }
        }
    }
    public function onMessage(ConnectionInterface $fromConn, $msg){
    
        $data = json_decode($msg, true);
        if (!$data || !isset($data['username'], $data['message'], $data['profilePictureUrl'])) return;

        $messageData = [
            'recipientId' => $data['recipientId'],
            'type' => $data['type'],
            'username' => $data['username'],
            'userId' => $data['userId'],
            'profilePictureUrl' => 'http://localhost/samtalerpanett/uploads/' . basename($data['profilePictureUrl']),
            'message' => $data['message']
        ];

        if($data['type'] === 'global' && $data['recipientId'] === 'all'){
            $encodedMessage = json_encode($messageData);
            foreach ($this->clients as $clientConn) {
                $clientConn->send($encodedMessage);
            }
            file_put_contents(__DIR__ . '/global_chat/global_chat_log.txt', json_encode($messageData) . PHP_EOL, FILE_APPEND);

        }
        elseif($data['type'] === 'direct' && $data['recipientId'] !== 'all'){
            $this->directMessage(dbConnection(), $messageData);
        }
        
    }

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

    public function onError(ConnectionInterface $conn, \Exception $e) {
        file_put_contents(__DIR__ . '/WebSocket_error.log', date('c') . " Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
        $conn->close();
    }
}

$server = new App('localhost', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();
?>
