<?php
/**
 * 展示统计数
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_View_Count extends _08_M_Ajax_View_Count_Base
{
    public function aFieldWhiteList(){
        return array('hdnum','awds','deal','yds','tjs');
    }
}