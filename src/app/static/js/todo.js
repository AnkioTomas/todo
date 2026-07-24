/**
 * Todo 工作台
 * 列表切换对齐 book：同 pathname + query → pjax:prevented → 只刷数据，不换面板。
 * @file todo.js
 */

window.pageLoadFiles = ['Toaster', 'URLUtils'];

window.pageOnLoad = function () {
    const $workspace = $('.todo-workspace');
    if ($workspace.length === 0) {
        return false;
    }

    const state = {
        view: 'list',
        listId: 0,
        defaultListId: parseInt($workspace.data('default-list-id'), 10) || 0,
        selectedId: 0,
        tasks: [],
        noteTimer: null,
        titleTimer: null,
    };

    const $taskList = $('#todo-task-list');
    const $empty = $('#todo-task-empty');
    const $detailPane = $('#todo-detail-pane');
    const $detailEmpty = $('#todo-detail-empty');
    const $detailBody = $detailPane.find('.todo-detail-body');

    const req = () => $.request;

    const toastError = (msg) => {
        if (window.$ && $.toaster) {
            $.toaster.error(msg || '操作失败');
        }
    };

    const toastOk = (msg) => {
        if (window.$ && $.toaster) {
            $.toaster.success(msg || '已保存');
        }
    };

    const todayYmd = () => {
        const d = new Date();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return parseInt(`${d.getFullYear()}${m}${day}`, 10);
    };

    const formatDue = (dueAt) => {
        if (!dueAt) {
            return '';
        }
        const d = new Date(dueAt * 1000);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    const dueLabel = (dueAt) => {
        if (!dueAt) {
            return '';
        }
        const dateStr = formatDue(dueAt);
        const today = formatDue(Math.floor(Date.now() / 1000));
        if (dateStr === today) {
            return '今天到期';
        }
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tY = tomorrow.getFullYear();
        const tM = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const tD = String(tomorrow.getDate()).padStart(2, '0');
        if (dateStr === `${tY}-${tM}-${tD}`) {
            return '明天到期';
        }
        return `截止 ${dateStr}`;
    };

    const isNarrow = () => window.matchMedia('(max-width: 900px)').matches;

    const setField = (id, value) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = value == null ? '' : String(value);
        }
    };

    const getField = (id) => {
        const el = document.getElementById(id);
        return el ? String(el.value || '') : '';
    };

    const showDetailPane = (show) => {
        if (show) {
            $detailPane.removeAttr('hidden');
            $detailEmpty.attr('hidden', '');
            $detailBody.removeAttr('hidden');
            if (isNarrow()) {
                $detailPane.addClass('todo-detail-open');
                $workspace.addClass('todo-detail-active');
            }
            return;
        }
        $detailBody.attr('hidden', '');
        $detailEmpty.removeAttr('hidden');
        $detailPane.removeClass('todo-detail-open');
        $workspace.removeClass('todo-detail-active');
        if (!isNarrow()) {
            $detailPane.removeAttr('hidden');
        } else {
            $detailPane.attr('hidden', '');
        }
    };

    const clearDetail = () => {
        state.selectedId = 0;
        $taskList.find('.todo-task-item').removeClass('is-active');
        showDetailPane(false);
    };

    const fillDetail = (task) => {
        if (!task) {
            clearDetail();
            return;
        }
        state.selectedId = task.id;
        setField('todo-detail-title', task.title || '');
        setField('todo-detail-note', task.note || '');
        setField('todo-detail-due', formatDue(task.due_at));
        const doneEl = document.getElementById('todo-detail-done');
        if (doneEl) {
            doneEl.checked = task.completed === 1;
        }
        const mydayEl = document.getElementById('todo-detail-myday');
        if (mydayEl) {
            mydayEl.checked = task.my_day_date === todayYmd();
        }
        $('#todo-detail-star').attr('icon', task.important === 1 ? 'star' : 'star_border');
        showDetailPane(true);
    };

    const escapeHtml = (str) => {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    };

    const renderTasks = () => {
        $taskList.empty();
        if (!state.tasks.length) {
            $empty.removeAttr('hidden');
            return;
        }
        $empty.attr('hidden', '');
        state.tasks.forEach((task) => {
            const due = dueLabel(task.due_at);
            const active = task.id === state.selectedId ? ' is-active' : '';
            const done = task.completed === 1 ? ' is-completed' : '';
            const starIcon = task.important === 1 ? 'star' : 'star_border';
            $taskList.append(`
                <div class="todo-task-item${active}${done}" data-id="${task.id}">
                    <mdui-checkbox class="todo-task-check" ${task.completed === 1 ? 'checked' : ''}></mdui-checkbox>
                    <div class="todo-task-main">
                        <div class="todo-task-title">${escapeHtml(task.title)}</div>
                        ${due ? `<div class="todo-task-due">${escapeHtml(due)}</div>` : ''}
                    </div>
                    <mdui-button-icon class="todo-task-star" icon="${starIcon}"></mdui-button-icon>
                </div>`);
        });
    };

    const syncTitle = () => {
        const $active = $('#todo-drawer-list mdui-list-item[active]').first();
        let pageTitle = String($active.text() || '').trim();
        if (!pageTitle) {
            const titles = { today: '今天', important: '重要', planned: '已计划' };
            pageTitle = titles[state.view] || '任务';
        }
        $('.todo-pane-title').text(pageTitle);
        $('mdui-top-app-bar-title').text(pageTitle);
        const appName = 'Todo';
        document.title = `${pageTitle} - ${appName}`;
        const titleEl = document.getElementById('title');
        if (titleEl) {
            titleEl.textContent = `${pageTitle} - ${appName}`;
        }
    };

    const applyParams = () => {
        const params = $.url.getAllParams();
        let view = params.view || 'list';
        if (!['today', 'important', 'planned', 'list'].includes(view)) {
            view = 'list';
        }
        let listId = parseInt(params.list_id || '0', 10) || 0;
        if (view === 'list' && listId <= 0) {
            listId = state.defaultListId;
        }
        state.view = view;
        state.listId = listId;
        $workspace.attr('data-view', state.view);
        $workspace.attr('data-list-id', String(state.listId || 0));
        syncTitle();
    };

    const loadTasks = () => {
        const data = { view: state.view };
        if (state.view === 'list') {
            data.list_id = state.listId;
        }
        req().get('/todo/taskApi/index', data, (res) => {
            if (res.code !== 200) {
                toastError(res.msg);
                return;
            }
            state.tasks = res.data || [];
            clearDetail();
            renderTasks();
        });
    };

    /** 对齐 book.js：同 path query 切换只刷数据 */
    const reload = () => {
        applyParams();
        loadTasks();
    };

    const updateTask = (payload, cb) => {
        req().postForm('/todo/taskApi/update', payload, (res) => {
            if (res.code !== 200) {
                toastError(res.msg);
                return;
            }
            const updated = res.data;
            const idx = state.tasks.findIndex((t) => t.id === updated.id);
            if (idx >= 0) {
                state.tasks[idx] = updated;
            }
            if (typeof cb === 'function') {
                cb(updated);
                return;
            }
            renderTasks();
            if (state.selectedId === updated.id) {
                fillDetail(updated);
            }
        });
    };

    const targetListId = () => {
        if (state.view === 'list' && state.listId > 0) {
            return state.listId;
        }
        return state.defaultListId;
    };

    const addTask = () => {
        const title = getField('todo-add-input').trim();
        if (!title) {
            return;
        }
        req().postForm('/todo/taskApi/create', {
            list_id: targetListId(),
            title: title,
        }, (res) => {
            if (res.code !== 200) {
                toastError(res.msg);
                return;
            }
            setField('todo-add-input', '');
            const task = res.data;
            if (state.view === 'today') {
                updateTask({ id: task.id, my_day: 1 }, () => loadTasks());
                return;
            }
            if (state.view === 'important') {
                updateTask({ id: task.id, important: 1 }, () => loadTasks());
                return;
            }
            loadTasks();
        });
    };

    $('#todo-add-btn').on('click', addTask);
    $('#todo-add-input').on('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTask();
        }
    });

    $taskList.on('click', '.todo-task-item', function (e) {
        if ($(e.target).closest('.todo-task-check, .todo-task-star').length) {
            return;
        }
        const id = parseInt($(this).data('id'), 10);
        const task = state.tasks.find((t) => t.id === id);
        if (!task) {
            return;
        }
        $taskList.find('.todo-task-item').removeClass('is-active');
        $(this).addClass('is-active');
        fillDetail(task);
    });

    $taskList.on('change', '.todo-task-check', function (e) {
        e.stopPropagation();
        const id = parseInt($(this).closest('.todo-task-item').data('id'), 10);
        updateTask({ id: id, completed: this.checked ? 1 : 0 }, (updated) => {
            if (state.view !== 'list' && updated.completed === 1) {
                state.tasks = state.tasks.filter((t) => t.id !== updated.id);
                if (state.selectedId === updated.id) {
                    clearDetail();
                }
                renderTasks();
                return;
            }
            renderTasks();
            if (state.selectedId === updated.id) {
                fillDetail(updated);
            }
        });
    });

    $taskList.on('click', '.todo-task-star', function (e) {
        e.stopPropagation();
        const id = parseInt($(this).closest('.todo-task-item').data('id'), 10);
        const task = state.tasks.find((t) => t.id === id);
        if (!task) {
            return;
        }
        updateTask({ id: id, important: task.important === 1 ? 0 : 1 }, (updated) => {
            if (state.view === 'important' && updated.important === 0) {
                state.tasks = state.tasks.filter((t) => t.id !== updated.id);
                if (state.selectedId === updated.id) {
                    clearDetail();
                }
                renderTasks();
                return;
            }
            renderTasks();
            if (state.selectedId === updated.id) {
                fillDetail(updated);
            }
        });
    });

    $('#todo-detail-back').on('click', () => {
        clearDetail();
        if (isNarrow()) {
            $detailPane.attr('hidden', '');
        }
    });

    $('#todo-detail-star').on('click', () => {
        if (!state.selectedId) {
            return;
        }
        const task = state.tasks.find((t) => t.id === state.selectedId);
        if (!task) {
            return;
        }
        updateTask({ id: task.id, important: task.important === 1 ? 0 : 1 });
    });

    $('#todo-detail-delete').on('click', () => {
        if (!state.selectedId) {
            return;
        }
        const id = state.selectedId;
        req().postForm('/todo/taskApi/delete', { id: id }, (res) => {
            if (res.code !== 200) {
                toastError(res.msg);
                return;
            }
            state.tasks = state.tasks.filter((t) => t.id !== id);
            clearDetail();
            renderTasks();
            toastOk('已删除');
        });
    });

    $('#todo-detail-done').on('change', function () {
        if (!state.selectedId) {
            return;
        }
        updateTask({ id: state.selectedId, completed: this.checked ? 1 : 0 });
    });

    $('#todo-detail-myday').on('change', function () {
        if (!state.selectedId) {
            return;
        }
        updateTask({ id: state.selectedId, my_day: this.checked ? 1 : 0 }, (updated) => {
            if (state.view === 'today' && updated.my_day_date !== todayYmd()) {
                state.tasks = state.tasks.filter((t) => t.id !== updated.id);
                clearDetail();
                renderTasks();
                return;
            }
            renderTasks();
            fillDetail(updated);
        });
    });

    $('#todo-detail-due').on('change', function () {
        if (!state.selectedId) {
            return;
        }
        updateTask({ id: state.selectedId, due_at: getField('todo-detail-due') || '' }, (updated) => {
            if (state.view === 'planned' && !updated.due_at) {
                state.tasks = state.tasks.filter((t) => t.id !== updated.id);
                clearDetail();
                renderTasks();
                return;
            }
            renderTasks();
            fillDetail(updated);
        });
    });

    $('#todo-detail-title').on('input', function () {
        if (!state.selectedId) {
            return;
        }
        const value = getField('todo-detail-title');
        clearTimeout(state.titleTimer);
        state.titleTimer = setTimeout(() => {
            if (!value.trim()) {
                return;
            }
            updateTask({ id: state.selectedId, title: value.trim() });
        }, 400);
    });

    $('#todo-detail-note').on('input', function () {
        if (!state.selectedId) {
            return;
        }
        const value = getField('todo-detail-note');
        clearTimeout(state.noteTimer);
        state.noteTimer = setTimeout(() => {
            updateTask({ id: state.selectedId, note: value });
        }, 500);
    });

    // 新建列表 / ICS：drawer 在 layout 壳里，只绑一次
    if (!window.__todoDrawerBound) {
        window.__todoDrawerBound = true;

        $(document).on('click', '#todo-new-list-btn', () => {
            const dialog = document.getElementById('todo-list-dialog');
            if (dialog) {
                setField('todo-list-title-input', '');
                dialog.open = true;
            }
        });

        $(document).on('click', '#todo-ics-btn', () => {
            const dialog = document.getElementById('todo-ics-dialog');
            if (!dialog) {
                return;
            }
            $.request.get('/todo/icsApi/info', {}, (res) => {
                if (res.code !== 200) {
                    toastError(res.msg);
                    return;
                }
                setField('todo-ics-url', res.data.url || '');
                dialog.open = true;
            });
        });
    }

    $('#todo-list-dialog-cancel').on('click', () => {
        const dialog = document.getElementById('todo-list-dialog');
        if (dialog) {
            dialog.open = false;
        }
    });

    const appendDrawerList = (list) => {
        const $list = $('#todo-drawer-list');
        const $divider = $list.find('mdui-divider').first();
        const url = `/todo/main/index?view=list&list_id=${list.id}`;
        const item = document.createElement('mdui-list-item');
        item.setAttribute('rounded', '');
        item.setAttribute('icon', 'list');
        item.setAttribute('data-link', url);
        item.setAttribute('data-pjax', 'true');
        item.setAttribute('data-match', `^/todo/main/index\\?([^#]*&)?view=list&list_id=${list.id}(&|$)`);
        item.setAttribute('data-list-id', String(list.id));
        item.setAttribute('data-is-default', '0');
        item.classList.add('todo-list-item');
        item.textContent = list.title;
        if ($divider.length) {
            $divider[0].before(item);
        } else {
            $list[0].appendChild(item);
        }
        return item;
    };

    $('#todo-list-dialog-ok').on('click', () => {
        const title = getField('todo-list-title-input').trim();
        if (!title) {
            toastError('请输入列表名称');
            return;
        }
        req().postForm('/todo/listApi/create', { title: title }, (res) => {
            if (res.code !== 200) {
                toastError(res.msg);
                return;
            }
            const list = res.data;
            const dialog = document.getElementById('todo-list-dialog');
            if (dialog) {
                dialog.open = false;
            }
            // 走 SidebarManager → loadUri 同 path → pjax:prevented
            appendDrawerList(list).click();
        });
    });

    $('#todo-ics-close').on('click', () => {
        const dialog = document.getElementById('todo-ics-dialog');
        if (dialog) {
            dialog.open = false;
        }
    });

    $('#todo-ics-copy').on('click', async () => {
        const url = getField('todo-ics-url');
        if (!url) {
            return;
        }
        try {
            await navigator.clipboard.writeText(url);
            toastOk('已复制');
        } catch (e) {
            document.getElementById('todo-ics-url')?.select?.();
            toastOk('请手动复制');
        }
    });

    $('#todo-ics-reset').on('click', () => {
        req().postForm('/todo/icsApi/reset', {}, (res) => {
            if (res.code !== 200) {
                toastError(res.msg);
                return;
            }
            setField('todo-ics-url', res.data.url || '');
            toastOk('订阅链接已重置');
        });
    });

    if (isNarrow()) {
        $detailPane.attr('hidden', '');
    } else {
        $detailPane.removeAttr('hidden');
        showDetailPane(false);
    }

    // 对齐 book.js
    $.emitter.on('pjax:prevented', reload);
    reload();

    return false;
};

window.pageOnUnLoad = function () {
};
