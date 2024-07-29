<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$error = '';

$success_message ='';

if(isset($_POST["register"]))
{
    session_start();
    if(isset($_SESSION['user_data'])){
        header('location:chatroom.php');
    }

    require_once('database/ChatUser.php');

    $user_object = new ChatUser;

    $user_object->setUserName($_POST['user_name']);

    $user_object->setUserEmail($_POST['user_email']);

    $user_object->setUserPassword($_POST['user_password']);

    $user_object->setUserProfile($user_object->make_avatar(strtoupper($_POST['user_name'][0])));

    $user_object->setUserStatus('Enabled');

    $user_object->setUserCreatedOn(date('Y-m-d H:i:s'));

    $user_object->setUserVerificationCode(md5(uniqid()));

    $user_data = $user_object->get_user_data_by_email();

    if(is_array($user_data) && count($user_data)>0){
        $error = 'This Email already register';
    }else{
        if($user_object->save_data()){
            // $mail = new PHPMailer(true);

            // $mail->isSMTP();

            // $mail->Host = 'smtpout.secureserver.net';
            // $mail->SMTPAuth = true;
            // $mail->Username =  'stas.r.d87@gmail.com';
            // $mail->Password = 'nhdz flkn ybhv hkhv';
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // $mail->Port = 80;
            // $mail->setFrom('srudenko2005@gmail.com', 'Aleksandr');
            // $mail->addAddress($user_object->getUserEmail());
            // $mail->isHTML(true);
            // $mail->Subject = 'Registration Verification for Chat Application demo';
            // $mail->Body = '
            // <p>Thank you for registration for Chat Application Demo</p>
            // <a href="http://localhost:8000/verify.php?code='.$user_object->getUserVerificationCode().'"></a>
            // ';
            // $mail->send();
            $success_message ='Verification email sent to' .$user_object->getUserEmail().', so before login first verify your email';
        }else{
            $error = 'Somthing went wrong';
        }
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
                        <form action="" method="POST" id="register_form">
                            <div class="form_group">
                                <label>Enter your name</label>
                                <input type="text" name="user_name" id="user_name" class="form-control" data-parsley-pattern="/[a-zA-Z\s]+$/" required />
                            </div>
                            <div class="form_group">
                                <label>Enter your email</label>
                                <input type="text" name="user_email" id="user_email" class="form-control" data-parsley-type="email" required />
                            </div>
                            <div class="form_group">
                                <label>Enter your password</label>
                                <input type="password" name="user_password" id="user_password" class="form-control" data-parsley-pattern="^[a-zA-Z]+$" required />
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" name="register" class="btn btn-success" value="Resgister" />
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
        $('#register_form').parsley();
    })
</script>