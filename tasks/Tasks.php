<?php

namespace ItemAdapters\Tasks;

use Kwabs\DbAdapter\Item;

class Tasks extends Item
{
    public static $dbTable = 'tasks';
    public static $dbParamsOut = ['id', 'type', 'item_id', 'time_created', 'time_completed', 'status'];
    public static $editable = [];
    public static $dbScheme = [
        'id' => ['type' => 'int', 'autoincrement' => true, 'primaryKey' => true],
        'type' => ['type' => 'text'],
        'item_id' => ['type' => 'int'],
        'time_created' => ['type' => 'int'],
        'time_completed' => ['type' => 'int'],
        'status' => ['type' => 'text'],
        'tries' => ['type' => 'int', 'default' => 0],
    ];

    public static $task_types = [
        'mail' => 'ItemAdapters\Tasks\EmailsTasks'
    ];

    public static function run()
    {
        $tasks = self::getPlain('status != ?', [1]);
        $counts = ['tasks' => count($tasks), 'goods' => 0, 'bad' => 0];
        foreach ($tasks as $task) {
            $class = self::$task_types[$task->type];
            $result = $class::run($task->item_id);
            if ($result == 1) {
                $counts['goods']++;
                $params = [
                    'time_completed' => time(),
                    'status' => 1,
                    'tries' => intval($task->tries) + 1,
                ];
            } else {
                $counts['bad']++;
                $params = [
                    'status' => $result,
                    'tries' => intval($task->tries) + 1,
                ];
            }
            self::updateByID($task->id, $params);
        }
        return $counts;
    }
}