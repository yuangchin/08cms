<?php
/**
 * 选择关键字
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/mykeyword/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 * @since     nv50
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Mykeyword_Base extends _08_Models_Base
{
    public function __toString()
    {    	
    	$str = '<div class="coolbg4" onmousedown="aListSetMoving.Move(\'mykeyword\',event)">[<a href="javascript:void(0)" onclick="javascript:HideObj(\'mykeyword\');">关闭</a>]</div><div class="wsselect">';
        $Wordlinks_Table = parent::getModels('Wordlinks_Table');
        $Wordlinks_Table->select('sword', true)->limit(100)->exec();
    	while( $s = $Wordlinks_Table->fetch() ){
    		$str .= "<a href=\"javascript:void(0)\" onclick=\"javascript:PutKeyword('{$s['sword']}')\">{$s['sword']}</a> | ";
    	}
	    $str .= "</div><div class='coolbg5'>&nbsp;</div>";
    	
        return $str;
    }
}