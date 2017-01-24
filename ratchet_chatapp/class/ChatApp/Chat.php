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
        echo "New connection! ({$conn->resourceId})\n";
        $this->clients[$conn->resourceId] = (object)['conn' => $conn ,'details'];

    }
    public function onMessage(ConnectionInterface $from, $msg) {
        echo "message from {$from->resourceId}:";
        $msg=json_decode($msg);
      if(isset($msg->type)){
        if($msg->type=="handshake"){
          add_device($msg->user,$msg->id,$msg->devname,$msg->devuser,($from->resourceId));
          $this->clients[$from->resourceId]->details=$msg;
          //echo "do an handshake for android";
        }else if($msg->type=="response"){
          $data=["type"=>"screenshot","image"=>$msg->response];
          $this->clients[$msg->to]->conn->send(json_encode($data));
        }else if($msg->type=="contacts"){
          $data=["type"=>"contact","list"=>$msg->response];
          $this->clients[$msg->to]->conn->send(json_encode($data));
        }else if($msg->type=="gallery"){
          $data=["type"=>"gallery","list"=>$msg->response];
          $this->clients[$msg->to]->conn->send(json_encode($data));
        }

      }else{
            //echo "message from browser ".$msg->device." | ".$msg->cmd;
            if(isset($msg->device)&&isset($msg->cmd)){
              $sock=get_socket(($msg->device));
              echo "reply for get sock $sock \n";
              if($sock==-1){
                //device inactive;
                $res=["type"=>"response_error","dev"=>$msg->device,"message"=>"device inactive"];
                $from->send(json_encode($res));
                echo "replied as error";
              }else{
                $data=["cmd"=>$msg->cmd,"from"=>$from->resourceId];
                $this->clients[$sock]->conn->send(json_encode($data));
              }
            }
            else{
              echo "unknown message";
            }
      }
    }
    public function onClose(ConnectionInterface $conn) {
        if(isset($this->clients[$conn->resourceId]));
          disconnect_device($this->clients[$conn->resourceId]->details->id,$conn->resourceId);
        unset($this->clients[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
