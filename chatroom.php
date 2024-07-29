<?php 
session_start();



if(!isset($_SESSION['user_data'])){
    header('location:index.php');
}

require('database/ChatUser.php');

require('database/ChatRooms.php');

$chat_object = new ChatRooms;

$chat_data = $chat_object->get_all_chat_data();

$user_object = new ChatUser;

$user_data = $user_object->get_user_all_data();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="vendor-front/jquery-3.7.1.min.js"></script>
    <script src="vendor-front/parsley.js"></script>
    <style type="text/css">
        html, body{
            height: 100%;
            width: 100%;
            margin: 0;
        }
        #wrapper{
            display: flex;
            flex-flow: column;
            height: 100%;
        }
        #remaining{
            flex-grow: 1;
        }
        #messages{
            height: 200px;
            background: whitesmoke;
            overflow: auto;
        }
        #chat-room-frm{
            margin-top: 10px;
        }
        #user_list{
            height: 450px;
            overflow-y: auto;
        }
        #messages_area{
            height: 650px;
            overflow-y: auto;
            background-color: #e6e6e6;
        }
    </style>
    <title>ChatRoom</title>
</head>
<body>
    <div class="container">
        <br />
        <h3 class="text-center">PHP chat</h3>
        <br />
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Chat Room</h3>
                        <div>
                            <a href="privatechat.php" class="btn">Private chat</a>
                        </div>
                        <div class="card-body" id="messages-area">
                        <?php 
                        foreach($chat_data as $chat){
                            
                            if(isset($_SESSION['user_data'][$chat['userid']])){
                                $from = "Me";
                                $row_class = 'row justify-content-start';
                                $background_class = 'text-dark alert-light';
                            }else{
                                $from = $chat['user_name'];
                                $row_class = 'row jsutifyy-content-end';
                                $background_class = 'alert-success';
                            };
                            echo '<div class="'.$row_class.'"><div class="col-sm-10">
                                    <div class="shadow-sm alert'.$background_class.'">
                                        <b>
                                        '.$from.' - 
                                        </b>'.$chat["msg"].'
                                        <br/>
                                        <div class="text-right">
                                        <small><i>'.$chat['created_on'].'</i></small>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                        ?>
                        </div>
                    </div>
                    <form method="POST" id="chat_form">
                        <div class="input-group">
                            <textarea name="chat_message" id="chat_message" class="from-control" placeholder="Type Message Here" required></textarea>
                            <div class="input-group-append">
                                <button type="submit" name="send" id="send">Send</button>
                            </div>
                        </div>
                        <div id="validation_error"></div>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <?php 
                foreach($_SESSION['user_data'] as $key => $value){
                    $login_user_id = $value['id'];
                    ?>
                    <input type="hidden" name="login_user_id" id="login_user_id" value="<?php echo $login_user_id?>"/>
                    <div class="mt-3 mb-3 text-center">
                        <img src="<?php echo $value['profile']; ?>" width="150" />
                        <h3 class="mt-2"><?php echo $value['name']?></h3>
                        <a href="profile.php" class="btn">Edit</a>
                    </div>
                    <?php
                }
                ?>

                <div class="card mt-3">
                    <div class="card-header">
                        Use List
                    </div>
                    <div class="card-body" id="user_list">
                        <div class="list-group list-group-flush">
                            <?php 
                            if(count($user_data)>0){
                                foreach($user_data as $key => $user){
                                    $icon = '<i class="fa fa-circle text-danger"></i>';

                                    if($user['user_login_status'] == 'Login'){
                                        $icon = '<i class="fa fa-circle text-success">Online</i>';
                                    };

                                    if($user['user_id'] != $login_user_id){
                                        echo '
                                        <a class"list-group-item list-grop-item-action">
                                        <img src="'.$user["user_profile"].'" class="img-fluid rounded-circle"/>
                                        <span><strong>'.$user["user_name"].'</strong></span>
                                        <span>'.$icon.'</span></a>
                                        ';
                                    }
                                }
                            }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript">
    $(document).ready(function(){
        var conn = new WebSocket('ws://localhost:8081');
        conn.onopen = function(e) {
            console.log("Connection established!");
        };

        conn.onmessage = function(e) {
            console.log(e.data);

            var data = JSON.parse(e.data)
            var row_class = ''
            var background_class = ''
            if(data.from == 'Me'){
                row_class = 'row justify-content-start'
                background_class = 'text-dark alert-light'
            }else{
                row_class = 'row justify-content-end'
                background_class = 'alert-success'
            }
            var html_data = "<div class='"+row_class+"'><div class='col-sm-10'><div class'shadow-sm alert"+background_class+"'><b>"+data.from+"</b>"+data.msg+"<br/><div class='text-right'><smal><i>"+data.dt+"</i></small></div></div></div></div>"

            $('#messages-area').append(html_data);
            $('#chat_message').val('')
        };
        $('#chat_from').parsley();

        $('#messages_area').scrollTop($('#messages_area')[0]);
        $('#chat_form').on('submit', function(event){
            event.preventDefault();
            if($('#chat_form').parsley().isValid()){
                var user_id = $('#login_user_id').val()
                var message = $('#chat_message').val()
                var data = {
                    userId : user_id,
                    msg:message
                };
                conn.send(JSON.stringify(data));
                $('#messages_area').scrollTop($('#messages_area')[0]);
            }
        });
        // $('#logout').click(function(){
        //     user_id = $('#login_user_id').val();
        //     $.ajax({
        //         url: 'action.php',
        //         method:"POST",
        //         data:{user_id:user_id, action:'leave'},
        //         success:function(data){
        //             var response = JSON.parse(data)
        //             if(response.status == 1){
        //                 location = 'index.php'
        //             }
        //         }
        //     })
        // })
    })
</script>
</html>