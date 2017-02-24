<?php
/**
 *  memsearchsbase.cls.php 会员列表中搜索项目的处理基类	 
 *处理各个搜索项的筛选值，展示，及SQL 此类为extend_example/libs/xxxx/memsearch.cls.php的基类
 *  
 *
 * @copyright			(C) 2008-2013 08cms
 * @license				http://www.08cms.com/license/
 * @lastmodify			2013-2-23
 */
!defined('M_COM') && exit('No Permisson');
class cls_memsearchsbase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放
	public $cfgs = array();//搜索项配置
	public $nvalues = array();//当前筛选值
	public $wheres = array();//列项目配置
	public $filters = array();//url中字串
	public $htmls	= array();//搜索区域的html代码
	public $orderby = '';//排序字串
	public $no_list = false;//因为权限等原因，查询内容需要为空
	
    function __construct($cfg = array()){
		
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;	
		if(empty($this->A['orderby'])) $this->A['orderby'] = "{$this->A['pre']}mid DESC";
		$this->orderby = $this->A['orderby'];
		
		//检查管理后台权限(管理角色中指定允许管理的模型)
		if(!$this->mc){
			global $a_mchids;
			if(!empty($this->A['mchid'])){//指定模型时
				if(!array_intersect(array(-1,$this->A['mchid']),$a_mchids)) $this->no_list = 1;
				else $this->wheres['mchid'] = "{$this->A['pre']}mchid='{$this->A['mchid']}'";
			}elseif(empty($a_mchids)){//管理角色中未设置可管理的模型
				$this->no_list = 1;
			}elseif(!in_array(-1,$a_mchids) && $a_mchids){//只取允许管理的模型显示出来
				$this->wheres['mchid'] = "{$this->A['pre']}mchid ".multi_str($a_mchids);
			}
		}
	}
	
	
	/**
	* 添加筛选操作项目
	*
	* @ex $oL->s_additem('keyword',array('fields' => array(),));
	*
	* @param    string     $key  项目关键字 可以自己定制项目，但要定制相应方法，也可以是以下值 等等
						keyword：搜索关键字
						checked：审核搜索
						indays：几天内搜索
						outdays：几天前搜索
						nchid: 搜索会员模型
						mctid: 认证搜索
	* @param    array      $cfg  项目配置参数 可选，默认为空 
						type：定义类型之后，会启用function type_{type}()来处理,other,ugid,field方法
						fields：搜索文档模型特定的数据库字段，可联合关键字搜索，一般为标题，会员，文档ID
						
	*/
	public function additem($key = '',$cfg = array()){//可追加$key、$cfg之外的传参
		if(!$key) return false;
		if(!isset($cfg['pre'])) $cfg['pre'] = $this->A['pre'];
		$this->cfgs[$key] = $cfg;
		$args = array_slice(func_get_args(),2);//key,cfg之后的参数传入后续方法中
		if(!$this->call_method("user_$key",$args)){//定制方法
			if('ugid' == substr($key,0,4)){//通用的类系方法
				$type = 'ugid';
			}elseif(!empty($this->cfgs[$key]['type'])){//定义了type的方法
				$type = $this->cfgs[$key]['type'];
			}else $type = 'other';//只处理隐含的传参
			$this->call_method("type_$type",array($key) + $args);
		}
	}
	
	protected function call_method($func,$args = array()){//可以额外增加传参
		if(method_exists($this,$func)){
			call_user_func_array(array(&$this,$func),$args);
			return true;
		}else return false;
	}
	
	protected function del_item($key){
		unset($this->cfgs[$key]);
		return false;
	}	
	
	protected function type_field($key){
		$field = cls_cache::Read('mfield',$this->A['mchid'],$key);
		if(!$field || !in_array($field['datatype'],array('select','mselect','cacc',))) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		$a_field = new cls_field;
		$field['issearch'] = 1;//强制为可搜索字段
		$a_field->init($field,@$GLOBALS[$key]);
		$a_field->deal_search($cfg['pre']);
		if(!empty($a_field->ft)) $this->filters += $a_field->ft;
		if(!empty($a_field->searchstr)) $this->wheres[$key] = $a_field->searchstr;
		unset($a_field);
		
		if(empty($cfg['hidden'])){
			$sarr = cls_field::options_simple($field,array('blank' => '&nbsp; &nbsp; '));
			$title = empty($cfg['title']) ? "-{$field['cname']}-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('' => $title) + $sarr,@$GLOBALS[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,@$GLOBALS[$key]);
	}
	
	// 按会员组搜索，排除管理组系
	// skip：不输出html参数; eg: $oL->s_additem("ugid3",array('skip'=>1)); 
	protected function type_ugid($key){
		$grouptypes = cls_cache::Read('grouptypes');
		$gtid = max(0,intval(str_replace('ugid','',$key)));
		if(!$gtid || ($gtid== 2) || empty($grouptypes[$gtid])) return $this->del_item($key);
		if(!($ugidsarr = ugidsarr($gtid,$this->A['mchid'],1))) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);

		if(!empty($this->nvalue[$key])){//针对性处理wherestr
			$this->wheres[$key] = "{$cfg['pre']}grouptype{$gtid}='{$this->nvalue[$key]}'";
		}
		if(!empty($cfg['skip'])) return; // 不输出html(skip)处理
		if(empty($cfg['hidden'])){
			$grouptypes = cls_cache::Read('grouptypes');
			$title = empty($cfg['title']) ? "-{$grouptypes[$gtid]['cname']}-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('0' => $title) + $ugidsarr,$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	
	// ids：指定允许列出的id（用于关联） $oL->s_additem("ccid3",array('ids'=>$ccid3s));
	// skip：不输出html参数; eg: $oL->s_additem("ccid3",array('skip'=>1)); 
	// self：设为1时处理自动类系，否则默认为排除自动类系，
	//custom 自定义字段 用于自定义字段关联类系的情况
	private function type_ccid_final($key,$custom){
		$cotypes = cls_cache::Read('cotypes');
		$coid = max(0,intval(str_replace('ccid','',$key))); //关联类系的配置
		
		empty($custom) && $custom = $key;
		
		if(!$coid || empty($cotypes[$coid])) return $this->del_item($custom);
		
		$cfg = &$this->cfgs[$custom]; //自定义字段的配置
		if(empty($cfg['self']) && $cotypes[$coid]['self_reg']) return $this->del_item($custom);
		
		$this->init_item($custom,'int+',1);

		if(!empty($this->nvalue[$custom]) && $ccids = sonbycoid($this->nvalue[$custom],$coid)){//针对性处理wherestr
			if($cnsql = cnsql($coid,$ccids,$cfg['pre'])) $this->wheres[$custom] = $cnsql;
		}

		if(!empty($cfg['skip'])) return; // 不输出html(skip)处理
		if(empty($cfg['hidden'])){
			isset($cotypes[$coid]['cname']) || $cotypes[$coid]['cname']='';
			$title = empty($cfg['title']) ? "-{$cotypes[$coid]['cname']}-" : $cfg['title'];
			$ids = empty($cfg['ids']) ? array() : $cfg['ids']; //ids：指定的类系ID
			$this->htmls[$custom] = '<span>'.cn_select($custom,array(
			'value' => $this->nvalue[$custom],
			'coid' => $coid,
			'notip' => 1,
			'addstr' => $title,
			'vmode' => 0,
			'framein' => 1,
			'ids' =>$ids,)).'</span> ';	
		}else $this->htmls[$custom] = $this->input_hidden($custom,$this->nvalue[$custom]);
	}
	
	protected function type_other($key){
		$field = empty($this->A['fields']) ? cls_cache::Read('mfield',$this->A['mchid'],$key) : @$this->A['fields'][$key];
		//自定义字段关联类系处理
		if( $field['coid'] && $field['datatype'] == 'cacc'){
			 $coid = max(0,intval($field['coid']));
			 return $this->type_ccid_final("ccid$coid",$key);
		}
		//按整数，隐藏域处理
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int');
		$this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	//会员模型筛选，指定模型时无效
	//ids :array类型 需要筛选的mchid  $oL->s_additem("nchid",array('ids'=>array()));
	protected function user_nchid(){
		$key = substr(__FUNCTION__,5);
		if($this->A['mchid'] || !$mchidsarr = cls_mchannel::mchidsarr()) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		
		if(!empty($this->nvalue[$key])){//针对性处理wherestr
			$this->wheres[$key] = "{$cfg['pre']}mchid='{$this->nvalue[$key]}'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-会员类型-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('0' => $title) + $mchidsarr,$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_mctid(){//会员认证
		$key = substr(__FUNCTION__,5);
		if(!$mctidsarr = $this->mctidsarr()) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		
		if(!empty($this->nvalue[$key]) && isset($mctidsarr[$this->nvalue[$key]])){//针对性处理wherestr
			$this->wheres[$key] = "{$cfg['pre']}mctid{$this->nvalue[$key]}<>0";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-会员认证-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('0' => $title) + $mctidsarr,$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_checked(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1');
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-审核-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'0' => '未审','1' => '已审',),$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_orderby(){//可以传入$cfg['options']
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);
		if(empty($cfg['options'])){
			$title = empty($cfg['title']) ? "-默认排序-" : $cfg['title'];
			$cfg['options'] = array(
				0 => array($title,$this->A['orderby']),
				1 => array('上次登录时间',$cfg['pre'].'lastvisit DESC'),
				2 => array('上次活动时间',$cfg['pre'].'lastactive DESC'),
			);
		}
		$sarr = array();
		foreach($cfg['options'] as $k => $v){
			$sarr[$k] = $v[0];
			if($this->nvalue[$key] == $k) $this->orderby = $v[1];
		}
		if(empty($cfg['hidden'])){
			$this->htmls[$key] = $this->input_select($key,$sarr,$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_indays(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		if($this->nvalue[$key]){//针对性处理wherestr
			global $timestamp;
			$this->wheres[$key] = $cfg['pre']."regdate>'".($timestamp - 86400 * $this->nvalue[$key])."'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "天内" : $cfg['title'];
			$this->htmls[$key] = $this->input_text($key,$this->nvalue[$key],'注册时间',2).$title;
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_outdays(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		if($this->nvalue[$key]){//针对性处理wherestr
			global $timestamp;
			$this->wheres[$key] = $cfg['pre']."regdate<'".($timestamp - 86400 * $this->nvalue[$key])."'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "天前" : $cfg['title'];
			$this->htmls[$key] = $this->input_text($key,$this->nvalue[$key],'注册时间',2).$title;
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	//fields：传入搜索字段(需包含表前缀)，多个字段使用数组传入
	protected function user_keyword(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'keyword',1);
		
		if(empty($cfg['fields'])){
			$fields = array($cfg['pre'].'mname' => '会员帐号',$cfg['pre'].'mid' => '会员ID');
		}else $fields = $cfg['fields'];
		
		$mode_key = "mode_{$key}";
		if(!empty($this->nvalue[$key])){//针对性处理wherestr
			$i = 0;
			foreach($fields as $k => $v){
				if($i++ == $this->nvalue[$mode_key]){ 
					$this->wheres[$key] = $k=="m.mid" ? $k."=".intval($this->nvalue[$key])."" : $k.sqlkw($this->nvalue[$key]); 
				}
			}
		}
		$narr = array();$i = 0;
		foreach($fields as $k => $v) $narr[$i++] = $v;
		$this->htmls[$key] = $this->input_select($mode_key,$narr,empty($this->nvalue[$mode_key]) ? 0 : $this->nvalue[$mode_key]);
		$this->htmls[$key] .= $this->input_text($key,$this->nvalue[$key],'搜索',10);
	}
	
	//$nowhere=1时，不要处理wheres
	protected function init_item($key,$type = '',$nowhere = 0){
		$cfg = &$this->cfgs[$key];
		switch($type){
			case 'int+'://正整数
				$this->nvalue[$key] = $GLOBALS[$key] = empty($GLOBALS[$key]) ? 0 : intval($GLOBALS[$key]);
				if($this->nvalue[$key]){
					$this->filters[$key] = $this->nvalue[$key];
					$nowhere || $this->wheres[$key] = "{$cfg['pre']}$key='{$this->nvalue[$key]}'";
				}
				break;
			case 'int-1'://不设置为-1，否则为整数
				$this->nvalue[$key] = $GLOBALS[$key] = isset($GLOBALS[$key]) ? intval($GLOBALS[$key]) : -1;
				if($this->nvalue[$key] != -1){
					$this->filters[$key] = $this->nvalue[$key];
					$nowhere || $this->wheres[$key] = "{$cfg['pre']}$key='{$this->nvalue[$key]}'";
				}
				break;
			case 'keyword'://按模糊搜索的关键词
				$this->nvalue[$key] = $GLOBALS[$key] = empty($GLOBALS[$key]) ? '' : trim($GLOBALS[$key]);
				if($this->nvalue[$key]) $this->filters[$key] = stripslashes($this->nvalue[$key]);
				$mode_key = "mode_$key";
				$this->nvalue[$mode_key] = $GLOBALS[$mode_key] = empty($GLOBALS[$mode_key]) ? 0 : intval($GLOBALS[$mode_key]);
				if($this->nvalue[$mode_key]) $this->filters[$mode_key] = $this->nvalue[$mode_key];
				break;
			case 'str'://字串精确匹配
				$this->nvalue[$key] = $GLOBALS[$key] = empty($GLOBALS[$key]) ? '' : trim($GLOBALS[$key]);
				if($this->nvalue[$key]){
					$this->filters[$key] = stripslashes($this->nvalue[$key]);
					$nowhere || $this->wheres[$key] = "{$cfg['pre']}$key='{$this->nvalue[$key]}'";
				}
				break;
			case 'int'://整数
				$this->nvalue[$key] = $GLOBALS[$key] = empty($GLOBALS[$key]) ? 0 : intval($GLOBALS[$key]);
				if($this->nvalue[$key]){
					$this->filters[$key] = $this->nvalue[$key];
					$nowhere || $this->wheres[$key] = "{$cfg['pre']}$key='{$this->nvalue[$key]}'";
				}
				break;
		}
	}
	
	protected function input_checkbox($name = '',$sarr = array(),$value = '',$ppr = 0){
		$re = '';$i = 0;
		foreach($sarr as $k => $v){
			$checked = in_array($k,$value) ? 'checked' : '';
			$re .= "<input class=\"checkbox\" type=\"checkbox\" name=\"{$name}[]\" value=\"$k\" $checked>$v";
			$re .= $ppr && !(++$i % $ppr) ?  '<br />' : '';
		}
		return $re;
	}	
	protected function input_text($name = '',$value = '',$title = '',$size = 4){
		return "<input class=\"text\" name=\"$name\" type=\"text\" value=\"$value\" size=\"$size\" style=\"vertical-align: middle;\"".($title ? " title=\"$title\"" : '').">\n";
	}	
	protected function input_select($name = '',$sarr = array(),$value = ''){
		return "<select style=\"vertical-align: middle;\" name=\"$name\">".makeoption($sarr,$value)."</select>\n";
	}
	protected function input_hidden($name = '',$value = ''){
		return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";
	}	
	
	protected function mctidsarr(){
		$mctypes = cls_cache::Read('mctypes');
		$re = array();
		foreach($mctypes as $k => $v){
			if(empty($this->A['mchid']) || in_array($this->A['mchid'],explode(',',$v['mchids']))){
				$re[$k] = $v['cname'];
			}
		}
		return $re;
	}
	
}
