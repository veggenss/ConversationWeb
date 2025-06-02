<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

require __DIR__ . '/../vendor/autoload.php';

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "({$conn->resourceId}) has Connected!\n";
    }

    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);

        if (!isset($data['username'], $data['profilePictureUrl'], $data['message'])) {
            return; // ignorer misdannet data
        }

        // Bygg full URL til profilbildet med korrekt base path
        $baseUrl = 'http://localhost/projects/samtalerpanett';
        $profilePictureFile = basename($data['profilePictureUrl']); 
        $fullProfilePictureUrl = $baseUrl . '/uploads/' . $profilePictureFile;

        $type = isset($data['to_user_id']) ? 'dm' : 'global';
        $messageData = [
            'type' => $type,
            'username' => $data['username'],
            'profilePictureUrl' => $fullProfilePictureUrl,
            'message' => $data['message']
        ];

        file_put_contents(__DIR__ . '/global_chat_log.txt', json_encode($messageData) . PHP_EOL, FILE_APPEND);

        $encodedMessage = json_encode($messageData);
        foreach ($this->clients as $clientConn) {
            $clientConn->send($encodedMessage);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "({$conn->resourceId}) has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = new App('localhost', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();
