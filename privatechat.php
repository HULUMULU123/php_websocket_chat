<?php

use MyApp\Chat;

session_start();

if(!isset($_SESSION['user_data'])){
    header('location:index.php');
};

require('database/ChatUser.php');
require('database/ChatRooms.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="vendor-front/jquery-3.7.1.min.js"></script>
    <script src="vendor-front/parsley.js"></script>
    <style type="text/css">
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
        }

        #wrapper{
            display: flex;
            flex-grow: column;
            height: 100%;
        }
        #remaining{
            flex-grow: 1;
        }
        #messages {
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
            
            overflow-y: auto;
        }
    </style>
    <title>Document</title>
</head>
<body>
    <div class="containner-fluid">
        <div class="row">
            <div class="col-lg-3" style="background-color: #f1f1f1;  border-right:1px solid #ccc;">
                <?php 
                    $login_user_id = '';

                    $token ='';
                    foreach($_SESSION['user_data'] as $key => $vlaue){
                        $login_user_id = $vlaue['id'];
                        $user_object = new ChatUser;
                        $user_object->setUserId($login_user_id);
                        $user_data = $user_object->get_user_all_data();
                        $token = $vlaue['token'];
                    
                ?>
                <input type="hidden" name="login_user_id" id="login_user_id" value="<?php echo $login_user_id; ?>"/>
                <input type="hidden" name="is_active_chat" id="is_active_chat" value="No" />
                <div class="">
                    <img src="<?php echo $vlaue['profile'];?>" width="150" />
                    <h3 class="mt-2"><?php echo $vlaue['name']?></h3>
                    <a href="profile.php">Edit</a>
                </div>
                
                <?php }
                
                $user_object = new ChatUser;
                $user_object->setUserId($login_user_id);
                $user_data = $user_object->get_user_all_data_with_status_count();
                ?>
            </div>
            <div class="list-group" style=" margin-bottom:10px; overflow-y:scroll; -webkit-overflow-scrolling:touch;">
                <?php 
                foreach($user_data as $key => $user){
                    $icon = 'offline';
                    if($user['user_login_status'] == 'Login'){
                        $icon = 'Online';
                    }

                    if($user['user_id'] != $login_user_id){
                        if($user['count_status'] > 0){
                            $total_unread_message = '<span>'.$user['count_status'].'</span>';
                        }else{
                            $total_unread_message = '';
                        }
                        echo "<a class='select_user' data-userid ='".$user['user_id']."'>
                        <img src='".$user['user_profile']."' width='150'/>
                        <span>
                        <strong><span id='list_user_name_".$user['user_id']."'>
                        ".$user['user_name']."</span>
                        
                        <span id='userid_".$user['user_id']."'>".$total_unread_message."
                        </strong></span>
                        </span>
                        <span id='userstatus_".$user['user_id']."'>".$icon."</span>
                        </a>";
                    }
                }
                ?>
            </div>
            <div>
                <br/>
                <h3>Realtime one to one chat</h3>
                <hr/>
                <br/>
                <div id="chat_area">

                </div>
            </div>
        </div>
    </div>
    
</body>
<script type="text/javascript">
    $(document).ready(function(){

        var receiver_userid = '';
        
        var conn = new WebSocket('ws://localhost:8081?token="<?php echo $token ?>"/');

        conn.onopen = function(e){
            console.log('Connection established')
            console.log('"token"<?php echo $token?>')
        }

        conn.onmessage = function(event){
            console.log('msg')
            var  html_data =''
            var data = JSON.parse(event.data)

            var row_class = '';
            var background_class = '';

            if (data.from == 'Me'){
                row_class = 'row Me'
                background_class = 'alert-primary'
            }else{
                row_class = 'row'
                background_class = 'alert-success'
            }

            if(receiver_userid == data.userId || data.from == "Me"){
                if($('#is_active_chat').val()=='Yes'){
                    html_data += `
                    <div class=`+row_class+`>
                        <div>
                            <div class=`+background_class+`>
                            <b>`+data.from+`</b>`+data.msg+`<br/>
                            <div>
                            <small>`+data.datetime+`</small></div>
                        </div>
                    </div>
                    `;
                    
                    $('#messages_area').append(html_data)
                    $('messages_area').scrollTop($('#messages_area')[0].scrollHeight)
                    $('#chat_message').val("")
                }
                
            }
        }
        conn.onclose = function(event){
            console.log('connection close')
        }


        function make_chat_area(user_name){
            var html = `
            <div class="card">
                <div class="card-heder">
                    <div class="row">
                        <div>
                        <b>Chat with <span id="chat_user_name">`+user_name+`</span></b>
                        </div>
                        <div>
                        <a href="chatroom.php">Group chat</a>
                        <button type="button" id="close_chat_area" data-dissmiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        </div>
                    </div>
                </div>
                <div id="messages_area"></div>
            </div>

            <form id="chat_form" method="POST">
            <div>
                <textarea id="chat_message" name="chat_message" placeholder="Type Message Here" required></textarea>
                
                <div><button type="submit" id="send" name="send">Send</button></div>
                </div>
                <div id="validation_error"></div>
                <br />
            </form>
            `

            $('#chat_area').html(html)

            $('#chat_form').parsley()
        }
        $(document).on('click', '.select_user', function(){
            receiver_userid = $(this).data('userid')

            var from_user_id = $('#login_user_id').val();

            var receiver_user_name = $('#list_user_name_' + receiver_userid).text()

            $('.select_user.active').removeClass('active');

            $(this).addClass('active');

            make_chat_area(receiver_user_name);

            $('#is_active_chat').val('Yes')

            $.ajax({
                url:'action.php',
                method:'POST',
                data:{action: 'fetch_chat', to_user_id:receiver_userid, from_user_id:from_user_id},
                dataType:"JSON",
                success:function(data){
                    console.log(data)
                    if(data.length > 0){
                        var html_data = '';

                        for(var count = 0; count < data.length; count++){
                            var row_class = '';
                            var background_class = '';
                            var user_name = '';
                            if(data[count].from_user_id == from_user_id){
                                row_clasS ='start';
                                background_class = 'aler-primary';
                                user_name = 'Me';
                            }else{
                                row_clasS ='end';
                                background_class = 'aler-success';
                                user_name = data[count].from_user_name;
                            }

                            html_data += `
                            <div class="`+row_class+`">
                            <div>
                            <div class="`+background_class+`">
                            <b>`+user_name+` - </b>`+data[count].chat_message+`<br/>
                            <div>
                            <small>`+data[count].timestamp+`</small></div>
                            </div>
                            </div>
                            </div>`
                        }

                        $('#userid'+receiver_userid).html('')

                        $('#messages_area').html(html_data)
                        $('#messages_area').scrollTop($('#messages_area')[0].scrollHeight)
                    }
                },
                error: (e)=> {console.log(e)}
            })
        })

        $(document).on('click', '#close_chat_area', function(){
            $('#chat_area').html('')
            $('.select_user.active').removeClass('class')
        })

        $(document).on('submit', '#chat_form', function(event){
            event.preventDefault()
            
            if($('#chat_form').parsley().isValid()){
                var user_id = $('#login_user_id').val();

                var message = $('#chat_message').val();

                var data = {
                    userId: user_id,
                    msg: message,
                    receiver_userid: receiver_userid,
                    command: 'private'
                }

                conn.send(JSON.stringify(data))
            }
        })
    })

</script>
</html>