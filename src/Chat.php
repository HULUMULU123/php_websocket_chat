<?php

namespace MyApp;

use ChatRooms;
use ChatUser;
use PrivateChat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require dirname(__DIR__) . "/database/ChatRooms.php";
require dirname(__DIR__) . "/database/PrivateChat.php";
require dirname(__DIR__) . "/database/ChatUser.php";

class Chat implements MessageComponentInterface {

    protected $clients;

    public function __construct(){
        $this->clients = new \SplObjectStorage;

    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        
        $conId = $conn->resourceId;
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring, $queryarray);
        
        $user_object = new \ChatUser;

        $user_object->setUserToken(substr($queryarray['token'], 1, -2));
        echo "Newe connection! ({$conId})\n";
        $user_object->setUserConnectionId($conId);
        
        echo "id {$queryarray['token']}";
        echo "user obj {$user_object->getUserToken()}";
        
        $res = $user_object->udate_user_connection_id();
        echo "hres";
        echo "res {$res}";
        echo "bres";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connections%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '': 's');

        $data = json_decode($msg, true);

        if($data['command'] == 'private'){
            //private chat

            $private_chat_object = new PrivateChat;

            $private_chat_object->setToUserId($data['receiver_userid']);

            $private_chat_object->setFromUserId($data['userId']);
            echo "msg is {$data['msg']}";
            $private_chat_object->setChatMessage($data['msg']);

            $timestamp = date('Y-m-d h:i:s');

            $private_chat_object->setTimeStamp($timestamp);

            $private_chat_object->setStatus('Yes');

            $chat_message_id = $private_chat_object->save_chat();

            $user_object = new ChatUser;

            $user_object->setUserId($data['userId']);

            $sender_user_data = $user_object->get_user_data_by_id();

            $user_object->setUserId($data['receiver_userid']);

            $receiver_user_data = $user_object->get_user_data_by_id();

            $sender_user_name = $sender_user_data['user_name'];

            $data['datetime'] = $timestamp;
            echo "data {$data}";
            $receiver_user_connection_id = $receiver_user_data['user_connection_id'];

            foreach($this->clients as $client){
                if($from == $client){
                    $data['from'] = 'Me';
                }else{
                    $data['from'] = $sender_user_name;
                }

                if($client->resourceId == $receiver_user_connection_id || $from == $client){
                    $client->send(json_encode($data));
                    
                }else{
                    $private_chat_object->setStatus('No');
                    $private_chat_object->setChatMessageId($chat_message_id);

                    $private_chat_object->update_chat_status();
                }
            }
        }else{

        

        $chat_object = new ChatRooms;

        $chat_object->setUserId($data['userId']);

        $chat_object->setMessage($data['msg']);

        $chat_object->setCreatedOn(date("Y-m-d h:i:s"));

        $chat_object->save_chat();

        $user_object = new ChatUser;

        $user_object->setUserId($data['userId']);

        $user_data = $user_object->get_user_data_by_id();

        $user_name = $user_data['user_name'];

        $data['dt'] = date("d-m-Y h:i:s");


        foreach($this->clients as $client)
        {
            // if($from !== $client){
            //     $client->send($msg);
            // }
            if($from == $client){
                $data['from'] = "Me";
            }else{
                $data['from'] = $user_name;
            }
            $client->send(json_encode($data));
        }}
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}

?>