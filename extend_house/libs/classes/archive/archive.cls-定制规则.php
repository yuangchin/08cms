<?php
class cls_archive extends cls_archivebase{

	//单图设置为多图
	function fm_phpSetImgtype(&$fields){ 
	
		$fyimg_count = cls_env::mconfig('fyimg_count');
		$fyimg_count = empty($fyimg_count) ? 20 : $fyimg_count;
	
		$fythumb = $fields['thumb'];
		$fythumb['cname'] = '房源图片';  
		$fythumb['ename'] = 'fythumb';   
		$fythumb['datatype'] = 'images'; 
		$fythumb['issearch'] = '1'; //图片属性2:0-关闭,1-开启
		$fythumb['imgComment'] = '占位'; //title_for_prop2
		$fythumb['min'] = '0';
		$fythumb['max'] = $fyimg_count;
		$fythumb['guide'] = '';
		$fields['fythumb'] = $fythumb;
	}
	
	//设置图片类别
	function fm_jsSetImgtype($filed='thumb'){ 
		$fcfg = cls_cache::Read('field',121,'lx');
		$farr = cls_field::options($fcfg); 
		$opts = str_replace(array("\r","\n",'"'),array('','',"'"),makeoption($farr)); //print_r($opts);
		$sels = "<select id='(kid)' onchange='fytu_setvalue(this)'><option value=''>-请选择-</option>$opts</select>";
		?>
		<style type="text/css">div.item_link{ display:none; }</style>
		<script type='text/javascript'>
		var s_tmp = "<?php echo $sels; ?>";
		function _$( domID ) {
			var _domObject = parent.document.getElementById(domID);
			if ( _domObject == null ){ _domObject = document.getElementById(domID); } 
			return _domObject;
		}
		function fytu_timerscan(){ 
			$("[id='imgbox_fmdata[<?php echo $filed; ?>]']").find('.progressWrapper').each(function(index, element) {
				var k0 = $(this).attr('id').toString().replace('SWFUpload_0_fmdata[<?php echo $filed; ?>]','');
				var kid = 'img_box_setopt_'+k0; 
				if(!_$(kid)){ 
					setTimeout("fytu_addselect('"+k0+"','"+kid+"')",300);
				}else{ 
					$(this).find('.item_box').show(); //有时候会不显示???
				} //console.log($('#'+kid));
			}); // clear ???
			fytu_setvalue();
			setTimeout('fytu_timerscan()',500);
		}
		function fytu_addselect(k0,kid){ 
			var img = _$('SWFUpload_0_fmdata[<?php echo $filed; ?>]'+k0+'_img'); 
			if(img && $(img).attr('src').indexOf('loading.gif')<=1){ //等待加载完才执行
				var kc = $(_$('SWFUpload_0_fmdata[<?php echo $filed; ?>]'+k0)).find('div:first');
				//$(kc).before(s_tmp.replace('(kid)',kid)); //.before, append
				//$(kc).html(s_tmp.replace('(kid)',kid) + $(kc).html());
				//$(kc).children('a').before(s_tmp.replace('(kid)',kid)); //ok
				//$(kc).children('span').after(s_tmp.replace('(kid)',kid));
				var _1stinput = $(kc).find('.item_input')[0]; 
				$(_1stinput).before(s_tmp.replace('(kid)',kid));
				$('div.progressWrapper').removeClass('h110'); //去掉高度限制,根据版面情况确定
				$('div.progressWrapper').removeClass('h130'); 
			}
		}
		//function fytu_delselect(e){ }
		function fytu_setvalue(e){ 
			var uploadID = '_08_upload_fmdata[<?php echo $filed; ?>]'; 
			var boxID = 'imgbox_fmdata[<?php echo $filed; ?>]'; 
			var currentBox, _value = '', _src, textareaValue,textareaLink;
			for(var i = 0; i < _$(boxID).childNodes.length; ++i){	
				currentBox = _$(boxID).childNodes[i].childNodes[0];
				if ( currentBox != undefined ){
					textareaValue = currentBox.lastChild.previousSibling.childNodes[1].value;
					textareaLink = currentBox.lastChild.childNodes[1].value = $(currentBox).find('select:first').val(); //currentBox.lastChild.childNodes[1].value;          
					if( _value ){ _value += '\n'; } //console.log(textareaLink);
					var itm_box = currentBox.getElementsByTagName('img')[0]; //用getElementsByTagName可避免中间添加元素后查找失败
					_src = itm_box.getAttribute('src') ? itm_box.getAttribute('src') : itm_box.getAttribute('value');
					_sValue = (textareaValue) ? ('|' + textareaValue) : ''; // && textareaValue != originalValue
					_sLink = (textareaLink) ? ('|' + textareaLink) : ''; // && textareaLink != swfu.customSettings.imgsCom
					_sValue = (!_sValue && _sLink) ? '|' : _sValue; //如果_sValue为空,_sLink不为空,则还是要个|分开
					_value += _src + _sValue + _sLink;
				}
			}
			$('div.item_link').hide(); 
			_$(uploadID).value = decodeURIComponent(_value);
		} 
		fytu_timerscan(); $(document).ready(function(){  }); 
		</script> 
		<?php
	}
	

		
	// 会员中心,添加特价房,选所属楼盘
	function fm_mylpSelect(){
		$db = _08_factory::getDBO();
		$curuser = cls_UserMain::CurUser();
		$mid = $curuser->info['mid']; 
		// ids
   		$row = $db->select('loupan')->from('#__members_13')
        ->where("mid = $mid")->exec()->fetch(); 
		$row = empty($row) ? '' : $row['loupan'];
    	$a = explode(',', $row);
		$s = '0';
		foreach($a as $k){
			$k = intval($k);
			if($k) $s .= ",$k";
		}
		// options
		$db->select('aid,subject')->from("#__archives15")
		->where("aid IN ($s)")
		->limit(100)->exec(); 
		$re = '<option value="">-请选择楼盘-</option>';
		while($row=$db->fetch()){
			$re .= "\n<option value='$row[aid]'>".$row['subject']."</option>";	
		}
		trbasic('<span style="color:red">*</span> 所属楼盘','',makeselect("{$this->fmdata}[pid]",$re,'rule="must"'),'');
	}
	
