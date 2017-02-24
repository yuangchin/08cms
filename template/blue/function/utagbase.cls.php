<?php

// 模版自定义搜索排序类(基类)
class cls_usobase{
	
	static $init_cfg = array(); //模版初始配置
	static $fpath = ''; //由参数ntype获得的路径标记, ntype第一字符
	static $fdata = ''; //由参数ntype获得的数据类型标记, ntype第二字符
	static $nodes = array(); //节点配置
	static $rids_cfg = array(); //关联项配置
	static $rids_data = array(); //关联项数据
	static $rids_for = array();
	static $urls = array(); //搜索urls: cnstr,filterstr,filsearch,filorder等
	static $addno = '0'; //附加页
	static $addstr = ''; //附加页str
	static $csfull = ''; //脚本完整路径如：http://192.168.1.11/auto/mspace/index.php?
	
	/* 初始化-主入口, cfg参数如下：
	  ntype    : 两个字母ab形式如下, a与b自由组合, ca可省略为c, mm可省略为m, c:类目节点/m:会员频道/oa手机类目/om:手机会员/cm类目节点下的会员,详情如下说明
	             a:节点类型及路径 --- c:类目节点[root],  m:会员节点[member],  o:手机节点[mobile],  s:会员空间[mspace]<暂未用>
				 b:列表数据类型   --- m:会员[member],    a:文档[archive],     u:交互[commu]<未用>, c:类目[class]<???暂未用>
	  chid     : 会员或文档模型:chid,mchid
	  nodes    : 表示节点的组合, 栏目:array('caid'), 栏目+ccid8:array('caid','caid,ccid8'), ugid33+ccid20:array('ugid33','ccid20')
	             栏目可缩写为0, 类系可只写数字；如：array('0','0,1','0,2','0,12','0,17','0,18','0,1,17'),
	  orderbys : 可用的排序字段, 如: array('mid','jian','msclicks','authentication'),
	  rids     : 关联项目, 如: array(1,2)
	  filparas : 连接中附加清除的项目, 如: 'carsfullname'
	  addno    : 默认用当前页addno, 有此参数则此参数优先,如首页的搜索区块连接到内页去
	  cnstr:   : 指定参数cnstr,     如:caid=33首页的搜索区块连接到内页去;
	  filterstr: 指定参数filterstr, <暂未用>
	  csname   : 入口文件，index.php可不指定
	  usword   : 关键字searchword参与检索
	*/
	static function init($cfgs){
		cls_uso::$init_cfg = $cfgs;
		$ntype = isset($cfgs['ntype']) ? $cfgs['ntype'] : 'c'; 
		if($ntype=='c') $ntype = 'ca'; //兼容之前
		if($ntype=='m') $ntype = 'mm'; //兼容之前
		cls_uso::$fpath = substr($ntype,0,1);
		cls_uso::$fdata = substr($ntype,-1); 
		$nodes = empty($cfgs['nodes']) ? array('-1') : $cfgs['nodes'];
		foreach($nodes as $k1=>$v1){ //兼容栏目/类系用数字
			$arr2 = explode(',',$v1);
			foreach($arr2 as $k2=>$v2){
				$vx = empty($v2) ? 'caid' : (is_numeric($v2) ? "ccid$v2" : $v2);
				$arr2[$k2] = $vx;
			}
			$nodes[$k1] = implode(',',$arr2);
		}
		cls_uso::$nodes = $nodes;
		cls_uso::$rids_cfg = cls_uso::$rids_data = cls_uso::$urls = array(); //addno,addstr在init_urls()中初始化
		cls_uso::$csfull = '';
		if(isset($cfgs['orderbys'])) cls_usql::order_bys(); //排序字段检查
		cls_uso::init_rids(); //类目关联检查
		cls_uso::init_urls(); //urls初始化
	}
	
	// 处理关联类系
	static function init_rids(){
		if(empty(cls_uso::$init_cfg['rids'])) return;
		$rids = cls_uso::$init_cfg['rids'];
		$rarr = array(); cls_uso::$rids_for[] = -1; //默认一个,用于in_array()比较
		foreach($rids as $rid){
			$cnrel = cls_cache::Read('cnrel', $rid);
			if(empty($cnrel)) return;
			$coid = $cnrel['coid'];
			$coid1 = $cnrel['coid1'];
			cls_uso::$rids_cfg[$coid1] = $coid;
			foreach($cnrel['cfgs'] as $k=>$v){
				$rarr["ccid{$coid}_$k"] = $v; //格式如：ccid20_1296=11,22,33, 这样组为了下面好用而已
			}
		} 
		cls_uso::$rids_data = $rarr;
		// 处理子类别? 这里未实现; 需要时再考虑
	}
	
