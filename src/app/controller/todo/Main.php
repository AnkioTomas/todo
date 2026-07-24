<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\TodoListDao;
use app\database\model\TodoListModel;
use nova\framework\http\Response;
use nova\plugin\login\controller\BaseViewController;

class Main extends BaseViewController
{
    public function index(): Response
    {
        $userId = $this->userModel->id;
        $default = TodoListDao::getInstance()->ensureDefault($userId);

        $view = (string)$this->request->get('view', 'list');
        if (!in_array($view, ['today', 'important', 'planned', 'list'], true)) {
            $view = 'list';
        }

        $listId = (int)$this->request->get('list_id', 0);
        if ($view === 'list' && $listId <= 0) {
            $listId = $default->id;
        }

        $titles = [
            'today' => '今天',
            'important' => '重要',
            'planned' => '已计划',
            'list' => '任务',
        ];
        $pageTitle = $titles[$view] ?? '任务';
        if ($view === 'list' && $listId > 0) {
            $list = TodoListDao::getInstance()->findOwned($listId, $userId);
            if ($list !== null) {
                $pageTitle = $list->title;
            }
        }

        return $this->viewResponse->asTpl('index', [
            'pageTitle' => $pageTitle,
            'currentView' => $view,
            'currentListId' => $listId,
            'defaultListId' => $default->id,
        ]);
    }

    protected function getMenu(): array
    {
        $userId = $this->userModel->id;
        $default = TodoListDao::getInstance()->ensureDefault($userId);
        /** @var TodoListModel[] $lists */
        $lists = TodoListDao::getInstance()->listByUser($userId);

        $menu = [
            [
                'title' => '今天',
                'url' => '/todo/main/index?view=today',
                'icon' => 'wb_sunny',
                'pjax' => true,
                'match' => 'view=today',
            ],
            [
                'title' => '重要',
                'url' => '/todo/main/index?view=important',
                'icon' => 'star',
                'pjax' => true,
                'match' => 'view=important',
            ],
            [
                'title' => '已计划',
                'url' => '/todo/main/index?view=planned',
                'icon' => 'event_note',
                'pjax' => true,
                'match' => 'view=planned',
            ],
        ];

        foreach ($lists as $list) {
            $menu[] = [
                'title' => $list->title,
                'url' => '/todo/main/index?view=list&list_id=' . $list->id,
                'icon' => $list->is_default === 1 ? 'checklist' : 'list',
                'pjax' => true,
                'match' => 'list_id=' . $list->id,
                'list_id' => $list->id,
                'is_default' => $list->is_default,
            ];
        }

        return $menu;
    }
}
