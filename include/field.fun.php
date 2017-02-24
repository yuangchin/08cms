<?php
!defined('M_COM') && exit('No Permission');
@include_once _08_EXTEND_LIBS_PATH.'functions'.DS.'custom.fun.php';
function specialarr($cfg = array()){
//guide 提示信息 // frmcell表单元素 //addcheck 诸如标题检测 //type类型//varname 表单单元id
//($varname,$value = '',$type = 'htmltext',$mode=0,$min=0,$max=0,$validator='',$guide='')
//cacc ($varname,$value='',$coid=0,$source=0,$ids='',$vmode=0,$smode=0,$validator='',$guide='')
	global $cms_abs,$subject_table,$init_map, $ck_plugins_enable,$cms_top;
	foreach(array('validator','value','guide','code',) as $v) $$v = '';
	foreach(array('mode','min','max','coid','source','vmode','smode','wmid', 'filter', 'editor_height', 'rpid', 'auto_compression_width','regular') as $v)
    {
        $$v = 0;
    }      
    $auto_page_size = 5;
	extract($cfg, EXTR_OVERWRITE);
	$ret = array('type' => $type,'varname' => $varname,'guide' => $guide,'addcheck' => '','frmcell' => '','mode');
	$ret['mode'] = $mode ? $mode : 0;
	$ret['more'] = isset($more) ? $more : array();
	$ret['view'] = isset($view) ? $view : 'S';
	if($type == 'htmltext'){
	    if ( empty($editor_height) )
        {
             $editor_height = 500;
        }
        
        if ( $mode && !defined('M_ADMIN') )
        {
            $toolbars_name = ($mode == 1 ? 'toolbar_simple' : 'toolbar_basic');
            $_08_Ueditor = _08_factory::getInstance('_08_Ueditor');
            $toolbars = $_08_Ueditor->getToolbars($toolbars_name);
            $toolbars .= 'enableContextMenu: false,';
        }
        else
        {
        	$toolbars = '';
        }
        
        if ( empty($max) )
        {
            $max_string = '';
        }
        else
        {
            $max_string = "maximumWords: '$max',";
        }
        $_varname = preg_replace('/[^\w]/', '', $varname);
        $_object_varname = "_08_ueditor_{$_varname}";
        $value = addcslashes(str_replace(array("\n", "\r"), '', cls_url::tag2atm($value, 1)), "'");
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
        $ueditor_appkey = cls_env::GetG('ueditor_appkey');
        
        
        if (preg_match('/\[#(.*)#\]/isU', $value))
        {           
        	$page_size_string = '<input name="sptype" value="field_default" type="radio" />按字段默认配置(' . $auto_page_size . ' KB)';
            $page_size_string .= '&nbsp;&nbsp;<input name="sptype" value="hand" checked="1" type="radio"> 手动';
        }
        else
        {
        	$page_size_string = '<input name="sptype" checked="1" value="field_default" type="radio" />按字段默认配置(' . $auto_page_size . ' KB)';
            $page_size_string .= '&nbsp;&nbsp;<input name="sptype" value="hand" type="radio"> 手动'; 
        }
        
		$ret['frmcell'] = _08_HTML::getEditorPlugins($ck_plugins_enable, $_varname) . (defined('M_ADMIN') ? " 分页方式： {$page_size_string} <input name=\"sptype\" value=\"auto\" type=\"radio\"> 自动　大小：<input name=\"spsize\" id=\"spsize\" value=\"0\" size=\"6\" type=\"text\"> K (分页符： <font color=\"#ff0000\">[#分页标题#] </font>)<br/>" : strpos($ck_plugins_enable,'paging_management') ? '(分页符： <font color=\"#ff0000\">[#分页标题#] </font>)' : '');
		empty($wmid) || $ret['frmcell'] .= '<span style="margin: 5px;"><input type="checkbox" name="wmid" id="wmid_' . $varname . '"'.  (empty($wmid) ? '' : ' checked="checked"') .' onclick="cancelwmid(this);" /><label for="wmid_' . $varname . '">水印</label> <span style="color:red;">(提示：每次上传前可选择是否使用水印)</span></span>';
		$ret['frmcell'] .= <<<HTML
        <script id="{$_varname}" type="text/plain" style="width:100%;height:{$editor_height}px;" name="{$varname}"></script>
        <script type='text/javascript'>		
			function cancelwmid(t){
			if(t.checked){
				{$_object_varname}.options.serverUrl = CMS_URL + uri2MVC("upload=ueditor&wmid={$wmid}&rpid={$rpid}&auto_compression_width={$auto_compression_width}");
			}else{
				{$_object_varname}.options.serverUrl = CMS_URL + uri2MVC("upload=ueditor&wmid=0&rpid={$rpid}&auto_compression_width={$auto_compression_width}");
			}	
			}
            onbeforeunloadStatus = true;
            var {$_varname}Function = function() {
                {$_object_varname} = UE.getEditor('{$_varname}', {
                    {$toolbars}
                    {$max_string}
                    webAppKey: '$ueditor_appkey',
                    serverUrl: CMS_URL + uri2MVC("upload=ueditor&wmid={$wmid}&rpid={$rpid}&auto_compression_width={$auto_compression_width}"),
                    textarea:'$varname',
                    //pageBreakTag:'[#分页标题#]',//不启用编辑器的分页样式
					customDomain:'$cms_top'||document.domain,
                    charset:"$mcharset"
                });
                {$_object_varname}.ready(function() {
                    //设置编辑器的内容
                    {$_object_varname}.setContent('$value');
                });
				if('{$regular}'== '1'){
					{$_object_varname}.addListener('blur',function(){
						checkbadwords({$_object_varname}.getContentTxt()); //getContent,getContentTxt 
					});
				}
            }            
            window.onbeforeunload = function(){
                return '页面离开后填写的信息有可能会丢失。';
            }
            
            var _forms = document.getElementsByTagName('form');
            for(var i = 0; i < _forms.length; ++i)
            {
                _forms[i].onsubmit = function() {
                    window.onbeforeunload = function(){};
                }
            }
            
            if (/IE\s+[6|7|8]/i.test(navigator.userAgent))
            {
                window.onload = function() {
                    {$_varname}Function();
                }
            }
            else
            {
            	{$_varname}Function();
            }
        </script>
HTML;
     /*   $ret['frmcell'] .= "<!--<textarea  id=\"$varname\" name=\"$varname\" style=\"width:100%;height:" . ($mode ? 200 : 500) . "px\" wmid=\"$wmid\"$validator>" . htmlspecialchars(cls_url::tag2atm($value, 1)) . '</textarea>'.
		"<script type=\"text/javascript\">CKEDITOR.replace('$varname',{" . ($mode ? "toolbar : 'simple'" : 'height : ' . $editor_height) . ", wmid: {$cfg['wmid']}});</script>--><div id=\"imgbox_".$varname.'" style="display:none;"></div><input type="hidden" id="_08_upload_'.$varname.'" value="">';*/
	}elseif(in_array($type,array('file','flash','media','image', 'images','files','medias','flashs'))){		
		$ret['frmcell'] = _08_M_Upload_View::showButton($cfg);
	}elseif($type == 'text'){
		if($subject_table && ($varname == 'subject' || strpos($varname,'[subject]'))){
			empty($validator) || $validator .= ' offset="'.(defined('M_ADMIN')?5:1).'" class="ruler" ';
			$ret['addcheck'] = "<input type=\"button\" value=\"检查重名\" onclick=\"checksubject(this,'$subject_table','$varname');\">";
			defined('M_ADMIN') && $ret['addcheck'] .= "<input type=\"hidden\" id=\"color\" name=\"color\"><img id=\"setcolor\" width=\"15\" height=\"16\" style=\"vertical-align:middle; margin-left:5px; cursor:pointer;\" onclick=\"ShowColor(event);\" src=\"{$cms_abs}images/admina/colour.png\"> 还可以输入<font color=\"red\" id=\"inputnum\">$cfg[max]</font>字符";
		}
                if($subject_table && ($varname == 'extcode' || strpos($varname,'[extcode]'))){
			empty($validator) || $validator .= ' offset="'.(defined('M_ADMIN')?5:1).'" class="ruler" ';
			$ret['addcheck'] = "<input type=\"button\" value=\"检查分机号\" onclick=\"checkwebcallext(this,'$varname');\">";
		}
		if((strpos($varname,'[source]') || strpos($varname,'[author]')) && defined('M_ADMIN')){
			empty($validator) || $validator .= ' offset="1" ';
			$mytype = strpos($varname,'[source]')?'Source':'Author';
			$ret['addcheck'] .= "<input type=\"button\" id=\"sel$mytype\" onclick=\"Select$mytype(event);\" value=\"选择\">";
		}
		if(strpos($varname,'[keywords]') && defined('M_ADMIN')){
			empty($validator) || $validator .= ' offset="1" ';
			$ret['addcheck'] .= "<input type=\"button\" id=\"selkeywords\" onclick=\"SelectKeywords(event)\" value=\"浏览\">";
		}
		$ret['frmcell'] = "<input ".((defined('M_ADMIN') && strpos($varname,'[subject]'))?'onkeyup="strlen_verify(this,'.(!empty($cfg['max']) ? $cfg['max'] : "''").')"':'')." type=\"text\" size=\"".($mode ? 60 : 20)."\" id=\"$varname\" name=\"$varname\" value=\"$value\"$validator>";
	}elseif($type == 'multitext'){
		$ret['frmcell'] = "<textarea rows=\"".($mode ? 10 : 4)."\" id=\"$varname\" name=\"$varname\" cols=\"".($mode ? 90 : 50)."\"$validator>$value</textarea>";
        if($regular == '1'){  
            $jqcode = "$(\"[id='$varname']\").change(function(){ checkbadwords($(this).val()); });";
            $ret['frmcell'] .= "<script type='text/javascript'>$(document).ready(function(){ $jqcode; });</script>"; 
        }
	}elseif($type == 'select'){
		$ret['frmcell'] = $mode ? $value : "<select name=\"$varname\"$validator>$value</select>";
	}elseif($type == 'mselect'){
		if($validator){ //字段多项选择的[必选认证],没有带过来,这里修正一下
        	if(strpos($value,"multiple=\"multiple\"")>0){ 
				$value = str_replace(array("multiple=\"multiple\""),array("multiple=\"multiple\" $validator"),$value);
			}else{
				$validator = "\n<input type='hidden' name='cbs_{$varname}_vals' id='cbs_{$varname}_vals' value='' $validator>";
				$validator .= "\n<script type='text/javascript'>resetCheckbox('{$varname}', 'init');</script>";
				$value .= $validator;
			}
		}
		$ret['frmcell'] = $value;
	}elseif($type == 'date'){
		$ret['frmcell'] = "<input type=\"text\" id=\"$varname\" name=\"$varname\" value=\"$value\" onfocus=\"DateControl({format:$mode})\" class=\"Wdate\" style=\"width:" . ($mode ? 152 : 92) . "px\" $validator>";
	}elseif($type == 'map'){	
        $ret['frmcell'] = "<input class=\"btnmap\" type=\"button\" onmouseover=\"this.onfocus()\" onfocus=\"_08cms.map.setButton(this,'marker','$varname','','$min','$max');\" /> <label class=\"maplab\" for=\"$varname\">纬度,经度：</label><input class=\"maptxt\" type=\"text\" id=\"$varname\" name=\"$varname\" value=\"$value\">\n";
	}elseif($type == 'vote'){
		$value = $value ? unserialize($value) : array();
		$length = count($value);
		$ret['frmcell'] = '';
		foreach($value as $k => $v){
			$ret['frmcell'] .= "<div id=\"{$varname}[$k]\"><span vote=\"subject\">$v[subject]</span>&nbsp;"
				."[<a href=\"javascript://\" onclick=\"_08cms.vote.editVote(this,'$varname','$max','$mode',$k)\">编辑</a>]&nbsp;"
				."[<a href=\"javascript://\" onclick=\"_08cms.vote.delVote(this,'$varname',$k)\">删除</a>]</div>\n";
			foreach($v as $x => $z){
				if(is_array($z)){
					foreach($z as $a => $b){
						if(is_array($b)){
							foreach($b as $c => $e) $ret['frmcell'] .= "<input type=\"hidden\" name=\"{$varname}[$k][$x][$a][$c]\" value=\"$e\">\n";
						}else $ret['frmcell'] .= "<input type=\"hidden\" name=\"{$varname}[$k][$x][$a]\" value=\"$b\">\n";
					}
				}else $ret['frmcell'] .= "<input type=\"hidden\" name=\"{$varname}[$k][$x]\" value=\"$z\">\n";
			}
		}
		$ret['frmcell'] .= "<a href=\"javascript://\" onclick=\"_08cms.vote.addVote(this,'$varname','$max','$mode')\">[".'添加投票'."]</a>\n";
	}elseif($type == 'texts'){
		$value = $value ? unserialize($value) : array();
		empty($max) && $max = 0;
		preg_match_all('/(?:^|\n)([^|]*)/', $mode, $mode);
		$mode = implode('|', $mode[1]);
		$ret['frmcell'] = "<script type=\"text/javascript\">_08cms.fields.texts('$varname', '$mode', " . jsonEncode($value) . ", $max)</script><input type=\"hidden\"$validator />\n";
	}elseif($type == 'cacc'){
		$tpid = empty($tpid) ? 0 : cls_string::ParamFormat($tpid);
		$vmode < 3 && $arr = cacc_arr($ftype,$tpid,$ename);
		$validator && $validator = " rule=\"must\"$validator";
		// for 按字母排序 add Letter
		!isset($arr) && $arr = array();
		if($arr){
			foreach($arr as $k=>$v){
				if($v['level']==0 && $v['letter']){ 
					$arr[$k]['title'] = $v['letter'].' '.$v['title']; //,"$v[title]"
				}
			}
		}
		if(!$vmode && !$smode){
			$arr = array(0 =>array('title' => '请选择','level' => 0)) + $arr;
			foreach($arr as $k => $v) $arr[$k]['title'] = str_repeat('&nbsp; &nbsp; ',$v['level']).$v['title'];
			$ret['frmcell'] = "<select name=\"$varname\" id=\"$varname\"$validator>".umakeoption($arr,$value).'</select>';
		}elseif($vmode <= 2){
			$str = ''; cls_catalog::uccidstop($arr); //print_r($arr);
			foreach($arr as $k => $v){ 	
				$str .= (empty($str) ? '' : ',')."[$k,$v[pid],'".addslashes($v['title'])."',".(empty($v['unsel']) ? 0 : 1) . ']'; 
			}
			$validator && $validator = "<input type=\"hidden\" vid=\"$varname\"$validator />";
			$ret['frmcell'] = "<script>var data = [$str];\n_08cms.fields.linkage('$varname', data, '$value',$smode);</script>$validator";
		}else{
			$validator && $validator = "<input type=\"hidden\" vid=\"$varname\"$validator />";
			$mcharset = cls_env::getBaseIncConfigs('mcharset');
			$ret['frmcell'] = "<div><script>_08cms.fields.linkage('$varname','ajax=cacc&type=$ftype&tpid=$tpid&ename=$ename&charset=$mcharset','$value',$smode);</script></div>$validator";
		}
	}
	return $ret;
}

