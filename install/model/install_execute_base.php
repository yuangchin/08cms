<?php
/**
 * 开始执行安装模型基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Install_Execute_Base extends _08_Install_Base
{
    private $params = null;
    
    /**
     * 数据库驱动对象
     * 
     * @var object
     */
    private $db = null;
    
    /**
     * 定义一个请求最大执行SQL的条数
     * 
     * @var int
     */
    const MAXNUM = 50;
    
    /**
     * 合并多个请求然后显示的数据表名称
     * 
     * @var array
     */
    private $tableNames = array();
    
    private $_insertTableName = array();
	
   
    /**
     * 保存执行失败的SQL
     * 
     * @var array
     */
    private $badSQL = array();
    
    private static $_competence = null;
    
    /**
     * 用load data infile的方式安装数据包
     */
    public function loadDataInFile( $iterator )
    {
        if ( $iterator->isFile() )
        {
            $ext = strrchr($iterator->getFilename(), '.');
            if ( strtolower($ext) == '.txt' )
            {
				$tableName = $this->params->configs['tblprefix'].$iterator->getBasename($ext);
								
                $fileName = str_replace('\\', '/', $iterator->getPathname());
                $dbhost = $this->params->configs['dbhost'];
				$loadlocal =  strripos($dbhost, 'localhost') === FALSE && strripos($dbhost, '127.0.0.1') === FALSE ? 'LOAD DATA LOCAL INFILE' : 'LOAD DATA INFILE';
                $this->db->query("{$loadlocal} '{$fileName}' INTO TABLE {$tableName} 
				CHARACTER SET {$this->params->configs['dbcharset']} 
				FIELDS TERMINATED BY ','
				OPTIONALLY ENCLOSED BY '''' 
				LINES TERMINATED BY '\n' 
				",'',true);
            }
        }
    }
    
    /**
     * 用insert into的方式安装数据包
     */
    public function insertIntoData()
    {
        if ( isset($this->_request['sql_file']) ){
			if($this->_request['sql_file'] == '08cms_basic'){
				$needTableNames = $this->installTableNames['basic'];	
				$subPath = '';	
			}elseif($this->_request['sql_file'] == '08cms_optional'){
				$needTableNames = $this->installTableNames['optional'];		
				$subPath = 'optional/';	
			}
			$tableIndex = empty($this->_request['tableindex']) ? 0 : intval($this->_request['tableindex']); 
			
			if($needTableName = @$needTableNames[$tableIndex]){
				return $this->_parseDatas($this->_sqlPath . 'insert_sql/' .$subPath . $needTableName . '.sql');			
			}
		}
		else{
			return $this->_parseDatas($this->_sqlPath . parent::SQL_FILE);
		}
    }
    
    
    /**
     * 解析数据包
     */
    protected function _parseDatas( $baseSQLFile )
    {
        $jumpurl = '';
        $file = _08_FilesystemFile::getInstance();
        if ( (bool)$file->_fopen($baseSQLFile, 'r') )
        {
            if ( $file->_flock(LOCK_SH) )
            {
                if ( isset($this->_request['ftell']) )
                {
                    $file->_fseek((int) $this->_request['ftell']);
                }
                $jumpurl = $this->_ExecSQL( $file );
                $file->_flock(LOCK_UN);
            }
            
            $file->_fclose();
        }
        else
        {
        	$jumpurl = null;
        }
        
        return $jumpurl;
    }
    
    /**
     * 开始执行SQL
     * 
     * @param  object $file    已经打开的SQL文件句柄
     * @return string $jumpurl 返回要跳转的链接
     * 
     * @since  nv50
     */
    protected function _ExecSQL( _08_FilesystemFile $file )
    {
        $flag = false;
        $jumpurl = $sqls = '';
        $maxLine = 0;

        # 一行行读取文件内容，以免一下使用太大的内存
        while( false !== ($sql = $file->_fgets(4096)) )
        {
            #$sql = str_replace("\r", "\n", $sql);
            $noteTag = ltrim(substr($sql, 0, 2));
            
            # /**/ 注释方法处理
            if ( $noteTag == '/*' )
            {
                $flag = true;
            }
            if ( $flag && (false !== strpos($sql, '*/')) )
            {
                $sql = substr($sql, strpos($sql, '*/') + 2);
                $flag = false;
            }
            
            # 过滤注释
            if ( $flag || ($noteTag == '--') || (substr(trim($sql), 0, 1) == '#') || (trim($sql) == ';') )
            {
                continue;
            }
            
            $sqls .= $sql;
            if ( (substr($sqls, strlen($sqls) - 2) == ";\n") || (substr($sqls, strlen($sqls) - 3) == ";\r\n") )
            {
                #echo $this->_replacePrefix($sqls) . "\n\n\n";
                $sqls = $this->replaceEngine(trim($sqls));
                if ( !$this->checkLoadDataCompetence() && preg_match('/^INSERT\s+INTO\s+(?<q>`)?([^\s]+?)\k<q>?\s+/i', $sqls, $_insertTableName) )
                {
                    $_insertTableName[2] = $this->_replacePrefix($_insertTableName[2]);
                    if ( !in_array($_insertTableName[2], $this->_insertTableName) && (@$this->_request['last_table'] != $_insertTableName[2]) )
                    {
                        $this->_insertTableName[] = $this->_request['last_table'] = $_insertTableName[2];
                    }
                }
                $query_flag = $this->db->query( $this->_replacePrefix($sqls), '', true );
                $ftell = $file->_ftell();
                $this->_request['ftell'] = $ftell;
                $jumpurl = ('?' . http_build_query($this->_request));

                if ( ++$maxLine > self::MAXNUM )
                {
                    break;
                }
                
                if ( $query_flag )
                {
                    # 注：该正则需要 >= PHP5.2.2
                    if ( preg_match('/^CREATE\s+TABLE(\s+IF NOT EXISTS)?\s(?<q>`)?([^\s]+?)\k<q>?\s*\(/i', $sqls, $_tableName) )
                    {
                        $tableName = trim($_tableName[3]);
                        $this->tableNames[] = $this->_replacePrefix($tableName);                        
                    }
                }
                else
                {
                	$this->badSQL[] = ('Error:' . $this->db->errno() . "\r\n" . $this->_replacePrefix($sqls));  
                }
                
                $sqls = '';
            }
        }
        
        return $jumpurl;
    }
    
    /**
     * 兼容MYSQL4.X语法
     */
    public function replaceEngine( $sql )
    {
        if ( 0 === stripos($sql, 'CREATE TABLE') )
        {        
        	//安装包中使用TYPE=***的方式打包的;
        	if(mysql_get_server_info() >= '5.5')
            {
        		if(preg_match("/\s*TYPE\s*=\s*([a-z]+?)/isU",$sql,$matches))
                {
        			$Typestr = trim($matches[0]);
        			$Type = $matches[1];
        			if(strcmp($Type,'MYISAM') && strcmp($Type,'HEAP')) $Type = 'MYISAM';
        			$sql = str_replace($Typestr,"ENGINE=$Type DEFAULT CHARSET={$this->params->configs['dbcharset']}", $sql);
        		}
        	}
            else if ( !preg_match('/DEFAULT\s+CHARSET/isU', $sql) )
            {
                $sql = preg_replace('/((ENGINE|TYPE)\s*=\s*\w+)\s+/isU', "$1 DEFAULT CHARSET={$this->params->configs['dbcharset']} ", $sql);
            }
        }
        
        return $sql;
    }
    
    /**
     * 判断当前数据库用户是否有FILE权限
     * 
     * @return bool 如果有权限返回TRUE，否则返回FALSE
     * 
     * @since  nv50
     */
    public function checkLoadDataCompetence()
    {
		
        if ( is_null(self::$_competence) )
        {
            self::$_competence = false;
            if ( is_dir($this->_sqlLoadPath) )
            {
                $q = $this->db->query("SHOW GRANTS FOR CURRENT_USER",'SILENT');
				if(empty($q)) return false; //可能有如下错误: Error: The MySQL server is running with the --skip-grant-tables option so it cannot execute this statement
                $row = $this->db->fetch_array($q);
				$courrentRow = current($row);
                if ( (0 === stripos($courrentRow, 'GRANT ALL PRIVILEGES')) || (false !== stripos($courrentRow, 'FILE')) )
                {
                    self::$_competence = true;     
                }  
            } 
        }               
        
        return self::$_competence;
    }
    
    /**
     * 初始化目录与系统缓存
     */
    public function initCache( &$message )
    {
        $timestamp = TIMESTAMP;
        $siteInfo = include(M_ROOT.'dynamic' . DIRECTORY_SEPARATOR . 'site_info.tmp.php');
        $username = trim($siteInfo['username']);
        $password = _08_Encryption::password(trim($siteInfo['password']));
        $hostname = trim($siteInfo['site_name']);
       # $hostname = @trim(urldecode($siteInfo['site_name']));
        $email = trim($siteInfo['email']);
        @unlink(M_ROOT.'dynamic' . DIRECTORY_SEPARATOR . 'site_info.tmp.php');
        if ( !isset($_SERVER['SERVER_ADDR']) && isset($_SERVER['SERVER_NAME']) )
        {
            $_SERVER['SERVER_ADDR'] = gethostbyname($_SERVER['SERVER_NAME']);
        }
        else
        {
        	$_SERVER['SERVER_ADDR'] = '127.0.0.1';
        }
        $backupdir = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].substr($timestamp, 0, 4)),8,6);
    	@mkdir(M_ROOT.'dynamic/backup_'.$backupdir, 0777);
    	$hosturl = 'http://'.$_SERVER['HTTP_HOST'];
    	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    	$cmsurl = substr($php_self,0,-17);
        $configs = cls_envBase::getBaseIncConfigs('dbhost, dbuser, dbpw, dbname, pconnect, tblprefix');
        $tblprefix = $configs['tblprefix'];
    	$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$configs['dbhost'].$configs['dbuser'].$configs['dbpw'].$configs['dbname'].$username.$password.$configs['pconnect'].substr($timestamp,0,6)),8,6).cls_string::Random(10);
    	$this->db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value, cftype) VALUES ('authkey','$authkey','visit')",'',true);
    	$this->db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value, cftype) VALUES ('hosturl','$hosturl','site')",'',true);
    	$this->db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value, cftype) VALUES ('cmsurl','$cmsurl','site')",'',true);
    	$this->db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value, cftype) VALUES ('hostname', '$hostname','site')",'',true);
		$this->db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value, cftype) VALUES ('cmsname', '$hostname','site')",'',true);
    	$this->db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value, cftype) VALUES ('backupdir','$backupdir','')",'',true);
    	$this->db->query("REPLACE INTO {$tblprefix}mconfigs (varname, value, cftype) VALUES ('dir_userfile','userfiles','upload')",'',true);
    	$this->db->query("REPLACE INTO {$tblprefix}members (mid,mname,isfounder,password,email,checked,regdate) VALUES ('1','$username','1','$password','$email','1','$timestamp');",'SILENT',true);
    	$this->db->query("REPLACE INTO {$tblprefix}members_1 (mid) VALUES ('1')",'SILENT',true);
    	$this->db->query("REPLACE INTO {$tblprefix}members_sub (mid) VALUES ('1')",'SILENT',true);
        $initPath = M_ROOT.'dynamic/records';
        _08_FileSystemPath::checkPath($initPath, true);
        _08_FileSystemPath::clear( $initPath );
        $string = '<?PHP exit(); ?>' . "\n";
        $yearmonth = date('Ym_', $timestamp);
        foreach ( array('adminlog', 'badlogin', 'currencylog') as $filename ) 
        {
            file_put_contents($initPath . DS . $yearmonth . $filename . '.php', $string);
        }
        
        cls_CacheFile::ReBuild();
        $initPath = str_replace('\\', '/', $initPath);
        $message .= <<<HTML
        <li>初始化目录 {$initPath} 完成。</li>
        <li>初始化记录 {$yearmonth}adminlog 完成。</li>
        <li>初始化记录 {$yearmonth}badlogin 完成。</li>
        <li>初始化记录 {$yearmonth}currencylog 完成。</li>
        <li style="margin-bottom:18px;">初始化系统缓存完成。</li>
HTML;
        @touch($this->_lockFile);
    }
    
    public function getter( $name )
    {
        if ( property_exists($this, $name) )
        {
            return $this->$name;
        }
        
        return null;
    }
    
    /**
     * 替换数据表前缀
     */
    protected function _replacePrefix($sql, $prefix = '#__')
    {
        $sql = $this->db->replacePrefix($sql, '{$tblprefix}');
        return $this->db->replacePrefix($sql, $prefix);
    }
    
    public function __construct( stdClass $params )
    {
        parent::__construct();
        $this->tableNames = $this->badSQL = array();
        $this->db = $params->db;
        unset($params->db);
        $this->params = $params;
		
    }
}