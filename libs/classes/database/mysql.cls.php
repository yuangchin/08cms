<?php
!defined('M_COM') && exit('No Permisson');
class cls_mysql{
	var $link;
	var $name = '';
    protected $_mdebug = true;
	function connect($dbhost,$dbuser,$dbpw,$dbname = '',$pconnect = 0,$halt = TRUE,$ncharset = ''){
		$func = !$pconnect ? 'mysql_connect' : 'mysql_pconnect';
		if(!$this->link = @$func($dbhost,$dbuser,$dbpw,1)){
			if($halt){
				$this->halt('Can not connect to MySQL server', '', false);
			}else return false;
		}else{
			if($this->version() > '4.1'){
				global $dbcharset;
                $mcharset = cls_envBase::getBaseIncConfigs('mcharset');
				$ncharset = empty($ncharset) ? (empty($dbcharset) ? str_replace('-','',strtolower($mcharset)) : $dbcharset) : $ncharset;
                mysql_query("SET @@SESSION.sql_mode = '';", $this->link);                
                if ( !function_exists('mysql_set_charset') || (false === @mysql_set_charset($ncharset, $this->link)) )
                {
                    // 如果mysql_set_charset失败时才用这方法设置
                    $serverset = $ncharset ? 'character_set_connection='.$ncharset.', character_set_results='.$ncharset.', character_set_client=binary' : '';
                    // 已经不再考虑低于MYSQL 5.0.1版本
    				// $serverset .= $this->version() > '5.0.1' ? ((!$serverset ? '' : ',').'sql_mode=\'\'') : '';
    				$serverset && mysql_query("SET $serverset", $this->link);
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
	function select_db($dbname){
		return mysql_select_db($dbname, $this->link);
	}
	function query($sql, $type = '', $new_class = false){
		
		global $_mdebug;
		$func = ($type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query')) ? 'mysql_unbuffered_query' : 'mysql_query';

		//cls_env::repGlobalValue($sql, false, true);
        empty($new_class) && $this->escape_old_sql($sql);

		$dbstart = microtime(TRUE);
		if(!($query = $func($sql, $this->link))){
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
		$result = mysql_real_escape_string(stripslashes($string), $this->link);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
			$result = str_replace('[08cmsKwBlank]','%',$result);
		}

		return $result;
	}
	
	function fetch_array($query, $result_type = MYSQL_ASSOC){
		return mysql_fetch_array($query, $result_type);
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
	function affected_rows(){
		return mysql_affected_rows($this->link);
	}
	function error(){
		return $this->link ? mysql_error($this->link) : mysql_error();
	}
	function errno(){
		return intval($this->link ? mysql_errno($this->link) : mysql_errno());
	}
	function result($query, $row){
		return @mysql_result($query, $row);
	}
	function num_rows($query){
		return mysql_num_rows($query);
	}
	function num_fields($query){
		return mysql_num_fields($query);
	}
	function free_result($query){
		return mysql_free_result($query);
	}
	function insert_id(){
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	function fetch_row($query){
		return mysql_fetch_row($query);
	}
	function fetch_fields($query){
		return mysql_fetch_field($query);
	}
	function version(){
		return mysql_get_server_info($this->link);
	}
	function close(){
		return mysql_close($this->link);
	}
	function halt($message = '', $sql = '', $checkUser = true){
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
        return mysql_fetch_object($query, $class_name);
    }
}
?>