function multi_val_arr($val,&$cfg){
	if($cfg['datatype'] == 'map'){
		if(!$val) return array(0,0);
		$re = explode(',',$val);
		foreach(array(0,1) as $var) $re[$var] = empty($re[$var]) ? 0 : floatval($re[$var]);
		return $re;
	}
	return false;
}

function cacc_arr($type = 'a',$tpid = 0,$ename = ''){
	if(!$type || !$ename || !($field = cls_cache::Read(($type == 'a' ? '' : $type).'field',empty($tpid) ? 0 : cls_string::ParamFormat($tpid),$ename))) return array();
	return cls_field::options($field);
}
function select_arr($innertext='',$fromcode=0){
	$field = array('datatype' => 'select','innertext' => $innertext,'fromcode' => $fromcode,);
	return cls_field::options($field);
}

function upload_s($newvalue,$oldvalue = '',$mode = 'image',$rpid=0,$wmid=0){
	global $db,$tblprefix;
	$c_upload = cls_upload::OneInstance();
	if(!$newvalue) return '';
	$oldvalue = str_replace('|','#',$oldvalue);
	$newvalue = str_replace('|','#',$newvalue);
	$oldarr = explode('#',$oldvalue);
	$newarr = explode('#',$newvalue);
	if(!$newarr[0]) return '';
	if($newarr[0] == $oldarr[0])return $oldvalue;
	if(cls_url::islocal($newarr[0],1)){
		$filename = basename($newarr[0]);
		$newvalue = cls_url::save_atmurl($newarr[0]);
		if($ufid = $db->result_one("SELECT ufid FROM {$tblprefix}userfiles WHERE filename='$filename' AND aid='0'")) $c_upload->ufids[] = $ufid;
	}else{
		$atm = $c_upload->remote_upload($newarr[0],$rpid,$wmid);
		$newvalue = $atm['remote'];
		if(($mode == 'image') && !empty($atm['width']) && !empty($atm['height'])){
			$newvalue .= '#'.$atm['width'].'#'.$atm['height'];
			$sized = 1;
		}
	}
	if($mode == 'image'){
		if(empty($sized) && cls_url::islocal($newarr[0])){
			$info = @getimagesize(cls_url::local_atm($newarr[0]));
			$newvalue .= '#'.(empty($info[0]) ? '' : $info[0]).'#'.(empty($info[1]) ? '' : $info[1]);
		}
	}else $newvalue .= !empty($newarr[1]) ? '#'.intval($newarr[1]) : '';
	unset($newarr,$atm,$info);
	return $newvalue;
}
function upload_m($newvalue,$oldvalue = '',$mode = 'image',$rpid=0,$wmid=0){
	global $db,$tblprefix;
	if(!$newvalue) return '';
	
	$c_upload = cls_upload::OneInstance();
	$oldvalue = !$oldvalue ?  array() : unserialize($oldvalue);
	$oldarr = array();
	foreach($oldvalue as $k => $v) $oldarr[$v['remote']] = $v;
	
	$temps = array_filter(explode("\n",$newvalue));
	if(!$temps) return '';
	$newarr = array();
	foreach($temps as $v){
		$v = str_replace(array("\n","\r"),'',$v);
		$row = explode('|',$v);
		$row[0] = trim($row[0]);
		if(!$row[0]) continue;
		$filename = basename($row[0]);
		$atm = array();
		if(array_key_exists($row[0],$oldarr)){//旧数据
			$atm = $oldarr[$row[0]];
		}else{
			if(cls_url::islocal($row[0],1)){//新的本地文件将附件id得到以便获取与文档的关联
				$atm['remote'] = cls_url::save_atmurl($row[0]);
				if($info = $db->fetch_one("SELECT ufid,size FROM {$tblprefix}userfiles WHERE filename='$filename' AND aid='0'")){
					$c_upload->ufids[] = $info['ufid'];
					$atm['size'] = $info['size'];
				}
			}else $atm = $c_upload->remote_upload($row[0],$rpid,$wmid);
		}
		$atm['title'] = empty($row[1]) ? '' : strip_tags($row[1]);

        if(in_array($mode,array('image'))){            
            $atm['link'] = empty($row[2]) ? '' : strip_tags($row[2]); 
        }elseif(in_array($mode,array('flash','media'))){
            if(!empty($row[2])) $atm['player'] = intval($row[2]);
        }

		if($mode == 'image' && empty($atm['width']) && $info = @getimagesize(cls_url::local_atm($row[0]))){//某些情况下的图片尺寸补全
			$atm['width'] = $info[0];
			$atm['height'] = $info[1];
		}
		$atm && $newarr[] = $atm;
	}
	unset($temps,$row,$atm,$info,$oldvalue,$oldarr);
	return $newarr;
}
function rm_filesize($url){
	$url = parse_url($url);
	if($fp = fsockopen($url['host'],empty($url['port']) ? 80 : $url['port'],$error)){
		fputs($fp,"GET ".(empty($url['path']) ? '/' : $url['path'])." HTTP/1.1\r\n");
		fputs($fp,"Host:$url[host]\r\n\r\n");
		while(!feof($fp)){
			$tmp = fgets($fp);
			if(trim($tmp) == ''){
				break;
			}elseif(preg_match('/Content-Length:(.*)/si',$tmp,$arr)){
				return trim($arr[1]);
			}
		}
	}
	return 0;
}

function atm_size($value,$datatype,$mode=0){//使用没有经过addslashes的值,以k为单位
	if(empty($value)) return 0;
	$size = 0;
	if(in_array($datatype,array('image','flash','media','file'))){
		$temps = explode('#',$value);
		if($url = cls_url::tag2atm($temps[0])) $size = cls_url::islocal($url) ? filesize(cls_url::local_file($url)) : rm_filesize($url);
	}elseif(in_array($datatype,array('images','flashs','medias','files'))){
		if($temps = @unserialize($value)){
			foreach($temps as $v){
				if($url = cls_url::tag2atm($v['remote'])){
					$size += isset($v['size']) ? $v['size'] : (cls_url::islocal($url) ? filesize(cls_url::local_file($url)) : rm_filesize($url));
					if($mode) break;
				}
			}
		}
	}
	unset($temps,$url);
	return intval($size / 1024);
}

function atm_byte($value, $datatype){
	return cls_string::WordCount($datatype == 'htmltext' ? cls_string::HtmlClear($value) : $value);
}