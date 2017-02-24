<?php
/**
 * 配置数据库信息模型基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Install_Database_Base extends _08_Install_Base
{
    /**
     * 数据库驱动对象
     * 
     * @var object
     */
    private $db = null;
    
    public function __construct( array $configs )
    {
        parent::__construct();
        $this->db = _08_factory::getDBO( $configs );
        $selectDB = false;
        
        # 创建数据库
        if ( isset($configs['dbname']) )
        {
			if(@mysql_get_server_info() > '4.1')
            {
                $dbcharset = str_replace( '-', '', strtolower(cls_envBase::getBaseIncConfigs('mcharset')) );
                if ( empty($dbcharset) )
                {
                    $dbcharset = 'utf8';
                }
                @mysql_query("CREATE DATABASE IF NOT EXISTS `{$configs['dbname']}` DEFAULT CHARACTER SET $dbcharset");
        	}
            else
            {
                @mysql_query("CREATE DATABASE IF NOT EXISTS `{$configs['dbname']}`");
            } 
            $selectDB = @$this->db->select_db($configs['dbname']);
        }
        
        if ( !$selectDB || !is_resource($this->db->link) )
        {
            $this->_stop('数据库信息不正确。');
        }
    }
    
    /**
     * 创建/base.inc.php文件
     * 
     * @param  array $configs base.inc.php文件的数据内容
     * @return bool           创建成功返回TRUE，否则返回FALSE
     * 
     * @since  nv50
     */
    public function createBaseIncFile( array $configs )
    { 
        $basefile = M_ROOT . 'base.inc.php';
		if( $configfile = file2str($basefile) )
        {
			$configfile = preg_replace("/[$]dbhost\s*\=\s*[\"'].*?[\"'];/is", "\$dbhost = '{$configs['dbhost']}';", $configfile);
			$configfile = preg_replace("/[$]dbuser\s*\=\s*[\"'].*?[\"'];/is", "\$dbuser = '{$configs['dbuser']}';", $configfile);
			$configfile = preg_replace("/[$]dbpw\s*\=\s*[\"'].*?[\"'];/is", "\$dbpw = '{$configs['dbpw']}';", $configfile);
			$configfile = preg_replace("/[$]dbname\s*\=\s*[\"'].*?[\"'];/is", "\$dbname = '{$configs['dbname']}';", $configfile);
			$configfile = preg_replace("/[$]adminemail\s*\=\s*[\"'].*?[\"'];/is", "\$adminemail = '{$configs['adminemail']}';", $configfile);
			$configfile = preg_replace("/[$]tblprefix\s*\=\s*[\"'].*?[\"'];/is", "\$tblprefix = '{$configs['tblprefix']}';", $configfile);
			$configfile = preg_replace("/[$]ckpre\s*\=\s*[\"'].*?[\"'];/is", "\$ckpre = '".cls_string::Random(3)."_';", $configfile);
			$configfile = preg_replace("/[$]phpviewerror\s*\=\s*(.*?);/is", "\$phpviewerror = 1;", $configfile);
			$configfile = preg_replace("/[$]excache_prefix\s*\=\s*[\"'].*?[\"'];/is", "\$excache_prefix = '".cls_string::Random(6)."_';", $configfile);
			if( str2file(trim($configfile), $basefile) )
            {
				return true;
			}
		}
        return false;
    }
}