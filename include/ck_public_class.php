<?php
/**
 * CK公共操作类
 *
 * 以后该操作类用于CK插件开发公共接口，减少重复代码
 *
 * @package   CkPublicClass
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
if ( !defined('M_ADMIN') && !defined('M_MCENTER') )
{
    define('M_ADMIN', TRUE);     /// 目前限制CK插件只能在后台与会员中心使用
}
//$memberid || die('请先登录！');
require_once M_ROOT . 'include' . DS . 'admina.fun.php';
abstract class CkPublicClass extends _08_Controller_Base
{
    /**
     * 设置返回给CK的JS函数代码，函数名称必须命名为：getReturn
     *
     * @var   string
     * @since 1.0
     */
    private $_get_return_function_str = '';

    /**
     * 设置弹窗标题
     *
     * @var   string
     * @since 1.0
     */
    protected $_title = '';

    /**
     * 脚本定向参数
     *
     * @var   string
     * @since 1.0
     */
    protected $_action;

    /**
     * 查询标题
     *
     * @var   string
     * @since 1.0
     */
    protected $_subject = '';

    /**
     * 查询区域
     *
     * @var   int
     * @since 1.0
     */
    protected $_ccid1 = 0;

    /**
     * 查询商圈
     *
     * @var   int
     * @since 1.0
     */
    protected $_ccid2 = 0;

    /**
     * 查询信息条件
     *
     * @var   string
     * @since 1.0
     */
    protected $_where = '';

    /**
     * 查询SQL语句
     *
     * @var   string
     * @since 1.0
     */
    protected $_sql = '';
    
    protected $_handlekey = 0;
    
    protected $_uri;

    /**
     * 包：CkPublicClass 的构造方法
     *
     * 该构造方法在子类的构造方法或是最开始时必须要调用（ 如：parent::__construct(); ）
     *
     * @since 1.0
     */
    public function __construct()
    {
        global $cms_abs, $mcharset, $fmdata, $cmsurl,$cms_top;
        parent::__construct();
        $this->_action = empty($this->_get['action']) ? 'init' : trim($this->_get['action']);
		// 如果字段不一样。可对action设置为非 serach 值然后在子类里重新获取即可
        if(in_array($this->_action, array('search'))) {
            if(!empty($this->_get['subject'])) {
                $this->_subject = addcslashes(trim($this->_get['subject']), '%_');
                $this->_where .= " AND a.subject LIKE '%{$this->_subject}%'";
            }
            if(!empty($fmdata['ccid1'])) {
                $this->_ccid1 = (int)$fmdata['ccid1'];
                $this->_where .= " AND a.ccid1 = {$this->_ccid1}";
            }
            if(!empty($fmdata['ccid2'])) {
                $this->_ccid2 = (int)$fmdata['ccid2'];
                $this->_where .= " AND a.ccid2 = {$this->_ccid2}";
            }
        }
        
        self::_setNoCache();
        if ( empty($this->_get['parent_wid']) )
        {
            $wid = 'main';
            $parent_wid = 0;
        }
        else
        {
        	$parent_wid = (int)$this->_get['parent_wid'];
            $wid = "_08winid_{$parent_wid}";
        }
        
        $this->_handlekey = empty($this->_get['handlekey']) ? 0 : (int) $this->_get['handlekey'];
        $varname = empty($this->_get['varname']) ? '' : trim($this->_get['varname']);
        
        $this->_uri = "&varname={$varname}&parent_wid={$parent_wid}";
        echo <<< EOT
            <!DOCTYPE html PUBLIC "-W3CDTD XHTML 1.0 TransitionalEN" "http:www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http:www.w3.org/1999/xhtml" >
            <head>
            <title>{$this->_title}</title>
            <meta http-equiv="Content-Type" content="text/html; charset={$mcharset}" />
            <link href="{$cms_abs}images/admina/contentsAdmin.css" rel="stylesheet" type="text/css" />
            <link href="{$cms_abs}images/common/window.css" rel="stylesheet" type="text/css" />
            <script type="text/javascript">var CMS_ABS = "{$cms_abs}" , CMS_URL = "{$cmsurl}", MC_ROOTURL = "MC_ROOTURL";var originDomain = originDomain || document.domain;document.domain = "{$cms_top}" || document.domain;</script>
            <script type="text/javascript" src="{$cmsurl}images/common/jquery-1.10.2.min.js"></script>
            <script type="text/javascript" src="{$cmsurl}include/js/common.js"></script>
            <script type="text/javascript" src="{$cmsurl}include/js/admina.js"></script>
            <script type="text/javascript" src="{$cmsurl}include/js/floatwin.js"></script>
            <script type="text/javascript" src="{$cmsurl}include/js/_08cms.js"></script>
            <script type="text/javascript" src="{$cmsurl}include/js/setlist.js"></script>
            <script type="text/javascript">
                var getParentObject = function() {
                    // 后台浮动窗
                    var parentDocument = parent.document.getElementById('{$wid}');
                    // 后台主框架
                    if (!parentDocument)
                    {
                        parentDocument = parent.document.getElementById('main');
                    }
                    if (parentDocument)
                    {
                        var parentObject = parentDocument.contentWindow._08_ueditor_{$varname};
                    }
                    else// 会员中心
                    {
                    	var parentObject = document._08_ueditor_{$varname};
                    }
                    
                    if (!parentObject)
                    {
                        parentObject = parent._08_ueditor_{$varname};
                    }
                    
                    return parentObject;
                };
            </script>
            </head>
            <body>
EOT;
    }

    /**
     * 设置文件不缓存
     *
     * @static
     * @since 1.0
     */
    private static function _setNoCache()
    {
        _08_Http_Request::clearCache();
    }

    /**
     * 文本信息显示样式
     *
     * 设置返回给CK的JS函数代码，函数名称必须命名为：getReturn
     *
     * @since 1.0
     */
    protected function SetReturnFunctionStrInfo()
    {
        $this->_get_return_function_str = <<< EOT
            function getReturn(obj)
            {
                var parentObject = getParentObject();
                var _html = template('house-info-template',select_data);                             
                parentObject.execCommand('inserthtml', _html,true); 
                winclose();
            }
EOT;
    }

    /**
     * 是否显示文档图片
     *
     * @param  string $file_url 要判断的图片地址
     *
     * @return bool   显示返回TRUE，否则返回FALSE
     *
     * @static
     * @since  1.0
     */
    public static function isShowImg($file_url)
    {
        if (empty($file_url))
            return false;
        if (false !== stripos($file_url, ':') || cls_url::is_remote_atm($file_url))
            return true;
        return is_file(M_ROOT . $file_url) ? true : false;
    }
    
    /**
     * 图片显示模式
     *
     * 设置返回给CK的JS函数代码，函数名称必须命名为：getReturn
     *
     * @since 1.0
     */
    protected function SetReturnFunctionStrTu()
    {
        $this->_get_return_function_str = <<< EOT
            function getReturn(obj)
            {
                var parentObject = getParentObject();
                var contents = parentObject.getContent();
                var rule = new RegExp(obj.src);
                var rule2 = new RegExp('^<p.*' + obj.src + '.*</p>$');
                if (rule.test(contents))
                {
                    obj.style.border = 'none';
                    obj.style.height = '120px';
                    parentObject.setContent(contents.replace(rule2, ''));
                }
                else
                {
                    obj.style.border = '1px red solid';
                    obj.style.height = '118px';
                	parentObject.execCommand('inserthtml', '<p style="text-align:center;"><img src="' + obj.src + '" title="' + obj.title + '" /></p>');
                }
                
            }
EOT;
    }

    /**
     * 该接口默认显示图片的样式
     *
     * @param  array  $row        从数据库查询出来的数据行
     * @param  string $field_name 因本系统有的地方调用的字段是tupian，有的是thumb，所以调用权交给调用者
     *
     * @return string $str        返回样式字符串
     *
     * @since  1.0
     */
    protected function showImgStyle($row, $field_name = 'tupian')
    {
        global $cms_abs;
        cls_ArcMain::Url($row);
        $tupian = explode('#', $row[$field_name]);
        $is_afile = self::isShowImg($tupian[0]);
        $str = '<div style="float:left; margin:15px 0px 0px 27px; text-align:center;">
                    <a href="javascript:void(0);" title="' . $row['subject'] . '">
                        <img alt="' . $row['subject'] . '" src="' . ($is_afile ? cls_url::tag2atm($row[$field_name]) :
                        $cms_abs . 'images/common/nopic.gif') . '" width="160" height="120" ' .
                        ($is_afile ? 'onclick = "return getReturn(this);"' : 'onclick = "alert(\'暂无图片可插入\');"') . '/>
                    </a><br />
                    <p style="width:160px; height:50px; line-height:25px; overflow:hidden;">
                        <a href="' . $row['arcurl'] . '" target="_blank" title="' . $row['subject'] . '">' .
                        $row['subject'] . '</a>
                    </p>
                </div>';
        return $str;
    }

    /**
     * 包：CkPublicClass 的析构方法
     *
     * 只要是继承于该基类下的子类，在最后都会调用该方法
     *
     * @since 1.0
     */
    public function __destruct()
    {
        echo <<< EOT
            <script type="text/javascript">
            {$this->_get_return_function_str}
                function winclose()
                {
                    floatwin('close_{$this->_handlekey}',-1);
                }
            </script>
            </body></html>
EOT;
    }
}
