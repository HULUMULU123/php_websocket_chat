<?php 
session_start();

if(!isset($_SESSION['user_data'])){
    header('location:index.php');
}

require('database/ChatUser.php');

$user_object = new ChatUser;

$user_id = '';
foreach($_SESSION['user_data'] as $key => $value){
    $user_id = $value['id'];
}

$user_object->setUserId($user_id);

$user_data = $user_object->get_user_data_by_id();

$message = '';
if(isset($_POST['edit'])){
    $user_profile = $_POST['hidden_user_profile'];
    
    if($_FILES['user_profile']['name'] != ''){
        $user_profile = $user_object->upload_image($_FILES['user_profile']);
        
        $_SESSION['user_data'][$user_id]['profile'] = $user_profile;

    }

    $user_object->setUserName($_POST['user_name']);
    $user_object->setUserEmail($_POST['user_email']);
    $user_object->setUserPassword($_POST['user_password']);
    $user_object->setUserProfile($user_profile);
    $user_object->setUserId($user_id);

    if($user_object->update_data()){
        $_SESSION['user_data'][$user_id]['user_name'] = $user_object->getUserName();
        
        $message = '<div>Prodile details updated</div>';
    }else{
        $message = '<div>Updated error</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="vendor-front/jquery-3.7.1.min.js"></script>
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
    <title>Profile</title>
</head>
<body>
    <div class="container">
        <br />
        <br />
        <h3 class="text-center">PHP chat</h3>
        <br />
        <br />
        <?php echo $message?>
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">Profile</div>
                    <div class="col-md-6 text-right"><a href="chatroom.php">Go to chat</a></div>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" id="profile_form" enctype="multipart/form-data">
                    <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="user_name" id="user_name" class="from-control" required value="<?php echo $user_data['user_name'];?>"/>
                    </div>
                    <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="user_email" id="user_email" class="from-control" required readonly value="<?php echo $user_data['user_email'];?>"/>
                    </div>
                    <div class="form-group">
                    <label>Password</label>
                    <input type="Password" name="user_password" id="user_password" class="from-control" required value="<?php echo $user_data['user_password'];?>"/>
                    </div>
                    <div class="form-group">
                    <label>Profile</label>
                    <input type="file" name="user_profile" id="user_profile" class="from-control" />
                    <br/>
                    <img src="<?php echo $user_data['user_profile'];?>" width="100"/>
                    <input type="hidden" name="hidden_user_profile" value="<?php echo $user_data['user_profile'];?>"/>
                    </div>
                    <div class="form-group text-center">
                        <input type="submit" name="edit" value="Edit"/>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</body>
<script type="text/javascript">
    $(document).ready(function(){

    })
</script>
</html>