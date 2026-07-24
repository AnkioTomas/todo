<script src="/static/bundle?file=
framework/libs/vhcheck.min.js,
framework/libs/mdui.global.min.js,
framework/bootloader.js,
framework/utils/Loading.js,
framework/utils/Logger.js,
framework/utils/Loader.js,
framework/utils/Timing.js,
framework/utils/Event.js,
framework/utils/Toaster.js,
framework/utils/Request.js,
framework/utils/Form.js,
components/formDialog/DialogForm.js,
framework/theme/ThemeSwitcher.js,
framework/language/NodeUtils.js,
framework/language/TranslateUtils.js,
framework/language/Language.js,
framework/imageLoader/ImageLoader.js,
framework/pjax/nprogress.js,
framework/pjax/PjaxUtils.js,
framework/layout.js,
&type=js&v={$__v}"></script>

<mdui-dialog-form label="新建列表" id="todo-list-dialog" saveName="创建">
    <form>
        <mdui-text-field name="title" label="列表名称" required clearable></mdui-text-field>
    </form>
</mdui-dialog-form>

<script>
    document.querySelector("#hiddenBody").remove();

    const listDialog = document.getElementById('todo-list-dialog');

    listDialog.submit('/todo/listApi/create', (_data, res) => {
        location.href = "/todo/main/index?view=list&list_id=" + res.data.id;
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
</script>
