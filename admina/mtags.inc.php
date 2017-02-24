<?PHP
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);
if(isset($ttype) && !in_array($ttype,array('ctag','rtag')) && ($action != 'mtaginsert')) {
    cls_message::show('参数错误');
}
if(!empty($mtagnew)) cls_Array::array_stripslashes($mtagnew);//不存数据库，将转义取消
include_once dirname(__FILE__) . DS . 'mtags/_taginit.php';
if($action != 'mtaginsert') {
    $mtags = load_mtags($ttype);
}
empty($sclass) && $sclass = 0;
$new_action = (isset($textid) ? "&src_type=mtagcode&infloat=1&textid=$textid" : '');
empty($floatwin_id) || $new_action .= '&floatwin_id=' . $floatwin_id;
empty($fn) || $new_action .= '&fn=' . $fn;
empty($caretpos) || $new_action .= '&caretpos=' . $caretpos;
if($action == 'mtagadd'){
	$tclass = empty($tclass) ? (empty($mtagnew['tclass']) ? '' : $mtagnew['tclass']) : $tclass;
	if(!submitcheck('bmtagadd') && !submitcheck('bmtagcode')){
		if(!$tclass && $ttype != 'rtag') cls_message::show('请指定标识类型');
		if($helpstr = _tag_helpstr($ttype,$tclass)) $helpstr = '&nbsp; &nbsp;>>'.$helpstr;
		$_submiturl = "?entry=$entry&action=$action&sclass=$sclass&ttype=$ttype".($tclass ? "&tclass=$tclass" : '').$new_action;
		tabheader('添加 - '.($ttype == 'rtag' ? '区块标识' : $tclassarr[$tclass]).$helpstr,'mtagsadd',$_submiturl,2,1,1);
    	$mtag =& cls_mtagsHeader::showTagTitle(@$mtag, @$mtagnew);
		if($ttype != 'rtag') trhidden('mtagnew[tclass]',$tclass);
        
		list($modeAdd,$modeSave) = array(1,0);
        isset($tclass) && _08_FilesystemFile::filterFileParam($tclass);
		include(dirname(__FILE__) . DS . "mtags/".($tclass ? $tclass : 'rtag').".php");
		if(empty($_nobutton)){
			if(!empty($templatebase)){
				echo "<input type='button' value='当前处于扩展模版,不能添加' style='color:#999'>";
			}else{
				echo strbutton('bmtagadd','添加');
			}
			if($ttype != 'rtag') echo "&nbsp; &nbsp; <input class=\"button\" type=\"submit\" name=\"bmtagcode\" value=\"生成代码\" onclick=\"this.form.target='mtagcodeiframe'; _08_resubmit = 1; \">";
			echo "</form><br>";
			echo "<iframe id=\"mtagcodeiframe\" name=\"mtagcodeiframe\" frameborder=\"0\" width=\"100%\"  height=\"200\" style=\"display:none\"></iframe>";
		}
		
		a_guide($ttype.(empty($mtagnew['tclass']) ? 'edit' : $mtagnew['tclass']));
	}elseif(submitcheck('bmtagcode')){
		$TagCodeIsAdd = 1;
		include(dirname(__FILE__) . DS . "mtags/_tagcode.php");
	}else{
		$TagCodeIsAdd = 1;
		include(dirname(__FILE__) . DS . "mtags/_tagsave.php");

        // 插入成功后回调父窗口函数
        if(!empty($textid)) {
            $floatwin_id = isset($floatwin_id) ? (int)$floatwin_id : 0;
            $caretpos = isset($caretpos) ? (int)$caretpos : 0;
            if($floatwin_id === 0) {
                $floatwin_id = 'main';
            } else {
                $floatwin_id = "_08winid_{$floatwin_id}";
            }
            if($ttype == 'rtag') {
                $new_tags = '{tpl$' . $mtagnew['ename'] . '}';
            } else {
                $new_tags = str_replace(
                    array("\r\n", "\n", "\r"),
                    array('[!!!]', '[!-!]', '[!!-]'),
                    _tag2code($mtag, true)
#                    addslashes(_tag2code($mtag, true))
                );
            }
            cls_phpToJavascript::insertParentWindowString($floatwin_id, $textid, $new_tags, $caretpos);

            cls_message::show('标识插入完成',axaction(2,"?entry=$entry&action=mtagsedit&sclass=$sclass&ttype=$ttype&tclass=$tclass"));
        }

		cls_message::show('标识添加完成',axaction(6,"?entry=$entry&action=mtagsedit&sclass=$sclass&ttype=$ttype&tclass=$tclass"));
	}
}elseif($action == 'update'){
	$mtags = load_mtags($ttype,1);
	cls_message::show('标识列表重建完成',axaction(6,"?entry=$entry&action=mtagsedit&sclass=$sclass&ttype=$ttype&tclass=$tclass"));
}elseif($action == 'mtagcode'){
	empty($mtags[$tname]) && cls_message::show('请指定正确的标识');
	$mtag = cls_cache::Read($ttype,$tname,'');
	$tclass = empty($mtag['tclass']) ? '' : $mtag['tclass'];
	$mtagcode = mtag_code($ttype,$mtag);  
	tabheader($tclassarr[$mtag['tclass']].'&nbsp; -&nbsp; '.$mtag['cname']);
	_view_tagcode($mtagcode,_tag_helpstr($ttype,$tclass,'标识帮助'),0);
	tabfooter();
}elseif($action == 'mtagsedit'){
	$keyword = empty($keyword) ? '' : trim($keyword);
	$tclass = empty($tclass) ? '' : trim($tclass);
	if(!submitcheck('bmtagsedit')){
		$rsubmiturl = "?entry=mtags&action=mtagsedit&ttype=$ttype".($tclass ? "&tclass=$tclass" : '');
		if($ttype != 'rtag'){
			$ftclassarr = array($tclass ? ">><a href=\"?entry=mtags&action=mtagsedit&sclass=$sclass&ttype=$ttype\">全部分类</a>" : "<b>-全部分类-</b>");
			foreach($tclassarr as $k => $v) $ftclassarr[] = $tclass == $k ? "<b>-$v-</b>" : "<a href=\"?entry=mtags&action=mtagsedit&ttype=$ttype&tclass=$k\">$v</a>";
			echo tab_list($ftclassarr,9,0);
		}
		$str = ''; 
		$stradd = "&nbsp; >><a href=\"?entry=mtags&action=mtagadd&sclass=$sclass&ttype=$ttype&mtagnew[tclass]=$tclass\" onclick=\"return floatwin('open_mtagsedit',this)\">添加</a>";
		if(empty($templatebase)){ //使用原始模版
			if($tclass || $ttype == 'rtag') $str .= "$stradd";
		}else{
			if($tclass && $ttype == 'ctag') $str .= "$stradd"; //复合标识:显示添加入口,但不保存
		}
		if($helpstr = _tag_helpstr($ttype,$tclass)) $str .= '&nbsp;>>'.$helpstr;
		$str .= "&nbsp; >><a href=\"?entry=mtags&action=update&ttype=$ttype&tclass=$tclass\" onclick=\"return floatwin('open_mtagsedit',this)\">重建列表</a>";
		$str .= "&nbsp; <input class=\"text\" name=\"keyword\" type=\"text\" value=\"$keyword\" style=\"vertical-align: middle;width:180px\">&nbsp; <input class=\"btn\" type=\"submit\" name=\"bfilter\" id=\"bfilter\" value=\"筛选\">";
		if(in_array($ttype,array('rtag')) && $tplbase = cls_env::GetG('templatebase')){ $tips = "<li>※提示：当前处于扩展模版,继承自基础模版[$tplbase]；基础模版下的区块不能删除,可从基础模版[扩展]到当前模板。</li>"; echo "<div style='color:red'>$tips</div>"; }
		tabheader($lang[$ttype].'管理'.$str,'mtagsedit',$rsubmiturl,'9');
		$_copy = empty($templatebase) ? '复制' : '扩展';
		trcategory(array('序号',array('标识标题','txtL'),array('标识样式','txtL'),array('类型','txtL'),'排序','关闭',array('<input class="checkbox" type="checkbox" name="chkall" onclick="deltip(this,0,checkall,this.form,\'delete\')">删?','txtL'),'编辑',$_copy,'代码'));
		$page = empty($page) ? 1 : max(1, intval($page));
		$p = 0;
		$atpp = 50;#每页50个
		$keys = $instances = $sclasses = array();
		$counts = 0;
		foreach($mtags as $key => $mtag)
			if((!$tclass || $tclass == $mtag['tclass']) && (!$keyword || in_str($keyword,($ttype == 'rtag' ? '{tpl$' : '{c$').$mtag['ename'].'}') || in_str($keyword,$mtag['cname']))){
				$counts++;
				$keys[] = $key;
				if(!isset($mtag['tclass']) || empty($mtag['ename'])){
					echo "\n<br>[$key:".@$mtag['cname']."] 出错,请修复或删除相关文件!\n";
					continue;
				}
                $tclasses[] = $mtag['tclass'];
                
                # 获取当前子项的sclass
                $mtag_ = cls_cache::Read($ttype,$mtag['ename'],'');
                # 该处只为兼容之前的封装标识，如果封装标识缓存文件不存在则不处理
				$sclassflag = 0; //标记
                if(isset($mtag_['tclass'])) 
                {
                    //只有这几类标签才找getSclass; 否则文件多了，is_callable找类找方法找不到会很慢
					if(in_array($mtag['tclass'],array('archive','archives','catalogs','commus','farchives','members','pushs',))){
						if( empty($instances[$mtag_['tclass']]) || !is_object($instances[$mtag_['tclass']]) )
						{
							$instances[$mtag_['tclass']] = _08_factory::getMtagsInstance($mtag_['tclass']);
						}
						if( is_callable(array($instances[$mtag_['tclass']], 'getSclass')) )
						{
							$sclasses[] = $instances[$mtag_['tclass']]->getSclass($mtag_['setting']);
							$sclassflag = 1;
						}
					}
                }
				if(empty($sclassflag)) $sclasses[] = ''; //保证与tclasses的对应有下标
			}
		$page = min($page, max(1, ceil($counts / $atpp)));
		$i = ($page - 1) * $atpp;
		$end = $i + $atpp;
		$end > $counts && $end = $counts;
		for(; $i < $end; ++$i){
		    $index = $i + 1;
			$key = $keys[$i];
			$mtag = $mtags[$key];
			$mtagcodestr = $ttype == 'rtag' ? '-' : "<a href=\"?entry=mtags&action=mtagcode&sclass=".@$sclasses[$i]."&ttype=$ttype&tname=$key&tclass={$tclasses[$i]}\" onclick=\"return floatwin('open_mtagsedit',this)\">代码</a>";
			$typestr = @$tclassarr[$mtag['tclass']];
			echo "<tr class=\"txt\">" .
					"<td class=\"txtC w30\">$index</td>\n" .
					"<td class=\"txtL\"><input type=\"text\" size=\"30\" maxlength=\"30\" name=\"mtagsnew[$key][cname]\" value=\"" . mhtmlspecialchars($mtag['cname']) . "\"></td>\n" .
					"<td class=\"txtL\">" . tag_style($key) . "</td>\n" .
					"<td class=\"txtL\">$typestr</td>\n" .
					"<td class=\"txtC w50\"><input type=\"text\" size=\"4\" name=\"mtagsnew[$key][vieworder]\" value=\"$mtag[vieworder]\"></td>\n" .
					"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"mtagsnew[$key][disabled]\" value=\"1\"" . (empty($mtag['disabled']) ? '' : ' checked') . "></td>\n";
			if(empty($templatebase) || in_array($ttype,array('ctag'))) { //复合标识或[基础模版下]的区块标识
				echo "<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$key]\" value=\"$key\" onclick=\"deltip()\"></td>\n" .
					"<td class=\"txtC w30\"><a href=\"?entry=mtags&action=mtagsdetail&ttype=$ttype&sclass=" . @$sclasses[$i] . "&tname=$key&tclass=" . @$sclasses[$i] . "\" onclick=\"return floatwin('open_mtagsedit',this)\">详情</a></td>\n" .
					"<td class=\"txtC w30\"><a href=\"?entry=mtags&action=mtagsdetail&ttype=$ttype&sclass=" . @$sclasses[$i] . "&tname=$key&iscopy=1&tclass=" . @$sclasses[$i] . "\" onclick=\"return floatwin('open_mtagsedit',this)\">复制</a></td>\n";
			}elseif(!empty($templatebase)&&!file_tplexists($key,0)){ //[扩展模版下]的无扩展模板
				echo "<td class=\"txtC w40 tips1\">--</td>\n" .
					"<td class=\"txtC w30\"><a href=\"?entry=mtags&action=mtagsdetail&ttype=$ttype&sclass=" . @$sclasses[$i] . "&tname=$key&tclass=" . @$sclasses[$i] . "&isbase=1\" onclick=\"return floatwin('open_mtagsedit',this)\">详情</a></td>\n" .
					"<td class=\"txtC w30\"><a href=\"?entry=mtags&action=mtagscopy&ttype=rtag&tname=$key\" onclick=\"return floatwin('open_mtagsedit',this)\">扩展</a></td>\n";
			}elseif(!empty($templatebase)&&file_tplexists($key,0)){ //[扩展模版下]的有扩展模板
				echo "<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$key]\" value=\"$key\" onclick=\"deltip()\"></td>\n" .
					"<td class=\"txtC w30\"><a href=\"?entry=mtags&action=mtagsdetail&ttype=$ttype&sclass=" . @$sclasses[$i] . "&tname=$key&tclass=" . @$sclasses[$i] . "\" onclick=\"return floatwin('open_mtagsedit',this)\">详情</a></td>\n" .
					"<td class=\"txtC w30 tips1\">扩展</td>\n";
			}
			echo "<td class=\"txtC w30\">$mtagcodestr</td>\n</tr>\n";
		}
		tabfooter('bmtagsedit','修改');
		echo multi($counts, $atpp, $page, $rsubmiturl . ($keyword ? '&keyword=' . urlencode($keyword) : ''));
		a_guide($ttype.'edit');
	}else{
		if(!empty($delete)){
			foreach($delete as $k){
				if(!empty($templatebase)){
					$rtag = cls_cache::Read('rtag',$k,'');
					$tname = cls_tpl::rel_path($rtag['template'],'dir');
					file_exists($tname) && unlink($tname);
				}else{
					cls_CacheFile::Del($ttype,$k,'');
					unset($mtagsnew[$k],$mtags[$k]);
				}
			}
		}
		if(!empty($mtagsnew)){
			cls_Array::array_stripslashes(@$mtagsnew);
			foreach($mtagsnew as $k => $v){
				$mtag = cls_cache::Read($ttype,$k,'');
				$v['cname'] = empty($v['cname']) ? $mtags[$k]['cname'] : $v['cname'];
				$mtag['cname'] = $v['cname'];
				$mtag['vieworder'] = max(0,intval($v['vieworder']));
				if(empty($mtag['vieworder'])) unset($mtag['vieworder']);
				if(empty($v['disabled'])){
					unset($mtag['disabled']);
				}else $mtag['disabled'] = 1;
				cls_CacheFile::Save($mtag,cls_cache::CacheKey($ttype,$k),$ttype);
				mtags_update($mtags,$mtag);
			}
		}
		mtags_cache($mtags,$ttype);
		adminlog('编辑'.$lang[$ttype].'管理列表');
		cls_message::show('标识修改完成',M_REFERER);
	}
}elseif($action == 'mtagsdetail' && $tname){
    if(!empty($fn) && $ttype != 'rtag') {
        $mtag = read_select_file($fn);
        // 转换非封装标识的字符编码
        if(@$mtag['tag_type'] != 'encapsulated') $mtag = cls_string::iconv('UTF-8', $mcharset, $mtag);
    } else if(!($mtag = cls_cache::Read($ttype,$tname,''))) cls_message::show('请指定正确的标识');    

	$tclass = empty($mtag['tclass']) ? '' : $mtag['tclass'];
	$iscopy = empty($iscopy) ? 0 : 1;
	$iscopystr = $iscopy ? '&iscopy=1' : '';
	$isbasestr = empty($isbase) ? '' : '&isbase=1';
	if(!submitcheck('bmtagsdetail') && !submitcheck('bmtagcode')){
		if($helpstr = _tag_helpstr($ttype,$tclass)) $helpstr = '&nbsp; &nbsp;>>'.$helpstr;
		$disabledstr = empty($mtag['disabled']) ? '' : ' <本标识关闭中>';
		$_submiturl = "?entry=$entry&action=$action&ttype=$ttype&handlekey=$handlekey&sclass=$sclass&tname=$tname$iscopystr$isbasestr".$new_action;
        if(empty($mtag['tclass']) && !empty($textid)) cls_message::show('标识不完整!');
		tabheader('设置 - '.($ttype == 'rtag' ? '区块标识' : $tclassarr[@$mtag['tclass']]).$disabledstr.$helpstr,'mtagsdetail',$_submiturl,2,1,1);
    	$mtag =& cls_mtagsHeader::showTagTitle(@$mtag, @$mtagnew);

        if($ttype != 'rtag') trhidden('mtagnew[tclass]',$tclass);
        trhidden('iscopy', empty($iscopy) ? 0 : 1);
		list($modeAdd,$modeSave) = array(0,0);
        isset($tclass) && _08_FilesystemFile::filterFileParam($tclass);
		include(dirname(__FILE__) . DS . "mtags/".($tclass ? $tclass : 'rtag').".php");
		if(empty($_nobutton)){
			if(cls_env::GetG('textid')=='restore'){
				echo "<input type='button' value='当前正在用标签还原,不能保存' style='color:#999'>";
			}else{
				echo "<input class=\"button\" type=\"submit\" name=\"bmtagsdetail\" value=\"".($iscopy ? '复制' : '提交')."\">";
			}
			echo ($ttype != 'rtag' ? "&nbsp; &nbsp; &nbsp; &nbsp; <input class=\"button\" type=\"submit\" name=\"bmtagcode\" value=\"生成代码\" onclick=\"this.form.target='mtagcodeiframe'; _08_resubmit = 1;\">" : '').
			"</form><br><iframe id=\"mtagcodeiframe\" name=\"mtagcodeiframe\" frameborder=\"0\" width=\"100%\"  height=\"200\" style=\"display:none\"></iframe>";
		}
		a_guide($ttype.(empty($mtag['tclass']) ? 'edit' : $mtag['tclass']));
	}elseif(submitcheck('bmtagcode')){# 生成代码
		$TagCodeIsAdd = 0;
		include(dirname(__FILE__) . DS . "mtags/_tagcode.php");
	}else{
		$TagCodeIsAdd = 0;
		include(dirname(__FILE__) . DS . "mtags/_tagsave.php");

        // 修改成功后回调父窗口函数
        if(!empty($textid)) {
            // 读取选中文本
            $createrange = cls_cache::cacRead($fn, _08_TEMP_TAG_CACHE);
            // 只对非封装标识更新到原窗口
            if($createrange['tag_type'] == 'non-encapsulated') {
                $createrange['old_str'] = json_encode(stripslashes($createrange['old_str']));
                $floatwin_id = isset($floatwin_id) ? (int)$floatwin_id : 0;
                if($floatwin_id === 0) {
                    $floatwin_id = 'main';
                } else {
                    $floatwin_id = "_08winid_{$floatwin_id}";
                }
                $caretpos = array_pop(explode('_', trim($fn)));
                $new_tags = str_replace(
                    array("\r\n", "\n", "\r"),
                    array('[!!!]', '[!-!]', '[!!-]'),
                   # addslashes(_tag2code($mtag, true))
                   _tag2code($mtag, true)
                );
                
                #exit($new_tags);
                echo <<<EOT
                <script type="text/javascript">
                    var obj = window.parent.document.getElementById("$floatwin_id").contentWindow;
                    // 更新选中标签信息为新标签设置信息
                    obj.updateTagStr("{$textid}", {$createrange['old_str']}, '{$new_tags}', $caretpos);
                </script>
EOT;
                cls_message::show('标识'.($iscopy ? '复制' : '修改').'完成',axaction(2,"?entry=mtags&sclass=$sclass&action=mtagsedit&ttype=$ttype&handlekey=$handlekey"));
            } else {
                unset($createrange);
            }
        }
        cls_message::show('标识'.($iscopy ? '复制' : '修改').'完成',axaction(6,"?entry=mtags&sclass=$sclass&action=mtagsedit&ttype=$ttype&handlekey=$handlekey"));
	}
} else if($action == 'mtaginsert') { // 插入新标识
    tabheader(
        '插入新标识 >> 请先选择要插入的标识类型 <input name="insert_block_id" value="插入区块标识" type="submit" class="btn" style="margin-left:50px;" />',
        'taginsert',"?entry=mtags&action=mtagadd&ttype=rtag&types=mtaginsert" . $new_action
    );
    $ftclassarr = array();
    foreach($tclassarr as $k => $v) $ftclassarr[] = @$tclass == $k ? "<b>-$v-</b>" : "<a href=\"?entry=mtags&action=mtagadd&ttype=ctag&mtagnew[tclass]=$k&sclass=$sclass&types=mtaginsert$new_action\">$v</a>";
    echo '<div class="ctag">复合标识类别</div>';
	echo tab_list($ftclassarr,9,0);
    tabfooter();
    echo '</form>';
    a_guide('mtaginsert');
    echo <<<EOT
        <style type="text/css">
        <!--
            #taginsert td { height:28px; line-height:28px; }
            .ctag {
                position:absolute;
                /* 左上角
                margin: 38px 0 0 10px;
                +margin: -2px 0 0 10px;
                */
                margin: 184px 0 0 685px;
                +margin: 143px 0 0 685px;
                display:block;
                background-color:#fff;
                color:#134D9D;
                padding:0 3px;
            }
        -->
        </style>
        <script type="text/javascript">
            document.getElementsByTagName('table')[1].style.cssText = 'margin-top:15px; +margin-top:5px; border:1px #134D9D solid;';
        </script>
EOT;
}elseif($action == 'mtagscopy' && $tname){
    $rtag = cls_cache::Read('rtag',$tname,'');
    $msg = rtag_basic2extend($rtag['template']) ? '模板扩展完成' : '基础模板原文件不存在';
    cls_message::show($msg,axaction(6,"?entry=$entry&action=mtagsedit&ttype=rtag"));
}