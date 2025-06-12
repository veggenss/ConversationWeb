<?php
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
        echo "({$conn->resourceId}) has Connected!\n";
    }

    public function onMessage(ConnectionInterface $fromConn, $msg){
        $data = json_decode($msg, true);
        if (!$data || !isset($data['username'], $data['message'], $data['profilePictureUrl'])) return;

        $messageData = [
            'type' => 'global',
            'username' => $data['username'],
            'profilePictureUrl' => 'http://localhost/projects/samtalerpanett/uploads/' . basename($data['profilePictureUrl']),
            'message' => $data['message']
        ];

        file_put_contents(__DIR__ . '/global_chat/global_chat_log.txt', json_encode($messageData) . PHP_EOL, FILE_APPEND);

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
