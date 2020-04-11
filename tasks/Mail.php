<?php

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    public static function sendEmail($input)
    {
        $smtp_config = Config::get('smtp');
        $mail = new PHPMailer(true);
        try {
            $mail->IsSMTP();
            $mail->CharSet = "utf-8";
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Host = $smtp_config['host'];
            $mail->Port = $smtp_config['port'];
            $mail->Username = $smtp_config['username'];
            $mail->Password = $smtp_config['password'];
            $mail->SetFrom($smtp_config['username'], "Братья Чистовы");
            $mail->Subject = $input->subject;
            $mail->isHTML(true);
            foreach (json_decode($input->email) as $email){
                $mail->addAddress($email);
            }
            $body = file_get_contents("app/views/mail_templates/". $input->template . '.php');
            if (!$body){
                return 'Template not found';
            }
            $search = [];
            $replace = [];
            $input->data = (array) json_decode($input->data);
            $data = self::parseData($input->data);
            foreach ($data as $key => $value) {
                $search[] = '{' . $key . '}';
                $replace[] = $value;
            }
            $body = str_replace($search, $replace, $body);
            $mail->Body = $body;
            $mail->AltBody = $input->data['altbody'] ? $input->data['altbody'] : null;

            $mail->send();
            return 1;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function parseData($request){
        global $globalCity;
        foreach ($request as $k => $v){
            $data['request_' . $k] = $v;
        }
        foreach ($globalCity as $k => $v){
            $data['global_' . $k] = $v;
        }
        return $data;
    }
}