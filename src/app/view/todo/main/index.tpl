<title id="title">{$pageTitle} - {$title}</title>
<style id="style">
@import url("/static/css/todo.css?v={$__v}");
</style>
<div id="container" class="todo-page mt-0">
    <div class="todo-workspace"
         data-view="{$currentView}"
         data-list-id="{$currentListId}"
         data-default-list-id="{$defaultListId}">
        <div class="todo-pane todo-pane-tasks">
            <div class="todo-pane-header">
                <h1 class="todo-pane-title">{$pageTitle}</h1>
            </div>
            <div class="todo-add-row">
                <mdui-text-field id="todo-add-input" label="添加任务" clearable></mdui-text-field>
                <mdui-button id="todo-add-btn" icon="add">添加</mdui-button>
            </div>
            <div id="todo-task-list" class="todo-task-list"></div>
            <div id="todo-task-empty" class="todo-empty" hidden>暂无任务</div>
        </div>

        <div class="todo-pane todo-pane-detail" id="todo-detail-pane" hidden>
            <div class="todo-detail-toolbar">
                <mdui-button-icon id="todo-detail-back" icon="arrow_back" class="todo-detail-back"></mdui-button-icon>
                <div style="flex-grow:1"></div>
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
            <div id="todo-detail-empty" class="todo-empty todo-detail-empty-hint">选择左侧任务查看详情</div>
        </div>
    </div>

    <mdui-dialog id="todo-list-dialog" headline="新建列表">
        <mdui-text-field id="todo-list-title-input" label="列表名称" clearable></mdui-text-field>
        <mdui-button slot="action" variant="text" id="todo-list-dialog-cancel">取消</mdui-button>
        <mdui-button slot="action" variant="tonal" id="todo-list-dialog-ok">创建</mdui-button>
    </mdui-dialog>

    <mdui-dialog id="todo-ics-dialog" headline="日历订阅">
        <p class="todo-ics-hint">将此链接添加到 Apple / Google 日历即可订阅未完成且有截止日期的任务。</p>
        <mdui-text-field id="todo-ics-url" label="订阅地址" readonly></mdui-text-field>
        <mdui-button slot="action" variant="text" id="todo-ics-reset">重置链接</mdui-button>
        <mdui-button slot="action" variant="tonal" id="todo-ics-copy">复制</mdui-button>
        <mdui-button slot="action" variant="text" id="todo-ics-close">关闭</mdui-button>
    </mdui-dialog>
</div>
<script id="script" src="/static/js/todo.js?v={$__v}"></script>
