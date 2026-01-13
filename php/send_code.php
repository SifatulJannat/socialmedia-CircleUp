<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendCode($email, $subject, $otp) {

    $mail = new PHPMailer(true);

    try {
        // DEBUG (TEMPORARY)
        $mail->SMTPDebug = 0; // set 2 if still not working
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'YOUR_GMAIL@gmail.com';
        $mail->Password   = 'YOUR_16_DIGIT_APP_PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('YOUR_GMAIL@gmail.com', 'Pictogram');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
            <h3>$subject</h3>
            <p>Your OTP code:</p>
            <h2>$otp</h2>
            <p>Valid for 5 minutes</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("MAIL ERROR: ".$mail->ErrorInfo);
        return false;
    }
}
