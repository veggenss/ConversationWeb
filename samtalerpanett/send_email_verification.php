<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

function sendVerificationEmail($to, $username, $token){
    $mail = new PHPMailer(true);
    try{
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ibuypower.holo.katowice2014@gmail.com';
        $mail->Password = 'lzab rogd rvzy mffj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('ibuypower.holo.katowice2014@gmail.com', 'Samtaler på Nett');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Bekreft e-posten din';

        $verificationUrl = "http://localhost/projects/samtalerpanett/verify_email.php?token=$token";
        $mail->Body = "<p>Hei <strong>$username</strong>,</p><p>Klikk på linken under for å bekrefte e-posten din:</p><a href='$verificationUrl'>$verificationUrl</a>";

        $mail->send();
        return true;
    }
    catch (Exception $e) {
        error_log('E-postfeil' . $mail->ErrorInfo);
        return false;
    }
}
?>