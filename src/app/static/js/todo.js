/**
 * Todo 工作台：同 path + query 切换走 pjax:prevented，只刷数据。
 * @file todo.js
 */
window.pageLoadFiles = ['Toaster', 'URLUtils', 'Layer', 'Form'];

window.pageOnLoad = function () {
    const $list = $('#todo-task-list');
    const $empty = $('#todo-task-empty');
    const $detail = $('#todo-detail-pane');
    const defaultListId = parseInt(
        $('#todo-drawer-list .todo-list-item[data-is-default="1"]').attr('data-list-id'),
        10,
    ) || 0;

    const state = { view: 'list', listId: 0, selectedId: 0, tasks: [] };

    const ymd = (ts) => (ts ? $.formatDateTime(new Date(ts * 1000)).slice(0, 10) : '');
    const todayInt = () => parseInt($.formatDateTime(new Date()).slice(0, 10).replace(/-/g, ''), 10);

    const $form = $('#item');

    const setDetail = (task) => {
        state.selectedId = task ? task.id : 0;
        $list.find('.todo-task-item').removeClass('is-active');
        if (!task) {
            $detail.attr('hidden', '');
            return;
        }
        $(`.todo-task-item[data-id="${task.id}"]`).addClass('is-active');
        $.form.val($form, {
            id: task.id,
            title: task.title || '',
            note: task.note || '',
            due_at: ymd(task.due_at),
            completed: task.completed,
            my_day: task.my_day_date === todayInt() ? 1 : 0,
        });
        $('#todo-detail-star').attr('icon', task.important === 1 ? 'star' : 'star_border');
        $detail.removeAttr('hidden');
    };

    const render = () => {
        $list.empty();
        if (!state.tasks.length) {
            $empty.text('暂无任务').show();
        } else {
            $empty.hide();
        }
        state.tasks.forEach((t) => {
            const due = ymd(t.due_at) || '无期限';
            $list.append(`
                <div class="todo-task-item d-flex items-start gap-2${t.id === state.selectedId ? ' is-active' : ''}${t.completed === 1 ? ' is-completed' : ''}" data-id="${t.id}">
                    <mdui-checkbox class="todo-task-check flex-none" ${t.completed === 1 ? 'checked' : ''}></mdui-checkbox>
                    <div class="todo-task-main flex-1 min-w-0">
                        <div class="todo-task-title body-large break-words">${$.escapeHtml(t.title || '')}</div>
                        <div class="todo-task-due text-primary label-small mt-1">${due}</div>
                    </div>
                    <mdui-button-icon class="todo-task-star flex-none" icon="${t.important === 1 ? 'star' : 'star_border'}"></mdui-button-icon>
                </div>`);
        });
    };

    const reload = (keepId = 0) => {
        const p = $.url.getAllParams();
        state.view = ['today', 'important', 'planned', 'list'].includes(p.view) ? p.view : 'list';
        state.listId = parseInt(p.list_id || '0', 10) || 0;
        if (state.view === 'list' && state.listId <= 0) {
            state.listId = defaultListId;
        }

        const link = location.pathname + location.search;
        let title = String($(`#todo-drawer-list mdui-list-item[data-link="${link}"]`).first().text() || '').trim();
        if (title) {
            $('.todo-pane-title').text(title);
        }
        const canDel = state.view === 'list'

        const $del = $('#todo-list-delete-btn');
        if (canDel) {
            $del.removeAttr('hidden');
        } else {
            $del.attr('hidden', '');
        }

        const data = { view: state.view };
        if (state.view === 'list') {
            data.list_id = state.listId;
        }
        $.request.get('/todo/taskApi/index', data, (res) => {
            if (res.code !== 200) {
                $.toaster.error(res.msg);
                return;
            }
            state.tasks = res.data;
            render();
            const task = keepId ? state.tasks.find((t) => t.id === keepId) : null;
            setDetail(task || null);
        });
    };

    const save = (payload, soft = false) => {
        $.request.postForm('/todo/taskApi/update', payload, (res) => {
            if (res.code !== 200) {
                $.toaster.error(res.msg || '操作失败');
                return;
            }
            if (soft) {
                const u = res.data;
                const i = state.tasks.findIndex((t) => t.id === u.id);
                if (i >= 0) {
                    state.tasks[i] = u;
                }
                render();
                return;
            }
            reload(state.selectedId);
        });
    };

    const addTask = () => {
        const title = String($('#todo-add-input').val() || '').trim();
        if (!title) {
            return;
        }
        const data = {
            list_id: state.view === 'list' && state.listId > 0 ? state.listId : defaultListId,
            title,
        };
        if (state.view === 'today') {
            data.my_day = 1;
        }
        if (state.view === 'important') {
            data.important = 1;
        }
        $.request.postForm('/todo/taskApi/create', data, (res) => {
            if (res.code !== 200) {
                $.toaster.error(res.msg || '操作失败');
                return;
            }
            $('#todo-add-input').val('');
            reload();
        });
    };

    $('#todo-add-btn').on('click', addTask);
    $('#todo-add-input').on('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTask();
        }
    });

    $list.on('click', '.todo-task-item', function (e) {
        if ($(e.target).closest('.todo-task-check, .todo-task-star').length) {
            return;
        }
        const task = state.tasks.find((t) => t.id === parseInt($(this).data('id'), 10));
        if (task) {
            setDetail(task);
        }
    });

    $list.on('change', '.todo-task-check', function (e) {
        e.stopPropagation();
        save({
            id: parseInt($(this).closest('.todo-task-item').data('id'), 10),
            completed: $(this).prop('checked') ? 1 : 0,
        });
    });

    $list.on('click', '.todo-task-star', function (e) {
        e.stopPropagation();
        const id = parseInt($(this).closest('.todo-task-item').data('id'), 10);
        const task = state.tasks.find((t) => t.id === id);
        if (task) {
            save({ id, important: task.important === 1 ? 0 : 1 });
        }
    });

    $('#todo-detail-back').on('click', () => setDetail(null));

    $('#todo-detail-star').on('click', () => {
        const task = state.tasks.find((t) => t.id === state.selectedId);
        if (task) {
            save({ id: task.id, important: task.important === 1 ? 0 : 1 });
        }
    });

    $('#todo-detail-delete').on('click', () => {
        if (!state.selectedId) {
            return;
        }
        $.request.postForm('/todo/taskApi/delete', { id: state.selectedId }, (res) => {
            if (res.code !== 200) {
                $.toaster.error(res.msg || '操作失败');
                return;
            }
            $.toaster.success('已删除');
            reload();
        });
    });

    $('#todo-list-delete-btn').on('click', function () {
        if ($(this).is('[hidden]')) {
            return;
        }
        const listId = state.listId;
        const name = String($('.todo-pane-title').text() || '').trim() || '该列表';
        $.layer.confirm({
            msg: `确定删除列表「${$.escapeHtml(name)}」吗？其中的任务也会一并删除。`,
            title: '删除列表',
            yes: () => {
                $.request.postForm('/todo/listApi/delete', { id: listId }, (res) => {
                    if (res.code !== 200) {
                        $.toaster.error(res.msg || '操作失败');
                        return;
                    }
                    $(`#todo-drawer-list .todo-list-item[data-list-id="${listId}"]`).remove();
                    $.toaster.success('列表已删除');
                    const url = `/todo/main/index?view=today`;
                    $.url.setUri(url);
                    $.emitter.emit('pjax:prevented', new URL(url, location.origin).searchParams);
                });
            },
        });
    });

    // 先拉列表，再绑详情表单：表单初始化失败不能挡住任务渲染
    $.emitter.on('pjax:prevented', () => reload());
    reload();

    $.form.onChange($form, 400, (data) => {
        const id = parseInt(data.id, 10) || 0;
        if (!id || id !== state.selectedId) {
            return;
        }
        const title = String(data.title || '').trim();
        if (!title) {
            return;
        }
        const payload = {
            id,
            title,
            note: data.note || '',
            due_at: data.due_at || '',
            completed: data.completed ? 1 : 0,
            my_day: data.my_day ? 1 : 0,
        };
        const task = state.tasks.find((t) => t.id === id);
        if (task
            && task.title === payload.title
            && (task.note || '') === payload.note
            && ymd(task.due_at) === payload.due_at
            && task.completed === payload.completed
            && (task.my_day_date === todayInt() ? 1 : 0) === payload.my_day) {
            return;
        }
        const soft = task
            && task.completed === payload.completed
            && (task.my_day_date === todayInt() ? 1 : 0) === payload.my_day
            && ymd(task.due_at) === payload.due_at;
        save(payload, soft);
    });

    return false;
};

window.pageOnUnLoad = function () {};
