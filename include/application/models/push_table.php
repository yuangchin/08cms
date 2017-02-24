<?php
/**
 * 推送模型操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Push_Table extends _08_M_Active_Record
{
    private $table = '#__';
    
    public function getTableName( $tableID = '' )
    {
        return $this->table;
    }
    
    public function __construct( $tableID = '' )
    {
        parent::__construct();
		
        @list($id, $aliases) = explode('|', $tableID);
		$this->table .= cls_PushArea::ContentTable($id) . $aliases;
		
        $this->_tableName = $this->table;
    }
}