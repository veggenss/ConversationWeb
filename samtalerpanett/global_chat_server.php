<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require __DIR__ . '/vendor/autoload.php';

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $clientConn){
        $this->clients->attach($clientConn);
        echo "New Connection! ({$clientConn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $fromConn, $msg){
        foreach ($this->clients as $clientConn){
            if($fromConn !== $clientConn){
                $clientConn->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $clientConn) {
        $this->clients->detach($clientConn);
        echo "Connection {$clientConn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $clientConn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $clientConn->close();
    }
}


use Ratchet\App;

$server = new app('localhost', 8080);
$server->route('/chat', new Chat, ['*']);
$server->run();
?>