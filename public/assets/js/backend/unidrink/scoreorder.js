define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    // 合并格式化方法
    Table.api.formatter = $.extend(Table.api.formatter,
        {
            statusCustom : function (value, row, index) {
                let number = value == 0 ? 0 : 1;
                let display = value == 0 ? '否' : '是';
                let color = value == 0 ? 'primary' : 'success';
                var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> ' + display + '</span>';
                if (value != 0){
                    html = '<a href="javascript:;" class="searchit" data-operate="=" data-field="' + this.field + '" data-value="' + number + '" data-toggle="tooltip" title="' + __('Time: %s', Moment(parseInt(value) * 1000).format('YYYY-MM-DD HH:mm:ss')) + '" >' + html + '</a>';
                } else {
                    html = '<a href="javascript:;" class="searchit" data-operate="=" data-field="' + this.field + '" data-value="' + number + '" data-toggle="tooltip" title="' + __('Click to search %s', display) + '" >' + html + '</a>';
                }
                return html;
            }
        }
    );
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'unidrink/scoreorder/index' + location.search,
                    add_url: 'unidrink/scoreorder/add',
                    edit_url: 'unidrink/scoreorder/edit',
                    del_url: 'unidrink/scoreorder/del',
                    multi_url: 'unidrink/scoreorder/multi',
                    import_url: 'unidrink/scoreorder/import',
                    table: 'unidrink_score_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.username', title: __('Username'), operate: false},
                        {field: 'product.title', title: __('Product'), operate: false},
                        {field: 'number', title: __('Number')},
                        {field: 'score', title: __('Score')},
                        {field: 'total_score', title: __('Total_score')},
                        {field: 'status', title: __('Status'), searchList: {"-1":__('Status -1'),"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'have_paid_status', title: __('Have_paid_status'),searchList: {"0":__('No'),"1":__('Yes')}, formatter: Table.api.formatter.statusCustom},
                        {field: 'have_delivered_status', title: __('Have_delivered_status'),searchList: {"0":__('No'),"1":__('Yes')}, formatter: Table.api.formatter.statusCustom},
                        {field: 'have_received_status', title: __('Have_received_status'),searchList: {"0":__('No'),"1":__('Yes')}, formatter: Table.api.formatter.statusCustom},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'unidrink/scoreorder/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'unidrink/scoreorder/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'unidrink/scoreorder/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});