<?php
/**
 * 推送模块操作类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_visualization
{
    /**
     * 显示推送代码
     *
     * @param array $config_param 配置参数
     * @since 1.0
     */
    public static function showCode(array $config_param)
    {
        global $_kp, $cms_abs;
		$curuser = cls_UserMain::CurUser();
        if(!defined('IN_MOBILE') && isset($_GET['visualization']))
        {
            @include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin.fun.php';
			if(!$curuser->NoBackFunc('tpl'))
            {
                isset($_kp['addno']) && $config_param['addno'] = $_kp['addno'];
                isset($_kp['cnstr']) && $config_param['cnstr'] = $_kp['cnstr'];
                $config_param['type_id'] = (int)$_GET['visualization'];
                echo '<script type="text/javascript"> var visualization = ' . json_encode($config_param) . '; </script>';
                echo <<<EOT
                    <script type="text/javascript" src="{$cms_abs}include/js/common.js"></script>
                    <script type="text/javascript" src="{$cms_abs}include/js/admina.js"></script>
                    <script type="text/javascript" src="{$cms_abs}include/js/floatwin.js"></script>
                    <script type="text/javascript" src="{$cms_abs}include/js/visualization.js"></script>
                    <link rel="stylesheet" type="text/css" href="{$cms_abs}images/common/window.css"/>
EOT;
            }
        }
    }
}

