<?php
/**
 * 副件模型操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Farchives_Table extends _08_M_Active_Record
{
    private $table;
    
    public function getTableName( $tableID = '' )
    {        
        $this->table = '#__farchives';
        return ($this->table .= $tableID);
    }
}