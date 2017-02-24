<?php
/*
** 列表中搜索项目的处理类
** 处理各个搜索项的筛选值，展示，及SQL
** 此类为extend_example/libs/xxxx/pushsearchs.cls.php的基类
*/
!defined('M_COM') && exit('No Permisson');
class cls_pushsearchsbase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放
	public $cfgs = array();//搜索项配置
	public $nvalues = array();//当前筛选值
	public $wheres = array();//列项目配置
	public $filters = array();//url中字串
	public $htmls	= array();//搜索区域的html代码
	public $orderby = '';//排序字串
	
    function __construct($cfg = array()){
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;	
		if(empty($this->A['paid']) || !($this->area = cls_PushArea::Config($this->A['paid']))) exit('请指定推送位');
		if(empty($this->A['orderby'])){
			$this->A['orderby'] = "{$this->A['pre']}trueorder,{$this->A['pre']}pushid DESC";
		}
		$this->orderby = $this->A['orderby'];
	}
	
	public function additem($key = '',$cfg = array()){//可追加$key、$cfg之外的传参
		if(!$key) return false;
		if(!isset($cfg['pre'])) $cfg['pre'] = $this->A['pre'];
		$this->cfgs[$key] = $cfg;
		$args = array_slice(func_get_args(),2);//key,cfg之后的参数传入后续方法中
		if(!$this->call_method("user_$key",$args)){//定制方法
			if(!empty($this->cfgs[$key]['type'])){//定义了type的方法
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
		$field = empty($this->A['fields']) ? cls_PushArea::Field($this->A['paid'],$key) : @$this->A['fields'][$key];
		if(!$field || !in_array($field['datatype'],array('select','mselect','cacc',))) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		$a_field = new cls_field;
		$field['issearch'] = 1;//强制为可搜索字段
		$a_field->init($field,@$GLOBALS[$key]);
		$a_field->deal_search($cfg['pre']);
		// 处理栏目/类系(可搜索子类别)
		if($a_field->field['datatype']=='cacc'){ // && empty($a_field->field['coid'])
			$coid = $a_field->field['coid']; 
			$fname = empty($coid) ? 'caid' : "ccid$coid";
			$cnsql = cnsql($coid,sonbycoid(@$a_field->oldvalue,$coid),''); 
			$cnsql && $a_field->searchstr = $cfg['pre'].str_replace($fname,$a_field->field['ename'],$cnsql);	
		} 
		if(!empty($a_field->ft)) $this->filters += $a_field->ft;
		if(!empty($a_field->searchstr)) $this->wheres[$key] = $a_field->searchstr;
		unset($a_field);
		
		if(empty($cfg['hidden'])){
			$sarr = cls_field::options_simple($field,array('blank' => '&nbsp; &nbsp; '));
			$title = empty($cfg['title']) ? "-{$field['cname']}-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('' => $title) + $sarr,@$GLOBALS[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,@$GLOBALS[$key]);
	}
	
	protected function type_other($key){
		//按整数，隐藏域处理
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int');
		$this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
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
	protected function user_valid(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1',1);
		if($this->nvalue[$key] != -1){//针对性处理wherestr
			global $timestamp;
			if($this->nvalue[$key]){
				$this->wheres[$key] = "{$cfg['pre']}checked=1 AND {$cfg['pre']}startdate<'$timestamp' AND ({$cfg['pre']}enddate='0' OR {$cfg['pre']}enddate>'$timestamp')";
			}else{
				$this->wheres[$key] = "({$cfg['pre']}checked=0 OR ({$cfg['pre']}startdate>'$timestamp') OR ({$cfg['pre']}enddate>'0' AND {$cfg['pre']}enddate<'$timestamp'))";
			}
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-有效-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'0' => '无效','1' => '有效',),$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	protected function user_loadtype(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1',1);
		if($this->nvalue[$key] != -1){//针对性处理wherestr
			//0.列表加载, 11.手动添加, 21.自动推送
			$this->wheres[$key] = "loadtype='{$this->nvalue[$key]}' ";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-来源类型-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'0' => '手动推送','11' => '手动添加','21' => '自动推送',),$this->nvalue[$key]);
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
				1 => array('按推送时间',$cfg['pre'].'createdate DESC'),
				2 => array('按信息id',$cfg['pre'].'pushid DESC'),
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
			$this->wheres[$key] .= $cfg['pre']."createdate>'".($timestamp - 86400 * $this->nvalue[$key])."'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "天内" : $cfg['title'];
			$this->htmls[$key] = $this->input_text($key,$this->nvalue[$key],'',2).$title;
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_outdays(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		if($this->nvalue[$key]){//针对性处理wherestr
			global $timestamp;
			$this->wheres[$key] .= $cfg['pre']."createdate<'".($timestamp - 86400 * $this->nvalue[$key])."'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "天前" : $cfg['title'];
			$this->htmls[$key] = $this->input_text($key,$this->nvalue[$key],'',2).$title;
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	//fields：传入搜索字段(需包含表前缀)，多个字段使用数组传入
	protected function user_keyword(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'keyword',1);
		
		if(empty($cfg['fields'])){
			$fields = array($cfg['pre'].'subject' => '标题',$cfg['pre'].'mname' => '推送者帐号',$cfg['pre'].'fromid' => '来源ID',$cfg['pre'].'pushid' => '信息ID');
		}else $fields = $cfg['fields'];
		
		$mode_key = "mode_{$key}";
		if(!empty($this->nvalue[$key])){//针对性处理wherestr
			$i = 0;
			foreach($fields as $k => $v){
				if($i++ == $this->nvalue[$mode_key]) $this->wheres[$key] = $k.sqlkw($this->nvalue[$key]);
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
	
	
}
