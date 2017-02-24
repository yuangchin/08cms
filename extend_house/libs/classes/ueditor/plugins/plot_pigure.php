<?php
/**
 * CK插件：插入小区图片操作类
 *
 * 只要是CK插件，必须继承基类：CkPublicClass，并且要在构造方法或是该类开始调用前调用
 *
 * @author Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
class CkPlotPigure extends CkPublicClass {
    /**
     * 当前类对象句柄
     *
     * @var object
     * @static
     * @since 1.0
     */
    private static $_instance = null;

    /**
     * 图片所属分类ID
     *
     * @var   const
     * @since 1.0
     */
    const CAID = 7;

    /**
     * 图片所属模型ID
     *
     * @var   const
     * @since 1.0
     */
    const CHID = 7;

    /**
     * 包：CkPublicClass 的子类构造函数
     *
     * 如果子类要使用构造方法初始化，那该构造方法必须要调用基类的构造方法
     * 具体基类构造方法请查看文件：ck_public_class.php
     *
     * @since 1.0
     */
    public function __construct($title = '') {
        // 设置插件弹出窗口标题
        $this->_title = $title;
        parent::__construct();
    }

    /**
     * 插件入口
     *
     * 该插件使用了COOKIE方式获取值，所以在调用页面必须设置两个COOKIE
     * 房源小区ID：fyid、小区楼盘名称：lpmc
     *
     * @since 1.0
     */
    public function init() {
        global $m_cookie, $db, $tblprefix, $mcharset;
        $count = 0;
        $handlekey = (int)@$this->_get['parent_wid'];
        $fyid = empty($m_cookie['fyid' . $handlekey]) ? 0 : (int)$m_cookie['fyid' . $handlekey];
        $lpmc = empty($m_cookie['lpmc' . $handlekey]) ? '' : urldecode(trim($m_cookie['lpmc' . $handlekey]));
        if ( false === stripos($mcharset, 'UTF') )
        {
            $lpmc = cls_string::iconv('UTF-8', $mcharset, $lpmc);
        }
        tabheader($lpmc . ' >>> 小区图片', 'tu_list', _08_Http_Request::uri2MVC("editor=plot_pigure&action=search{$this->_uri}"));
        if(empty($fyid) || empty($lpmc)) {
            cls_message::show('请指定房源所属的小区！');
        }
        $query = $db->query("SELECT a.aid, a.subject, a.thumb, a.caid, a.chid,  a.chid, a.initdate, a.customurl, a.nowurl, a.jumpurl FROM {$tblprefix}".atbl(self::CHID)." a WHERE a.pid3={$fyid} AND a.chid = " . self::CHID);
        $str = '<tr><td align="center"><div style="width:100%;">';
        while($row = $db->fetch_array($query)) {
            $count = 1;
            $str .= $this->showImgStyle($row, 'thumb');
        }
        $str.= '</div></td></tr>';
        if($count == 0) cls_message::show('该小区无图片！');
        $str .= <<<HTML
            <tr><td height="50" align="center"><input type="button" value="完成插入" onclick="winclose();" /></td></tr>
HTML;
        echo $str;
        tabfooter();
        $this->SetReturnFunctionStrTu();
    }

    /**
     * 安装该插件功能
     *
     * @param string $title 浮动窗标题
     *
     * @static
     * @since 1.0
     */
    public static function Setup($title = '') {
        if(null == self::$_instance) {
            self::$_instance = new self($title);
        }
        self::$_instance->init();
    }
}

CkPlotPigure::Setup('插入小区图片 >> (提示：直接点击图片即可插入)');