	// 处理初始常用的url
	static function init_urls(){
		$_da = cls_Parse::Get('_da'); 
		foreach(array('cnstr','filterstr') as $k){
			$$k = cls_uso::$urls[$k] = isset(cls_uso::$init_cfg[$k]) ? cls_uso::$init_cfg[$k] : (isset($_da[$k]) ? $_da[$k] : '');
		} 
		if(!empty($filterstr)){ //清楚空项目; &ccid8=0&ccid23=0&ccid11=1246&ccid22=0&ccid20=0
			//$filterstr = preg_replace("/&(\w*)=[0]{1}/", '', $filterstr); //? &iscorp=0(非商家,个人)要保留
			$filterstr = preg_replace("/&(ccid\d+)=[0]{1}/", '', $filterstr); //? &ccid999=0(空类系)要去掉
			$filterstr = preg_replace("/&(\w*)=&/", '&', $filterstr);
			cls_uso::$urls['filterstr'] = $filterstr;
		} 
		$filorder = "orderby|ordermode|page";
		$filparas = empty(cls_uso::$init_cfg['usword']) ? "$filorder|searchword" : $filorder; 
		if(!empty(cls_uso::$init_cfg['filparas'])) $filparas .= "|".cls_uso::$init_cfg['filparas']."";
		$prego = "/&(?:$filorder)=[^&]*|\b(?:$filorder)=[^&]*&?/"; //[filterstr]去掉orderby|ordermode|page因素
		$pregs = "/&(?:$filparas)=[^&]*|\b(?:$filparas)=[^&]*&?/"; //[filterstr]去掉order相关及搜索关键字因素
		cls_uso::$urls['filorder'] = cls_uso::$urls['cnstr'].(empty($filterstr) ? '' : preg_replace($prego, '', $filterstr)); 
		cls_uso::$urls['filsearch'] = cls_uso::$urls['cnstr'].(empty($filterstr) ? '' : preg_replace($pregs, '', $filterstr)); 
		cls_uso::$urls['fullurl'] = cls_uso::$urls['cnstr'].$filterstr;
		cls_uso::$addno = $addno = empty(cls_uso::$init_cfg['addno']) ? (isset($_da['addno']) ? $_da['addno'] : 0) : cls_uso::$init_cfg['addno']; 
		cls_uso::$addstr = empty($addno) ? '' : "&addno=$addno"; 
	}

	// 检查节点
	static function node_check($cnstr){
		if(defined('IN_MOBILE')) return false;
		parse_str($cnstr,$a); 
		$b = $c = array();
		foreach($a as $k=>$v){
			if($k=='addno') continue;
			if($k=='caid'){
				$key = '0';
			}elseif(substr($k,0,4)=='ccid'){
				$key = substr($k,4);
			}elseif($k=='ugid'){
				$key = $k;
			}else{ //字段,不是节点
				return false;	
			}
			$b[$key] = $v;
			$c[$k] = $v;
		}
		ksort($b,SORT_NUMERIC); 
		$keys = array_keys($b); 
		$cnkey = ''; $cnstr = ''; 
		foreach($keys as $k){
			$key = empty($k) ? 'caid' : (is_numeric($k) ? "ccid$k" : $k);
			$cnkey .= (empty($cnkey) ? '' : ',').$key;
			$cnstr .= (empty($cnstr) ? '' : '&')."$key=".@$c[$key];
		} //print_r($cnkey); print_r(cls_uso::$nodes);
		if(in_array($cnkey,cls_uso::$nodes)){  
			if(cls_uso::$fpath=='m'){
				$node = cls_node::mcnodearr($cnstr); 
				$url = $node["mcnurl".(empty(cls_uso::$addno) ? '' : cls_uso::$addno)];
			}elseif(in_array(cls_uso::$fpath,array('c','o'))){
				$node = cls_node::cnodearr($cnstr, defined('IN_MOBILE') ? 1 : 0);
				$url = @$node["indexurl".(empty(cls_uso::$addno) ? '' : cls_uso::$addno)];
			}
			return $url;
		}else{
			return false;		
		}
	}
	
	/* 排序样式及连接html，可能需要自已照着改
	  $tpl 链接模版
	  $by 要排序的字段
	  $class 样式名称 如：array('当前降序','当前升序','未选中样式')
	  $class['defmode']=1, 默认的ordermode=1，否则默认ordermode=0
	*/
	static function order_tpl($tpl, $by, $classes){ //$orderby, $ordermode, 
		$orderby = cls_Parse::Get('_da.orderby');
		$ordermode = cls_Parse::Get('_da.ordermode');
		if($by!=$orderby){
			$defmode = empty($class['defmode']) ? '' : '&ordermode=1';
		}elseif($ordermode){
			$defmode = '';
		}else{
			$defmode = '&ordermode=1';
		}
		$url = cls_uso::$urls['filorder'].cls_uso::$addstr; 
		$url = cls_uso::format_url("$url&orderby=$by$defmode",1);
		$class = @$classes[$by == $orderby ? ($ordermode ? '1' : 0) : 2];
		$str = str_replace(array('(url)','(class)'),array($url,$class),$tpl);
		return $str;
	}
	
