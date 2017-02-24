<?php
/**
 * 售楼公司与经纪人会员中心微信插件管理
 * 有 aid : 售楼公司-管理楼盘的微信菜单
 * 无 aid : 经纪人-店铺微信菜单
 *
 * @author    08cms
 * @copyright Copyright (C) 2008 - 2014, 08CMS Inc. All rights reserved.
 * @version   x.x
 */
defined('M_MCENTER') || exit('No Permission');
class _08_Member_Weixin_Property extends _08_Plugins_MemberHeader
{ 
    public function __construct()
    {
		parent::__construct();
        $this->_get = cls_envBase::_GET_POST();
		$this->_get['aid'] = isset($this->_get['aid']) ? intval($this->_get['aid']) : 0;
		$this->_get['mid'] = $this->_curuser->info['mid'];
		if (empty($this->_get['cache_id']) )
		{
			cls_message::show('参数非法。', M_REFERER);
		}
		if($this->_get['aid']){
			if($this->_curuser->info['mchid']!=13) cls_message::show('此项操作无权限。');
			$this->_weixin_fromid_type = 'aid';
		}elseif($this->_get['mid']){ 
			if($this->_curuser->info['mchid']!=2) cls_message::show('此项操作无权限。');
			$this->_weixin_fromid_type = 'mid';
			$ginfo = '';
		}else{
			cls_message::show('参数非法。', M_REFERER);
		}
    }
}