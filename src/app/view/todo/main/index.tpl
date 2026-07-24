<title id="title">{$pageTitle} - {$title}</title>
<style id="style">
    .todo-workspace {
        display: grid;
        grid-template-columns: 1fr;
        min-height: calc(var(--vh, 1vh) * 100 - 64px);
    }

    .todo-workspace.is-detail-open {
        grid-template-columns: minmax(0, 1fr) minmax(280px, 22rem);
    }

    .todo-pane-tasks {
        display: grid;
        grid-template-rows: auto auto minmax(0, 1fr);
        border-right: 1px solid rgba(var(--mdui-color-outline), 0.2);
    }

    .todo-workspace:not(.is-detail-open) .todo-pane-tasks {
        border-right: none;
    }

    .todo-pane-header {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.5rem;
        align-items: center;
    }

    .todo-add-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.5rem;
        align-items: start;
    }

    .todo-task-scroll {
        overflow: auto;
        min-height: 0;
    }

    .todo-task-list {
        display: grid;
        gap: 0.25rem;
    }

    .todo-task-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 0.5rem;
        align-items: start;
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

    .todo-pane-detail {
        display: grid;
        grid-template-rows: auto 1fr;
        border-left: 1px solid rgba(var(--mdui-color-outline), 0.2);
    }

    .todo-pane-detail[hidden] {
        display: none !important;
    }

    .todo-detail-toolbar {
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        gap: 0.25rem;
        align-items: center;
    }

    .todo-detail-body {
        display: grid;
        gap: 0.75rem;
        align-content: start;
        overflow: auto;
        min-height: 0;
    }

    @media (max-width: 839px) {
        .todo-workspace.is-detail-open {
            grid-template-columns: 1fr;
        }

        .todo-pane-tasks {
            border-right: none;
        }

        .todo-pane-detail:not([hidden]) {
            position: fixed;
            inset: 0;
            z-index: 40;
            border-left: none;
            overflow: auto;
        }
    }
</style>
<div id="container" class="mt-0 px-0">
    <div class="todo-workspace bg-surface">
        <div class="todo-pane-tasks p-3">
            <div class="todo-pane-header mb-3">
                <h1 class="todo-pane-title headline-medium font-semibold m-0 min-w-0"></h1>
                <mdui-button-icon id="todo-list-delete-btn" icon="delete" title="删除列表" hidden></mdui-button-icon>
            </div>
            <div class="todo-add-row mb-3">
                <mdui-text-field id="todo-add-input" label="添加任务" clearable></mdui-text-field>
                <mdui-button id="todo-add-btn" icon="add">添加</mdui-button>
            </div>
            <div class="todo-task-scroll">
                <div id="todo-task-list" class="todo-task-list"></div>
                <div id="todo-task-empty" class="text-center opacity-50 body-large py-4 px-3" hidden>暂无任务</div>
            </div>
        </div>

        <div class="todo-pane-detail bg-surface p-3" id="todo-detail-pane" hidden>
            <div class="todo-detail-toolbar mb-3">
                <mdui-button-icon id="todo-detail-back" icon="arrow_back" title="关闭详情"></mdui-button-icon>
                <div></div>
                <mdui-button-icon id="todo-detail-star" icon="star_border"></mdui-button-icon>
                <mdui-button-icon id="todo-detail-delete" icon="delete"></mdui-button-icon>
            </div>
            <div class="todo-detail-body">
                <mdui-checkbox id="todo-detail-done">已完成</mdui-checkbox>
                <mdui-text-field id="todo-detail-title" label="标题"></mdui-text-field>
                <mdui-text-field id="todo-detail-due" type="date" label="截止日期" clearable></mdui-text-field>
                <mdui-checkbox id="todo-detail-myday">添加到今天</mdui-checkbox>
                <mdui-text-field id="todo-detail-note" label="备注" rows="6"></mdui-text-field>
            </div>
        </div>
    </div>
</div>
<script id="script" src="/static/js/todo.js?v={$__v}"></script>