	/* 排序样式及连接html，可能需要自已照着改
	  $title 显示名称
	  $by 要排序的字段
	  $orderby 当前存在的排序字段
	  $ordermode 当前的排序方式
	  $class 样式名称 如：array('当前降序','当前升序','未选中样式')
	  $class['defmode']=1, 默认的ordermode=1，否则默认ordermode=0
	*/
	static function order_set($title, $by, $orderby, $ordermode, $class){
		$url = cls_uso::$urls['filorder'].cls_uso::$addstr; 
		if($by!=$orderby){
			$defmode = empty($class['defmode']) ? '' : '&ordermode=1';
		}elseif($ordermode){
			$defmode = '';
		}else{
			$defmode = '&ordermode=1';
		}
		$url = cls_uso::format_url("$url&orderby=$by$defmode",1);
		$str = '<a rel="nofollow" class="' . @$class[$by == $orderby ? ($ordermode ? '1' : 0) : 2] . "\" href='$url'>$title</a>";
		return $str;
	}
	
	// 所有不限[清除当前]项的URL
	static function pick_urls($cfgs = array()){ 
		$_k = 'searchword'; $_kv = cls_Parse::Get("_da.searchword");
		if(!isset($cfgs[$_k]) && !empty($_kv)) $cfgs[$_k] = $_kv;
		$paras = cls_uso::$urls['filorder'];                   
		parse_str($paras,$a); 
		$cache = array(); 
		foreach($a as $key=>$v){
			if(empty($v)) continue;
			if($key=='searchmode') continue; //兼容之前
			$key = cls_string::ParamFormat($key); // preg_replace('/[^\w]/', '', $key);
			if(isset($cfgs[$key])){ //这里需求比较多...在cfgs中加参数一起实现, 支持{key},{v}占位符
				$title = $cfgs[$key];
				$title = str_replace(array('{key}','{v}'),array($key,$v),$title);
			}else{
				$chid = (cls_uso::$fdata=='m' ? 'm' : '').cls_uso::$init_cfg['chid'];
				$fkey = preg_match('/^ccid\d{1,6}$/i',$key) ? substr($key,4) : $key;
				$title = cls_uview::field_value($v, $fkey, $chid); //, $null='-'
				if($key == 'mchid' && $v=='1'){
					$title = '个人';
				}elseif($key == 'mchid' && $v=='2'){
					$title = '经纪人';
				}
			}
			if($key=='letter') $title = $v;
			$usearch = preg_replace("/&(?:$key)=[^&]*|\b(?:$key)=[^&]*&?/", '', $paras); 
			if($cnstr = cls_uso::node_check($usearch)){ 
				$url = $cnstr;
			}else{ 
				$url = cls_uso::format_url("$usearch".cls_uso::$addstr);
			}
			$cache[$key] = array(
				'title' => mhtmlspecialchars($title),
				'url' => $url, 
			);		
		}
		return $cache;
	}
	
	// 排除后的URL
	// key   : 排除的key(s), 如:orderby|ordermode
	// exstr : 附加的url, return:直接返回, fsale=2:附加参数, 空:自动判断节点
	static function extra_url($key,$exstr=''){
		if(is_numeric($key)) $key = "ccid$key"; 
		// 清除关联类系, 如：cls_uso::extra_url(1) -=> 不限区域</a> 同时清理[商圈]
		if(!empty(cls_uso::$rids_cfg) && substr($key,0,4)=='ccid'){
			$coid = intval(substr($key,4)); 
			if(in_array($coid,cls_uso::$rids_cfg)){ 
				foreach(cls_uso::$rids_cfg as $k=>$v){
					if($v==$coid){
						$key = "$key|ccid$k"; 
						break;	
					}
				}
			}
		} //注意,此[清除关联类系]如果是与栏目关联,没有用例,需要的再测试修正
		$usearch = preg_replace("/&(?:$key)=[^&]*|\b(?:$key)=[^&]*&?/", '', cls_uso::$urls['filsearch']);
		if($exstr=='return'){ //直接返回,供下一步组装,不含addno
			$url = cls_uso::format_url($usearch,1);
		}elseif(!empty($exstr)){ //附加参数,含addno,注意附加参数后还可能成为节点
			$tmp = $usearch.$exstr.cls_uso::$addstr; 
			$cnstr = cls_uso::node_check($tmp); 
			$url = $cnstr ? $cnstr : cls_uso::format_url($tmp,1);
		}elseif($cnstr = cls_uso::node_check("$usearch")){ //&$key=$k
			$url = $cnstr;
		}else{
			$url = cls_uso::format_url("$usearch".cls_uso::$addstr);
		}
		return $url;	
	}
	
