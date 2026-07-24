document.querySelector("#hiddenBody").remove();

const listDialog = document.getElementById('todo-list-dialog');

listDialog.submit('/todo/listApi/create', (_data, res) => {
    const list = res.data;
    const url = `/todo/main/index?view=list&list_id=${list.id}`;
    const $item = $(`<mdui-list-item
            rounded
            icon="list"
            data-link="${url}"
            data-pjax="true"
            data-match="^/todo/main/index\\?([^#]*&)?view=list&list_id=${list.id}(&|$)"
            data-list-id="${list.id}"
            data-is-default="0"
            class="todo-list-item">${$.escapeHtml(list.title)}</mdui-list-item>`);
    $('#todo-drawer-list > mdui-divider').first().before($item);
    $item[0].click();
});

$('#todo-new-list-btn').on('click', () => {
    listDialog.open(true);
});

$('#todo-copy-mcp-btn').on('click', () => {
    $.request.get('/todo/mcpApi/getConfig', {}, (res) => {
        if (res.code !== 200) {
            $.toaster.error(res.msg || '获取 MCP 配置失败');
            return;
        }
        const text = JSON.stringify({ mcpServers: res.data.mcpServers }, null, 2);
        if ($.copy(text)) {
            $.toaster.success('MCP 配置已复制到剪贴板');
        } else {
            $.toaster.error('复制失败');
        }
    });
});