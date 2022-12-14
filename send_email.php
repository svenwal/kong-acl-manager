<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './libs/Exception.php';
require './libs/PHPMailer.php';
require './libs/SMTP.php';

function send_email($to_address, $to_name, $subject, $message, $sender_email, $sender_password, $reply_to = "no-reply@konghq.com") {
    require("secrets.php");
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $config->email_smtp_host;    
        $mail->Timeout    = 5;                 
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $sender_email;                     //SMTP username
        $mail->Password   = $sender_password;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = $config->email_smtp_port;                                 //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom($sender_email, $sender_email);
        $mail->addAddress($to_address, $to_name); 
        $mail->addReplyTo($reply_to, 'No Reply');

    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = 'To view this email you need to have a HTML enabled email viewer!';
    
        $mail->send();
        return '';
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }

}

?>
