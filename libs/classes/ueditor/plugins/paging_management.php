<?php

/**
 * 分页标题管理
 *
 * @author Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
class ckPagingManagement extends CkPublicClass
{
    /**
     * 当前类对象句柄
     *
     * @var    object
     * @static
     * @since  1.0
     */
    private static $_instance = null;

    /**
     * 包：CkPublicClass 的子类构造函数
     *
     * 如果子类要使用构造方法初始化，那该构造方法必须要调用基类的构造方法
     * 具体基类构造方法请查看文件：ck_public_class.php
     *
     * @since 1.0
     */
    public function __construct($title = '')
    {
        // 设置插件弹出窗口标题
        $this->_title = $title;
        parent::__construct();
    }

    /**
     * 插件入口
     *
     * @since nv50
     */
    public function init()
    {
		echo <<<EOT
        <div style="width:90%; margin:15px auto; text-align:left;" id="show_styles"></div>
		<script type="text/javascript">
            var parentObject = getParentObject();
            var editorContent = parentObject.getContent();
			var sourceDiv = document.getElementById('show_styles');
            var pageData, pageContents = '';
            var patt = /\[#(.*?)#\]/;
            var testString = editorContent.replace(/(\s|<.*?>|&nbsp;)/, '').search(patt);
            if (testString == -1)
            {
                pageContents = ('<div style="font-size:14px; text-align:center; width:100%; margin:50px 0;">该信息无分页。</div>');
            }
            else
            {
                if (testString == 0)
                {
                    var page = 1;
                }
                else
                {
                	var page = 2;
                }
                for(var i = 0; (pageData = editorContent.match(patt)) && (i < 100); ++i)
                {
                    pageContents += ('<div style="height:30px;">第' + page + '页标题：[#<input type="text" name="ck_page' + i + '" value="' + pageData[1] + '" style="border:1px #ccc solid;" id="ck_page' + i + '"/>#]</div>');
                    editorContent = editorContent.replace(pageData[0], '<!--08_REPLACE_PAGE_' + i + '-->');
                    ++page;
                }
                pageContents = ('<div style="font-size:14px;">共有' + (page - 1) + '页： ( [#<font style="color:red;">这里面的是分页标题</font>#] )<br /><br />' + pageContents + '<div style="width:100%; text-align:center; margin-top:30px"><input type="button" value="保存返回" onclick="setValue();"/></div></div>');                
            }
            
            sourceDiv.innerHTML = pageContents;
			function setValue()
            {
				var str = '', newEditorContent = editorContent;
				var sourceDiv = document.getElementById('show_styles');
				var elements = sourceDiv.getElementsByTagName("input");
				for(i=0; i< elements.length - 1; i++)
				{
					if(elements[i].value == '')
                    {
						alert('分页标题不能为空！');
						return false;
					}
                    newEditorContent = newEditorContent.replace('<!--08_REPLACE_PAGE_' + i + '-->', '[#' + elements[i].value + '#]');
				}
                if ( newEditorContent )
                {
                    parentObject.setContent(newEditorContent);
                }
                
                winclose();
			}
		</script>
EOT;
    }

    /**
     * 安装该插件功能
     *
     * @param string $title 浮动窗标题
     *
     * @static
     * @since 1.0
     */
    public static function Setup($title = '')
    {
        if(null == self::$_instance)
        {
            self::$_instance = new self($title);
        }
        self::$_instance->init();
    }
}

ckPagingManagement::Setup('分页标题管理');