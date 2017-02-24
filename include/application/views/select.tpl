<?php
/**
 * HTML  select节点模板
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
$select = '';
if( !empty($this->selectdatas) && $this->type == 0 )
{
    $select .= makeselect(
        $this->selectname,
        makeoption(
            $this->selectdatas,
            empty($this->selectedkey) ? '' : $this->selectedkey,
            empty($this->defalutvalue) ? '' : $this->defalutvalue
        ),
        empty($this->selectstr) ? '' : $this->selectstr
    );
}

return $select;