	// 输出url模版，供js调用
	// return : ?caid=33&ccid11=1247&ccid20=1296&ccid1=[08cms_user_ccid], 其中()
	static function extmp_url($key,$tpl='[08cms_user_ccid]'){
		$val = cls_Parse::Get("_da.$key");
		$usearch = preg_replace("/&(?:$key)=[^&]*|\b(?:$key)=[^&]*&?/", '', cls_uso::$urls['filsearch']);
		$url = cls_uso::format_url("$usearch&$key=$tpl".cls_uso::$addstr);
		return $url;
	}
	
	// url格式化, 处理$cms_abs,$mobiledir,member/,mspace/等路径及en_virtual(),
	static function format_url($str,$dynamic=0){
		if(defined('IN_MOBILE')) $dynamic=1;
		if(!defined('UN_VIRTURE_URL')) $dynamic=1;//搜索页用动态
		if(empty(cls_uso::$csfull)){
			$cms_abs = cls_env::mconfig('cms_abs');
			if(cls_uso::$fpath=='o'){
				$mobiledir = cls_env::mconfig('mobiledir');
                $cspath = "$mobiledir/";
			}elseif(cls_uso::$fpath=='m'){
				$memberdir = cls_env::mconfig('memberdir');
                $cspath = "$memberdir/";
			}elseif(cls_uso::$fpath=='s'){
				$mspacedir = cls_env::mconfig('mspacedir');
                $cspath = "$mspacedir/";
			}else{
				$cspath = "";	
			}
			// 不是index.php入口的要指定入口，不能用$_SERVER['SCRIPT_NAME'],否则在后台生成静态的入口为admina.php
			$csname = empty(cls_uso::$init_cfg['csname']) ? 'index.php' : cls_uso::$init_cfg['csname']; 
			cls_uso::$csfull = "{$cms_abs}{$cspath}$csname?";
		} 
		$csfull = (strstr($str,'http://') ? '' : cls_uso::$csfull)."$str"; 
		$csfull = str_replace('?&','?',$csfull); 
		//$csfull = cls_env::repGlobalURL($csfull); //让核心处理
		$fkw = array('searchword=','orderby=','ordermode='); //这些关键字，不处理伪静态
		$fkn = 0; foreach($fkw as $k) strstr($str,$k) && $fkn++;
		if($dynamic || $fkn){
			//return $csfull;
		}else{ 
			$csfull = cls_url::en_virtual($csfull);	
		}
		return cls_url::view_url($csfull);
	}
	
	// 字段项目url
	static function field_urls($key){ 
		$field = cls_cache::Read((cls_uso::$fdata=='m' ? 'm' : '').'field', cls_uso::$init_cfg['chid'], $key);
		$arr = cls_field::options($field);
		$key == 'mchid' && $arr = array('1'=>'个人','2'=>'经纪人');
		$cache = array(); 
		$usearch = preg_replace("/&(?:$key)=[^&]*|\b(?:$key)=[^&]*&?/", '', cls_uso::$urls['filsearch']);
		foreach($arr as $k => $v){
			if(empty($k)) continue;
			$cache[$k] = array(
				'title' => $v,
				'url' => cls_uso::format_url("$usearch&$key=$k".cls_uso::$addstr),
			);
		}
		return $cache;
	}
	
	/* 类系节点 url
	  $coid 类系ID，0为栏目
	  $pid 所属父类，默认为顶级; -1为所有
	*/
	static function caco_urls($coid, $pid=0, $ext=''){ 
		$_da = cls_Parse::Get('_da'); 
		$key = empty($coid) ? 'caid' : "ccid$coid"; 
		$caco = $cbak = empty($coid) ? cls_cache::Read('catalogs') : cls_cache::Read('coclasses', $coid); 
		// cls_uso::$rids_cfg[$coid1] = $coid; // $rids_cfg[2]=1; 商圈<=-区域
		// $rarr["ccid{$coid}_$k"] = $v; // ccid20_1296=11,22,33
		if(isset(cls_uso::$rids_cfg[$coid])){ // 如[商圈]要处理关联 $rids_cfg[2]=1; 商圈<=-区域
			$rpcoid = cls_uso::$rids_cfg[$coid];  
			$rpkey = empty($rpcoid) ? 'caid' : "ccid$rpcoid"; 
			$rpid = @$_da[$rpkey]; // 如区域[ccid1]的值
			$relids = ','.@cls_uso::$rids_data["ccid{$rpcoid}_$rpid"].','; 
			foreach($caco as $k=>$v){
				if(!strstr($relids,",$k,")){
					unset($caco[$k]);
				}
			}
		}else{  
			if($pid!==-1){ //所有
				foreach($caco as $k=>$v){ 
					if($pid!=$v['pid']){
						unset($caco[$k]);
					}
				}
			}
		} //print_r(cls_uso::$rids_cfg); //Array ( [2] => 1 [14] => 3 )
		cls_uso::caco_url_ext($caco, $coid, $pid, $ext); // 各系统扩展部分: 子类别怎么处理? 为空怎么处理? 
		$cache = array(); 
		$clrkey = $key; // (如果需要)清楚关联类系, 如：选[区域]清理[商圈]
		if(!empty(cls_uso::$rids_cfg) && in_array($coid,cls_uso::$rids_cfg)){
			foreach(cls_uso::$rids_cfg as $k=>$v){
				if($v==$coid){
					$clrkey = "$key|ccid$k"; 
					break;	
				}
			}
		} //注意,此[清楚关联类系]如果是与栏目关联,没有用例,需要的再测试修正
		$usearch = preg_replace("/&(?:$clrkey)=[^&]*|\b(?:$clrkey)=[^&]*&?/", '', cls_uso::$urls['filsearch']);
		foreach($caco as $k=>$v){ 
			if($cnstr = cls_uso::node_check("$usearch&$key=$k")){ 
				$url = $cnstr; 
			}else{ 
				$url = cls_uso::format_url("$usearch&$key=$k".cls_uso::$addstr);
			}
			$cache[$k] = array(
				'title' => $v['title'],
				'url' => $url, 
			);	
		}
		return $cache;
	}
	
