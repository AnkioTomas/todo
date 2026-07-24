<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\model\TaskModel;
use app\todo\TodoOps;
use nova\framework\http\Response;
use nova\plugin\login\controller\BaseAPIController;

class TaskApi extends BaseAPIController
{
    public function index(): Response
    {
        try {
            $result = TodoOps::listTasks(
                $this->userModel->id,
                (string)$this->request->get('view', 'list'),
                (int)$this->request->get('list_id', 0)
            );
        } catch (\RuntimeException $e) {
            return $this->fail($e);
        }

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => array_map(static fn (TaskModel $task) => $task->toArray(false), $result['tasks']),
        ]);
    }

    public function detail(): Response
    {
        try {
            $task = TodoOps::getTask($this->userModel->id, (int)$this->request->get('id', 0));
        } catch (\RuntimeException $e) {
            return $this->fail($e);
        }

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $task->toArray(false),
        ]);
    }

    public function create(): Response
    {
        try {
            $task = TodoOps::createTask($this->userModel->id, (string)$this->request->post('title', ''), [
                'list_id' => (int)$this->request->post('list_id', 0),
                'important' => (int)$this->request->post('important', 0),
                'my_day' => (int)$this->request->post('my_day', 0),
            ]);
        } catch (\RuntimeException $e) {
            return $this->fail($e);
        }

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $task->toArray(false),
        ]);
    }

    public function update(): Response
    {
        $id = (int)$this->request->post('id', 0);
        $post = $this->request->post();
        if (!is_array($post)) {
            $post = [];
        }

        $patch = [];
        foreach (['title', 'note', 'important', 'completed', 'due_at', 'my_day', 'list_id'] as $key) {
            if (array_key_exists($key, $post)) {
                $patch[$key] = $post[$key];
            }
        }

        try {
            $task = TodoOps::updateTask($this->userModel->id, $id, $patch);
        } catch (\RuntimeException $e) {
            return $this->fail($e);
        }

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $task->toArray(false),
        ]);
    }

    public function delete(): Response
    {
        try {
            TodoOps::deleteTask($this->userModel->id, (int)$this->request->post('id', 0));
        } catch (\RuntimeException $e) {
            return $this->fail($e);
        }

        return Response::asJson(['code' => 200, 'msg' => 'success', 'data' => []]);
    }

    private function fail(\RuntimeException $e): Response
    {
        $code = $e->getCode();
        if ($code !== 400 && $code !== 404) {
            $code = 400;
        }

        return Response::asJson(['code' => $code, 'msg' => $e->getMessage(), 'data' => []]);
    }
}
