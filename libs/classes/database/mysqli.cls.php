<?php
/**
 * MySQL增强扩展接口操作类
 *
 * 如果使用MySQL4.1.3或更新版本，强烈建议使用该接口，具体原因
 * 与区别请查看：http://docs.php.net/manual/zh/mysqli.overview.php
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
!defined('M_COM') && exit('No Permisson');
class cls_mysqli
{
	public $link;
	public $name = '';
    protected $_mdebug = true;
    
    /**
     * 链接数据库
     */
	public function connect( $dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = TRUE, $ncharset = '')
    {
        $dbport = @trim(cls_envBase::getBaseIncConfigs('dbport'));
		if(!$this->link = @mysqli_connect($dbhost, $dbuser, $dbpw, null, empty($dbport) ? 3306 : (int) $dbport)){
			if($halt){
				$this->halt('Can not connect to MySQL server', '', false);
			}else return false;
		}else{
			if($this->version() > '4.1')
            {
				global $dbcharset;
                $mcharset = cls_envBase::getBaseIncConfigs('mcharset');
				$ncharset = empty($ncharset) ? (empty($dbcharset) ? str_replace('-','',strtolower($mcharset)) : $dbcharset) : $ncharset;
                mysqli_query($this->link, "SET @@SESSION.sql_mode = '';");                
                if ( !function_exists('mysqli_set_charset') || (false === @mysqli_set_charset($this->link, $ncharset)) )
                {
                    // 如果mysqli_set_charset失败时才用这方法设置
                    $serverset = $ncharset ? 'character_set_connection='.$ncharset.', character_set_results='.$ncharset.', character_set_client=binary' : '';
    				$serverset && mysqli_query($this->link, "SET $serverset");
                }
			}
                        
			if($dbname && !@$this->select_db($dbname)){
				if($halt){
					$this->halt("Can not select database $dbname");
				}else return false;
			}
			$this->name = $dbhost.'_'.$dbname;
		}
		return true;
	}
    
    /**
     * 选择数据库名
     */
	public function select_db($dbname)
    {
		return mysqli_select_db($this->link, $dbname);
	}
    
    /**
     * 发送一个数据库查询
     */
	public function query($sql, $type = '', $new_class = false)
    {
		
		global $_mdebug;
        empty($new_class) && $this->escape_old_sql($sql);
        
		$dbstart = microtime(TRUE);
		if( !($query = mysqli_query($this->link, $sql)) )
        {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY'){
				$this->close();
				require M_ROOT.'base.inc.php';
				$this->connect($dbhost,$dbuser,$dbpw,$dbname,$pconnect,true,$dbcharset);
				return $this->query($sql,'RETRY'.$type);
			} elseif($type != 'SILENT' && substr($type, 5) != 'SILENT'){
				$this->halt('MySQL Query Error', $sql);
			}
		}
        
        if ($this->_mdebug)
        {
            empty($_mdebug) || $_mdebug->add($sql,microtime(TRUE)-$dbstart,$this->name);
        }
		return $query;
	}
    
    /**
     * 关闭对调试信息的记录
     **/
    public function closeDebug()
    {
        $this->_mdebug = false;
    }
    
    # 临时性把本系统之前写的SQL重新用mysql_real_escape_string函数转义下
    public function escape_old_sql( &$sql, $new_sql_param = array(), $action = false, $extra = false )
    {
        #$sql = "DELETE FROM cms_asession WHERE (mid='1' AND ip='127.0.0.1') OR dateline<1414507990";
        try
        {            
            $_08_SQL_Parser = _08_SQL_Parser::getInstance($sql);
            $sql = $_08_SQL_Parser#->setDebug()
                                   ->mergeSQL();
        }
        catch(UnableToCalculatePositionException $e)
        {
            $this->halt('MySQL Parse Error', $sql);
        }
    }

    /**
     * 过滤敏感字
     *
     * @param  string $text   要过滤的文本信息
     * @param  bool   $extra  如果该方法用于搜索请设置为true
     *
     * @return string $result 过滤后的文本
     * @since  1.0
     */
    public function escape( $string, $extra = false )
	{
		$result = mysqli_real_escape_string($this->link, stripslashes($string));

		if ($extra)
		{
			$result = addcslashes($result, '%_');
			$result = str_replace('[08cmsKwBlank]','%',$result);
		}

		return $result;
	}
	
	public function fetch_array($query, $result_type = MYSQLI_ASSOC)
    {
        return mysqli_fetch_array($query, $result_type);
	}
    
	function ex_fetch_array($sql,$ttl = 0,$type = ''){//扩展缓存有效
		$ExCacheKey = md5($this->name.$sql);
		$re = GetExtendCache($ExCacheKey,$ttl);
		if($re === false){
			if($query = $this->query($sql,$type)){
				$re = array();
				while($r = $this->fetch_array($query)) $re[] = $r;
				$this->free_result($query);
				SetExtendCache($ExCacheKey,$re,$ttl);
			}
		}
		return $re ? $re : array();
	}
	function fetch_one($sql,$ttl = 0,$type = ''){//只取出符合条件的第一条记录，扩展缓存有效
		$ExCacheKey = md5($this->name.$sql);
		$re = GetExtendCache($ExCacheKey,$ttl);
		if($re === false){
			if($query = $this->query($sql,$type)){
				$re = $this->fetch_array($query);
				$this->free_result($query);
				SetExtendCache($ExCacheKey,$re,$ttl);
			}
		}
		return $re ? $re : array();
	}
	function result_one($sql,$ttl = 0,$type = '') {//返回第一个记录的第一个字段,扩展缓存有效
		$ExCacheKey = md5($this->name.$sql);
		$re = GetExtendCache($ExCacheKey,$ttl);
		if($re === false){
			if($query = $this->query($sql,$type)){
				$re = $this->result($query,0);
				$this->free_result($query);
				SetExtendCache($ExCacheKey,$re,$ttl);
			}
		}
		return $re ? $re : '';
	}
    
	public function affected_rows()
    {
		return mysqli_affected_rows($this->link);
	}
    
	public function error()
    {
		return $this->link ? mysqli_error($this->link) : mysqli_connect_error();
	}
    
	public function errno()
    {
		return $this->link ? mysqli_errno($this->link) : mysqli_connect_errno();
	}
    
    /**
     * @deprecated nv50 等所有调用SQL查询的地方都换了新类调用时废弃该函数
     */
	public function result($query, $row)
    {
        #return @mysql_result($query, $row);
        
        # 用 $this->fetch_array() 是这样代替？？？有待验证
        $rowResult = $this->fetch_array($query, MYSQLI_NUM);
        return @$rowResult[$row];
	}
    
	public function num_rows($query)
    {
        return mysqli_num_rows($query);
	}
    
	public function num_fields($query)
    {
        die('请用 _08_MysqlQuery::getTableColumns() 代替。');
	}
    
	public function free_result($query)
    {
		return mysqli_free_result($query);
	}
    
	public function insert_id()
    {
		return ($id = mysqli_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
    
	public function fetch_row($query)
    {
		return mysqli_fetch_row($query);
	}
    
	public function fetch_fields($query)
    {
		return mysqli_fetch_field($query);
	}
    
	public function version()
    {
		return mysqli_get_server_info($this->link);
	}
    
	public function close()
    {
		return mysqli_close($this->link);
	}
    
	public function halt($message = '', $sql = '', $checkUser = true)
    {
		global $timestamp,$tblprefix,$_no_dbhalt;
        # 当数据库链接失败时不判断用户也显示信息，不然判断用户时会出现死循环
        if ( $checkUser && class_exists('cls_UserMain') )
        {
            $curuser = cls_UserMain::CurUser();
        }
        else
        {
        	$curuser = null;
        }
		if(empty($_no_dbhalt)) include M_ROOT.'include/mysql.err.php';
	}
    
    public function fetch_object($query, $class_name = 'stdClass')
    {
        return mysqli_fetch_object($query, $class_name);
    }
    
    public function __construct()
    {        
        if( !function_exists('mysqli_connect') )
        {
            $this->halt('数据库连接错误：MYSQLI扩展不可用！', '', false);
        }
    }
}