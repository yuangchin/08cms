<?php

/**
 * 插入楼盘信息操作类
 *
 * @author Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
class CkHouseInfo extends CkPublicClass {
    /**
     * 楼盘信息所属模型ID
     *
     * @var   const
     * @since 1.0
     */
    const CHID = 4;
    /**
     * 当前类对象句柄
     *
     * @var    object
     * @static
     * @since  1.0
     */
    private static $_instance = null;

    /**
     * 当前文档类对象句柄
     *
     * @var    object
     * @static
     * @since  1.0
     */
    private static $_arc_instance = null;

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
     * 获取文档类句柄
     *
     * @return object $_arc_instance 返回文档类句柄
     *
     * @since  1.0
     */
    private static function getArcInstance() {
        if(null == self::$_arc_instance) {
            self::$_arc_instance = new cls_arcedit;
        }
        return self::$_arc_instance;
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
        if(!submitcheck('next')) {
            global $arc;
            $arc = self::getArcInstance();
            $arc->archive['ccid1'] = $this->_ccid1;
            $arc->archive['ccid2'] = $this->_ccid2;
            ob_start();
            relCcids(1,2,1,1,'fmdata',@$arc->archive['ccid1'],@$arc->archive['ccid2']);
            $content = ob_get_contents();
            ob_end_clean();
            tabheader(
                $content . ' 楼盘名称 <input type="text" id="subject" name="subject" value="' . strip_tags(stripcslashes(trim($this->_subject))) . '" class="text" /> ' . strbutton('search','查询') . '
             <!--   <input type="submit" name="add" value="加入选择" class="btn" onclick="return addSelect(this.form);" />
                <input type="submit" name="insert" value="下一步" class="btn" /> --> ',
                'fangyuan_list',
                _08_Http_Request::uri2MVC("editor=house_info&action=search{$this->_uri}")
            );
        }
		
        if(submitcheck('search') && $this->_where) {
            $this->showSearchStyle();
        }elseif(submitcheck('insert')){
			$this->showNextStyle();
		}else{
            // 初始化选择项
            cls_message::show('请先选择查询项目！');
        }
    }
	
	 /**
     * 显示下一步样式
     *
     * @since 1.0
     */
    public function showNextStyle() {
        global $selectid, $insert_flag;
        tabheader(
            '样式预览',
            'hangqing_list',
            _08_Http_Request::uri2MVC("editor=house_info&action=search{$this->_uri}")
        );
        echo '<tr><td align="center">';		
        $r = $this->setStep2Info($selectid, $insert_flag);
        if ( !$insert_flag )
        {    
			
		     echo '<input type="button" value="查看" onclick="createStyle(this.form)" />';
		/*
            foreach($r as $v)
            {
                foreach($v as $v2)
                {
                    if(!in_array($v2['ename'], array('subject', 'qtbz', 'xqjs', 'dt', 'xqhs', 'keywords', 'description', 'thumb')))
                    {
                        echo '<div style="width:130px; +width:135px; float:left; text-align:right; overflow:hidden; height:20px"><label for="' . $v2['ename'] . '">' . $v2['cname'] . '</label> <input type="checkbox" value="' .$v2['ename']. '" name="ename[]" id="' .$v2['ename']. '" class="checkbox"  onclick="createStyle(this.form)" /></div>';
                    }
                }
                break;
            }
		 */
        }
		
        $url = _08_Http_Request::uri2MVC("editor=house_info&action=init{$this->_uri}");
        echo <<<HTML
        </td></tr>
        <tr><td style="padding:20px"><div id="show_style" style="text-align:left;">选择属性可预览效果</div></td></tr>
        <tr><td style="padding-bottom:50px;"><input type="button" value="重新选择" onclick="history.go(-1);" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="保存返回" onclick="getReturn()"/></td></tr>
HTML;
    }



    /**
     * 设置下一步参数
     *
     * @param  array $checkeds 返回的项目
     * @return array $r 返回参数数组
     */
    private function setStep2Info($checkeds, $insert_flag = 0) {
        global $db, $tblprefix, $cms_abs, $mcharset,$ftp_enabled;
		$tmpfile = M_ROOT . 'static/ueditor1_4_3/plugintpl/house_info.tpl';
        $r = $row2 = array();
        $callbackURL = M_REFERER;
        if(empty($checkeds) || !is_array($checkeds)) {
            echo '<script type="text/javascript"> alert("请先选择产品！"); history.go(-1); </script>';
            exit;
        }
        #$query = $db->query("SELECT * FROM {$tblprefix}" . atbl(self::CHID) . " WHERE aid IN (" . implode(',', array_map('intval' , $checkeds)) . ")");
        $db->select()->from('#__' . atbl(self::CHID))->where('aid')->_in(array_map('intval' , $checkeds))->exec();
        $fields = cls_cache::Read('fields', self::CHID);
		$rows = array('list'=>array());
        while($row = $db->fetch()) {
            cls_ArcMain::Url($row);			
			if(isset($row['thumb'])){
			 	$row['thumb'] = (empty($ftp_enabled)?$cms_abs:$ftp_url).$row['thumb'];
			}
			cls_ArcMain::Parse($row);
            #$row['cotime'] = ($row['cotime'] ? date('Y-m-d', $row['cotime']) : '');
            $q = $db->query("SELECT * FROM {$tblprefix}archives_{$row['chid']} WHERE aid = {$row['aid']}");
            $row2 = $db->fetch_array($q);
			//相册
			$xcquery = $db->query("SELECT * FROM {$tblprefix}archives12 WHERE pid3 = {$row['aid']}");
			$xc = array();
			while($xrow = $db->fetch_array($xcquery)){
				$xc[] = (empty($ftp_enabled)?$cms_abs:$ftp_url).$xrow['thumb'];;
			}
			$row['xc'] = $xc;
            $rows['list'][] = array_merge((array) $row, (array) $row2);
            $r[] = $fields;
        }
        $rows = @cls_string::iconv($mcharset, 'UTF-8', $rows);
		echo '<script type="text/javascript" src="' . $cms_abs . 'static/js/artTemplate.js' . '"></script>';
		echo '<script id="house-info-template" type="text/html"> ' . (file_exists($tmpfile) ? file_get_contents($tmpfile) : '') .' </script>';
        echo '
            <script type="text/javascript">
                var insert_flag = "'. (bool)$insert_flag. '";
                var select_data = ' . (!empty($rows) ? json_encode($rows) : '""') . ';
                var fields = ' . json_encode($fields) . ';';
        echo <<<EOT
                function createStyle(forms) {
					/*
                    var html = '', viewWidth = 600;
                    if ( document.getElementById('style_width').value != null )
                    {
                        viewWidth = document.getElementById('style_width').value;
                    }
					
                    var width = parseInt(viewWidth);
					*/	
																						
                    var html = template('house-info-template',select_data);
					if(window.console)console.log(select_data);
                    document.getElementById('show_style').innerHTML = html;
                    return false;
                }

                function makeData(datas, val, aid) {
                    var return_value = '', vals;
                    switch(fields[val].datatype) {
                        case 'date' :
                        case 'text' :
						case 'htmltext' :
						case 'multitext':
                        case 'float' : return_value += datas[val]; break;
                        case 'select' : return_value += fields[val].innertext && innerTextToArray(fields[val].innertext)[datas[val]]; break;
                        case 'mselect' :
                            for(var d in datas[val].split('	')) {
								var _arr_val = datas[val].split('	')[d];
                                vals = fields[val].innertext && innerTextToArray(fields[val].innertext)[_arr_val];
                                if(d && vals != undefined) return_value += vals + '&nbsp;&nbsp;';
                            }
                        break;
						case 'image':
							 return_value += '<img src="' + CMS_ABS +  datas[val] + '">'; break;
                        // 该功能暂时不起用，因内容页会把代码转换成文本了。
                        case 'map' :
                            return_value += '<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.2"><\/script><script type="text/javascript" src="' + CMS_ABS + 'template/red/map/near.js"><\/script><div id="map_canvas"></div>';
                            return_value += '<script type="text/javascript">';
                            return_value += "_08cms.map.create('map_canvas','" + aid + "', '" + select_datas[val] + "',15);";
                            return_value += '<\/script>';
                        break;
                    }
                    return (return_value == 'undefined' ? '-' : return_value);
                }

                /**
                 * 把字段类型的文本值转成数组
                 */
                function innerTextToArray(texts) {
                    var arr = Array(), parts2;
                    var parts = texts.split('\\n');
                    for(var i = 0; i < parts.length; ++i) {
                        parts2 = parts[i].split('=');
                        arr[parts2[0]] = parts2[1];
                    }
                    return arr;
                }
              </script>
EOT;
        $this->SetReturnFunctionStrInfo();
        if ( (bool) $insert_flag )
        {
            echo <<<JAVASCRIPT
            <script type="text/javascript">
                window.onload = function() {
                    createStyle(document.forms[0]);
                    setTimeout(getReturn, 10);
                }                
            </script>
JAVASCRIPT;
        }
        return $r;
    }

    /**
     * 显示搜索样式
     *
     * @since 1.0
     */
    public function showSearchStyle() {
        global $db, $tblprefix, $aid, $fcdisabled2;
        $sql_str = " FROM {$tblprefix}" . atbl(self::CHID) . " AS a WHERE a.checked = 1 " . $this->_where;
        $query = $db->query("SELECT a.subject, a.ccid1, a.ccid2, a.aid, a.chid, a.caid, a.initdate, a.customurl, a.nowurl {$sql_str} ORDER BY a.aid DESC");
        $max_num = $db->result_one("SELECT COUNT(*) $sql_str");
        if(empty($max_num)) cls_message::show('找不到相关信息！');
        // 输出已经加入的项
        if(isset($_COOKIE['ids']) && isset($_COOKIE['arcstr'])) {
            echo '<tr><td id="show_select" colspan="4" style="color:red" align="left">';
            $ids = explode(',', $_COOKIE['ids']);
            $arcstr = explode(',', cls_string::iconv('GBK', 'UTF-8', urldecode($_COOKIE['arcstr'])));
            $len = count($ids);
            for($i = 1; $i < $len; ++$i) {
                if($arcstr[$i]) {
                    $ids[$i] = (int)$ids[$i];
                    echo '&nbsp;&nbsp;<input type="checkbox" value="' . $ids[$i] . '" name="checkeds[]" checked="checked" onclick="closed(this);" id="checkeds'.$ids[$i].
                         '" title="'.$arcstr[$i].'"/><label for="checkeds'.$ids[$i].'" title="点击关闭选择">' . $arcstr[$i] . '</label>';
                }
            }
            echo '</td></tr>';
        }
        $ccid1_arr = cls_cache::Read('coclasses', 1);
        foreach($ccid1_arr as $k=>$v){
        	if($v['coid'] == 1){
        		$ccid1arr[$k] = $v['title'];
        	}else{
        		unset($ccid1_arr[$k]);
        	}
        }
        $ccid2_arr = cls_cache::Read('coclasses', 2);
        foreach($ccid2_arr as $k=>$v){
        	if($v['coid'] == 2){
        		$ccid2arr[$k] = $v['title'];
        	}else{
        		unset($ccid2_arr[$k]);
        	}
        }
        if (empty($fcdisabled2))
        {
            trcategory(array('序号',array('名称','txtL'),array('所属区域/商圈','txtC'), array("全选<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">|txtL")));
        }
        else
        {
        	trcategory(array('序号',array('名称','txtL'), array("全选<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid', 'chkall')\">|txtL")));
        }
        
        while($row = $db->fetch_array($query)) {
            cls_ArcMain::Url($row);
            echo "<tr class=\"txt\"><td class=\"txtC w100\">{$row['aid']}</td>\n";
            echo "<td class=\"txtL\"><a href=\"{$row['arcurl']}\" target=\"_blank\" id=\"arc{$row['aid']}\">{$row['subject']}</a></td>\n";
            if (empty($fcdisabled2))
            {
                echo "<td class=\"txtC\">" . (@$ccid1arr[$row['ccid1']] ? @$ccid1arr[$row['ccid1']] : '-') . '/' . (@$ccid2arr[$row['ccid2']] ? @$ccid2arr[$row['ccid2']] : '-') . "</td>\n";
            }
            echo "<td class=\"txtC w50\">";
        	echo "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[]\" value=\"{$row['aid']}\">";//配置
        	echo "</tr>\n";
        }
        echo <<<HTML
            <tr><td colspan="4">                       
            <input type="submit" name="insert" value="下一步" class="btn" />
            </td></tr></table>
HTML;
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

CkHouseInfo::Setup('插入楼盘信息');