	/**
	* 关联合辑 扩展
	* 楼盘关联 视频,开发商等
	*
	* @param    int    $pid    合辑项目id
	* @param    int    $chid   来源模型id
	* @param    string $title  合辑项目名
	*/
	function fm_relalbum($pid='0',$chid=0, $title='关联合辑'){
		global $db,$tblprefix;
		$rid = isset($this->predata["pid$pid"]) ? $this->predata["pid$pid"] : 0;
		$subject = $rid ? $db->result_one("SELECT subject FROM {$tblprefix}".atbl($chid)." WHERE aid='$rid'") : '';
		$hidpid = "<input type=\"hidden\" id=\"pid{$pid}\" name=\"pid{$pid}\" value=\"$rid\"/>";
		$relbtn = "<input type=\"button\" value=\"关联$title\" onclick=\"return floatwin('open_arcexit_{$pid}','?entry=extend&extend=rel_lp1&chid=$chid')\">";
		$clrbtn = "<input type=\"button\" value=\"清除关联\" onclick=\"document.getElementById('pid{$pid}').value='';document.getElementById('pid{$pid}text').innerHTML = '';\">";
        $hidname = '';
        $pid == 6 && $hidname = "<input id='".$this->fmdata."[kfsname]' name='".$this->fmdata."[kfsname]' type='hidden' value='".$subject."'>";
		trbasic($title,'',"$hidname<span id=\"pid{$pid}text\">$subject</span> $hidpid $relbtn $clrbtn",'');
	}
	
	// 检测楼盘是否重复: (js)
	// 0: 楼盘表
	// 5: 楼盘表+临时小区表
	// 115-写字楼楼盘, 116-商铺楼盘, 
	function fm_lpExist($leixing=5){
		$leixing = empty($leixing) ? 0 : $leixing;
		echo '<script type="text/javascript">';
		echo 'window._08cms_validator && _08cms_validator.init("ajax","fmdata[subject]",{url:CMS_ABS+"'._08_Http_Request::uri2MVC("ajax=lpexist&leixing=$leixing&lpname=%1").'"})';
		echo '</script>';
	}
	
	// 租赁方式组合: (2合一组合) (array('fkfs','zlfs')); // 租赁方式,付款方式
	function fm_czumode($cfields=array()){
		$cfields = empty($cfields) ? array('zlfs','fkfs') : $cfields;
		$a_field = new cls_field; $str = ''; $pfix = $this->fmdata;
		foreach($cfields as $f){
			if(isset($this->fields[$f])){
				$this->fields[$f]['mode'] = '0';
				$cfg = $this->fields[$f];
				$a_field->init($cfg,isset($this->predata[$f]) ? $this->predata[$f] : @$cfg['vdefault']);
				$varr = $a_field->varr($this->fmdata);
				$_opt0 = "<option value='0'>-".$this->fields[$f]['cname']."-</option>";
				$varr['frmcell'] = str_replace("<select name=\"{$pfix}[$f]\">","<select name=\"{$pfix}[$f]\">$_opt0",$varr['frmcell']);
				$str .= $varr['frmcell'].'&nbsp; ';
				$this->fields_did[] = $f;
			}
		}
		unset($a_field);
		$str && trbasic('租赁方式','',$str,''); 
	}
	
	// 楼层/楼型组合: (3合一组合) ?楼型
	function fm_clouceng(){
		if(isset($this->fields['szlc']) && isset($this->fields['zlc'])){
			$str = "第<input type=\"text\" value='".@$this->predata['szlc']."' name=\"{$this->fmdata}[szlc]\" id=\"{$this->fmdata}[szlc]\" size= \"2\">层 ";
			$str .= " &nbsp; 共<input type=\"text\" value='".@$this->predata['zlc']."' name=\"{$this->fmdata}[zlc]\" id=\"{$this->fmdata}[zlc]\" size= \"2\">层 ";
			trbasic('楼层','',$str,'',array('guide'=>'请输入整数，-x表示地下第x层。'));
			$this->fields_did[] = 'szlc';
			$this->fields_did[] = 'zlc';
		}else{
			$cfields = array('szlc','zlc');
			$a_field = new cls_field; 
			foreach($cfields as $f){
			if(isset($this->fields[$f])){
				$cfg = $this->fields[$f]; 
				$a_field->init($cfg,isset($this->predata[$f]) ? $this->predata[$f] : @$cfg['vdefault']);
				$a_field->isadd = $this->isadd;
				$a_field->trfield($this->fmdata);
				$this->fields_did[] = $f;
			}	}
		}
	}

	// 房东信息: (3合一组合,发布类型设置,房东信息加guide,房东信息自动加载会员资料)
	function fm_cfanddong($fields=array()){
		$curuser = cls_UserMain::CurUser();
		if($this->mc&&in_array($this->chid,array(2,3,117,118,119,120))){
			trbasic('发布类型设置','',makeradio('sendtype',array('1'=>'发布到网站前台','0'=>'放入后台仓库'),1),'');
		}
		if(($curuser->info['mchid']!=2)&&$this->mc){ // 经纪人特有,非经纪人隐藏
			$this->fields_did[] = 'fdname';
			$this->fields_did[] = 'fdtel';
			$this->fields_did[] = 'fdnote';
			//return;
		}
		$cfields = empty($fields) ? array('lxdh','xingming') : $fields;
		$a_field = new cls_field; $str = '';
		foreach($cfields as $f){
			$cfg = $this->fields[$f]; 
			if($this->mc && $this->isadd){
				$cfg['guide'] = ' [<a id="user_info_link" href="?action=memberinfo" onclick="return showInfo(this.id,this.href)">完善资料</a>] 注:'.$cfg['cname'].' 资料自动加载会员资料。';
			}
			$a_field->init($cfg,isset($this->predata[$f]) ? $this->predata[$f] : '');
			$a_field->isadd = $this->isadd;
			$a_field->trfield($this->fmdata);
			$this->fields_did[] = $f;
		}
		$u_dianhua = @$curuser->info['lxdh']; //echo 'xx'; print_r($curuser);
		$u_xingming = @$curuser->info['xingming'];
		if(!$u_xingming) $u_xingming = $curuser->info['mname'];
		if($this->mc && $this->isadd){
			echo "<script type='text/javascript'>
				var xingming = \$id('fmdata[".$fields[1]."]'),lxdh=\$id('fmdata[".$fields[0]."]');
				if(xingming) xingming.value = '$u_xingming';
				if(lxdh) lxdh.value = '$u_dianhua';
			</script>";	
		}
		unset($a_field);
	}

	// 类型/属性组合: (n合一组合) fwjg-房屋结构,zxcd-装修程度,cx-朝向,fl-房龄
	function fm_ctypes($cfields=array()){
		$cfields = empty($cfields) ? array('fwjg','zxcd','cx','fl') : $cfields;
		$a_field = new cls_field; $str = ''; $pfix = $this->fmdata;
		foreach($cfields as $f){
			if(isset($this->fields[$f])){
				$this->fields[$f]['mode'] = '0';
				$cfg = $this->fields[$f];
				$a_field->init($cfg,isset($this->predata[$f]) ? $this->predata[$f] : @$cfg['vdefault']);
				$varr = $a_field->varr($this->fmdata);
				$_opt0 = "<option value='0'>-".$this->fields[$f]['cname']."-</option>";
				$varr['frmcell'] = str_replace("<select name=\"{$pfix}[$f]\">","<select name=\"{$pfix}[$f]\">$_opt0",$varr['frmcell']);
				if($f=='fl'){ // 处理添加时,房龄默认显示-不详, 如果是[0=不详]项，则去掉此项显示-房龄-
					$varr['frmcell'] = str_replace("<option value=\"0\" selected=\"selected\">不详</option>","",$varr['frmcell']);
					$this->isadd && $varr['frmcell'] = str_replace("selected=\"selected\"","",$varr['frmcell']);
				}
				$str .= $varr['frmcell'].'&nbsp; ';
				$this->fields_did[] = $f;
			}
		}
		unset($a_field);
		trbasic('类型/属性','',$str,''); 
	}

