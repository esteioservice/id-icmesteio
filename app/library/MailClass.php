<?php

namespace App\library;
use PHPMailer\PHPMailer\PHPMailer;

class MailClass
{
    function sendEmail($usuario,$nome,$subject,$mensagem,$address,$addressName = "Solicitação Escoteiros Online")
    {
    
        $mail = new PHPMailer();
        $mail->SMTPDebug  = 0; // debugging: 1 = errors and messages, 2 = messages only
        $mail->CharSet = "utf-8";
        $mail->isSMTP(); // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth   = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host       = env('MAIL_HOST');
        $mail->Port       = env('MAIL_PORT');; // or 587
        $mail->IsHTML(true);
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SetFrom(env('MAIL_USERNAME'), $nome);
        $mail->Subject = $subject;
        $mail->Body    = '<div style="text-align:center">
                               '.$mensagem.'
                          </div>';
        $mail->AddAddress($address, $addressName);
        return $mail->send();
         
       
    }
}