	// 各系统扩展部分: 子类别怎么处理? 为空怎么处理? 是否出路热门推荐类别?
	static function caco_url_ext(&$caco, $coid, $pid=0, $ext=''){
		
	}

	/*
	生成完整的节点项HTML，需要自已照着改
		$title 显示名称
		$field 栏目、类系ID或字段名称
		$value 当前值
		$rid 关系ID，暂未实现
	*/
	static function fliter_html($title, $field, $value, $rid = 0){
		if(is_numeric($field)){
			$rows = cls_uso::caco_urls($field, $rid);
			$field = $field ? "ccid$field" : 'caid';
		}else{
			$rows = cls_uso::field_urls($field);
		}
		$current = $value ? '' : ' class="current"';
		$dhmtl = "\n<dl><dt>{$title}：</dt><dd><ul>";
		$dhmtl .= "\n<li$current><a href='".cls_uso::extra_url($field)."'>不限</a></li>";
		foreach($rows as $k => $v){
			$current = $k == $value ? ' class="current"' : '';
			$dhmtl .= "\n<li$current><a href=\"$v[url]\">$v[title]</a></li>";
		}
		$dhmtl .= "\n</ul></dd></dl>";
		echo $dhmtl;	
	}

}

// 模版自定义元素显示(基类)
class cls_uviewbase{
	// 字段值对应的标题值
	// $ids: 原值, 可以是[,]号分开,也可是[tab键]
	// $field: 0/caid-栏目, 数字-类系, 字符串-字段
	// $chid: 2-文档模型2, m2-会员模型2, cu9-交互模型9 
	// $null: 为空时的返回值; 或为类似<span class='(value)'>(title)</span>的显示模版
	// Demo : cls_uview::field_value($tslp, 'tslp', 4, "<span class='ts_(value)'>(title)</span>");
	//   -=>  <span class='ts_2'>小户型投资地产</span><span class='ts_3'>教育地产</span><span class='ts_4'>旅游地产</span>
	static function field_value($ids, $field=0, $chid=0, $null=''){
		if(empty($ids)) return strpos($null,'</') ? '' : $null; //使用模版的返回空字符串
		//$ids = is_array($ids) ? implode(',',$ids) : $ids; echo $ids;
		$ids = explode(',', str_replace(array(", ","\t",",,"),',',$ids)); 
		if(empty($field) || $field=='caid'){
			$arr = cls_cache::Read('catalogs'); 
		}elseif(is_numeric($field)){
			$arr = cls_cache::Read('coclasses', $field); 
		}else{ //字段
			if(preg_match('/^cu\d{1,6}$/i',$chid)){ //会员模型字段
				$chid = str_replace(array('cu','CU'),'',$chid); 
				$field = cls_cache::Read('cufield', $chid, $field);
            }elseif(preg_match('/^m\d{1,6}$/i',$chid)){ //会员模型字段
				$chid = str_replace(array('M','m'),'',$chid); 
				$field = cls_cache::Read('mfield', $chid, $field); 
			}else{
				$_da = cls_Parse::Get('_da');
				$chid = (empty($chid) && !empty($_da['chid'])) ? $_da['chid'] : $chid;
				$field = cls_cache::Read('field', $chid, $field); 
			} 
			$arr = cls_field::options($field); 
		} 
		$re = '';
		if(strpos($null,'</') && strpos($null,'>')){ //类似<span class='(value)'>(title)</span>的显示模版
			$tpl = $null;
			$null = '';
		}else{
			$tpl = '';
		}
		foreach($ids as $k){
			if(isset($arr[$k])){ 
				$v = $arr[$k]; 
				$title = is_array($v) ? $v['title'] : $v;
				if($tpl){
					$itm = str_replace(array('(value)','(title)'),array($k,$title),$tpl);
					$re .= $itm;	
				}else{
					$re .= (empty($re) ? '' : ', ').$title;
				}
			}
		}
		return empty($re) ? $null : $re;
	}		
	
