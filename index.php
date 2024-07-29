<?php 

session_start();

$error = '';

$success_message ='';

if(isset($_SESSION['user_data'])){
    header('location:chatroom.php');
}
if(isset($_POST['login'])){
    require_once('database/ChatUser.php');

    $user_object = new ChatUser;
    $user_object->setUserEmail($_POST['user_email']);
    $user_data = $user_object->get_user_data_by_email();

    if(is_array($user_data) && count($user_data)>0){
        if($user_data['user_status'] == 'Enabled'){
            if($user_data['user_password'] == $_POST['user_password']){
                $user_object->setUserId($user_data['user_id']);
                $user_object->setUserLoginStatus('Login');

                $user_token = md5(uniqid());
                
                $user_object->setUserToken($user_token);
                $user_data = $user_object->get_user_data_by_email();

                if($user_object->update_user_login_data()){
                    $_SESSION['user_data'][$user_data['user_id']] = [
                        'id' => $user_data['user_id'],
                        'name' => $user_data['user_name'],
                        'profile' => $user_data['user_profile'],
                        'token' => $user_token,
                    ];

                    header("location:chatroom.php");
                }
            }else{
                $error='wrong password';
            }
        }else{
            $error='Please verify';
        }
    }else{
        $error = 'Wrong Email address';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="vendor-front/jquery-3.7.1.min.js"></script>
    <title>Document</title>
</head>
<body>
   <div class="container">
        <br/>
        <br/>
        <h1 class="text-center">PHP chat</h1>

        <div class="row justify-content-md-center">
            <div class="col col-md-4 mt-5">
                <?php 
                    if($error != ''){
                        echo 'Erorrrr' . $error;
                    }
                    if($success_message != ''){
                        echo 'Success!!!!!';
                    }
                ?>
                <div class="card">
                    <div class="card-header">Register</div>
                    <div class="card-body">
                        <form action="" method="POST" id="login_form">
                                <label>Enter your email</label>
                                <input type="text" name="user_email" id="user_email" class="form-control" data-parsley-type="email" required />
                            </div>
                            <div class="form_group">
                                <label>Enter your password</label>
                                <input type="password" name="user_password" id="user_password" class="form-control" data-parsley-pattern="^[a-zA-Z]+$" required />
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" name="login" class="btn btn-success" value="Login" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</body>
</html>

<script>
    $(document).ready(function(){
        $('#login_form').parsley();
    })
</script>