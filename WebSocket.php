<?php
// Velkommen til WebSocket.php - Her ligger masse alien kode som ingen kan fortså :)

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/include/db.inc.php';
$mysqli = dbConnection();
$d = date("[Y/m/d/l:H:i:s] ");
class Chat implements MessageComponentInterface { protected $clients; protected $userConnections = []; public function __construct() { $this->clients = new \SplObjectStorage(); }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        parse_str($conn->httpRequest->getUri()->getQuery(), $query);
        if(isset($query['userId'])){
            $userId = $query['userId'];
            $conn->userId = $userId;
            if(!isset($this->userConnections[$userId])){
                $this->userConnections[$userId] = new \SplObjectStorage();
            }
            $this->userConnections[$userId]->attach($conn);
            echo date("[Y/m/d/l:H:i:s] ") . "User $userId | $conn->resourceId has Connected!\n";
        }
        else{
            echo date("[Y/m/d/l:H:i:s] ") . "[$d]  Unknown user connected $conn->resourceId\n";
        }
    }

    private function directMessage($mysqli, $messageData){

        //finner conversation Id hvor userid og recipient id matcher
        $conv_query = "SELECT id FROM dm_conversations WHERE (user1_id = ? AND user2_id = ?) OR (user2_id = ? AND user1_id = ?)";
        $conv_stmt = $mysqli->prepare($conv_query);
        if(!$conv_stmt){
            echo date("[Y/m/d/l:H:i:s] ") . "prepare conversation query failed $mysqli->error \n";
            return;
        }

        $conv_stmt->bind_param("iiii", $messageData['userId'], $messageData['recipientId'], $messageData['userId'], $messageData['recipientId']);
        $conv_stmt->execute();
        $conv_stmt->store_result();

        if($conv_stmt->num_rows === 0){
            echo date("[Y/m/d/l:H:i:s] ") . "Kunne ikke finne samtale mellom " . $messageData['userId'] . " og " . $messageData['recipientId'];
            return;
        }

        $conversationId = NULL;
        $conv_stmt->bind_result($conversationId);
        $conv_stmt->fetch();

        $msg_query = "INSERT INTO dm_messages (conversation_id, sender_id, to_user_id, message) VALUES (?, ?, ?, ?)";

        $msg_stmt = $mysqli->prepare($msg_query);
        if(!$msg_stmt){
            echo date("[Y/m/d/l:H:i:s] ") . "message prepare failed $mysqli->error \n";
            return;
        }

        $msg_stmt->bind_param("iiis", $conversationId, $messageData['userId'], $messageData['recipientId'], $messageData['message']);
        if(!$msg_stmt->execute()){
            echo date("[Y/m/d/l:H:i:s] ") . "message insertion failed $mysqli->error \n";
            return;
        }
        $this->sendToUser($messageData['userId'], $messageData['recipientId'], json_encode($messageData));
    }

    private function sendToUser($userId, $recipientId, $message){
        //Sender til senderen
        if(isset($this->userConnections[$userId])){
            foreach($this->userConnections[$userId] as $conn){
                $conn->send($message);
            }
        }

        //Sender til recipient
        if(isset($this->userConnections[$recipientId])){
            foreach($this->userConnections[$recipientId] as $conn){
                $conn->send($message);
            }
        }
    }
    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);
        if (!$data || !isset($data['username'], $data['message'], $data['profilePictureUrl'])) return;

        $userId = $fromConn->userId ?? null;
        if(!$userId){
            echo date("[Y/m/d/l:H:i:s] ") . "User ID mangler fra tilkoblingen\n";
            return;
        }

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
        echo date("[Y/m/d/l:H:i:s] ") . "User $userId | $conn->resourceId has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        file_put_contents(__DIR__ . '/WebSocket_error.log', date('c') . " Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
        $conn->close();
    }
}

$server = new App('localhost', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();