	// 户型组合: (5合一组合) $h!=0是剔除自动填充房源标题功能
	function fm_chuxing($cfields=array(),$h=0){
		$cfields = empty($cfields) ? array('shi','ting','wei','chu','yangtai') : $cfields;
		$a_field = new cls_field; $str = ''; $pfix = $this->fmdata;
		foreach($cfields as $f){
			if(isset($this->fields[$f])){
				$this->fields[$f]['mode'] = '0';
				$cfg = $this->fields[$f];
				$a_field->init($cfg,isset($this->predata[$f]) ? $this->predata[$f] : @$cfg['vdefault']);
				$varr = $a_field->varr($this->fmdata);
				if($h==0) $varr['frmcell'] = str_replace("<select ","<select id=\"{$pfix}[$f]\" onchange='auto_fillx()' ",$varr['frmcell']);
				$str .= $varr['frmcell'].'&nbsp; ';
				$this->fields_did[] = $f;
			}
		}
		unset($a_field);
		trbasic('户型','',$str,''); 
	}
	
	// 区域-商圈 (2合一组合,判断参数)
	function fm_rccid1(){
		$fcdisabled2 = cls_env::mconfig('fcdisabled2'); 
		if(!empty($fcdisabled2)){
			parent::fm_ccid(1);
		}else{
			relCcids(1,2,1,1,$this->fmdata,@$this->arc->archive['ccid1'],@$this->arc->archive['ccid2']);	
		}
		//$this->fm_resetCoids(array(1,2));
		resetCoids($this->coids, array(1,2));
	}
	// 地铁-站点 (2合一组合,判断参数)
	function fm_rccid3(){
		$mconfigs = cls_cache::Read('mconfigs');
		$fcdisabled3 = $mconfigs['fcdisabled3'];
		if(empty($fcdisabled3)) relCcids(3,14,2,0,$this->fmdata,@$this->arc->archive['ccid3'],@$this->arc->archive['ccid14']);
		//$this->fm_resetCoids(array(3,14));
		resetCoids($this->coids, array(3,14));
	}
	
	// 扩展的js,房屋配套-全选
	function fm_fyext($chid=3, $mc=1){
		$curuser = cls_UserMain::CurUser();
		echo "<script type='text/javascript'>
		var str = \"<br><input class='checkbox' type='checkbox' name='chkallfwpt' onclick=\\\"checkall(this.form, 'fmdata[fwpt]', 'chkallfwpt')\\\">全选\";
		var tmp_fwpts = document.getElementsByName('fmdata[fwpt][]')[1];
		if(tmp_fwpts){ //有些模型无此dom对象
			var tmp_td = tmp_fwpts.parentNode.parentNode.parentNode.getElementsByTagName('td')[0];
			tmp_td.innerHTML += str; //alert(tmp_tr);
		}
		var auto_fields = 'shi|ting|wei'.split('|');
		var auto_fnames = '室|厅|卫'.split('|');
		var isadd = '{$this->isadd}';

		//功能 :自动填充房源标题
		function auto_fillx(){
			var tmp0 = \$id('fmdata[lpmc]').value,tmpx='';
			for(i=0;i<auto_fields.length;i++){
				var fid = auto_fields[i];
				var elm = \$id('fmdata['+fid+']');  
				if(elm && elm.value!='100'){
					tmpx += elm.value + auto_fnames[i]; 
				}
			}
			tmp0 += ' ' + tmpx;
			elm = \$id('fmdata[mj]'); 
			if(elm && elm.value>'0'){
				tmp0 += ' ' + elm.value + '㎡';
			}
			var asubj = \$id('fmdata[subject]');
			if(asubj.value.length==0 || isadd=='1') asubj.value = tmp0; 
		} //  new Array(); 小区名称 3室2厅1卫 157㎡ [如果是添加或者为空则执行这个自动填写过程]
		</script>"; 
	}
	
