<?php
/* 
** 单个字段的内容编辑(显示与数据保存)，搜索处理
*/
!defined('M_COM') && exit('No Permission');
#@set_time_limit(0);
include_once _08_INCLUDE_PATH."field.fun.php";
class cls_field{
	var $field = array();
	var $oldvalue = '';
	var $newvalue = '';
	var $isadd = 0;
	var $searchstr = '';
	var $ft = array();
	var $submitstr = '';
	var $haltfunc = '';
	var $halturl = '';
	var $error = '';
	function __construct($field = array(),$oldvalue = ''){
		$this->init($field,$oldvalue);
	}
	public static function getDecorator($field=array(),$oldvalue=''){
		return new cls_fieldDecorator(new self($field,$oldvalue));
	}
	function init($field = array(),$oldvalue = ''){
		$this->field = $field;
		$this->oldvalue = $oldvalue;
		$this->newvalue = '';
		$this->isadd = 0;
		$this->searchstr = '';
		$this->ft = array();
		$this->submitstr = '';
		$this->haltfunc = '';
		$this->halturl = '';
		$this->error = '';
	}
	
	public static function options_simple($field = array(),$cfg = array()){//返回array($id => $title)的数组
		//默认：$cfg = array('blank' => '','onlysel' => 0)
		if(!$field) return array();
		if($field['datatype'] == 'cacc'){
			$na = self::options($field);
			$re = array();
			foreach($na as $k => $v){
				if(!empty($cfg['onlysel']) && !empty($v['unsel'])) continue;
				$re[$k] = (!empty($cfg['blank']) ? str_repeat($cfg['blank'],$v['level']) : '').$v['title'];
			}
		}else $re = self::options($field);
		return $re;
	}
	public static function options($field = array()){
		$re  = array();
		if(!$field) return $re;
		if($field['datatype'] == 'cacc'){//返回保留类目完整参数的数组
			if(!$field['innertext']){
				return $field['coid'] ? cls_cache::Read('coclasses',$field['coid']) : cls_cache::Read('catalogs');
			}else{
				if($ids = @eval($field['innertext'])){
					$re = $field['coid'] ? cls_cache::Read('coclasses',$field['coid']) : cls_cache::Read('catalogs');
					$nids = array();foreach($ids as $id) $nids = array_merge($nids,cls_catalog::Pccids($id,$field['coid'],1));
					if(!($nids = array_unique($nids))) return array();
					foreach($re as $k => $v){
						if(!in_array($k,$nids)){
							unset($re[$k]);
						}elseif(!in_array($k,$ids)) $re[$k]['unsel'] = 1;
					}
				}else return $re;
			}
		}elseif(in_array($field['datatype'],array('select','mselect',))){//返回简单的array($key=>$val,)的数组
			if(!$field['fromcode']){
				$temps = explode("\n",$field['innertext']);
				foreach($temps as $v){
					$temparr = explode('=',str_replace(array("\r","\n"),'',$v));
					$temparr[1] = isset($temparr[1]) ? $temparr[1] : $temparr[0];
					$re[$temparr[0]] = $temparr[1];
				}
				unset($temps,$temparr);
			}else{
				$re = @eval($field['innertext']);
			}
		}
		return $re;
	}	
	
	
	function trfield($varpre='',$addtitle=''){
		$varr = $this->varr($varpre,$addtitle);
		trspecial($varr['trname'],$varr);
	}
	
