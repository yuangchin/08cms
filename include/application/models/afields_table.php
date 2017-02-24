<?php
/**
 * 字段模型操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Afields_Table extends _08_M_Active_Record
{
    private $table = '#__afields';
    
    public function getTableName( $tableID = '' )
    {
        return $this->table;
    }
}