	// 交互统计 : $cuid : 交互ID(42=团购报名)
	// aid,$ext=array('checked'=>1,'tocid'=>0,),
	static function commu_count($cuid,$aid=0,$ext=array()){
		$db = _08_factory::getDBO();
		$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$_da = cls_Parse::Get('_da');
		$aid = empty($aid) ? 0 : max(0,intval($aid));
		$commu = cls_cache::Read('commu',$cuid);
		$sql = "SELECT COUNT(*) AS count FROM {$tblprefix}$commu[tbl] WHERE 1=1 ";
		$aid && $sql .= " AND aid='$aid' ";
		if(!empty($ext)){
			foreach($ext as $k=>$v){
				if(strstr('<>',substr($v,0,1))){
					// 处理：>=6, <=6, >6, <6 等格式
					$v = "'$v'"; $v = str_replace(array("'>=","'<=","'>","'<",),array(">='","<='",">'","<'",),$v);
					$sql .= " AND $k$v ";	
				}else{
					$sql .= " AND $k='$v' ";	
				}
			}
		}
		$cnt = $db->result_one($sql); if(!$cnt) $cnt = 0;
		return $cnt;
	}
	
	// 文本分页导航条
	// $option: 显示模版,可为option或具体html代码, 可用:[$url]','[$n]','[$title]','[$css]'占位符
	//          cls_utag::TextMpNav('option');
	//          cls_utag::TextMpNav('<li><a href="[$url]" title="[$title]" [$css]>第[$n]页：[$title]</a></li>','class="act"');
	// $nowcss: 当前页css，如:'class="act"'（默认）
	// $elsecss:其它页css，如:'class="gray"'（默认为空）
	static function TextMpNav($tpl='option',$nowcss='',$elsecss=''){
		$mp = cls_Parse::Get('_mp'); 
		if($tpl=='option'){
			$nowcss || $nowcss = 'selected="selected"';
			$tpl = '<option value="[$url]" [$css]>第[$n]页：[$title]</option>';
		}else{
			$nowcss || $nowcss = 'class="act"';	
		}
		$subject = cls_Parse::Get('_da.subject'); 
		$re = ''; 
		foreach($mp['titles'] as $k => $v){ //echo "\n:::$k"; if($k==$mp['mppage']) echo " --- $k) ";
			$title = $v ? $v : $subject;
			$url = $mp['mpurls'][$k];
			$icss = $k==$mp['mppage'] ? $nowcss : $elsecss; //nowpage,mppage
			$istr = str_replace(array('[$url]','[$n]','[$title]','[$css]'),array($url,$k,$title,$icss),$tpl);
			$re .= "\n$istr";
		}
		return $re;
	}
	
	// 分页标签中，评论等交互显示[-N楼-]
	// rowid:$v['sn_row']
	//       cls_utag::CommuFloor($v['sn_row']);
	static function CommuFloor($rowid=0){
		$mp = cls_Parse::Get('_mp'); 
		$floor = $mp['mpacount']-($mp['mppage']-1)*$mp['limits']-($rowid-1);
		// nowpage/mppage
		return $floor;
	}
	
	// 模版中显示字段html， 需要加载: include_once M_ROOT."./include/adminm.fun.php";
	static function form_item($cfg,$val='',$fmdata='fmdata'){
		$a_field = new cls_field;
		$a_field->init($cfg,$val); 
		$varr = $a_field->varr('fmdata','addtitle');
		unset($a_field); 
		return @$varr['frmcell'];
	}
	
	// 模版中显示图片上传的button
	static function form_btn_file($cfg,$val='',$custom=array()){
		$field = cls_field::getDecorator($cfg,$val);			
		$field->trfield('fmdata',$custom);			
	}
	//类目表单的option部分
	static function form_opt_coid($coid,$val=''){
		$dt_arr = cls_catalog::uccidsarr($coid);
		$opts = umakeoption($dt_arr,$val);
		return $opts;
	}
	
	//字段表单的option部分
	static function form_opt_field($cfg,$val=''){
		$opts = cls_uview::form_item($cfg,$val);
		return strip_tags($opts,'<option>');
    }
	
