<?php
/**
 * 文档模型操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Archives_Table extends _08_M_Active_Record
{
    private $table = '#__archives';
    
    public function getTableName( $tableID = '' )
    {
        return $this->table;
    }
    
    public function __construct( $tableID = '' )
    {
        parent::__construct();
        $this->table .= preg_replace('/archives/i', '', $tableID);
        $this->_tableName = $this->table;
    }
}