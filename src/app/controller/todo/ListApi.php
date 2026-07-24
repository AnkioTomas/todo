<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\TaskDao;
use app\database\dao\TodoListDao;
use app\database\model\TodoListModel;
use nova\framework\http\Response;
use nova\plugin\login\controller\BaseAPIController;

class ListApi extends BaseAPIController
{
    public function index(): Response
    {
        $userId = $this->userModel->id;
        TodoListDao::getInstance()->ensureDefault($userId);
        $lists = TodoListDao::getInstance()->listByUser($userId);

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => array_map(static fn (TodoListModel $list) => $list->toArray(false), $lists),
        ]);
    }

    public function create(): Response
    {
        $title = trim((string)$this->request->post('title', ''));
        if ($title === '') {
            return Response::asJson(['code' => 400, 'msg' => '列表名称不能为空', 'data' => []]);
        }

        $userId = $this->userModel->id;
        TodoListDao::getInstance()->ensureDefault($userId);

        $list = new TodoListModel();
        $list->user_id = $userId;
        $list->title = $title;
        $list->sort_order = TodoListDao::getInstance()->nextSortOrder($userId);
        $list->is_default = 0;
        $list->created_at = time();
        $list->id = TodoListDao::getInstance()->insertModel($list);

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $list->toArray(false),
        ]);
    }

    public function rename(): Response
    {
        $id = (int)$this->request->post('id', 0);
        $title = trim((string)$this->request->post('title', ''));
        if ($id <= 0 || $title === '') {
            return Response::asJson(['code' => 400, 'msg' => '参数错误', 'data' => []]);
        }

        $list = TodoListDao::getInstance()->findOwned($id, $this->userModel->id);
        if ($list === null) {
            return Response::asJson(['code' => 404, 'msg' => '列表不存在', 'data' => []]);
        }

        $list->title = $title;
        TodoListDao::getInstance()->updateModel($list);

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $list->toArray(false),
        ]);
    }

    public function delete(): Response
    {
        $id = (int)$this->request->post('id', 0);
        if ($id <= 0) {
            return Response::asJson(['code' => 400, 'msg' => '参数错误', 'data' => []]);
        }

        $list = TodoListDao::getInstance()->findOwned($id, $this->userModel->id);
        if ($list === null) {
            return Response::asJson(['code' => 404, 'msg' => '列表不存在', 'data' => []]);
        }
        if ($list->is_default === 1) {
            return Response::asJson(['code' => 400, 'msg' => '默认列表不能删除', 'data' => []]);
        }

        TaskDao::getInstance()->deleteByList($list->id, $this->userModel->id);
        TodoListDao::getInstance()->delete()->where(['id' => $list->id, 'user_id' => $this->userModel->id])->commit();

        return Response::asJson(['code' => 200, 'msg' => 'success', 'data' => []]);
    }
}
