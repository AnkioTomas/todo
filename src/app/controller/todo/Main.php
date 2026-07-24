<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\TodoListDao;
use app\database\model\TodoListModel;
use nova\framework\http\Response;
use nova\plugin\login\controller\BaseViewController;
use nova\plugin\tpl\Pjax;

class Main extends BaseViewController
{
    private const string BASE = '/todo/main/index';

    /**
     * 登录落地：对齐 book 的 firstUri 跳转，保证后续同 pathname 才能触发 pjax:prevented。
     */
    public function home(): Response
    {
        $default = TodoListDao::getInstance()->ensureDefault($this->userModel->id);
        return Pjax::redirectTo(self::BASE . '?view=list&list_id=' . $default->id);
    }

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

        return $this->viewResponse->asTpl('index', [
            'pageTitle' => $this->resolveTitle($view, $listId, $userId),
            'currentView' => $view,
            'currentListId' => $listId,
            'defaultListId' => $default->id,
        ]);
    }

    protected function getMenu(): array
    {
        $userId = $this->userModel->id;
        TodoListDao::getInstance()->ensureDefault($userId);
        /** @var TodoListModel[] $lists */
        $lists = TodoListDao::getInstance()->listByUser($userId);

        $menu = [
            $this->smartItem('今天', 'wb_sunny', 'today'),
            $this->smartItem('重要', 'star', 'important'),
            $this->smartItem('已计划', 'event_note', 'planned'),
        ];

        foreach ($lists as $list) {
            $menu[] = [
                'title' => $list->title,
                'icon' => $list->is_default === 1 ? 'checklist' : 'list',
                'url' => self::BASE . '?view=list&list_id=' . $list->id,
                'pjax' => true,
                'match' => '^/todo/main/index\?([^#]*&)?view=list&list_id=' . $list->id . '(&|$)',
                'list_id' => $list->id,
                'is_default' => $list->is_default,
            ];
        }

        return $menu;
    }

    /**
     * @return array{title:string,icon:string,url:string,pjax:bool,match:string}
     */
    private function smartItem(string $title, string $icon, string $view): array
    {
        return [
            'title' => $title,
            'icon' => $icon,
            'url' => self::BASE . '?view=' . $view,
            'pjax' => true,
            'match' => '^/todo/main/index\?([^#]*&)?view=' . $view . '(&|$)',
        ];
    }

    private function resolveTitle(string $view, int $listId, int $userId): string
    {
        $titles = [
            'today' => '今天',
            'important' => '重要',
            'planned' => '已计划',
        ];
        if (isset($titles[$view])) {
            return $titles[$view];
        }
        if ($listId > 0) {
            $list = TodoListDao::getInstance()->findOwned($listId, $userId);
            if ($list !== null) {
                return $list->title;
            }
        }
        return '任务';
    }
}
