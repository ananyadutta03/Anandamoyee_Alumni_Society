<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';


function sendEmail($to, $subject, $body)
{

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;

        $mail->Username   = 'email';
        $mail->Password   = 'Password';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;


        $mail->setFrom(
            'email',
            'Anandamoyee Alumni Association'
        );


        $mail->addAddress($to);


        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $body;


        $mail->send();

        return true;


    } catch(Exception $e){

        error_log(
            "Mail Error: ".$mail->ErrorInfo
        );

        return false;
    }

}