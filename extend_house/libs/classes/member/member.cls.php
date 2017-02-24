<?php
class cls_member extends cls_memberbase{
	
	// 售楼公司-管理的写字楼
	function user_xiezilou($key,$mode = 'init'){
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$xiezilou = isset($this->predata[$key]) ? $this->predata[$key] : ''; 
				trbasic('管理的写字楼','',getArchives('115',$xiezilou,100,'xiezilou[]','写字楼'),'');
				//echo "xx1,";
			break;
			case 'sv'://保存处理
				global $xiezilou;//提交过来的数据				
				$xiezilou = empty($xiezilou) ? "" : ",".implode(',',$xiezilou);
				$mchid = $this->mchid;
				$this->auser->updatefield('xiezilou',$xiezilou,"members_$mchid");
			break;
		}
	}
    
	// 售楼公司-管理的商铺
	function user_shaopu($key,$mode = 'init'){
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$shaopu = isset($this->predata[$key]) ? $this->predata[$key] : ''; 
				trbasic('管理的商铺','',getArchives('116',$shaopu,100,'shaopu[]','商铺'),'');
				//echo "xx1,";
			break;
			case 'sv'://保存处理
				global $shaopu;//提交过来的数据				
				$shaopu = empty($shaopu) ? "" : ",".implode(',',$shaopu);
				$mchid = $this->mchid;
				$this->auser->updatefield('shaopu',$shaopu,"members_$mchid");
			break;
		}
	}
    
	// 售楼公司-管理的楼盘
	function user_loupan($key,$mode = 'init'){
		$cfg = &$this->cfgs[$key];
		switch($mode){
			case 'init'://初始化
			break;
			case 'fm'://表单显示
				$loupan = isset($this->predata[$key]) ? $this->predata[$key] : ''; 
				trbasic('管理的楼盘','',getArchives('4',$loupan,100,'loupan[]','楼盘'),'');
				//echo "xx1,";
			break;
			case 'sv'://保存处理
				global $loupan;//提交过来的数据				
				$loupan = empty($loupan) ? "" : ",".implode(',',$loupan);
				$mchid = $this->mchid;
				$this->auser->updatefield('loupan',$loupan,"members_$mchid");
				//echo "xx2,$loupan,$mchid";
			break;
		}
	}
	
	// updatedb()-前,保存:管理的楼盘
	function sv_update(){
		$this->sv_items('loupan');
		$this->auser->updatedb();
	}    
    
    /**
     * 上传头像增加积分、会员中心发布页面跳转过来完善资料后自动跳回原来发布页面
     */
    function sv_all_common_ex($type=''){
        $curuser = cls_UserMain::CurUser();
        //原来头像为空的，并且提交信息头像不为空的情况下，加分
        empty($curuser->info['image']) && $this->sv_upload_image_point('image',1,'uploadpicture','上传头像');
        $jumpType = '';
        //如果是二手房、出租房源跳转过来的链接，提交之后直接返回发布页面
        !empty($type) && $jumpType = "?action=".$type;
        $this->sv_all_common(array('jumptype'=>$jumpType));
    }
    
    
    /**
     * 当头像为空时，上传头像，可获得积分（后续是否考虑，另建一个字段，标明是第一次上传头像？？因为目前积分不怎么重要）
     * @param string $currencyObj  加分字段名（规定填了哪个字段加分）
     * @param int    $currencyId   积分类别ID
     * @param int    $currencyType 加积分类型（比如发布文档、注册会员、网站投票等） 
     * @param string $remark       加积分说明
     * @param int    @mode         手动充/扣积分
     */
	function sv_upload_image_point($currencyObj,$currencyId,$currencyType,$remark,$mode=0){
		$db = _08_factory::getDBO();
        $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$curuser = cls_UserMain::CurUser();
		$currencys = cls_cache::Read('currencys');
        $timestamp = TIMESTAMP; 
		if(empty($curuser->info['mid']) || empty($currencys[$currencyId])) return;		
		$point = $currencys[$currencyId]['bases'][$currencyType];
        
		$db->query(" UPDATE {$tblprefix}members SET currency$currencyId = currency$currencyId + $point WHERE mid = ".$curuser->info['mid']);
		$db->query("INSERT INTO {$tblprefix}currency$currencyId SET
				value='$point',
				mid='".$curuser->info['mid']."',
				mname='".$curuser->info['mname']."',
				fromid='".$curuser->info['mid']."',
				fromname='".$curuser->info['mname']."',
				createdate='$timestamp',
				mode='$mode',
				remark='$remark'");
	}
}


