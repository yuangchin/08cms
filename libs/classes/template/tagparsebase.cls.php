<?PHP
/**
* 标签处理基类，具体类型标签处理类的基类，
* 
* 只在cls_Parse内使用，继承cls_Parse的方法及所有静态变量，如：$_mp、$G(后续)、$_da(后续)，模板设计者不会直接接触本类(如何将一些方法对外部开放?????)
* 对cls_Parse负责，外部模板不会直接接触本类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_TagParseBase extends cls_BasePage{
	
    const TAG_MP_OFFSET = 2;					# 分页导航中的偏移量
	protected $tag = array();					# 当前标签配置
	protected $_TagSqlBaseStr = NULL;			# 暂存处理好的标签SQL，以便重用，只在部分类型标签中有效
	
	abstract protected function TagReSult();	# 所有类型的标签采用统一的结果返回方法
	
	# 提取一个标签的数据结果，唯一外部入口
	public static function OneTag($tag){
		# 初步检查
		if(!($TagClass = @$tag['tclass'])){
			throw new cls_ParseException('标签'.@$tag['ename'].'的分类未定义');
		}
		
		# 某些类型的标签处理类共用
		$_TagClassTrans = array( # 标签类型转换，前者使用后者的处理类
			'advertising' => 'farchives',
			'acount' => 'archives',
			'mcount' => 'members',
			'flashs' => 'medias',
			'flash' => 'medias',
			'files' => 'medias',
			'file' => 'medias',
			'media' => 'medias',
			'image' => 'images',
			'vote' => 'votes',
		);
		if(isset($_TagClassTrans[$TagClass])) $TagClass = $_TagClassTrans[$TagClass];
		
		$TagClassName = "cls_Tag_$TagClass";
		if(!$TagClassName || !class_exists($TagClassName)){
			throw new cls_ParseException("标签处理类[$TagClassName]未定义");
		}
		
		$_TagInstance = new $TagClassName($tag);
		try{
			$Return = $_TagInstance->TagFetch();
		}catch(cls_ParseException $e){ # 暂留以后来处理这个标签意外信息，目前是忽略返回空值
			$Return = $_TagInstance->_TagError($e->getMessage(),$TagClass);
		}
		return $Return;
/*
		if(!empty($tag['ename']) && $_mdebug = cls_env::GetG('_mdebug')) $_mdebug->setvar('tag',$tag['ename']); # 用于SQL分析
		$this->_Set('G.tag',$tag); # 在$G中暂存当前标签，模板中需要用????
		
		# 根据标签类型，调用相应的处理类
		cls_env::SetG('_sqlintag',true); # 标签SQL缓存有用，不要删除。
		if(self::_GetTagInstance($tag)){
			$re = self::$_TagInstance->TagFetch();
		}else $re = '';
		cls_env::SetG('_sqlintag',false); # 标签SQL缓存有用，不要删除。
		return $re;
*/		
	}
	
    function __construct($tag = array()){
		$this->tag = $tag;
    }
	
	# 标签对象的数据提取入口
	protected function TagFetch(){
		$this->_TagInit(); # 初始化当前标签
		$this->TagMpInfo(); # 先分析分页情况及资料
		$Return = $this->TagReSult();
		return $Return;
	}
	
	# 留下接口，以后可能需要捕捉标签解析的错误信息进行展示，目前只是将结果设为空值
	protected function _TagError($Msg,$TagClass){
		#if(_08_DEBUGTAG) throw new cls_ParseException($Msg);	
		return in_array($TagClass,cls_Tag::TagClassByType('string')) ? '' : array();
	}
	
	# 按Sql的格式输出结果
	protected function TagResultBySql(){
		$ReturnArray = array();
		if($sqlstr = $this->TagSqlStr()){
			$ReturnArray = self::$db->ex_fetch_array($sqlstr,intval(@$this->tag['ttl']));
			foreach($ReturnArray as $k => &$v){
				$v = $this->TagOneRecord($v); # 返回结果的单条记录处理
				$v['sn_row'] = $k + 1;
			}
		}
		return $ReturnArray;
	}
	
	# 处理标签的SQL语句，$IsCount:统计查询(true)/内容查询(false)
	protected function TagSqlStr($IsCount = false){
		# 处理SqlStr的重用
		if(is_null($this->_TagSqlBaseStr)){ # 之前未处理过
			$SqlStr = $this->iTagSqlBaseStr();
			$this->_TagSqlBaseStr = $SqlStr;
		}else{ # 重用之前分析了的结果
			$SqlStr = $this->_TagSqlBaseStr;
		}
		
		if($SqlStr){
			# 分离处理 正常查询/数据统计查询 两种结果
			if(!$IsCount){ # 正常内容查询
				$SqlStr .= $this->iTagOrderStr();
				$SqlStr .= $this->iTagLimitStr();
			}else{
				$SqlStr = $this->SqlStrTransToCount($SqlStr);
			}
		}
		return $SqlStr;
	}
	
	# 分析SQL的主要部分(select、from、where)
	protected function iTagSqlBaseStr(){
		if(!empty($this->tag['isall'])){ #用户完全定义sqlstr
			$sqlstr = $this->TagHandWherestr();
		}else{ # 根据标签配置拼接sqlstr
			$sqlstr = $this->CreateTagSqlBaseStr();
		}
		return $sqlstr;
	}
	
	# 标签设置中手动输入的wherestr处理
	protected function TagHandWherestr(){
		$wherestr = '';
		if(!empty($this->tag['wherestr'])){
			$wherestr = empty($this->tag['isfunc']) ? $this->tag['wherestr'] :  @EvalFuncInTag($this->tag['wherestr']);
		}
		return $wherestr ? $wherestr : '';
	}
	
	# 处理ORDER BY排序字串
	protected function iTagOrderStr(){
		$OrderStr = empty($this->tag['orderstr']) ? '' : trim($this->tag['orderstr']);
		$OrderStr = $this->TagCustomOrderStr($OrderStr);
		$OrderStr = $OrderStr ? " ORDER BY $OrderStr" : $this->TagDefaultOrderStr();
		$OrderStr = preg_replace('/[^ \w\.\,]/', '', $OrderStr); # 临时处理，以后不要使用这种拼装好的SQL
		return $OrderStr;
	}
	
	# 处理LIMIT字串
	protected function iTagLimitStr(){
		$Limits = $this->TagInitLimits();
		$Start = $this->TagInitStart();
		return " LIMIT $Start,$Limits";
	}
	
	# 处理开始位置
	protected function TagInitStart(){
		$Limits = $this->TagInitLimits();
		$Start = empty($this->tag['mp']) ? 0 : ((int)cls_Parse::Get('_mp.nowpage') - 1) * $Limits;
		if(!empty($this->tag['startno'])) $Start += (int)$this->tag['startno'];
		return $Start;
	}
	
	# 处理列表条数限制
	protected function TagInitLimits(){
		return empty($this->tag['limits']) ? 10 : (int)$this->tag['limits'];
	}
	
	# 处理分页导航的显示页数
	protected function TagInitLength(){
		return empty($this->tag['length']) ? 10 : (int)$this->tag['length'];
	}
	
	
	# 将常规SQL语句转为SELECT COUNT的SQL语句
	protected function SqlStrTransToCount($SqlStr){
		if(!($SqlStr = (string)$SqlStr)) return '';
		if(preg_match('/^(.+?)\s+GROUP\s+BY(.+)$/is',$SqlStr,$matches)){
			return "SELECT COUNT(DISTINCT $matches[2]) ".stristr($matches[1],'FROM');
		}else{
			return 'SELECT COUNT(*) '.stristr($SqlStr,'FROM');
		}
	}

	# 强制索引处理
	protected function ForceIndexSql($tbl = ''){
		$Return = '';
		if(empty($this->tag['forceindex'])) return $Return;
		$na = array_filter(explode('.',$this->tag['forceindex']));
		if(empty($tbl) && count($na)  == 1){
			$Return = $na[0];
		}elseif(count($na)  == 2){
			$Return =$tbl == $na[0] ? $na[1] : '';
		}
		return $Return ? " FORCE INDEX ($Return)" : '';
	}
	
	# 抛出一个标签解析错误
	protected function TagThrowException($Msg){
		throw new cls_ParseException("标签[名称:{$this->tag['ename']}][类型:{$this->tag['tclass']}]解析错误：$Msg");	
	}
	
	protected function TagMpInfo(){		
		if(!empty(self::$_mp['_MpDone']) || empty($this->tag['mp'])) return;
		
		if(!in_array($this->tag['tclass'],cls_Tag::TagClassByType('mp'))) return;
		
		#初始化 $_mp
		self::$_mp['limits'] = $this->TagInitLimits();
		self::$_mp['length'] = $this->TagInitLength();
		self::$_mp['simple'] = empty($this->tag['simple']) ? 0 : 1;
		
		# 分页处理self::$_mp['acount']等不同类型标签的差异化部分
		$this->TagCustomMpInfo();
		
		# 分页统计信息
		if(!empty($this->tag['alimits'])){
			self::$_mp['acount'] = min(self::$_mp['acount'],$this->tag['alimits']);
		}
		if(self::$_mp['acount']){
			self::$_mp['pcount'] = ceil(self::$_mp['acount'] / self::$_mp['limits']);
		}
		self::$_mp['nowpage'] = max(1,min(self::$_mp['nowpage'],self::$_mp['pcount']));

		# 为了适应原始标签规则，做相应项的赋值，同时补全self::$_mp的基本数据
		self::$_mp['s_num'] = empty(self::$_mp['s_num']) ? self::$_mp['pcount'] : min(self::$_mp['pcount'],self::$_mp['s_num']);
		self::$_mp['mppage'] = self::$_mp['nowpage'];
		self::$_mp['mpcount'] = self::$_mp['pcount'];
		self::$_mp['mpacount'] = self::$_mp['acount'];

		# 取得分页导航
		self::$_mp['mpnav'] = $this->TagMpNav();
		
		# 设置已处理分页的标记
		self::$_mp['_MpDone'] = true;	
	}
	
	# 以下类型：'archives','catalogs','mccatalogs','farchives','commus','members','votes','searchs','msearchs','pushs',可默认使用本方法，而不需要在具体类中定义
	protected function TagCustomMpInfo(){
		if($sqlstr = $this->TagSqlStr(true)){
			if($num = self::$db->result_one($sqlstr,intval(@$this->tag['ttl']))){
				self::$_mp['acount'] = $num;
			}
		}
	}
	
	protected function TagMpNav(){
		
		# 不存在分页
		if(self::$_mp['pcount'] == 1) return '';
	
		# 计算页码范围
		list($from,$to) = $this->MpPageFromTo();
		
		// 针对文本分页，需要全部页码
		if($this->tag['tclass']=='text'){ 
			self::$_mp['mpurls'] = $this->MpUrls(); //参数:0,0
		}else{ //非文本分页，按from,to设置
			self::$_mp['mpurls'] = $this->MpUrls($from,$to);	
		} 
		
		foreach(array('mpstart' => 1,'mpend' => self::$_mp['pcount'],'mppre' => self::$_mp['nowpage'] - 1,'mpnext' => self::$_mp['nowpage'] + 1,) as $k => $v){
			self::$_mp[$k] = $this->MpLink($v);
		}
		
		# 分页导航代码		
		$_NavCode = '';
		if(self::$_mp['nowpage'] - self::TAG_MP_OFFSET > 1 && self::$_mp['pcount'] > self::$_mp['length']){
			$_NavCode .= '<a href="'.self::$_mp['mpstart'].'" class="p_redirect" target="_self">|<</a>';
		}
		if(self::$_mp['nowpage'] > 1 && !self::$_mp['simple']){
			$_NavCode .= '<a href="'.self::$_mp['mppre'].'" class="p_redirect" target="_self"><<</a>';
		}
		for($i = $from; $i <= $to; $i++){
			if($i == self::$_mp['nowpage']){
				$_NavCode .= '<a class="p_curpage">'.$i.'</a>';
			}else{
				$_NavCode .= '<a href="'.self::$_mp['mpurls'][$i].'" class="p_num" target="_self">'.$i.'</a>';
			}
		}
		if(self::$_mp['nowpage'] < self::$_mp['pcount'] && !self::$_mp['simple']){
			$_NavCode .= '<a href="'.self::$_mp['mpnext'].'" class="p_redirect" target="_self">>></a>';
		}
		if($to < self::$_mp['pcount']){
			$_NavCode .= '<a href="'.self::$_mp['mpend'].'" class="p_redirect" target="_self">>|</a>';
		}
		
		if($_NavCode){
			if(!empty(self::$_mp['simple'])){ # 显示记录数及总页数
				$_TotalCode = '<a class="p_total">&nbsp;'.self::$_mp['acount'].'&nbsp;</a><a class="p_pages">&nbsp;'.self::$_mp['nowpage'].'/'.self::$_mp['pcount'].'&nbsp;</a>';
				$_NavCode = $_TotalCode.$_NavCode;
			}
			$_NavCode = '<div class="p_bar">'.$_NavCode.'</div>';
		}
		return $_NavCode;
	}
	
	# 根据指定页码范围，返回分页导航Url数组。需要在MpInfo之后才能执行
	# $from = 0：从第1页开始。$to = 0：到最末页为止。
	protected function MpUrls($from = 0,$to = 0){
		$from = empty($from) ? 1 : max(1,(int)$from);
		$to = empty($to) ? self::$_mp['pcount'] : min(self::$_mp['pcount'],(int)$to);
		$Urls = array();
		for($i = $from; $i <= $to; $i++){
			$Urls[$i] = $this->MpLink($i);
		}
		return $Urls;
	}
	# 根据指定页码，返回其分页导航Url。需要在MpInfo之后才能执行
	protected function MpLink($PageNo = 0){
		$PageNo = min(self::$_mp['pcount'],max(1,(int)$PageNo)); #只允许从0->总页数之间选择
		if(self::$_mp['static'] && $PageNo <= self::$_mp['s_num']){
			$UrlPre = self::$_mp['surlpre'];
		}else{
			$UrlPre = self::$_mp['durlpre'];
		}
		$Url = cls_url::m_parseurl($UrlPre,array('page' => $PageNo));
		return $Url;
	}
	
	# 计算页码范围
	protected function MpPageFromTo(){
		if(self::$_mp['length'] > self::$_mp['pcount']){
			$from = 1;
			$to = self::$_mp['pcount'];
		}else{
			$from = self::$_mp['nowpage'] - self::TAG_MP_OFFSET;
			$to = $from + self::$_mp['length'] - 1;
			if($from < 1){
				$to = self::$_mp['nowpage'] + 1 - $from;
				$from = 1;
				if($to - $from < self::$_mp['length']) $to = self::$_mp['length'];
			}elseif($to > self::$_mp['pcount']){
				$from = self::$_mp['pcount'] - self::$_mp['length'] + 1;
				$to = self::$_mp['pcount'];
			}
		}
		return array($from,$to);
	}
	
	# 为了兼容子类中未定义函数，留下空的接口方法 *****************************************************
	
	# 初始化当前标签
	protected function _TagInit(){}
		
	# 不同类型标签的返回结果的单条记录处理
	protected function TagOneRecord($OneRecord){
		return $OneRecord;
	}
	
	# 不同类型标签的排序字串处理
	protected function TagCustomOrderStr($OrderStr){
		return $OrderStr;
	}
	
	# 取得默认的排序字串
	protected function TagDefaultOrderStr(){
		return '';
	}
	
	
}
