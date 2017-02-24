<?php
/**
 * 会员模型操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Members_Table extends _08_M_Active_Record
{
    private $table = '#__members';
    
    public function getTableName( $tableID = '' )
    {
        return $this->table;
    }
    
    public function __construct( $tableID = '' )
    {
        parent::__construct();
        $tableID && ($this->table .= $tableID);
        $this->_tableName = $this->table;
    }
}