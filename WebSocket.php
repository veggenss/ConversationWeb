<?php
// WebSocket.php - Nå med IoServer og mindre fjas :)

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/include/db.inc.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['username'], $data['message'], $data['profilePictureUrl'])) {
            echo "Invalid message received\n";
            return;
        }

        $messageData = [
            'recipientId' => $data['recipientId'],
            'state' => $data['state'],
            'username' => $data['username'],
            'userId' => $data['userId'],
            'profilePictureUrl' => 'http://localhost/samtalerpanett/uploads/' . basename($data['profilePictureUrl']),
            'message' => $data['message']
        ];

        $encodedMessage = json_encode($messageData);

        if ($data['state'] === 'global' && $data['recipientId'] === 'all') {
            foreach ($this->clients as $client) {
                $client->send($encodedMessage);
            }

            file_put_contents(__DIR__ . '/global_chat/global_chat_log.txt', $encodedMessage . PHP_EOL, FILE_APPEND);
        } elseif ($data['state'] === 'direct' && $data['recipientId'] !== 'all') {
            $this->handleDirectMessage($messageData);
        }
    }

    private function handleDirectMessage($messageData) {
        $mysqli = dbConnection();

        $conv_query = "SELECT id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user2_id = ? AND user1_id = ?)";
        $conv_stmt = $mysqli->prepare($conv_query);
        $conv_stmt->bind_param("iiii", $messageData['userId'], $messageData['recipientId'], $messageData['recipientId'], $messageData['userId']);
        $conv_stmt->execute();
        $conv_result = $conv_stmt->get_result();

        if (!$row = $conv_result->fetch_assoc()) {
            echo "Could not find conversation between {$messageData['userId']} and {$messageData['recipientId']}\n";
            return;
        }

        $conversationId = $row['id'];

        $msg_query = "INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)";
        $msg_stmt = $mysqli->prepare($msg_query);
        $msg_stmt->bind_param("iis", $conversationId, $messageData['userId'], $messageData['message']);
        $msg_stmt->execute();

        // Broadcast to all (simplified, no user-based filtering)
        foreach ($this->clients as $client) {
            $client->send(json_encode($messageData));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection closed! ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        file_put_contents(__DIR__ . '/WebSocket_error.log', date('c') . " Error: " . $e->getMessage() . "\n", FILE_APPEND);
        $conn->close();
    }
}

// Start the server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
