<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

require __DIR__ . '/vendor/autoload.php';

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Connected! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);

        if (!isset($data['username'], $data['profilePictureUrl'], $data['message'])) {
            return; // Ignore malformed messages
        }

        $messageData = [
            'username' => $data['username'],
            'profile_picture' => $data['profilePictureUrl'],
            'message' => $data['message']
        ];

        $encodedMessage = json_encode($messageData);

        foreach ($this->clients as $clientConn) {
            if ($fromConn !== $clientConn) {
                $clientConn->send($encodedMessage);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = new App('localhost', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();
