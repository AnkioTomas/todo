<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\IcsTokenDao;
use app\database\dao\TaskDao;
use app\database\model\TaskModel;
use nova\framework\http\Response;
use nova\framework\route\Controller;

/**
 * 公开 ICS 订阅，token 即鉴权，不走登录。
 */
class Ics extends Controller
{
    public function feed(string $token): Response
    {
        $token = preg_replace('/\.ics$/i', '', $token) ?? $token;
        $token = preg_replace('/[^a-f0-9]/i', '', $token) ?? '';

        $row = IcsTokenDao::getInstance()->findByToken($token);
        if ($row === null) {
            return Response::asText('Not Found', ['Content-Type' => 'text/plain; charset=utf-8'], 404);
        }

        $tasks = TaskDao::getInstance()->listForIcs($row->user_id);
        $body = $this->buildCalendar($tasks);

        return Response::asRaw($body, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="todo.ics"',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * @param TaskModel[] $tasks
     */
    private function buildCalendar(array $tasks): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Todo//Nova//CN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Todo',
        ];

        foreach ($tasks as $task) {
            $lines = array_merge($lines, $this->buildEvent($task));
        }

        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * @return string[]
     */
    private function buildEvent(TaskModel $task): array
    {
        $dueDate = date('Ymd', $task->due_at);
        $stamp = gmdate('Ymd\THis\Z', $task->updated_at > 0 ? $task->updated_at : time());

        return [
            'BEGIN:VEVENT',
            'UID:task-' . $task->id . '@todo',
            'DTSTAMP:' . $stamp,
            'DTSTART;VALUE=DATE:' . $dueDate,
            'SUMMARY:' . $this->escapeText($task->title),
            'DESCRIPTION:' . $this->escapeText($task->note),
            'STATUS:NEEDS-ACTION',
            'END:VEVENT',
        ];
    }

    private function escapeText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace([',', ';'], ['\\,', '\\;'], $text);
        return str_replace(["\r\n", "\r", "\n"], '\\n', $text);
    }
}