	// 房源-小区名称,选择
	//$mc:1为会员中心，0为后台
	//$is_no_addxq; 1  为前台免注册发布房源
	function fm_clpmc($mc=1,$is_no_addxq=0){
		global $db, $tblprefix, $ckpre, $handlekey, $cms_top, $mcharset, $ckdomain, $ckpath;
		$pid3 = @$this->arc->archive['pid3']; //echo ",$pid3,"; //print_r($this->arc);
		$lpmc = @$this->arc->archive['lpmc'];
        
        if ( false === stripos($mcharset, 'UTF') )
        {
            $this->arc->archive['lpmc'] = cls_string::iconv($mcharset, 'UTF-8', @$this->arc->archive['lpmc']);
        }
        if ( !empty($ckdomain) )
        {
            # 去掉域里开头的点
            $cms_top = substr($ckdomain, 1);
        }
        # 打开窗口时重新初始化CK插件ID与名称，如果升级该脚本时请继承下去
        msetcookie('fyid' . $handlekey, @$this->arc->archive['pid3']);
        msetcookie('lpmc' . $handlekey, urlencode(@$this->arc->archive['lpmc']));
        
        
		$mc = $this->mc;
		$add = $this->isadd;
		trhidden($this->fmdata.'[pid3]',$pid3);	
		
		trbasic('<font color="red"> * </font>小区名称',$this->fmdata.'[lpmc]',$lpmc,'text',array('w'=>60,'validate'=>' rule="text" must="1" mode="" rev="小区名称"','guide'=>'可以输入小区名称或小区地址进行搜索'));
		//$mc_dir=MC_DIR;
		?>
		<script type="text/javascript">
		var fmdata = '<?php echo $this->fmdata; ?>';
		function createobj(element,type,value,id){
			var e = document.createElement(element);
			e.type = type;
			e.value = value;
			e.id = id;
			return e;
		}
		function set_select(obj,value,dochange){
			if(obj==null) return;
			for(var j=0;j<obj.options.length;j++){
				if(obj.options[j].value == value){
					obj.options[j].selected = true;	
					if(dochange && obj.onchange)obj.onchange();
				}	
			}
		}

        
        
		var lpmc = $id(fmdata+'[lpmc]');
		lpmc.setAttribute('autocomplete','off');
		var divout = document.createElement('DIV');
		var pid3 = document.getElementsByName('fmdata[pid3]')[0];
		with(divout.style){position = 	'relative';left = 0+'px';top = 0+'px';zIndex = 100;}
		var showdiv = "	<div style=\"border: 1px solid rgb(102, 102, 102); position: absolute; z-index: 1000; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\" id=\"SuggestionDiv\"></div><iframe frameborder=\"0\" style=\"border: 0px solid rgb(102, 102, 102); position: absolute; z-index: 100; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\"></iframe>";
		divout.innerHTML = showdiv;
		lpmc.parentNode.insertBefore(divout,lpmc.nextSibling);
		var divin = $id('SuggestionDiv');
		var aj=Ajax("HTML","loading");

        
		lpmc.onkeyup = function(){
            var ccid1 = $id('fmdata[ccid1]') ? $id('fmdata[ccid1]') : document.getElementsByName('fmdata[ccid1]')[0];
            var ccid2 = $id('fmdata[ccid2]');
            var ccid3 = $id('fmdata[ccid3]');
            var ccid14 = $id('fmdata[ccid14]');
            var address =$id('fmdata[address]');
            var dt = $id('fmdata[dt]');
            //当选了小区后，又删除小区名称，则之前自动赋值的那些选项，全部清空
            if(lpmc.value == ''){
                $id('fmdata[subject]').value = '';
                set_select(ccid1,0,1);
				set_select(ccid2,0,0);
				set_select(ccid3,0,1);
				set_select(ccid14,0,0);
                address.value = dt.value = '';
            }
            var urlpara = lpmc.value == ' ' ? '&keywords='+encodeURIComponent(lpmc.value) :'&keywords='+encodeURIComponent(lpmc.value.replace(/(^\s*)|(\s*$)/g,''));
			var urlfull = CMS_ABS + uri2MVC('ajax=ajaxloupan'+urlpara);	
			aj.post(urlfull,'',function(re){
				eval("var s = "+re+";"); 
				divin.style.display = '';
				divin.nextSibling.style.display = '';
				var str="<table width=\"480px\" cellspacing=\"0\" cellpadding=\"4\" border=\"0\" bgcolor=\"#ffffff\" class=\"search_select\" id=\"Suggestion\" style=\"top: -1px;\";><tbody><tr><td height=\"16\" align=\"center\" style=\"color: rgb(153, 153, 153); padding-left: 3px; background-repeat: repeat-x; background-position: center center;\" >请点击选择房源所在小区（没有合适小区请点击关闭）</td><td><a style=\"cursor:pointer;text-decoration:none; color:red;\" onclick=\"closediv()\">关闭</a></td></tr>"
				for(i=0;i<s.length;i++){
                  str+="<tr onclick=\"sendaid("+i+")\" style=\"cursor:pointer\"><td index=\"1\" style=\"padding: 5px; color: rgb(51, 51, 51);\" ><span style=\"color: rgb(0, " + (s[i].aid==0 ? '080':'101') + ", 181);width:150px; display:block; float:left;\">"+s[i].subject+ (s[i].aid==0 ? '(临时小区)':'') + "</span><span style=\"display:block; float:left;width:280px; white-space:nowrap;overflow:hidden;\">地址："+s[i].address+"</span></td></tr>";
				}
				<?php if(empty($is_no_addxq) && $mc){ ?>
				if(s.length <= 3){
					str += "<tr onclick=\"addlpinfo()\" style=\"cursor:pointer\"><td index=\"1\" style=\"padding: 5px; color: rgb(51, 51, 51);\" ><span style=\"color: rgb(0, 101, 181);width:150px; display:block; float:left;\">添加新小区信息</span></td></tr>";	
				}
				<?php } ?>
				str+="</tbody></table>";
				divin.innerHTML = str;
				
			function sendaid(i){
				pid3.value = s[i].aid;
				lpmc.value = s[i].subject;		
				set_select(ccid1,s[i].ccid1,1);
				set_select(ccid2,s[i].ccid2,0);
				set_select(ccid3,s[i].ccid3,1);
				set_select(ccid14,s[i].ccid14,0);
				var thumb = document.getElementsByName('fmdata[thumb]')[0];
				var dt = $id('fmdata[dt]');
				var address = $id('fmdata[address]');
				if(thumb)   thumb.value = s[i].thumb;
				if(dt)      dt.value =s[i].dt;
				if(address) address.value = s[i].address;
				setcookie('<?php echo $ckpre;?>fyid<?php echo $handlekey;?>', s[i].aid<?php echo (empty($cms_top) ? '' : ", null, '{$ckpath}', '.{$cms_top}'");?>);
				setcookie('<?php echo $ckpre;?>lpmc<?php echo $handlekey;?>', encodeURIComponent(s[i].subject)<?php echo (empty($cms_top) ? '' : ", null, '{$ckpath}', '.{$cms_top}'");?>);
				divin.style.display="none";
				divin.nextSibling.style.display = 'none';
				lpmc.onfocus(); //没有这句,如果为空状态下选一个小区,选取后认证提示不会消失
				auto_fillx(); 
			}
			window.sendaid=sendaid;	
			});
		}
        
        /**
         * 添加临时小区后，把相关资料赋值给对应的房源发布页面
         */
		function sendaid2(vpid3,vname,vccid1,vccid2,vaddress,vdt){
			var pid3 = document.getElementsByName('fmdata[pid3]')[0];
			var ccid1 = $id('fmdata[ccid1]') ? $id('fmdata[ccid1]') : document.getElementsByName('fmdata[ccid1]')[0];
			var ccid2 = $id('fmdata[ccid2]') ? $id('fmdata[ccid2]') : document.getElementsByName('fmdata[ccid2]')[0];
			var address =$id('fmdata[address]');
			var dt = $id('fmdata[dt]');
				pid3.value = vpid3;
				lpmc.value = vname;
				set_select(ccid1,vccid1,1);
				set_select(ccid2,vccid2,0);	
				address.value = vaddress;
				dt.value = vdt;
		} 
		function closediv(){
			divin.nextSibling.style.display = 'none';
			divin.style.display="none";
		}
		function addlpinfo(){
			top.win = document.CWindow_wid; 
			return floatwin('open_addlp','?action=lpadd');
		}
		</script>
		<?
		$this->fields_did[] = 'lpmc';
	}	
	
