define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'uniprint/printer/index' + location.search,
                    add_url: 'uniprint/printer/add',
                    edit_url: 'uniprint/printer/edit',
                    del_url: 'uniprint/printer/del',
                    multi_url: 'uniprint/printer/multi',
                    import_url: 'uniprint/printer/import',
                    table: 'uniprint_printer',
                    printing_url: 'uniprint/printer/printing',
                    sync_url: 'uniprint/printer/sync',
                }
            });

            var table = $("#table");

            // 合并操作方法
            Table.api.events.operate = $.extend(Table.api.events.operate,
                {
                    'click .btn-printing': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];

                        $.ajax({
                            type: 'GET',
                            url: $.fn.bootstrapTable.defaults.extend.printing_url + '?id=' + ids,
                            contentType: 'application/json',
                            dataType: 'json',
                            success: function (res) {
                                if(res.code == 1) {
                                    Toastr.success(res.msg);
                                } else if(res.code == 0) {
                                    Toastr.error(res.msg);
                                } else {
                                    Toastr.error(res);
                                }
                            },
                            fail:function () {
                                Toastr.error('退款失败');
                            }
                        });

                    }
                }
            );

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'brand_id', title: __('Brand')},
                        {field: 'brand.title', title: __('Brand'),operate: false},
                        {field: 'device_id', title: __('Device_id'), operate: 'LIKE'},
                        {field: 'device_secret', title: __('Device_secret'), operate: 'LIKE'},
                        {field: 'card', title: __('Card'), operate: 'LIKE'},
                        {field: 'number', title: __('Number'), operate: false},
                        {field: 'status', title: __('Status'), searchList: {"0": '离线',"1": '在线',"2": '缺纸'}, formatter: Table.api.formatter.status},
                        {field: 'enable', title: __('Enable'), searchList: {"0": '否',"1": '是'}, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name: 'printing',
                                    text: '测试打印',
                                    classname: 'btn btn-xs btn-info btn-printing',
                                    extend: 'data-toggle="tooltip"',
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            let parenttable = table.closest('.bootstrap-table');
            let options = table.bootstrapTable('getOptions');
            //Bootstrap操作区
            let toolbar = $(options.toolbar, parenttable);
            // 更新打印机状态
            toolbar.on('click', '.btn-sync', function () {

                $.ajax({
                    type: 'GET',
                    url: $.fn.bootstrapTable.defaults.extend.sync_url ,
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function (res) {
                        if(res.code == 1) {
                            Toastr.success(res.msg);
                            table.bootstrapTable('refresh');
                        } else if(res.code == 0) {
                            Toastr.error(res.msg);
                        } else {
                            Toastr.error(res);
                        }
                    },
                    fail:function () {
                        Toastr.error('同步失败');
                    }
                });
            });
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
