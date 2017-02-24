<?php
/**
 * 房产文档类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class _08House_Archive extends cls_archive
{
    /**
     * 在文档逻辑里开启以下CK编辑器插件开关
     */
    # 插入房源信息，目前该功能只在资讯管理页开通
    public $__ck_house_info = '08cms_house_info';
    
    # 插入小区图，目前该功能只在房源管理页开通
    public $__ck_plot_pigure = '08cms_plot_pigure';
    
    # 插入户型图，目前该功能只在房源管理页开通
    public $__ck_size_chart = '08cms_size_chart';
    
    # 分页管理
    public $__ck_paging_management = '08cms_paging_management';
}