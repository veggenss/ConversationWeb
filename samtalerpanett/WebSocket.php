<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/include/db.inc.php';

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userConnections = [];
    protected $dbConn = null;

    
    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    private function getDbConnection() {
        if (!$this->dbConn) {
            $this->dbConn = getDBconnection();
            if (!$this->dbConn) {
                echo "Database tilkobling mislyktes\n";
            }
        }
        return $this->dbConn;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "({$conn->resourceId}) has Connected!\n";
    }

    private function storeDirectMessage($fromUsername, $toUserId, $message){
        $dbConn = $this->getDbConnection();
        if (!$dbConn) return;

        $fromUserId = NULL;
        $conversationId = NULL;

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

        $stmt = $dbConn->prepare("SELECT id FROM dm_conversations WHERE (user1_id = ? AND user2_id = ?) OR (user2_id = ? AND user1_id = ?)");
        $stmt->bind_param("iiii", $fromUserId, $toUserId, $toUserId, $fromUserId);
        $stmt->execute();
        $stmt->bind_result($conversationId);
        if (!$stmt->fetch()){
            echo "Fant ikke samtaler mellom id $fromUserId og id $toUserId\n";
            $stmt->close();
            return;
        }
        $stmt->close();

        $stmt = $dbConn->prepare("INSERT INTO dm_messages (conversation_id, user_id, to_user_id, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $conversationId, $fromUserId, $toUserId, $message);
        $stmt->execute();
        $stmt->close();
    }

    private function getUserIdByUsername($username) {
        $dbConn = $this->getDbConnection();
        if (!$dbConn) return null;

        $userId = null;
        $stmt = $dbConn->prepare("SELECT id FROM users WHERE username = ?");
        if (!$stmt) return null;

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($userId);
        $result = $stmt->fetch();
        $stmt->close();

        return $result ? $userId : null;
    }

    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);
        if (!$data) return;

        if (isset($data['type']) && $data['type'] === 'register' && isset($data['user_id'])){
            $userId = $data['user_id'];
            if (!isset($this->userConnections[$userId])){
                $this->userConnections[$userId] = new \SplObjectStorage();
            }
            $this->userConnections[$userId]->attach($fromConn);
            return;
        }

        if (!isset($data['username'], $data['profilePictureUrl'], $data['message'])) {
            return;
        }

        $type = isset($data['to_user_id']) ? 'dm' : 'global';
        $baseUrl = 'http://localhost/projects/samtalerpanett';
        $profilePictureFile = basename($data['profilePictureUrl']);
        $fullProfilePictureUrl = $baseUrl . '/uploads/' . $profilePictureFile;

        $messageData = [
            'type' => $type,
            'username' => $data['username'],
            'profilePictureUrl' => $fullProfilePictureUrl,
            'message' => $data['message']
        ];

        if ($type === 'dm') {
            $this->storeDirectMessage($data['username'], $data['to_user_id'], $data['message']);

            $senderUserId = $this->getUserIdByUsername($data['username']);
            $recipientUserId = $data['to_user_id'];

            $dbConn = $this->getDbConnection();
            if (!$dbConn) return;

            $recipientUsername = null;
            $stmt = $dbConn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->bind_param("i", $recipientUserId);
            $stmt->execute();
            $stmt->bind_result($recipientUsername);
            $stmt->fetch();
            $stmt->close();

            $messageData['to_username'] = $recipientUsername;
            $messageToSend = json_encode($messageData);

            if (isset($this->userConnections[$senderUserId])) {
                foreach ($this->userConnections[$senderUserId] as $conn){
                    $conn->send($messageToSend);
                }
            }

            if ($recipientUserId !== $senderUserId && isset($this->userConnections[$recipientUserId])){
                foreach ($this->userConnections[$recipientUserId] as $conn){
                    $conn->send($messageToSend);
                }
            }
            return;
        } else {
            file_put_contents(__DIR__ . '/global_chat/global_chat_log.txt', json_encode($messageData) . PHP_EOL, FILE_APPEND);
        }

        $encodedMessage = json_encode($messageData);
        foreach ($this->clients as $clientConn) {
            $clientConn->send($encodedMessage);
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