<?php

if(isset($_POST['action']) && $_POST["action"]=='fetch_chat'){

    require 'database/PrivateChat.php';
    $private_chat_objet = new PrivateChat;
    $private_chat_objet->setFromUserId($_POST['to_user_id']);
    $private_chat_objet->setToUserId($_POST["from_user_id"]);
    echo json_encode($private_chat_objet->get_all_chat_data());
}