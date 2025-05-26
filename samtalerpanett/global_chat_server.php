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
        $consoleLog = "Connected! ({$conn->resourceId})\n";
        echo "<script>console.log(" . json_encode($consoleLog) . ")</script>";
    }

    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);

        if (!isset($data['username'], $data['profilePictureUrl'], $data['message'])) {
            return; // Ignore malformed messages
        }

        // Bygg full URL til profilbildet med korrekt base path
        $baseUrl = 'http://localhost/projects/samtalerpanett';
        $profilePictureFile = basename($data['profilePictureUrl']); 
        $fullProfilePictureUrl = $baseUrl . '/uploads/' . $profilePictureFile;

        $messageData = [
            'username' => $data['username'],
            'profilePictureUrl' => $fullProfilePictureUrl,
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
        $consoleLog = "Connection {$conn->resourceId} has disconnected\n";
        echo "<script>console.log(" . json_encode($consoleLog) . ")</script>";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = new App('localhost', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();