	// 总价，单价，面积	组合
	// 当总价、面积字段都存在时，加载计算单价JS
	function fm_cprice(){
		$flist = array('zj','mj','dj');
		$js = 0;
		foreach($flist as $k){ 
			if(isset($this->fields[$k])){
				$this->fm_field($k);
				$js++;
			}
			$this->fields_did[] = $k;
		}
		echo "<script type='text/javascript'>
				var input_id = '".$this->fmdata."';
				var mj = \$id(input_id + '[mj]'); ";
		if($js==3){
			echo "
				var zj = \$id(input_id + '[zj]');
				var dj = \$id(input_id + '[dj]');
				dj.readOnly = true;			
				zj.onkeyup = mj.onkeyup =function(){
					if(zj.value!='' && mj.value!='' && mj.value!='0'){
						val = (parseFloat(zj.value) * 10000 / parseFloat(mj.value)).toFixed(0);
						if(!isNaN(val)) dj.value = val;
					}
					auto_fillx();"; 
		}else{
			echo "
				mj.onkeyup =function(){
					auto_fillx();";
		}
		echo "}</script>";
	}
	
	// 周边---自动关联[楼盘/小区],保存扩展
	function sv_zhoubian($fmdata,$aid,$chid=8){
		ex_zhoubian($aid,$chid,@$fmdata['dt']);
	}
	
	// 房源,保存扩展处理:优质房源,房源多图(放在保存合辑,图片之后)
	function sv_fyext($fmdata,$chid=3){
		$curuser = cls_UserMain::CurUser();
		$c_upload = cls_upload::OneInstance();
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$aid = $this->aid; //$chid = $this->chid;
		$sql = "SELECT count(*) FROM {$tblprefix}".atbl(121)." WHERE pid38='$aid' ";
		$imageNum = $db->result_one($sql);
		$imageNum = $imageNum ? $imageNum : 0; //图片数量
		$wordNum  = 0;//汉字所占字符数量
		$wordContent = 0;//排除图片后纯汉字的内容
		$updarr = array();
		// 优质房源：房源描述中有4张清晰的图 + 朝向 + 房龄 + 楼层+ 30个字以上的房源描述
		if(in_array($chid,array(2,3))){
            $goodHouse = 1;//优质房源标识
            foreach(array('content','cx','fl','szlc','zlc') as $k){
                empty($fmdata[$k]) && $goodHouse = 0;
                if($k == 'content'){           
                    $content = htmlspecialchars_decode($fmdata['content']);//将已经实体化的html转回普通字符
                    //$imageNum += substr_count($content,"<img");//获取图片个数 (编辑器里面的图片不再统计)
                    $wordContent = strip_tags($content);//全部去掉html代码
                    $wordNum  = strlen($wordContent);//获取没有html代码的纯文字字符串长度                    
                    ($imageNum <4 || $wordNum < 60) && $goodHouse = 0;
                }
            }
			if($goodHouse){ //优质房源
           		$updarr['goodhouse'] = 1; //$this->arc->updatefield('goodhouse',1);
				if($this->isadd){ //会员中心( && $this->mc)，添加时执行
					$curuser->basedeal('yzfy',1,1,'优质房源奖励',1);
				}
			}
		}
		$imageNum && $updarr['imgnum'] = $imageNum; // 房源多图
		if(!empty($updarr)){ //两个一起更新
			$db->update('#__'.atbl($chid), $updarr)->where("aid = $aid")->exec();	
		} //不用$this->arc->updatefield()，可能已经使用了sv_update()
		#exfy_imgnum($chid,$aid,$fmdata['content']); 
	}
	
	// 到期时间-扩展
	// 2,3,9,10,108 模型(会员中心,游客发布)使用，在$oA->sv_update()之前调用, 
	function sv_enddate($days=0){
		if(!$this->isadd) return;
		$_arc = $this->arc->archive;
		if(in_array($_arc['chid'],array(2,3,9,10,108,117,118,119,120))){
			if($_arc['chid']==108){ //招聘
				$mconfigs = cls_cache::Read('mconfigs');
				$zpvalid = $mconfigs['zpvalid'];
				$days = empty($zpvalid) ? 30 : max(1,intval($zpvalid));
			}else{ // 房源/需求
				$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
				$_cfg = $exconfigs['fanyuan']; //配置
				$_key = in_array($_arc['chid'],array(2,3,117,118,119,120)) ? 'fyvalid' : 'xqvalid';
				$this->arc->arcuser(); 
				$_gid = @$this->arc->auser->info['grouptype14']; 
				$_gid = empty($_gid) ? 0 : $_gid;
				$days = empty($_cfg[$_gid][$_key]) ? 30 : max(1,intval($_cfg[$_gid][$_key]));
			}
			$this->arc->setend($days);
		}
	}
	
		
	//结束时间：默认为空，即表示永久为空
	function fm_enddate($title=''){
	    $title = empty($title)?'结束时间':$title;
		trbasic($title,$this->fmdata."[enddate]",isset($this->predata['enddate']) ? (empty($this->predata['enddate'])?'':date('Y-m-d',$this->predata['enddate'])) : '','calendar',array('guide'=>'不限结束时间请留空','validate'=>makesubmitstr($this->fmdata."[enddate]",0,0,0,0,'date')));
	}
	
	
	/**楼盘文档页静态保存格式(数据显示)
	*勾选情况下，先提取后台的静态保存格式，然后把{aid}替换成楼盘拼音(要注意的是:如果提取到的静态保存格式不包含有aid
	*则固定为{$topdir}/{$y}{$m}/{$_pinyin}/{$addno}-{$page}.html)	
	*/
	function fm_customurl(){	
		$mconfigs = cls_cache::Read('mconfigs');
		#$customurl = strstr($mconfigs['arccustomurl'],'{$aid}')?$mconfigs['arccustomurl']:"{$topdir}/{$y}{$m}/{$aid}/{$addno}-{$page}.html";
		#$customurl = str_replace('{$aid}','{$_pinyin}',$customurl);
		$customurl = '{$topdir}/{$_pinyin}/{$addno}-{$page}.html';
		if(!$this->isadd){			
			echo "<tr style=\"display:none;\"><td></td><td><input type=\"hidden\" name=\"fmdata[subject_pinyin]\" id=\"fmdata[subject_pinyin]\" value=\"".(cls_string::Pinyin($this->predata['subject']))."\"></td></tr>";
		}
		trbasic('文档页静态保存格式',"fmdata[customurl]",isset($this->predata['customurl']) ? $this->predata['customurl'] : '','text',array('guide'=>'留空为默认格式，{$topdir}顶级栏目目录，{$cadir}所属栏目目录，{$y}年 {$m}月 {$d}日 {$h}时 {$i}分 {$s}秒 {$chid}模型id  {$aid}文档id {$page}分页页码 {$addno}附加页id，id之间建议用分隔符_或-连接。','w'=>50));	
		?>
        
		<script type="text/javascript">
		jQuery(document).ready(function() {
		  var customurl = jQuery("input[id='fmdata[customurl]']");
		  var url_val = '<?php echo $customurl;?>'; 
		  customurl.after("<input type='checkbox' id='is_pinyin' name='is_pinyin' style='margin-left:30px;'>");
		  jQuery("#is_pinyin").after("<span>用楼盘名称的拼音作为静态格式的一部分</span>");
		  jQuery("#is_pinyin").click(function(){
			  if(this.checked == true){			
				  customurl.val(url_val);
			  }else{
				  customurl.val('');
			  }
		  });
		  $obj = jQuery("input[name='fmdata[customurl]']");
		  if(jQuery($obj[0]).val()==''){
		  	jQuery("#is_pinyin").trigger('click');
		  	//console.log('ff');  
		  }
		});
		</script>
		<?php
	}
	
	/**	
 	* 楼盘文档页静态保存格式(数据入库)
	* @param  string $fmdata['subject_pinyin']：原标题转化的拼音
 	*/	
	function sv_customurl(){
		global $timestamp;
		$fmdata = &$GLOBALS[$this->fmdata];
		$ename = 'customurl';
		$_pinyin = cls_string::Pinyin(trim($fmdata['subject']));//标题转化拼音

		if($this->isadd){
			$_url_str = str_replace('{$_pinyin}',$_pinyin,$fmdata[$ename]);
		}else{
			//判断标题是否改变
			if($_pinyin == $fmdata['subject_pinyin']){
				$_url_str = str_replace('{$_pinyin}',$_pinyin,$fmdata[$ename]);
			}else{
				$_url_str = str_replace($fmdata['subject_pinyin'],$_pinyin,$fmdata[$ename]);				
			}
		}		
		$this->predata['nokeep'] = $this->arc->updatefield($ename,trim($_url_str));		
	}
    
    /**
     * 资讯合辑到楼盘
     *  
     */
    public function fm_info_to_building(){
        $caid = $this->predata['caid'];
        $catalog = cls_cache::Read('catalog',$caid);       
        $mconfigs = cls_cache::Read("mconfigs");      
        if($catalog['hejilanmu']){          
      		trbasic('<font color="red"> * </font>楼盘名称',$this->fmdata.'[lpmc]','','text',array('w'=>60,'validate'=>' rule="text" must="1" mode="" rev="楼盘名称"','guide'=>'可以输入楼盘名称进行搜索'));
            trhidden($this->fmdata.'[lpaid]','');
            ?>
                <script type="text/javascript">
                    var cms_abs = '<?php echo $mconfigs['cms_abs'];?>';
                    var lpmc = '<?php echo $this->fmdata?>' + '[lpmc]';
                    var lpmc = $(document.getElementById(lpmc));
                    var lpaid = '<?php echo $this->fmdata?>' + '[lpaid]';
                    var lpaid = $(document.getElementById(lpaid));
                    var divout = document.createElement('DIV');  
                    with(divout.style){position = 	'relative';left = 0+'px';top = 0+'px';zIndex = 100;}
                    var divout = $(divout);
              		var showdiv = "	<div style=\"border: 1px solid rgb(102, 102, 102); position: absolute; z-index: 1000; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\" id=\"showdiv\"></div>";              		
                    divout.html(showdiv);                                   
		            
               
                    divout.insertAfter(lpmc); 
                    lpmc.keyup(function(){
                        jQuery.getScript(cms_abs + uri2MVC('ajax=search_choice&chid=4&val='+lpmc.val()),function(){
                            var str = '<table width=\"480px\" cellspacing=\"0\" cellpadding=\"4\" border=\"0\" bgcolor=\"#ffffff\" class=\"search_select\" id=\"Suggestion\" style=\"top: -1px;\";><tbody><tr><td><a onclick=\"closeDiv()\">关闭</a></td></tr>';
                            
                            for(var i=0; i<data.length;i++){
                                str+="<tr onclick=\"setinfo("+data[i].aid+",'" + data[i].subject + "')\" style=\"cursor:pointer\"><td index=\"1\" style=\"padding: 5px; color: rgb(51, 51, 51);\" ><span style=\"color: rgb(0, 101, 181);width:150px; display:block; float:left;\">"+data[i].subject+"</span><span style=\"display:block; float:left;width:280px; white-space:nowrap;overflow:hidden;\">地址："+data[i].address+"</span></td></tr>";
                            }
                            str += '</tbody></table>';                          
                            
                            jQuery('#showdiv').html(str);
                            jQuery('#showdiv').css("display","block");
                           
                        });                 
  
                        
                    })
                    
                    function setinfo(aid,subject){
                        lpmc.val(subject);
                        lpaid.val(aid);
                        jQuery('#showdiv').css("display","none");  
                    }
                    
                    function closeDiv(){                        
                         jQuery('#showdiv').css("display","none");
                    }
             
                  
                </script>
            <?php
        }       
    }
    
    /**
     * 资讯合辑到楼盘
     *  
     */    
    public function sv_info_to_building(){
        $catalog = cls_cache::Read('catalog',1);
        $mconfigs = cls_cache::Read("mconfigs");  
        $db = _08_factory::getDBO();   
        if($catalog['hejilanmu']){
            $fmdata = &$GLOBALS[$this->fmdata];
            if(!empty($fmdata['lpaid'])){
                //通过楼盘aid检测是否存在楼盘               
                $fmdata['lpmc'] = trim($fmdata['lpmc']);
                $row = $db->select('COUNT(*) as num')->from('#__archives15 a')
                  ->where("a.aid = $fmdata[lpaid] ") 
                  ->exec()->fetch();
                if(!empty($row['num'])){
                    $db->insert( '#__aalbums', 
                        array(
                            'arid ' => 1, 
                            'inid ' => $this->aid, 
                            'pid' => $fmdata['lpaid'], 
                            'incheck ' => 1,                             
                        )
                    )->exec();
       
                }
                
                
            }
        }
        
        
    }
    
    /**
     * 开盘时间的设定自动赋值给开盘说明
       要确认开盘日期，开盘说明字段已启用
       如果开盘说明有内容，则不改变
     */
    public function fm_kp_info(){
        $fields = cls_cache::Read('fields',4);
        if(!$fields['kpsj']['available'] || !$fields['kprq']['available']){
            return false;
        }
      ?>
      <script type="text/javascript">
        var kpsj_value = '<?php echo isset($this->predata['kpsj']) ? (empty($this->predata['kpsj'])?'':date('Y-m-d',$this->predata['kpsj'])) : ''?>';
        var kpsj_id = '<?php echo $this->fmdata?>' + '[kpsj]';
        var kprq_id = '<?php echo $this->fmdata?>' + '[kprq]';
        var kpsj = $(document.getElementById(kpsj_id));
        var kprq = $(document.getElementById(kprq_id));
        //先给开盘时间赋值，后面用定时器来检测该值与页面上设置的值是否不同而采取动作
        setInterval(function(event){
            if(kpsj_value != kpsj.val()){                
                kpsj_value = kpsj.val();
                var kprqarr = kpsj_value.split('-');
                if(!kprq.val()){//如果开盘说明有内容，则不改变
                    kprq.val(kprqarr[0]+'年'+kprqarr[1]+'月'+kprqarr[2]+'日');
                }           
            }
        },100);
      </script>
      <?php
    } 
    
    
  /**
   * 楼盘价格编辑跳转链接
   */
  public function fm_dj_edit_url(){
    	trbasic('价格编辑','',"<a onclick=\"return floatwin('open_arcdj',this)\" href=\"?entry=extend&amp;extend=jiagearchive&aid=".$this->predata['aid']."&isnew=1\" class=\"scol_url_pub\"><font color='blue'>>>编辑楼盘价格</font></a>",'html22',array('guide'=>'点此可以进入楼盘价格编辑页面'));
  }
  
  /**
   * 楼盘分销显示所属楼盘
   */
  
   public function lpfx_to_building(){
        $caid = $this->predata['caid'];
        $catalog = cls_cache::Read('catalog',$caid);       
        $mconfigs = cls_cache::Read("mconfigs"); 
        $lpmc = empty($this->arc->archive['lpmc'])?'':$this->arc->archive['lpmc'];
		$this->fields_did[] = 'lpmc';   
		if(empty($this->isadd)){
			trbasic('<font color="red"> * </font>楼盘名称','',$lpmc,'');
			return;
		}
        $pid33 = empty($this->arc->archive['pid33'])?'0':$this->arc->archive['pid33'];
		trbasic('<font color="red"> * </font>楼盘名称',$this->fmdata.'[lpmc]',$lpmc,'text',array('w'=>60,'validate'=>' rule="text" must="1" mode="" rev="楼盘名称" autocomplete="off"','guide'=>'可以输入楼盘名称进行搜索'));
		trhidden($this->fmdata.'[pid33]',$pid33);
		?>
			<script type="text/javascript">
				var cms_abs = '<?php echo $mconfigs['cms_abs'];?>';
				var lpmc = $($id('<?php echo $this->fmdata?>' + '[lpmc]'));
				var lpaid = $($id('<?php echo $this->fmdata?>' + '[pid33]'));
				var lpmcbid = '0', lpmcstr = '';
				var divout = document.createElement('DIV');  
				with(divout.style){position = 	'relative';left = 0+'px';top = 0+'px';zIndex = 100;}
				var divout = $(divout);
				var showdiv = "	<div style=\"border: 1px solid rgb(102, 102, 102); position: absolute; z-index: 1000; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\" id=\"showdiv\"></div>";              		
				divout.html(showdiv);
				divout.insertAfter(lpmc); 
				
				lpmc.keyup(function(){
					if(lpmcstr===lpmc.val().toString()){ 
						jQuery(lpaid).val(lpmcbid);
						//console.log('11:'+lpmcstr+'::'+lpmc.val());
					}else{ //改变了就变0
						jQuery(lpaid).val(0);
						//console.log('12:'+lpmcstr+'::'+lpmc.val());	
					} // AND a.aid NOT IN(select pid33 FROM {$tblprefix}".atbl(113).")
					jQuery.getScript(cms_abs + uri2MVC('ajax=pagepick_loupan&aj_model=a,4,1&aj_thumb=thumb,120,90&aj_pagesize=50&aj_pagenum=1&leixing=1&isfenxiao=1&searchword='+encodeURIComponent(lpmc.val())+'&rescript=data'),function(){
						var str = '<table width=\"480px\" cellspacing=\"0\" cellpadding=\"4\" border=\"0\" bgcolor=\"#ffffff\" class=\"search_select\" id=\"Suggestion\" style=\"top: -1px;\";><tbody><tr><td><a onclick=\"closeDiv()\">关闭</a></td></tr>';
						for(var i=0; i<data.length;i++){
							str+="<tr onclick=\"setinfo("+data[i].aid+",'" + data[i].subject + "','"+ data[i].ccid1 +"','"+ data[i].kprq +"','"+ data[i].kpsjdate +"')\" style=\"cursor:pointer\"><td index=\"1\" style=\"padding: 5px; color: rgb(51, 51, 51);\" ><span style=\"color: rgb(0, 101, 181);width:150px; display:block; float:left;\">"+data[i].subject+"</span><span style=\"display:block; float:left;width:280px; white-space:nowrap;overflow:hidden;\">地址："+data[i].address+"</span></td></tr>";
						}
						str += '</tbody></table>';                          
						
						jQuery('#showdiv').html(str);
						jQuery('#showdiv').css("display","block");
						//lpmc.autocomplete = "off";
					   
					});                   
				})
				
				function setinfo(aid,subject,ccid1,kprq,kpsjdate){
					var kpsm = $id('fmdata[kprq]');
					lpmc.val(subject); 
					lpaid.val(aid);
					lpmc[0].onfocus();                       
					jQuery(kpsm).val(kprq);
					jQuery('#showdiv').css("display","none");  
					lpmcstr = subject;
					lpmcbid = aid; 
					$("select[name='fmdata[ccid1]'] option[value='"+ccid1+"']").attr("selected", true);
				}         
	
				//关闭下拉框        
				function closeDiv(){                        
					 jQuery('#showdiv').css("display","none");
					 //if(jQuery(lpaid).val()=='0') jQuery(lpmc).val('');
				}

			</script>
		<?php
	}


    // 商业楼盘出售出租-楼盘名称,选择
    //$mc:1为会员中心，0为后台
    //$is_no_addxq; 1  为前台免注册发布房源
    function fm_ulpmc($mc=1,$is_no_addxq=0){
        global $db, $tblprefix, $ckpre, $handlekey, $cms_top, $mcharset, $ckdomain, $ckpath;
        $pid36 = @$this->arc->archive['pid36']; //echo ",$pid3,"; //print_r($this->arc);
        $lpmc = @$this->arc->archive['lpmc'];

        if ( false === stripos($mcharset, 'UTF') )
        {
            $this->arc->archive['lpmc'] = cls_string::iconv($mcharset, 'UTF-8', @$this->arc->archive['lpmc']);
        }
        if ( !empty($ckdomain) )
        {
            # 去掉域里开头的点
            $cms_top = substr($ckdomain, 1);
        }
        # 打开窗口时重新初始化CK插件ID与名称，如果升级该脚本时请继承下去
        msetcookie('fyid' . $handlekey, @$this->arc->archive['pid3']);
        msetcookie('lpmc' . $handlekey, urlencode(@$this->arc->archive['lpmc']));


        $mc = $this->mc;
        $add = $this->isadd;
        trhidden($this->fmdata.'[pid36]',$pid36);

        trbasic('<font color="red"> * </font>楼盘名称',$this->fmdata.'[lpmc]',$lpmc,'text',array('w'=>60,'validate'=>' rule="text" must="1" mode="" rev="楼盘名称"','guide'=>'可以输入楼盘名称或楼盘地址进行搜索'));
        //$mc_dir=MC_DIR;
        ?>
        <script type="text/javascript">
            var fmdata = '<?php echo $this->fmdata; ?>';

            //功能:
            function createobj(element,type,value,id){
                var e = document.createElement(element);
                e.type = type;
                e.value = value;
                e.id = id;
                return e;
            }

            //功能:
            function set_select(obj,value,dochange){
                if(obj==null) return;
                for(var j=0;j<obj.options.length;j++){
                    if(obj.options[j].value == value){
                        obj.options[j].selected = true;
                        if(dochange && obj.onchange)obj.onchange();
                    }
                }
            }

            var lpmc = $id(fmdata+'[lpmc]');//<input type="text" size="60" id="fmdata[lpmc]" name="fmdata[lpmc]" value="" rule="text" must="1" mode="" rev="楼盘名称" autocomplete="off">
            lpmc.setAttribute('autocomplete','off');
            var divout = document.createElement('DIV');
            var pid36 = document.getElementsByName('fmdata[pid36]')[0];
            with(divout.style){position = 	'relative';left = 0+'px';top = 0+'px';zIndex = 100;}
            var showdiv = "	<div style=\"border: 1px solid rgb(102, 102, 102); position: absolute; z-index: 1000; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\" id=\"SuggestionDiv\"></div><iframe frameborder=\"0\" style=\"border: 0px solid rgb(102, 102, 102); position: absolute; z-index: 100; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\"></iframe>";
            divout.innerHTML = showdiv;
            lpmc.parentNode.insertBefore(divout,lpmc.nextSibling);
            var divin = $id('SuggestionDiv');
            var aj=Ajax("HTML","loading");


            lpmc.onkeyup = function(){
                var chid = '<?php echo(in_array($this->predata['caid'],array(613,614)) ? 115 : 116); ?>';
                var title = chid == '115' ? '写字楼' : '商铺';
                var ccid1 = $id('fmdata[ccid1]') ? $id('fmdata[ccid1]') : document.getElementsByName('fmdata[ccid1]')[0];
                var ccid2 = $id('fmdata[ccid2]');
                var ccidarr = ["ccid46", "ccid47", "ccid48", "ccid49"];
                var address =$id('fmdata[address]');
                var dt = $id('fmdata[dt]');
                //当选了小区后，又删除小区名称，则之前自动赋值的那些选项，全部清空
                if(lpmc.value == ''){//楼盘名称input输入框的value属性
                    $id('fmdata[subject]').value = '';
                    set_select(ccid1,0,1);
                    set_select(ccid2,0,0);
                    for(var j = 0,ccida,ccidb,ccidc; j<ccidarr.length;j++){
                        if(ccida = $("input[id^='_fmdata["+ccidarr[j]+"]']")) ccida.removeAttr('checked');
                        if(ccidb = $("input[name='fmdata["+ccidarr[j]+"]']")) ccidb.val('');
                        if(ccidc = $("input[id^='fmdata["+ccidarr[j]+"]']")) ccidc.removeAttr('checked');
                    }
                    address.value = dt.value = '';
                }
                var urlpara = lpmc.value == ' ' ? '&keywords='+encodeURIComponent(lpmc.value) :'&keywords='+encodeURIComponent(lpmc.value.replace(/(^\s*)|(\s*$)/g,''));
                var urlfull = CMS_ABS + uri2MVC('ajax=ajaxbus_loupan&chid='+chid+urlpara);
                aj.post(urlfull,'',function(re){
                    eval("var s = "+re+";");
                    divin.style.display = '';
                    divin.nextSibling.style.display = '';
                    var str="<table width=\"480px\" cellspacing=\"0\" cellpadding=\"4\" border=\"0\" bgcolor=\"#ffffff\" class=\"search_select\" id=\"Suggestion\" style=\"top: -1px;\";><tbody><tr><td height=\"16\" align=\"center\" style=\"color: rgb(153, 153, 153); padding-left: 3px; background-repeat: repeat-x; background-position: center center;\" >请点击选择"+title+"所在楼盘（没有合适楼盘请点击关闭直接填写）</td><td><a style=\"cursor:pointer;text-decoration:none; color:red;\" onclick=\"closediv()\">关闭</a></td></tr>"
                    for(i=0;i<s.length;i++){
                        str+="<tr onclick=\"sendaid("+i+")\" style=\"cursor:pointer\"><td index=\"1\" style=\"padding: 5px; color: rgb(51, 51, 51);\" ><span style=\"color: rgb(0, 101, 181);width:150px; display:block; float:left;\">"+s[i].subject+"</span><span style=\"display:block; float:left;width:280px; white-space:nowrap;overflow:hidden;\">地址："+s[i].address+"</span></td></tr>";
                    }
                    str+="</tbody></table>";
                    divin.innerHTML = str;

                    function sendaid(i){
                        pid36.value = s[i].aid;
                        lpmc.value = s[i].subject;
                        //console.log($id('_fmdata[ccid49]'));alert('info');

                        set_select(ccid1,s[i].ccid1,1);
                        set_select(ccid2,s[i].ccid2,0);

                        for(var j = 0; j<ccidarr.length;j++){
                            var kccid = ccidarr[j], vccid;
                            try{
                                eval("vccid = s[i]."+kccid);
                            }catch(ex){ continue; }
                            if(!vccid) { continue; }

                            var accid = vccid.split(',');

                            for(var k = 0; k<accid.length;k++){
                                var v2ccid = accid[k]; if(v2ccid.length==0) { continue; }
                                var occid1 = $id('_fmdata['+kccid+']'+v2ccid); //.checked = true;
                                var occid2 = $id('fmdata['+kccid+']'+v2ccid); //.checked = true;
                                if (occid1) occid1.checked = true;
                                if (occid2){
                                    occid2.checked = true;
                                    $("input[name='fmdata[ccid49]']").val(accid);
                                }
                            }
                        }
                        //console.log('::'); return;

                        var thumb = document.getElementsByName('fmdata[thumb]')[0];
                        var dt = $id('fmdata[dt]');
                        var address = $id('fmdata[address]');
                        if(thumb)   thumb.value = s[i].thumb;
                        if(dt)      dt.value =s[i].dt;
                        if(address) address.value = s[i].address;
                        setcookie('<?php echo $ckpre;?>fyid<?php echo $handlekey;?>', s[i].aid<?php echo (empty($cms_top) ? '' : ", null, '{$ckpath}', '.{$cms_top}'");?>);
                        setcookie('<?php echo $ckpre;?>lpmc<?php echo $handlekey;?>', encodeURIComponent(s[i].subject)<?php echo (empty($cms_top) ? '' : ", null, '{$ckpath}', '.{$cms_top}'");?>);
                        divin.style.display="none";
                        divin.nextSibling.style.display = 'none';
                        lpmc.onfocus(); //没有这句,如果为空状态下选一个小区,选取后认证提示不会消失
                        //auto_fillx();
                    }
                    window.sendaid=sendaid;
                });
            }

            //功能:关闭楼盘列表框
            function closediv(){
                divin.nextSibling.style.display = 'none';
                divin.style.display="none";
            }
        </script>
        <?
        $this->fields_did[] = 'lpmc';
    }


}
