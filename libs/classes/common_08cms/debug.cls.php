<?php
class cls_debug{
	var $uri = '';
	var $tpl = '';
	var $tag = '';
	var $querys = 0;
	var $wtime = 0;
	var $records = 0;
	var $counted = 0;
    protected static $_db = null;
    private $profiler = null;
	function __construct( $prefix = '' ){
        $this->profiler = _08_Profiler::getInstance($prefix);
	}
	function init(){
		$this->uri = $this->tpl = $this->tag = '';
		$this->querys = $this->wtime = $this->records = $this->counted = 0;
	}
	function setvar($name,$val){
		$this->$name = $val;
	}
	function add($sql,$time,$name){	
        if (!(self::$_db instanceof _08_MysqlQuery))
        {	
		    $db = _08_factory::getDBO();
            self::$_db = clone $db;
        }
        
		self::$_db->closeDebug();
		$prefix = strtolower(substr($sql,0,12));
		if(strpos($prefix,'into') !== false) return;//排除插入操作的统计，replace into,insert into,避免覆盖mysql_insert_id()的值
		if(cls_env::mconfig('viewdebug')){
			$this->querys ++;
			$this->wtime += $time;
		}

		if(cls_env::mconfig('debugenabled') && $time > (int)cls_env::mconfig('debuglow') / 1000){
			if(!cls_env::mconfig('debugadmin') && (defined('M_ADMIN') || defined('M_MCENTER'))) return;	
			if(!$this->counted){
			    $this->records = self::$_db->clear()->getTableRowNum('#__dbdebugs');
				$this->counted = 1;
			}
            
			if(++$this->records < 500000){
                $profilerInfo = $this->profiler->getDebugBacktrace($sql);
				$ddid = microtime(1).md5($name.$sql.$this->tpl);
                self::$_db->replace('#__dbdebugs', array(
                    'ddid' => substr($ddid,0,32),
                    'ddsql' => $sql,
                    'ddtbl' => $name,
                    'ddused' => round(1000 * $time,2),
                    'ddurl' => $this->uri ? $this->uri : M_URI,
                    'ddtpl' => $this->tpl,
                    'ddtag' => $this->tag,
                    'ddate' => cls_env::GetG('timestamp')
                ) )->exec();
                
                /*
				@mysql_query("INSERT INTO ".cls_env::GetG('tblprefix')."dbdebugs SET
				ddid='".substr($ddid,0,32)."',
				ddsql='".addslashes($sql)."',
				ddtbl='".addslashes($name)."',
				ddused='".round(1000 * $time,2)."',
				ddurl='".addslashes($this->uri ? $this->uri : M_URI)."',
				ddtpl='".addslashes($this->tpl)."',
				ddtag='".addslashes($this->tag)."',
				ddate='".cls_env::GetG('timestamp')."'"
				,$db->link);*/
			}
		}
	}
	
	/*
	 * 显示前台页面的调试信息
	*/
	function view(){
		
		$SourceArray = array();
		if(_08_DEBUGTAG){//非调试模式不显示模板信息
			$SourceArray['tplname'] = $this->tpl;
		}
		if(cls_env::mconfig('viewdebug')){//显示查询统计信息
			$SourceArray['querys'] = $this->querys;
			$SourceArray['use'] = round($this->wtime,4).'s';
		}
		$ViewString = '';
		if($SourceArray){
			foreach($SourceArray as $k => $v) $ViewString .= "[--n--]<!-- $k : $v -->";
			if(cls_env::mconfig('viewdebugmode') =='direct'){
				$ViewString = htmlspecialchars($ViewString);
				$ViewString = str_replace('[--n--]','<br>',$ViewString);
			}else{
				$ViewString = str_replace('[--n--]',"\n",$ViewString);
			}
		}
		return $ViewString;
	}
}
