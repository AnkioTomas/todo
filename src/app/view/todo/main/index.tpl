<title id="title">{$title}</title>
<style id="style">


    .todo-pane-detail {
        width: 22rem;
        border-left: 1px solid rgba(var(--mdui-color-outline), 0.2);
    }

    .todo-pane-detail[hidden] {
        display: none !important;
    }

    .todo-task-item {
        padding: 0.65rem 0.75rem;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .todo-task-item:hover {
        background: rgba(var(--mdui-color-on-surface), 0.06);
    }

    .todo-task-item.is-active {
        background: rgba(var(--mdui-color-primary), 0.12);
    }

    .todo-task-item.is-completed .todo-task-title {
        text-decoration: line-through;
        opacity: 0.55;
    }

    [hidden]{
        display:none;
    }

    @media (max-width: 839px) {
        .todo-pane-detail:not([hidden]) {
            position: fixed;
            inset: 0;
            z-index: 50;
            width: 100%;
            border-left: none;
            box-sizing: border-box;
            margin-top: 64px;
        }
    }
</style>
<div id="container" class="container p-4">
    <div class="todo-workspace bg-surface d-flex items-stretch">
        <div class="todo-pane-tasks flex-1 min-w-0 p-3">
            <div class="d-flex items-center justify-between gap-2 mb-3">
                <h1 class="todo-pane-title headline-medium font-semibold m-0 flex-1 min-w-0"></h1>
                <mdui-button-icon class="flex-none" id="todo-list-delete-btn" icon="delete" title="删除列表" hidden></mdui-button-icon>
            </div>
            <div class="d-flex items-start gap-2 mb-3">
                <mdui-text-field class="flex-1 min-w-0" id="todo-add-input" label="添加任务" clearable></mdui-text-field>
                <mdui-button class="flex-none" id="todo-add-btn" icon="add">添加</mdui-button>
            </div>
            <div id="todo-task-list" class="d-flex flex-col gap-1"></div>
            <div id="todo-task-empty" class="text-center opacity-50 body-large py-4 px-3">加载中…</div>
        </div>

        <div class="todo-pane-detail bg-surface flex-none p-3 overflow-auto" id="todo-detail-pane" hidden>
            <div class="d-flex items-center gap-1 mb-3">
                <mdui-button-icon id="todo-detail-back" icon="arrow_back" title="关闭详情"></mdui-button-icon>
                <div class="flex-1"></div>
                <mdui-button-icon id="todo-detail-star" icon="star_border"></mdui-button-icon>
                <mdui-button-icon id="todo-detail-delete" icon="delete"></mdui-button-icon>
            </div>
            <form id="item" class="d-flex flex-col gap-3">
                <mdui-text-field type="hidden" name="id"></mdui-text-field>
                <mdui-checkbox name="completed">已完成</mdui-checkbox>
                <mdui-text-field name="title" label="标题"></mdui-text-field>
                <mdui-text-field name="due_at" type="date" label="截止日期" clearable></mdui-text-field>
                <mdui-checkbox name="my_day">添加到今天</mdui-checkbox>
                <mdui-text-field name="note" label="备注" rows="6"></mdui-text-field>
            </form>
        </div>
    </div>
</div>
<script id="script" src="/static/js/todo.js?v={$__v}"></script>
