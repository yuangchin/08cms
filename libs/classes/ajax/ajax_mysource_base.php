<?php
/**
 * 选择来源
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/mysource/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 * @since     nv50
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Mysource_Base extends _08_Models_Base
{
    public function __toString()
    {
		$cms_abs = cls_env::mconfig('cms_abs');    	
		$mconfigs = cls_cache::Read('mconfigs');
		$cms_abs = $mconfigs['cms_abs'];
    	$str = '<div class="coolbg4" onmousedown="aListSetMoving.Move(\'mysource\',event)">[<a href="'.$cms_abs.'tools/edit_source.php?" onclick="HideObj(\'mysource\');removeObj(\'mysource\');return floatwin(\'editmysource\',this,400,400);">设置</a>]&nbsp;[<a href="javascript:void(0)" onclick="javascript:HideObj(\'mysource\');">关闭</a>]</div><div class="wsselect">';
    	$mysource = cls_cache::cacRead('mysource');
    	foreach($mysource as $s){
    		$str .= "<a href=\"javascript:void(0)\" onclick=\"javascript:PutSource('$s')\">$s</a> | ";
    	}
    	$str .= "</div><div class='coolbg5'>&nbsp;</div>";
    	
        return $str;
    }
}