	//桌面图标 die(cls_uview::deskIcon()); (用die避免调试模式下输出<!-- tplname : xxx.html -->)
	static function deskIcon($head=1){	
		$hostname = str_replace(array(' '),'',cls_env::mconfig('hostname')); //文件名中其它特殊字符???
		$hostname = cls_string::iconv(cls_env::getBaseIncConfigs('mcharset'),'gbk',$hostname);
		// Windows下,文件名都用gbk; 否则某些ie版本下会有乱码
		$cms_abs = cls_env::mconfig('cms_abs');
		$Shortcut = "[InternetShortcut]";
		$Shortcut .= "\nURL={$cms_abs}";
		$Shortcut .= "\nIDList=";
		$Shortcut .= "\nIconFile={$cms_abs}favicon.ico";
		$Shortcut .= "\nIconIndex=100";
		$Shortcut .= "\n[{000214A0-0000-0000-C000-000000000046}]";
		$Shortcut .= "\nProp3=19,2";
		if($head){
			header("Content-type: application/octet-stream"); 
			header("Content-Disposition: attachment; filename=$hostname.url;");  
		}
		return $Shortcut;
    }
		
}

// 模版组sql(基类)
class cls_usqlbase{
	
	// 处理可用的orderby字段；
	static function order_bys($ordbys=array()){
		$orderby = cls_Parse::Get('_da.orderby');
		$order_bys = empty($ordbys) ? @cls_uso::$init_cfg['orderbys'] : $ordbys;
		if($orderby && $order_bys){
			if(!in_array($orderby,$order_bys)) cls_Parse::Message("[$orderby]排序参数错误!");	
		}
	}
	
	/* 处理标签列表里面的orderstr
	  $fixarr   : 指定前缀
	  $deforder : 默认排序
	  $rearray  : 返回数组,extract(cls_uso::order_str());
	  $_ajda    : 不在模版中调用（如ajax，不能用cls_Parse::Get），传来的数组_da；
	*/
	static function order_str($rearray='0',$deforder='',$fixarr=array(),$_ajda=array()){ //,$cfgs=array()
		foreach(array('orderby','ordermode') as $k){ 
			$$k = isset($_ajda[$k]) ? $_ajda[$k] :  cls_Parse::Get("_da.$k"); 
		}
		//if(!empty($cfgs)) extract($cfgs);
		$fdata = isset($_ajda['aj_fdata']) ? $_ajda['aj_fdata'] : cls_uso::$fdata;
		$deffix = $fdata=='m' ? 'm.' : 'a.'; //暂时只考虑文档会员
		if($orderby){
			$nowfix = isset($fixarr[$orderby]) ? $fixarr[$orderby] : $deffix; 
			$orderstr = (in_array($orderby,array('aid','mid')) ? $deffix : '').$orderby;
			$orderstr = $orderstr.($ordermode ? '' : ' DESC');
		}else{
			$orderstr = $deforder;	
		}
		if($rearray){ //返回数组
			$re = array(); 
			foreach(array('orderby','ordermode','orderstr') as $k) $re[$k] = $$k;
			return $re;
		}else{
			return $orderstr;	
		}
	}
	
