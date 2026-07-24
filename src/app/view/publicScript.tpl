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

<mdui-dialog-form label="日历订阅" id="todo-ics-dialog" saveName="复制">
    <form>
        <p class="mb-3 opacity-75 body-medium">将此链接添加到 Apple / Google 日历即可订阅未完成且有截止日期的任务。</p>
        <mdui-text-field name="url" label="订阅地址" readonly></mdui-text-field>
        <mdui-button type="button" variant="text" id="todo-ics-reset" class="mt-2">重置链接</mdui-button>
    </form>
</mdui-dialog-form>

<script>
    document.querySelector("#hiddenBody").remove();

    const listDialog = document.getElementById('todo-list-dialog');
    const icsDialog = document.getElementById('todo-ics-dialog');


    listDialog.submit('/todo/listApi/create', (_data, res) => {
        location.href = "/todo/main/index?view=list&list_id=" + res.data.id;
    });

    icsDialog.submit(null, (data) => {
        if (!data.url) {
            return;
        }
        if ($.copy(data.url)) {
            $.toaster.success('已复制');
        } else {
            $.toaster.error('复制失败');
        }

        icsDialog.close();
    });

    $('#todo-new-list-btn').on('click', () => {
        listDialog.open(true);
    });

    $('#todo-ics-btn').on('click', () => {
        $.request.get('/todo/icsApi/info', {
        }, (res) => {
            if (res.code !== 200) {
                $.toaster.error(res.msg || '操作失败');
                return;
            }
            icsDialog.open();
            icsDialog.setValue( res.data );

        });
    });

    $('#todo-ics-reset').on('click', () => {
        $.request.postForm('/todo/icsApi/reset', {

        }, (res) => {
            if (res.code !== 200) {
                $.toaster.error(res.msg || '操作失败');
                return;
            }
            icsDialog.setValue(res.data);
            $.toaster.success('订阅链接已重置');
        });
    });
</script>
