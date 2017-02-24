<?php
/**
 *  arcsearchsbase.cls.php  列表中搜索项目的处理操作基类	 
 *   处理各个搜索项的筛选值，展示，及SQL 此类为extend_example/libs/xxxx/asearch.cls.php的基类
 *
 * @copyright			(C) 2008-2013 08cms
 * @license				http://www.08cms.com/license/
 * @lastmodify			2013-2-23
 */

!defined('M_COM') && exit('No Permisson');
class cls_arcsearchsbase{
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
		if(empty($this->A['orderby'])){
			if($this->A['isab'] == 1){
				if($this->A['abtbl']){
					$this->A['orderby'] = "{$this->A['bpre']}inorder DESC";	
				}else{
					$this->A['orderby'] = "{$this->A['pre']}inorder{$this->A['arid']} DESC";	
				}
			}else $this->A['orderby'] = "{$this->A['pre']}aid DESC";
		}
		$this->orderby = $this->A['orderby'];
	}
	
	
	/**
	* 添加筛选操作项目
	*
	* @ex $oL->s_additem('keyword',array('fields' => array(),));
	*
	* @param    string     $key  项目关键字 可以自己定制项目，但要定制相应方法，也可以是以下值 等等
						keyword：搜索关键字
						caid：搜索栏目
						checked：审核搜索
						valid：有效期搜索
						ccid$k：类系搜索
						orderby：排序搜索
						indays：几天内搜索
						outdays：几天前搜索
						
	* @param    array      $cfg  项目配置参数 可选，默认为空 
						type：定义类型之后，会启用function type_{type}()来处理,other,ccid,field方法
						fields：搜索文档模型特定的数据库字段，可联合关键字搜索，一般为标题，会员，文档ID
						
	*/
	public function additem($key = '',$cfg = array()){//可追加$key、$cfg之外的传参
		if(!$key) return false;
		if(!isset($cfg['pre'])) $cfg['pre'] = $this->A['pre'];
		$this->cfgs[$key] = $cfg;
		$args = array_slice(func_get_args(),2);//key,cfg之后的参数传入后续方法中
		if(!$this->call_method("user_$key",$args)){//定制方法
			if('ccid' == substr($key,0,4)){//通用的类系方法
				$type = 'ccid';
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
		$field = empty($this->A['fields']) ? cls_cache::Read('field',$this->A['chid'],$key) : @$this->A['fields'][$key];
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
	
	// ids：指定允许列出的id（用于关联） $oL->s_additem("ccid3",array('ids'=>$ccid3s));
	// skip：不输出html参数; eg: $oL->s_additem("ccid3",array('skip'=>1)); 
	// self：设为1时处理自动类系，否则默认为排除自动类系，
	protected function type_ccid($key){
		$cotypes = cls_cache::Read('cotypes');
		$coid = max(0,intval(str_replace('ccid','',$key)));
		if(!$coid || empty($cotypes[$coid]) || !in_array($coid,$this->A['coids'])) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['self']) && $cotypes[$coid]['self_reg']) return $this->del_item($key);
		
		$this->init_item($key,'int+',1);

		if(!empty($this->nvalue[$key]) && $ccids = sonbycoid($this->nvalue[$key],$coid)){//针对性处理wherestr
			if($cnsql = cnsql($coid,$ccids,$cfg['pre'])) $this->wheres[$key] = $cnsql;
		}
		if(!empty($cfg['skip'])) return; // 不输出html(skip)处理
		if(empty($cfg['hidden'])){
			isset($cotypes[$coid]['cname']) || $cotypes[$coid]['cname']='';
			$title = empty($cfg['title']) ? "-{$cotypes[$coid]['cname']}-" : $cfg['title'];
			$ids = empty($cfg['ids']) ? array() : $cfg['ids']; //ids：指定的类系ID
			$this->htmls[$key] = '<span>'.cn_select($key,array(
			'value' => $this->nvalue[$key],
			'coid' => $coid,
			'notip' => 1,
			'addstr' => $title,
			'vmode' => 0,
			'framein' => 1,
			'ids' =>$ids,)).'</span> ';	
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function type_other($key){
		//按整数，隐藏域处理
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int');
		$this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	/**
	* 栏目筛选
	*	ncaid 代表当前筛选的栏目key,是系统内置的
	*	caid  代表外部传过来的栏目key
	*/
	 
	protected function user_caid(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		$this->nvalue['ncaid'] = empty($GLOBALS['ncaid']) ? 0 : max(0,intval($GLOBALS['ncaid']));
		if(empty($this->nvalue['ncaid']) && !empty($GLOBALS['caid'])){ 
			$this->nvalue['ncaid'] = $GLOBALS['caid']; //默认定位于（当前项目）
		}
		$this->filters['ncaid'] = $this->nvalue['ncaid']; 
			
		$caids = empty($this->nvalue['ncaid']) ? sonbycoid($this->nvalue[$key]) : sonbycoid($this->nvalue['ncaid']);
		$caids = empty($caids) ? array(-1) : $caids;
		
		$ids = empty($cfg['ids']) ? array() : $cfg['ids']; //指定的栏目ID
		if(!$this->mc && !$this->A['pid']){
			//考虑了管理角色的栏目管理权限
			global $a_caids;
			if(!in_array(-1,$a_caids)) $caids = in_array(-1,$caids) ? $a_caids : array_intersect($caids,$a_caids);
			if(!$caids) $this->no_list = true;
		}
		if(!in_array(-1,$caids) && $cnsql = cnsql(0,$caids,$cfg['pre'])){
			$this->wheres[$key] = $cnsql;
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "--栏目--" : $cfg['title'];
			$this->htmls[$key] = cn_select('ncaid',array(
			'value' => $this->nvalue['ncaid'],
			'chid' => $this->A['chid'],
			'notip' => 1,
			'addstr' => $title,
			'vmode' => 0,
			'framein' => 1,
			'viewp' => -1,//完全隐藏无效类系
			'ids' => $ids,
			));	
			$this->htmls[$key] = '<span>'.$this->htmls[$key].'</span> ';
			$this->htmls[$key] .= $this->input_hidden($key,$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	protected function user_chid(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		
		if(empty($cfg['hidden'])){
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	//ids :array类型 需要筛选的chid  $oL->s_additem("chid",array('ids'=>array()));
	protected function user_nchid(){
		$channels = cls_channel::Config();
		
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		
		$ids = empty($cfg['ids']) ? array() : $cfg['ids']; //ids：指定允许列出的id
		$chidarr = array();
		foreach($channels as $k=>$v) {
			if(!empty($ids))  in_array($k,$ids) && $chidarr[$k] = $v['cname'];
			else $chidarr[$k] = $v['cname'];
		}
		$this->htmls[$key] = "<select style=\"vertical-align: middle;\" name=\"$key\">".makeoption($chidarr,$cfg['chid'])."</select>";
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
	protected function user_inchecked(){
		$key = substr(__FUNCTION__,5);
		if($this->A['isab'] != 1) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1',1);
		if($this->nvalue[$key] != -1){//针对性处理wherestr
			$field = empty($cfg['field']) ? "b.incheck" : $cfg['field'];
			$this->wheres[$key] = "$field='{$this->nvalue[$key]}'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-辑内有效-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'0' => '无效','1' => '有效',),$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	protected function user_valid(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1',1);
		if($this->nvalue[$key] != -1){//针对性处理wherestr
			global $timestamp;
			if($this->nvalue[$key]){
				$this->wheres[$key] = "({$cfg['pre']}enddate='0' OR {$cfg['pre']}enddate>'$timestamp')";
			}else{
				$this->wheres[$key] = "{$cfg['pre']}enddate>'0' AND {$cfg['pre']}enddate<'$timestamp'";
			}
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-有效-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'0' => '无效','1' => '有效',),$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	protected function user_orderby(){//可以传入$cfg['options']
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);
		if(empty($cfg['options'])){
			$title = empty($cfg['title']) ? "-排序方式-" : $cfg['title'];
			$cfg['options'] = array(
				0 => array($title,$this->A['orderby']),
				1 => array('按点击数',$cfg['pre'].'clicks DESC'),
				2 => array('按刷新时间',$cfg['pre'].'refreshdate DESC'),
				3 => array('按添加时间',$cfg['pre'].'createdate DESC'),
				4 => array('按文档排序',$cfg['pre'].'vieworder DESC'),
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
			$this->wheres[$key] = $cfg['pre']."createdate>'".($timestamp - 86400 * $this->nvalue[$key])."'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "天内" : $cfg['title'];
			$title = "<label class='sch_text'>$title</label>";
			$this->htmls[$key] = $this->input_text($key,$this->nvalue[$key],'',2).$title;
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_outdays(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int+',1);
		if($this->nvalue[$key]){//针对性处理wherestr
			global $timestamp;
			$this->wheres[$key] = $cfg['pre']."createdate<'".($timestamp - 86400 * $this->nvalue[$key])."'";
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "天前" : $cfg['title'];
			$title = "<label class='sch_text'>$title</label>";
			$this->htmls[$key] = $this->input_text($key,$this->nvalue[$key],'',2).$title;
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	//fields：传入搜索字段(需包含表前缀)，多个字段使用数组传入
	protected function user_keyword(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'keyword',1);
		
		if(empty($cfg['fields'])){
			if($this->mc) { 
				$fields = array($cfg['pre'].'subject' => '标题',$cfg['pre'].'keywords' => '关键词',$cfg['pre'].'aid' => '文档ID');
			}else { 
				$fields = array($cfg['pre'].'subject' => '标题',$cfg['pre'].'keywords' => '关键词',$cfg['pre'].'aid' => '文档ID',$cfg['pre'].'mname' => '会员');
			}
		}else $fields = $cfg['fields'];
		
		$mode_key = "mode_{$key}";
		if(!empty($this->nvalue[$key])){//针对性处理wherestr
			$i = 0;
			foreach($fields as $k => $v){
				if($i++ == $this->nvalue[$mode_key]){ 
					$this->wheres[$key] = $k=="a.aid" ? $k."=".intval($this->nvalue[$key])."" : $k.sqlkw($this->nvalue[$key]); 
				}
			}
		}
		$narr = array();$i = 0;
		foreach($fields as $k => $v) $narr[$i++] = $v;
		$this->htmls[$key] = $this->input_select($mode_key,$narr,empty($this->nvalue[$mode_key]) ? 0 : $this->nvalue[$mode_key]);
		$this->htmls[$key] .= $this->input_text($key,$this->nvalue[$key],'搜索',10);
	}
	
	// $nowhere=1时，不要处理wheres
	// defval, 默认值，覆盖或替代$GLOBALS[$key]。
	protected function init_item($key,$type = '',$nowhere = 0){
		$cfg = &$this->cfgs[$key];
		if(isset($cfg['defval'])) $GLOBALS[$key] = $cfg['defval'];
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
