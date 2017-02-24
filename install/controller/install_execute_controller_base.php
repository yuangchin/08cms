<?php
/**
 * 开始执行安装控制器基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08_INSTALL_EXEC') || exit('No Permission');
class _08_C_Install_Execute_Controller_Base extends _08_Install_Base
{
    /**
     * 要返回信息的JSON数组
     * 
     * @var array
     */
    private $jsonArray = array();
    
    private $_08_M_Install_Execute = null;
    
    /**
     * 开始执行安装
     */
    public function execute()
    {
        @set_time_limit(0);
        
        # 开始导入数据包
        $this->jsonArray['jumpurl'] = $this->_08_M_Install_Execute->insertIntoData();

        if ( empty($this->jsonArray['jumpurl']) )  # 执行完一个文件时，定位下一个文件
        {
            # 权限不足时终止安装
            if ( is_null($this->jsonArray['jumpurl']) ) # 执行当前文件出现意外
            {
                $this->jsonArray['message'] = '<li>安装失败，权限不足或安装正在进行中，请稍候再试。</li>';
                $this->jsonArray['jumpurl'] = '';
            }
            else 
            { 
                # 判断当前用户是否有FILE权限
				# 如果有权限制，使用 load_file 一次性执行完所有的数据导入
            	if ( $this->_08_M_Install_Execute->checkLoadDataCompetence() )
                {
                    $traversal = false;
                    if ( $this->_request['extdata'] == 'on' )
                    {
                        $traversal = true;
                    }
                    
                    _08_FileSystemPath::map(array($this->_08_M_Install_Execute, 'loadDataInFile'), $this->_sqlLoadPath, $traversal);
                }
                else
                {
					# 没FILE权限时只能一步步INSERT INTO
					# 切换文件要同时考虑 sql_file 与 tableindex
					
					# 安装完结构数据开始安装必选数据
					$_goend = false;
					
					if ( !isset($this->_request['sql_file']) ){
						
						$this->_request['sql_file'] = '08cms_basic';
						
					} elseif (strtolower($this->_request['sql_file']) == '08cms_basic'){
						
						$needTableNames = $this->installTableNames['basic'];	
						$tableIndex = empty($this->_request['tableindex']) ? 0 : intval($this->_request['tableindex']); 
						
						if(count($needTableNames) > ++ $tableIndex){
							
							$this->_request['tableindex'] = $tableIndex; // 只切换文件序号，不切换数据类型（basic/optional）
							
						}elseif(($this->_request['extdata'] == 'on') && !empty($this->installTableNames['optional'])){
							
							$this->_request['sql_file'] = '08cms_optional';
							$this->_request['tableindex'] = 0; // 重置 tableindex
							
						} else {
							
							$_goend = true;
						
						}
						
					} elseif (($this->_request['extdata'] == 'on') && (strtolower($this->_request['sql_file']) == '08cms_optional')){
						
						$needTableNames = $this->installTableNames['optional'];	
						$tableIndex = empty($this->_request['tableindex']) ? 0 : intval($this->_request['tableindex']); 
						
						if(count($needTableNames) > ++ $tableIndex){
							
							$this->_request['tableindex'] = $tableIndex; // 只切换文件序号，不切换数据类型（basic/optional）
							
						} else {
							
							$_goend = true;
							
						}
					
					}else{
						
						$_goend = true;

					}
					
					if($_goend) {
					
						$this->_request['sql_file'] = 'end';
						$this->jsonArray['jumpurl'] = '';
					
					}
					
                    
                    if ( $this->_request['sql_file'] !== 'end' )
                    {
                        $this->_request['ftell'] = 0;
                        $this->jsonArray['jumpurl'] = ('?' . http_build_query($this->_request));
                    }
					
                }
                
                if ( empty($this->jsonArray['jumpurl']) )
                {
                    # 在安装完最后初始化缓存
                    $this->_08_M_Install_Execute->initCache( $this->jsonArray['message'] );
                }
            }
        }
        else
        {
            $this->jsonArray['message'] = '';
            if ( isset($this->_request['sql_file']) )
            {
                if ( isset($this->_request['ftell']) && ($this->_request['ftell'] == 0) )
                {
                    if ( strtolower($this->_request['sql_file']) == '08cms_basic' )
                    {
                        $this->jsonArray['message'] = '<li>正在导入架构数据...</li>';
                    }
                    else
                    {
                    	$this->jsonArray['message'] = '<li>正在导入扩展数据...</li>';
                    }
                }                
            }
            else
            {        	    
                $tableNames = (array) $this->_08_M_Install_Execute->getter('tableNames');
                foreach ( $tableNames as $tableName ) 
                {
                    $this->jsonArray['message'] .= '<li>建立数据表 '. $tableName . '... 成功</li>';
                }
            }
            
            $badSQL = $this->_08_M_Install_Execute->getter('badSQL');
            if ( !empty($badSQL) )
            {
                $dateTime = date('Ymd', TIMESTAMP);
                @file_put_contents( _08_CACHE_PATH . 'debug' . DS . "install_sql{$dateTime}.log", implode("\r\n\r\n\r\n", $badSQL), FILE_APPEND);
            }
        }
        $this->jsonArray['insert_table_name'] = $this->_08_M_Install_Execute->getter('_insertTableName');
        @header('Content-Type: application/json;charset=UTF-8');
        echo _08_Documents_JSON::encode($this->jsonArray);
    }
    
    /**
     * 安装前验证与初始化
     */
    public function __construct()
    {
        parent::__construct();
        $this->_checkToken(); 
        $configs = cls_envBase::getBaseIncConfigs('dbhost, dbuser, dbpw, dbname, tblprefix, mcharset');
        $configs['pconnect'] = false;
        $configs['dbcharset'] = str_replace( '-', '', strtolower($configs['mcharset']) );
        $configs['flag'] = false;
        
        $db = _08_factory::getDBO( $configs );
        $selectDB = @$db->select_db($configs['dbname']);
        
        if ( !$selectDB || !is_resource($db->link) )
        {
            $this->_stop('数据库信息不正确。');
        }      
        
        if ( isset($this->_request['extdata']) )
        {
            $this->_request['extdata'] = strtolower(trim($this->_request['extdata']));
        }
        else
        {
        	$this->_request['extdata'] = '';
        }        
        
        $paramsClass = new stdClass();
        $paramsClass->configs = $configs;
        $paramsClass->db = $db;
        $this->_08_M_Install_Execute = _08_factory::getInstance('_08_M_Install_Execute', $paramsClass);
        $this->setReturnMessage();
    }
    
    /**
     * 设置返回信息
     */
    private function setReturnMessage()
    {
        $this->jsonArray = array('message' => '<li style="margin-bottom:18px;">%s完成。</li>', 'jumpurl' => '');
        if ( isset($this->_request['sql_file']) )
        {
            if ( strtolower($this->_request['sql_file']) == '08cms_basic' )
            {
                $message = '架构数据导入';
            }
            else
            {
            	$message = '扩展数据导入';
            }
        }
        else
        {
            if ( $this->_08_M_Install_Execute->checkLoadDataCompetence() )
            {
                $message = '数据库操作';
            }
            else
            {
            	$message = '建立数据表';
            }
        }            
        $this->jsonArray['message'] = sprintf($this->jsonArray['message'], $message);
    }
}