	function varr($varpre='',$addtitle=''){//需要更灵活的定义varname的方法
		if(empty($this->field['ename']) || empty($this->field['available'])) return array();
		$trname = ($this->field['notnull'] ? '<font color="red"> * </font>' : '').$this->field['cname'].$addtitle;
		$varname = $this->_varname($varpre);
		$this->make_submitstr($varname);
		$oldstr = $this->isadd ? $this->field['vdefault'] : $this->oldvalue;//多项选择		
		foreach(array('datatype','mode','guide','min','max','cnmode','wmid', 'filter', 'editor_height',
                      'rpid', 'auto_page_size', 'auto_compression_width','regular') as $var)
		{
			if(isset($this->field[$var])) {
				$$var = $this->field[$var];
			} else {
				$$var = '';
			}
		}
		$more = isset($this->field['more']) ? $this->field['more'] : array();
		$view = isset($this->field['view']) ? $this->field['view'] : 'S';
		$ret = array('trname' => $trname,);
		if($datatype == 'cacc'){
		    $configs = array('type' => $datatype,'varname' => $varname,'value' => $oldstr,'coid' => $this->field['coid'],
                             'code' => $this->field['innertext'],'ftype' => $this->field['type'], 'tpid' => $this->field['tpid'],
                             'ename' => $this->field['ename'],'vmode' => $mode,'smode' => $cnmode ? intval($cnmode) : 0,
                             'validator' => $this->submitstr,'guide' => $guide, 'filter' => $filter, 
                             'editor_height' => $editor_height, 'rpid' => $rpid, 'auto_page_size' => $auto_page_size,
                             'auto_compression_width' => $auto_compression_width);
			$ret += specialarr($configs);
		}else{
			if(in_array($datatype,array('text','int','float'))){
				$oldstr = mhtmlspecialchars($oldstr);
				//mysql中取出来是3.14e+006/3.14e-006形式的就转化
				if($datatype=='float' && strstr($oldstr,'e')){ 
					$oldstr = str_replace(',','',number_format($oldstr));
					//$oldstr = number_format($oldstr,-1,'.','');
				}
				$datatype = 'text';
			}elseif($datatype == 'select'){
				$sourcearr = self::options($this->field);
				$oldstr = !$mode ? makeoption($sourcearr,$oldstr) : makeradio($varname,$sourcearr,$oldstr);
			}elseif($datatype == 'mselect'){
				$sourcearr = self::options($this->field);
				$oldarr = explode("\t",$oldstr);
				$oldstr = !$mode ? multiselect($varname.'[]',$sourcearr,$oldarr) : makecheckbox($varname.'[]',$sourcearr,$oldarr);
			}elseif($datatype == 'multitext'){
				$oldstr = mhtmlspecialchars($oldstr);
			}elseif($datatype == 'date'){
				$oldstr = $oldstr ? date($mode ? 'Y-m-d H:i:s' : 'Y-m-d',$oldstr) : '';
			}elseif($datatype == 'map'){
				if($mode = $this->isadd)$oldstr = '';
				#$min = $this->field['length'];//此参数目前无效，注释掉				
				if(!($min = cls_env::mconfig('init_map_zoom'))) $min = 12;
				if(!($max = $this->field['vdefault'])) $max = cls_env::mconfig('init_map');
			}elseif($datatype == 'vote'){
				$max = $this->field['tpid'];#chid
				$mode = $this->field['type'];
			}elseif($datatype == 'texts'){
				$mode = $this->field['innertext'];
			}elseif($datatype == 'image'){
                $oldstr = mhtmlspecialchars(strip_tags($oldstr));
			}elseif($datatype == 'images'){
                $imgFlag = $this->field['issearch'] ? 'S' : 'H';                                             
                $imgComment = empty($this->field['imgComment']) ? '图片属性2' : $this->field['imgComment'];                
			}
            
            $configs = array('type' =>$datatype,'varname' => $varname,'value' => $oldstr,'mode' => $mode,'min' => $min,'max' => $max,'validator' => $this->submitstr,'guide' => $guide,'more'=>$more, 'filter' => $filter,'rpid' => $rpid);
            if ($datatype === 'htmltext')
            {
                $configs['wmid'] = $wmid;
				$configs['regular'] = $regular;
				$configs['auto_page_size'] = $auto_page_size;
                $configs['editor_height'] = $editor_height;
				$configs['auto_compression_width'] = $auto_compression_width;
            }
            elseif(in_array($datatype, array('image', 'images'), true))
            {
            	$configs['auto_compression_width'] = $auto_compression_width;
            	$configs['wmid'] = $wmid;
            	$configs['imgFlag'] = @$imgFlag;
            	$configs['view'] = @$view;
            	$configs['imgComment'] = @$imgComment;
            }
			elseif($datatype == 'multitext')
		    {
				$configs['regular'] = $regular;
			}
			$ret += specialarr($configs);
		}
		return $ret;
	}
	function deal_search($fpre = ''){//$fpre为查询字串中的表别名，如a.,c.,m.等
		if(!$this->field['issearch']) return;
		$fn = $this->field['ename'];
		global ${$fn},${$fn.'str'},${$fn.'from'},${$fn.'to'},${$fn.'_0'},${$fn.'_1'},${$fn.'diff'};
		if($this->field['datatype'] == 'select'){
			if($this->field['issearch'] == '1'){
				if(${$fn} != ''){
					$this->searchstr = $fpre.$fn."='".${$fn}."'";
					$this->ft[$fn] = stripslashes(${$fn});
				}
			}else{
				if(!empty(${$fn})){
					!is_array(${$fn}) && ${$fn} = array(${$fn});
					${$fn.'str'} = implode("\t",${$fn});
				}elseif(!empty(${$fn.'str'})){
					${$fn} = explode("\t",${$fn.'str'});
				}else ${$fn.'str'} = '';
				if(${$fn.'str'} != ''){
					$this->searchstr = $fpre.$fn." ".multi_str(${$fn});
					$this->ft["{$fn}str"] = stripslashes(${$fn.'str'});
				}
			}
		}elseif($this->field['datatype'] == 'mselect'){
			if($this->field['issearch'] == '1'){
				if(${$fn} != ''){
					$find = ${$fn}; //注意数据结构：正则匹配单个项，在开头,在中间，在结尾；效率待后续跟进
					//$find = "REGEXP '^($find)$|^($find)\t|\t($find)\t|\t($find)$'";
					//$this->searchstr = $fpre.$fn." $find ";
					$this->searchstr = "CONCAT('\t',$fpre$fn,'\t') LIKE '%\t$find\t%'"; //这个比REGEXP快些
					$this->ft[$fn] = stripslashes(${$fn});
				}
			}else{
				if(!empty(${$fn})){
					${$fn} =  is_array(${$fn}) ? ${$fn} : array(${$fn});
					${$fn.'str'} = @implode("\t",${$fn});
				}elseif(!empty(${$fn.'str'})){
					${$fn} = explode("\t",${$fn.'str'});
				}else ${$fn.'str'} = '';
				if(${$fn.'str'} != ''){
					foreach(${$fn} as $v){ 
						//$find = "REGEXP '^($v)$|^($v)\t|\t($v)\t|\t($v)$'";
						//$this->searchstr .= ($this->searchstr ? ' OR ' : '').$fpre.$fn." $find ";//sqlkw($v);
						$this->searchstr .= ($this->searchstr ? ' OR ' : '')."CONCAT('\t',$fpre$fn,'\t') LIKE '%\t$v\t%'";
					}
					$this->searchstr = '('.$this->searchstr.')';
					$this->ft["{$fn}str"] = stripslashes(${$fn.'str'});
				}
			}
		}elseif($this->field['datatype'] == 'text'){
			${$fn} = empty(${$fn}) ? '' : cls_string::CutStr(trim(${$fn}),20,'');
			if(${$fn} != ''){
				$this->searchstr = $this->field['issearch'] == 1 ? $fpre.$fn."='".${$fn}."'" : $fpre.$fn.sqlkw(${$fn});
				$this->ft[$fn] = stripslashes(${$fn});
			}
		}elseif($this->field['datatype'] == 'cacc'){
			if(${$fn} = empty(${$fn}) ? 0 : max(0,intval(${$fn}))){
				$this->searchstr = caccsql($fpre.$fn,$this->field['issearch'] == 1 ? array(${$fn}) : sonbycoid(${$fn},$this->field['coid'] ? $this->field['coid'] : 0),$this->field['cnmode']);
				$this->ft[$fn] = stripslashes(${$fn});
			}
		}elseif($this->field['datatype'] == 'map'){
			if(${$fn.'diff'} = empty(${$fn.'diff'}) ? 0 : abs(${$fn.'diff'})){
				$this->searchstr = cls_dbother::MapSql(${$fn.'_0'},${$fn.'_1'},${$fn.'diff'},$this->field['issearch'],$fpre.$fn);
				$this->ft["{$fn}_0"] = stripslashes(${$fn.'_0'});
				$this->ft["{$fn}_1"] = stripslashes(${$fn.'_1'});
				$this->ft["{$fn}diff"] = stripslashes(${$fn.'diff'});
			}
		}elseif(in_array($this->field['datatype'],array('int','float','date'))){
			if($this->field['issearch'] == '1'){
				${$fn} = trim(${$fn});
				if(($this->field['datatype'] == 'date' && !cls_string::isDate(${$fn}, $this->field['mode'])) || (in_array($this->field['datatype'],array('int','float')) && !is_numeric(${$fn}))) ${$fn} = '';
				if(${$fn} != ''){
					$this->field['datatype'] == 'int' && ${$fn} = intval(${$fn});
					$this->field['datatype'] == 'float' && ${$fn} = floatval(${$fn});
					$this->searchstr = $this->field['datatype'] == 'date' ? $fpre.$fn."='".strtotime(${$fn})."'" : $fpre.$fn."='".${$fn}."'";
					$this->ft[$fn] = stripslashes(${$fn});
				}
			}else{
				${$fn.'from'} = trim(${$fn.'from'});
				if(($this->field['datatype'] == 'date' && !cls_string::isDate(${$fn.'from'}, $this->field['mode'])) || (in_array($this->field['datatype'],array('int','float')) && !is_numeric(${$fn.'from'}))) ${$fn.'from'} = '';
				if(${$fn.'from'} != ''){
					$this->field['datatype'] == 'int' && ${$fn.'from'} = intval(${$fn.'from'});
					$this->field['datatype'] == 'float' && ${$fn.'from'} = floatval(${$fn.'from'});
					$this->searchstr = $this->field['datatype'] == 'date' ? $fpre.$fn.">='".strtotime(${$fn.'from'})."'" : $fpre.$fn.">='".${$fn.'from'}."'";
					$this->ft["{$fn}from"] = stripslashes(${$fn.'from'});
				}
				${$fn.'to'} = trim(${$fn.'to'});
				if(($this->field['datatype'] == 'date' && !cls_string::isDate(${$fn.'to'}, $this->field['mode'])) || (in_array($this->field['datatype'],array('int','float')) && !is_numeric(${$fn.'to'}))) ${$fn.'to'} = '';
				if(${$fn.'to'} != ''){
					$this->field['datatype'] == 'int' && ${$fn.'to'} = intval(${$fn.'to'});
					$this->field['datatype'] == 'float' && ${$fn.'to'} = floatval(${$fn.'to'});
					$this->searchstr .= ($this->searchstr ? " AND " : "").$fpre.$fn."<'".($this->field['datatype'] == 'date' ? strtotime(${$fn.'to'}) : ${$fn.'to'})."'";
					$this->ft["{$fn}to"] = stripslashes(${$fn.'to'});
				}
			}
		}
		return;
	}
	function DealByValue($nvalue,$haltfunc = 'cls_message::show',$halturl = ''){//通过直接传值来处理一个字段		
		$c_upload = cls_upload::OneInstance();
		$this->haltfunc = $haltfunc;
		$this->halturl = $halturl;
		$this->newvalue = $nvalue;
		$datatype = $this->field['datatype'];
		
		if($datatype == 'mselect'){
			$this->newvalue = !empty($this->newvalue) ? implode("\t",$this->newvalue) : '';
		}elseif($datatype == 'map'){
			if($this->newvalue){
				list($lng, $lat) = explode(',', $this->newvalue);
				if(is_numeric($lng) && is_numeric($lat)){
					$lng = floatval($lng); $lat = floatval($lat);
					($lng < -90 || $lng > 90 || $lat < -180 || $lat > 180) && $this->newvalue = '';
				}else{
					$this->newvalue = '';
				}
			}
		}elseif(in_array($datatype,array('image','file','flash','media'))){			
			$this->newvalue = upload_s($this->newvalue,$this->oldvalue,$datatype,$this->field['rpid'],array('wmid'=>@$this->field['wmid'],'auto_compression_width'=>@$this->field['auto_compression_width']));
		}elseif(in_array($datatype,array('images','files','medias','flashs'))){//返回数组，以便分析数量限制
			$this->newvalue = upload_m($this->newvalue,$this->oldvalue,substr($datatype,0,strlen($datatype) - 1),$this->field['rpid'],array('wmid'=>@$this->field['wmid'],'auto_compression_width'=>@$this->field['auto_compression_width']));
		}
		$this->pre_deal();
		$this->check_null();
		$this->check_regular();
        $this->check_safe_str();
		$this->check_limit();
		if($this->field['rpid'] &&  in_array($this->field['datatype'],array('text','multitext','htmltext'))){
			$this->newvalue = addslashes($c_upload->remotefromstr(stripslashes($this->newvalue),$this->field['rpid'],array('wmid'=>@$this->field['wmid'],'auto_compression_width'=>@$this->field['auto_compression_width'])));
		}
		$this->end_deal();
		return $this->newvalue;
	}
	function deal($varpre='',$haltfunc = 'cls_message::show',$halturl = ''){//通过表单数组前缀来处理一个字段
		$nvalue = $this->_PostValue($varpre);
		return $this->DealByValue($nvalue,$haltfunc,$halturl);
	}
	function halt($error = ''){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1);
		if(!$this->haltfunc){
			$this->error .= '<br>'.$error;
			return;
		}else{
		#	$func = $this->haltfunc;
		#	$func($error,$this->halturl);
			cls_message::show($error,$this->halturl);
		}
	}
	function pre_deal(){
		$min = $this->field['min'];
		$max = $this->field['max'];
		if(in_array($this->field['datatype'],array('htmltext','date','multitext','text','select','mselect'))){
			// htmltext一定不能过滤html代码, 但从multitext字段改为htmltext, 可能nohtml为1,而后台设置不会修改这个值
			($this->field['nohtml'] && ($this->field['datatype']!='htmltext')) && $this->newvalue = strip_tags($this->newvalue);
/*			if($this->field['datatype'] == 'htmltext' && !preg_match('/^\s*<[pP][^>]*>[\x00-\xff]*<[pP]\b/', $this->newvalue)){
			  $this->newvalue = preg_replace('/^\s*<[pP][^>]*>\s*|(?:\s|&nbsp;)*<\/[pP]>(?:\s|&nbsp;)*$/', '', $this->newvalue);
			}
*/			
			if($this->field['datatype'] != 'multitext') $this->newvalue = trim($this->newvalue);
			if(in_array($this->field['datatype'],array('htmltext','multitext','text'))){
				if($this->newvalue){
					if($min && cls_string::CharCount($this->newvalue) < $min) $this->halt($this->field['cname'].' 长度小于最小限制');
					if($max && cls_string::CharCount($this->newvalue) > $max) $this->halt($this->field['cname'].' 长度大于最大限制');
				}
			}
		}elseif(in_array($this->field['datatype'],array('int','float',))){
			if($this->newvalue = $this->field['datatype'] == 'int' ? intval($this->newvalue) : floatval($this->newvalue)){
				if(($min || $min == '0') && $this->newvalue < $min) $this->halt($this->field['cname'].' 小于最小限制');
				if(($max || $max == '0') && $this->newvalue > $max) $this->halt($this->field['cname'].'大于最大限制');
			}
		}elseif(in_array($this->field['datatype'],array('images','files','medias','flashs'))){
			if($counts = $this->newvalue ? count($this->newvalue) : 0){
				if($min && $counts < $min) $this->halt($this->field['cname'].' 附件数量小于最小限制');
				if($max && $counts > $max) $this->newvalue = array_slice($this->newvalue,0,$max,TRUE);
			}
			$this->newvalue = $this->newvalue ? addslashes(serialize($this->newvalue)) : '';
		}elseif($this->field['datatype'] == 'vote'){
			$this->oldvalue = empty($this->oldvalue) ? array() : unserialize($this->oldvalue);
			$i = 0;
			if(!empty($this->newvalue) && is_array($this->newvalue)){
				foreach($this->newvalue as $k => $v){
					if(!$min || $i <= $min){
						$this->newvalue[$k]['totalnum'] = empty($this->oldvalue[$k]['totalnum']) ? 0 : $this->oldvalue[$k]['totalnum'];
						$ii = 0;
						foreach($v['options'] as $x => $y){
							if(!$max || $ii <= $max){
								$this->newvalue[$k]['options'][$x]['votenum'] = empty($this->newvalue[$k]['options'][$x]['votenum']) ? 0 : $this->newvalue[$k]['options'][$x]['votenum'];
								$ii ++;
							}else unset($this->newvalue[$k]['options'][$x]);
						}
						$i ++;
					}else unset($this->newvalue[$k]);
				}
			}else $this->newvalue = '';
			$this->newvalue = empty($this->newvalue) ? '' : addslashes(serialize($this->newvalue));
		}elseif($this->field['datatype'] == 'texts'){
			$cfg = array();
			if($this->field['innertext'] && $temps = explode("\n",$this->field['innertext'])){
				foreach($temps as $k => $v){
					if(($v = explode('|',$v)) && $v[0]) $cfg[$k] = array(0 => $v[0],1 => empty($v[1]) ? 0 : max(0,intval($v[1])),2 => empty($v[2]) ? 0 : max(0,intval($v[2])));
				}
			}
			unset($temps);
			$i = 0;
			foreach($this->newvalue as $k => $v){
				if(!$max || $i <= $max){
					foreach($cfg as $x => $y) $this->newvalue[$k][$x] = empty($this->newvalue[$k][$x]) ? '' : strip_tags(trim($this->newvalue[$k][$x]));
					$i ++;
				}else unset($this->newvalue[$k]);
			}
			$this->newvalue = empty($this->newvalue) ? '' : addslashes(serialize($this->newvalue));
		}
		return;
	}
	
	// 正则格式数据检测
    public function check_regular(){
		$regular = $this->field['regular']; 
		if($this->newvalue && $regular){
			if(!preg_match($regular, $this->newvalue)) $this->halt("[".$this->field['cname']."]数据格式不对！");
		}
	}
	function check_null(){
		// select mselect cacc 不让０通过认证；其他可以
		if($this->field['notnull']){
			switch($this->field['datatype']){
				case 'select':				
				case 'mselect':
				case 'cacc':
				empty($this->newvalue) && $this->halt($this->field['cname'].'不能为空');
				break;
				default :
				strlen($this->newvalue)==0 && $this->halt($this->field['cname'].'不能为空');
			}
		}
		return;
	}
    
    /**
     * 判断是提交的数据是否有非法字符
     */
    public function check_safe_str()
    {
        // 暂时只对非后台操作判断
        if ( !defined('M_ADMIN') && isset($this->field['filter']) )
        {
            if( ((int)$this->field['filter'] === 1) && !cls_string::isSafeStr($this->newvalue))
            {
			     $this->halt($this->field['cname'].'存在非法字符');
            }
		
			/*$this->halt($this->field['cname'].'存在非法字符' . 
            (defined('M_ADMIN') ? "，如果不想过滤该值可在：<br />网站架构 -> 文档模型 -> 文档模型管理 -> 选择相应的模型 -> 字段 -> 选择字段名称为 “{$this->field['cname']}” 的详情项 -> 把 “提交前过滤” 设置为不过滤" : ''));*/
		}
    }
    
	function check_limit(){
		$mlimit = $this->field['mlimit'];
		if($this->field['datatype'] == 'date'){
			$mlimit = 'date';
		}elseif($this->field['datatype'] == 'int'){
			$mlimit = 'int';
		}elseif($this->field['datatype'] == 'float'){
			$mlimit = 'number';
		}
		if(empty($this->newvalue) || empty($mlimit)) return;
		switch($mlimit){
			case 'date':
				if(!preg_match("/^\d{10,}$/",$this->newvalue) && !cls_string::isDate($this->newvalue, $this->field['mode'])){ # 兼容直接传入时间戳(非日期选择器输入)
					$this->halt("{$this->field['cname']} 限输入日期");
				}
			break;
			case 'int':
				if(!is_numeric($this->newvalue)){
					$this->halt("{$this->field['cname']} 限输入整数");
				}
			break;
			case 'number':
				if(!is_numeric($this->newvalue)){
					$this->halt("{$this->field['cname']} 限输入数字");
				}
			break;
			case 'letter':
				if(!preg_match("/^[a-z]+$/i",$this->newvalue)){
					$this->halt("{$this->field['cname']} 限输入字母");
				}			
			break;
			case 'numberletter':
				if(!preg_match("/^[0-9a-z]+$/i",$this->newvalue)){
					$this->halt("{$this->field['cname']} 限输入字母与数字");
				}
			break;
			case 'tagtype':
				if(!preg_match("/^[a-z]+\w*$/i",$this->newvalue)){
					$this->halt("{$this->field['cname']} 限输入字母开头的_字母数字");
				}			
			break;
			case 'date':
				if(!cls_string::isEmail($this->newvalue)){
					$this->halt("{$this->field['cname']} 限输入Email");
				}			
			break;
		}
	}
	function end_deal(){
		if(!empty($this->newvalue)){
			if($this->field['datatype'] == 'date'){
				if(!preg_match("/^\d{10,}$/",$this->newvalue)){ # 兼容直接传入时间戳(非日期选择器输入)
					$this->newvalue = strtotime($this->newvalue);
				}
			}elseif($this->field['datatype'] == 'htmltext'){
				cls_url::html_atm2tag($this->newvalue);
			}
		}
		return;
	}
	function make_submitstr($varname=''){//需要当前值，单个图片可以处理，图集不要处理了,需要返回错误控件的焦点
		foreach(array('datatype','notnull','mlimit','regular','min','max',) as $var) $$var = $this->field[$var];
		//if(in_array($datatype,array('mselect'))) return; //'select',
		if(in_array($datatype,array('images','flashs','medias','files'))){
			$extmode = substr($datatype,0,strlen($datatype)-1);
		}elseif(in_array($datatype,array('image','flash','media','file'))) $extmode = $datatype;
		$exts = '';
		if(!empty($extmode)){
			$localfiles = cls_cache::Read('localfiles');
			$exts = implode(',',array_keys($localfiles[$extmode]));
		}
		if(!$notnull && !$mlimit && !$regular && !$min && !$max && !$exts && !in_array($datatype,array('date','int','float'))) return;
		$regular = str_replace('"', '&quot;', $regular);
		if(in_array($datatype,array('image','flash','media','file'))){
			$this->submitstr = " rule=\"adj\" must=\"$notnull\" exts=\"$exts\" offset=\"1\"";
		}elseif(in_array($datatype,array('images','flashs','medias','files'))){
			$this->submitstr = " rule=\"adjs\" must=\"$notnull\" exts=\"$exts\" min=\"$min\" max=\"$max\" offset=\"1\"";
		}elseif($datatype == 'htmltext'){
			$this->submitstr = " rule=\"html\" must=\"$notnull\" vid=\"$varname\" min=\"$min\" max=\"$max\"";
		}elseif($datatype == 'multitext'){
			$this->submitstr = " rule=\"text\" must=\"$notnull\" min=\"$min\" max=\"$max\"";
		}elseif($datatype == 'text'){
			$this->submitstr = " rule=\"text\" must=\"$notnull\" mode=\"$mlimit\" regx=\"$regular\" min=\"$min\" max=\"$max\"";
		}elseif($datatype == 'select'){ 
			$this->submitstr = " rule=\"must\"";
		}elseif($datatype == 'mselect'){ 
			$this->submitstr = " rule=\"must\"";
		}elseif($datatype == 'date'){
			$this->submitstr = " rule=\"date\" must=\"$notnull\" min=\"$min\" max=\"$max\"";
		}elseif($datatype == 'int'){
			$this->submitstr = " rule=\"int\" must=\"$notnull\" regx=\"$regular\" min=\"$min\" max=\"$max\"";
		}elseif($datatype == 'float'){
			$this->submitstr = " rule=\"float\" must=\"$notnull\" regx=\"$regular\" min=\"$min\" max=\"$max\"";
		}elseif(in_array($datatype,array('cacc','map'))){//,'vote'
			$this->submitstr = "";
		}elseif($datatype == 'texts'){
			empty($min) && $min = 0;
			empty($max) && $max = 0;
			$mode = str_replace(array("\r\n", "\r", "\n", '"'), array('&#10;', '&#10;', '&#10;', '&quot;'), $this->field['innertext']);
			$this->submitstr = " rule=\"texts\" must=\"$notnull\" vid=\"$varname\" exts=\"$mode\" min=\"$min\" max=\"$max\"";
		}
		$this->submitstr .= ' rev="' . str_replace('"', '&quot;', $this->field['cname']) . '"';
	}
	function _varname($varpre = ''){//返回字段的表单元素名称
		if(!$varpre){//前缀为空
			$re = $this->field['ename'];
		}elseif($varpre{0} == '_'){//如为_xx_，则返回xx_ename
			$re = substr($varpre,1).$this->field['ename'];
		}else{//数组格式
			$re = $varpre.'['.$this->field['ename'].']';
		}
		return $re;
	}
	function _PostValue($varpre=''){
		if(!$varpre || $varpre{0} == '_'){
			$varname = $this->_varname($varpre);
			return $GLOBALS[$varname];
		}else{
			$var = $GLOBALS[$varpre];
			return isset($var[$this->field['ename']]) ? $var[$this->field['ename']] : '';
		}
	}
	
	public static function field_votes($fname,$type,$id,$onlyvote = 1){
		global $db,$tblprefix;
		$arr = array(
			'archives' => array('fields','aid','chid'),
			'members' => array('mfields','mid','mchid'),
			'farchives' => array('ffields','aid','chid'),
			'catalogs' => array('cnfields','caid',0),
			'coclass' => array('cnfields','ccid',1),
			);
	
		if(!$fname || !$type || !$arr[$type][0] || !$id)  return array();
		$tbl = $type == 'archives' ? atbl($id,2) : $type;
		if(!$tbl || !($item = $db->fetch_one("SELECT * FROM {$tblprefix}$tbl WHERE ".$arr[$type][1]."='$id'")))  return array();
	
		$typeid = $arr[$type][2] ? $item[$arr[$type][2]] : '';
		$fields = cls_cache::Read($arr[$type][0],$typeid);
		if(!($field = @$fields[$fname]) || $field['datatype'] != 'vote') return array();
	
		$needadd = true;
		if($type == 'archives' && !$field['iscommon']){
			$tbl = $type."_$typeid";
		}elseif($type == 'members'){
			$tbl = $type.($field['iscommon'] ? '_sub' : "_$typeid");
		}elseif($type == 'farchives'){
			$tbl = $type."_$typeid";
		}else $needadd = false;
		if($needadd && $r = $db->fetch_one("SELECT * FROM {$tblprefix}$tbl WHERE ".$arr[$type][1]."='$id'")) $item += $r;
		return empty($item[$fname]) || !($votes = @unserialize($item[$fname])) ? array() : ($onlyvote ? $votes : $item);
	}
	
	
	
	
}
?>