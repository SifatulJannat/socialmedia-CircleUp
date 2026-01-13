<?php
require_once 'functions.php';
require_once 'send_code.php';

// Make sure OTP folder exists
$otp_folder = __DIR__ . '/../../otp_logs';
if (!is_dir($otp_folder)) {
    mkdir($otp_folder, 0777, true);
}

//-----------------------------------
// BLOCK USER
//-----------------------------------
if(isset($_GET['block'])){
    $user_id = $_GET['block'];
    $user = $_GET['username']; 
    if(blockUser($user_id)){
        header("location:../../?u=$user");
    }else{
        echo "something went wrong";
    }
}

//-----------------------------------
// DELETE POST
//-----------------------------------
if(isset($_GET['deletepost'])){
    $post_id = $_GET['deletepost'];
    if(deletePost($post_id)){
        header("location:{$_SERVER['HTTP_REFERER']}");
    }else{
        echo "something went wrong";
    }
}

//-----------------------------------
// SIGNUP
//-----------------------------------
if(isset($_GET['signup'])){
    $response = validateSignupForm($_POST);
    if($response['status']){
        if(createUser($_POST)){
            header('location:../../?login&newuser');
        }else{
            echo "<script>alert('something is wrong')</script>";
        }
    }else{
        $_SESSION['error']=$response;
        $_SESSION['formdata']=$_POST;
        header("location:../../?signup");
    }
}

//-----------------------------------
// LOGIN
//-----------------------------------
if(isset($_GET['login'])){
    $response = validateLoginForm($_POST);
  
    if($response['status']){
        $_SESSION['Auth'] = true;
        $_SESSION['userdata'] = $response['user'];

        // Generate OTP if email not verified
        if($response['user']['ac_status']==0){
            $_SESSION['code'] = $code = rand(111111,999999);

            // Save OTP to a file instead of email
            $file_path = $otp_folder . '/otp.txt';
            $file = fopen($file_path, "w");
            fwrite($file, "Your OTP is: $code\nGenerated at: " . date("Y-m-d H:i:s"));
            fclose($file);

            // Optional: for testing, you can show OTP in browser
            // echo "OTP generated. Check otp_logs/otp.txt";
        }

        header("location:../../");
    }else{
        $_SESSION['error'] = $response;
        $_SESSION['formdata'] = $_POST;
        header("location:../../?login");
    }
}

//-----------------------------------
// RESEND OTP
//-----------------------------------
if(isset($_GET['resend_code'])){
    $_SESSION['code'] = $code = rand(111111,999999);

    // Save OTP to file
    $file_path = $otp_folder . '/otp.txt';
    $file = fopen($file_path, "w");
    fwrite($file, "Your OTP is: $code\nGenerated at: " . date("Y-m-d H:i:s"));
    fclose($file);

    header('location:../../?resended');
}

//-----------------------------------
// VERIFY EMAIL
//-----------------------------------
if(isset($_GET['verify_email'])){
    $user_code = $_POST['code'];
    $code = $_SESSION['code'];

    if($code == $user_code){
        if(verifyEmail($_SESSION['userdata']['email'])){
            header('location:../../');
        }else{
            echo "something is wrong";
        }
    }else{
        $response['msg'] = !$_POST['code'] ? 'enter 6 digit code !' : 'incorrect verification code !';
        $response['field'] = 'email_verify';
        $_SESSION['error'] = $response;
        header('location:../../');
    }
}

//-----------------------------------
// FORGOT PASSWORD
//-----------------------------------
if(isset($_GET['forgotpassword'])){
    if(!$_POST['email']){
        $response['msg'] = "enter your email id !";
        $response['field'] = 'email';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    }elseif(!isEmailRegistered($_POST['email'])){
        $response['msg'] = "email id is not registered";
        $response['field'] = 'email';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    }else{
        $_SESSION['forgot_email'] = $_POST['email'];
        $_SESSION['forgot_code'] = $code = rand(111111,999999);

        // Save OTP for forgot password
        $file_path = $otp_folder . '/forgot_otp.txt';
        $file = fopen($file_path, "w");
        fwrite($file, "Your Forgot Password OTP is: $code\nGenerated at: " . date("Y-m-d H:i:s"));
        fclose($file);

        header('location:../../?forgotpassword&resended');
    }
}

//-----------------------------------
// LOGOUT
//-----------------------------------
if(isset($_GET['logout'])){
    session_destroy();
    header('location:../../');
}

//-----------------------------------
// VERIFY FORGOT PASSWORD OTP
//-----------------------------------
if(isset($_GET['verifycode'])){
    $user_code = $_POST['code'];
    $code = $_SESSION['forgot_code'];
    if($code == $user_code){
        $_SESSION['auth_temp'] = true;
        header('location:../../?forgotpassword');
    }else{
        $response['msg'] = !$_POST['code'] ? 'enter 6 digit code !' : 'incorrect verification code !';
        $response['field'] = 'email_verify';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    }
}

//-----------------------------------
// CHANGE PASSWORD
//-----------------------------------
if(isset($_GET['changepassword'])){
    if(!$_POST['password']){
        $response['msg'] = "enter your new password";
        $response['field'] = 'password';
        $_SESSION['error'] = $response;
        header('location:../../?forgotpassword');
    }else{
        resetPassword($_SESSION['forgot_email'], $_POST['password']);
        session_destroy();
        header('location:../../?reseted');
    }
}

//-----------------------------------
// UPDATE PROFILE
//-----------------------------------
if(isset($_GET['updateprofile'])){
    $response = validateUpdateForm($_POST, $_FILES['profile_pic']);
    if($response['status']){
        if(updateProfile($_POST, $_FILES['profile_pic'])){
            header("location:../../?editprofile&success");
        }else{
            echo "something is wrong";
        }
    }else{
        $_SESSION['error'] = $response;
        header("location:../../?editprofile");
    }
}

//-----------------------------------
// ADD POST
//-----------------------------------
if(isset($_GET['addpost'])){
    $response = validatePostImage($_FILES['post_img']);
    if($response['status']){
        if(createPost($_POST, $_FILES['post_img'])){
            header("location:../../?new_post_added");
        }else{
            echo "something went wrong";
        }
    }else{
        $_SESSION['error'] = $response;
        header("location:../../");
    }
}
