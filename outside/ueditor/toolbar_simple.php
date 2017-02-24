<?php
/**
 * 百度编辑器简易版本的按钮配置
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
return <<<JS
    toolbars: [
        [
            'undo', //撤销
            'redo', //重做
            'bold', //加粗
            'italic', //斜体
            'underline', //下划线
            'strikethrough', //删除线
            'subscript', //下标
            'superscript', //上标
            'fontborder', //字符边框
            'forecolor', //字体颜色
            'fontfamily', //字体
            'fontsize', //字号
            'justifyleft', //居左对齐
            'justifyright', //居右对齐
            'justifycenter', //居中对齐
            'justifyjustify', //两端对齐
            'selectall', //全选
            'emotion', //表情
            'drafts' // 从草稿箱加载
        ]
    ],
JS;
