<?php 

class Database_connection {
    function connect(){
        
        $connect = new PDO("mysql:host=localhost; dbname=websocket_chat", "root", "");
        return $connect;
    }
}

?>