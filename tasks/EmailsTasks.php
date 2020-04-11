<?php

namespace ItemAdapters\Tasks;

use Kwabs\DbAdapter\Item;
use Mail;

class EmailsTasks extends Item {
    public static $dbTable = 'emails';
    public static $dbParamsOut = ['id', 'email', 'template', 'data'];
    public static $editable = [];
    public static $dbScheme = [
        'id' => ['type' => 'int', 'autoincrement' => true, 'primaryKey' => true],
        'email' => ['type' => 'text'],
        'template' => ['type' => 'text'],
        'subject' => ['type' => 'text'],
        'data' => ['type' => 'text'],
    ];

    public static function log($emails, $template, $subject, $data) {
        unset($data['r']);
        $id = self::insert([
            'email' => json_encode($emails, JSON_UNESCAPED_UNICODE),
            'template' => $template,
            'subject' => $subject,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ])['id'];

        $task = [
            'type' => 'mail',
            'item_id' => $id,
            'time_created' => time(),
        ];
        Tasks::create($task);
        return 1;
    }

    public static function run($id){
        $email = self::getOne($id);
        return Mail::sendEmail($email);
    }
}