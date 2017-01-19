<?php
namespace ChatApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $browser;
    public function __construct() {
        $this->clients =[];
    }
    public function onOpen(ConnectionInterface $conn) {
        $this->clients[$conn->resourceId] = (object)['conn' => $conn ,'details'];
        echo "New connection! ({$conn->resourceId})\n";
    }
    public function onMessage(ConnectionInterface $from, $msg) {
        echo "message from {$from->resourceId}:".$msg;
        $msg=json_decode($msg);
        print_r($msg);
      if(isset($msg->type)){
        if($msg->type=="handshake"){
          add_device($msg->user,$msg->id,$msg->devname,$msg->devuser,($from->resourceId));
          $this->clients[$from->resourceId]->details=$msg;
          //echo "do an handshake for android";
        }else{
          echo "message sending";
        }
      }else{

      }
    }
    public function onClose(ConnectionInterface $conn) {
        disconnect_device($this->clients[$conn->resourceId]->details->id,$conn->resourceId);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