	/*/ 处理wherestr, 所有field已经指定 
	//  $cfgs = array( // 格式: array('field:','key','op','fmt'),
		
			array('subject,address','searchword','like','str'), // -=> 简化为 array('subject,address'),
			array('leixing','0','=','int'),                     // -=> 简化为 array('leixing'),
			array('company',0,'like'),                          // -=> 简化为 array('company'),
			array('caid','caidx1','in','0'),                    // -=> caid IN(1,2,3)
			array('ccid4','ccid4','atuo',4),                    // -=> 自动类系：a.mj>0 AND a.mj<50
			array('ccid1','ccid1','in',1),                      // -=> array('ccid1',0,'in',1), // ccid1 IN(26,146,148,1...7,4303,4308) 
			array('mianccid1','ccid1','inlike','1'),            // -=> CONCAT(',',mianccid1,',') LIKE '%\t$ccid1\t%' OR (...)
			array('fromaddress','ccid20'), 
			array('grouptype37','ugid37'),
			array('company','searchword'),
			array('ccid61',0,'auto'),                            //-=> 条件类系

		)
		field:字段名, 如 subject,address 或 subject 或 leixing 等, 可带前缀
		key:url参数,  如 searchword 或 0 或 '', 如果为空则与字段相同或是searchword
		op:操作,      如 like 或 auto(自动类系) 或 = 或 < 或 <= 或 inlike(类系多选) 或 mso1(多选字段) 等, 如果为空则为like或=
		fmt:参数过滤, 如 int 或 str 或 0 或 coid 等, 如果op为in,inlike时,fmt为0或coid, 如果为空则[op]为like时fmt为str
		$exstr        如 "leixing IN(0,1)"
		$_ajda        不在模版中调用（如ajax，不能用cls_Parse::Get），传来的数组_da；
	*/
	static function where_str($cfgs=array(array('subject')),$exstr='',$_ajda=array()){
		$re = ''; //$this->_get['ids']
		foreach($cfgs as $cfg){ 
			if(!$fields = @$cfg[0]) continue;
			$flag = in_array($fields,array('subject','company')) || strstr($fields,','); //常用判断
			if(!$key = @$cfg[1]){
				if($flag) $key = $fields=='company' ? 'company' : 'searchword';
				else $key = $fields;
			} 
			$val = isset($_ajda[$key]) ? $_ajda[$key] : cls_Parse::Get("_da.$key"); if(!$val) continue;   
			$op = empty($cfg[2]) ? ($flag ? 'like' : '=') : $cfg[2]; 
			$fmt = empty($cfg[3]) ? (($flag || $op=='like') ? 'str' : 'int') : $cfg[3]; 
			$val = $fmt=='int' ? intval($val) : $val; //$val在_da中已经转码加\'
			$field_arr = explode(',',$fields);  
			$istr = ''; $ior = 0; //or标记
			foreach($field_arr as $field){ 
				if($op=='like'){ //关键字
					$itmp = "$field ".sqlkw($val);
				}elseif($op=='auto'){ // 自动条件类系
					$itmp = cnsql($cfg[3],$val); 
				}elseif(in_array($op,array('>','>=','<','<='))){ //数字比较
					$itmp = "$field$op'$val'";
				}elseif(in_array($op,array('notnull','isnull'))){ // field!='' 或 field=''
					$itmp = "$field".($_iop=='isnull' ? "=" : "!=")."''";
				}elseif($op=='in'){ //分类,自分类; 查sonbycoid()子栏目/类目, caid IN(sonbycoid($caidx1))
					$fmt = empty($cfg[3]) ? 0 : $cfg[3]; 
					$ids = sonbycoid($val, $fmt, 1); 
					if($ids){
						$itmp = "$field IN(".implode(',',$ids).")";
					}else{
						$itmp = '';	
					}
				}elseif($op=='inlike'){ //多选字段,分类有子分类, CONCAT(',',mianccid1,',') LIKE '%\t$ccid1\t%' OR (...)
					$fmt = empty($cfg[3]) ? 0 : $cfg[3]; 
					$ids = sonbycoid($val, $fmt, 1); $itmp = ''; 
					if($ids){
						foreach($ids as $id){
							$itmp .= (empty($itmp) ? '' : ' OR ')."CONCAT(',',$field,',') LIKE '%,$id,%'";	
						}
					}
					if(strstr($itmp,"' OR CONCAT('")) $ior = 1;
				}elseif(in_array($op,array('mso1'))){ //多选字段搜1个([tab键]分开)
					$itmp = "CONCAT('\t',$field,'\t') LIKE '%\t$val\t%'";
				}else{ //其它情况呢?! 
					$itmp = "$field='$val'";
				}
				if($itmp && $istr){
					$istr .= " OR $itmp"; //搜索多个字段,count($field_arr)>1
					$ior = 1; //or标记
				}elseif($itmp){
					$istr = "$itmp";	
				}
			}
			$istr = $ior ? "($istr)" : $istr; //有or要加括号
			if($istr){
				$re .= (empty($re) ? '' : ' AND ')."$istr";
			}
		}
		if($exstr){
			$re .= (empty($re) ? '' : ' AND ')."$exstr";
		}
		return $re;
	}
	
	// 文档 普通列表/搜索列表 共用情况 组sql:
	static function sql_arc($extcond='',$skip=array()){
		$_da = cls_Parse::Get('_da'); //print_r($_da);
		$whrstr = $extcond ? " AND $extcond" : "";
		if(!empty($_da['wherearr'])){
			$whrarr = $_da['wherearr'];	
			foreach(array('checked','caid','chid') as $k) unset($whrarr[$k]); //固定忽略
			if(!empty($skip)) foreach($skip as $k) unset($whrarr[$k]); //自定义忽略
			if(!empty($whrarr)){
				foreach($whrarr as $k=>$v){
					if(substr($k,0,4)=='ccid') continue; //类系忽略
					$whrstr .= " AND $v";
				}
			}
		} 
		if($whrstr) $whrstr = substr($whrstr,5); 
		return $whrstr;
	}
	
	// 文档 普通列表/搜索列表 共用情况 组sql:
	static function sql_mem($extcond='',$skip=array()){
		$_da = cls_Parse::Get('_da');
		$whrstr = $extcond ? " AND $extcond" : "";
		if(!empty($_da['wherearr'])){
			$whrarr = $_da['wherearr'];	
			foreach(array('mchid') as $k) unset($whrarr[$k]); //固定忽略,'caid','checked',
			if(!empty($skip)) foreach($skip as $k) unset($whrarr[$k]); //自定义忽略
			if(!empty($whrarr)){
				foreach($whrarr as $k=>$v){
					//if(substr($k,0,4)=='ccid') continue; //类系忽略
					$whrstr .= " AND $v";
				}
			}
		} 
		if($whrstr) $whrstr = substr($whrstr,5); //echo "<br>1.$whrstr";
		return $whrstr;
	}
	
}
