<?php
class cls_userinfo extends cls_userbase{
	function exit_comp(){//当前会员退出公司
		global $db,$tblprefix;
		$arid = 4;
		if(!$this->info['mid']) return false;
		$db->query("UPDATE {$tblprefix}members SET pid$arid='0',inorder$arid=0,incheck$arid='0' WHERE mid={$this->info['mid']}");
		return true;
	}
	function ag2comp($pid=0,$arr = array()){//当前会员加入公司，不审核
		global $db, $tblprefix;
		$arid = 4;$schid = 2;$tchid = 3;
		if(!$pid || !$this->info['mid'] || $pid == $this->info['mid'] || $this->info['mchid'] != $schid) return false;
		if(!($abrel = cls_cache::Read('abrel', $arid)) || empty($abrel['available'])) return false;
		$pu = new cls_userinfo;
		$pu->activeuser($pid);
		if(!$pu->info['mid'] || !$pu->info['checked'] || $pu->info['mchid'] != $tchid) return false;
		$db->query("UPDATE {$tblprefix}members SET pid$arid='$pid',inorder$arid=0,incheck$arid=0 WHERE mid={$this->info['mid']}");
		return true;
	}
	function updatecrids($crids=array(),$updatedb=0,$remark='',$mode=0){//mode为1表示为手动充扣			
		global $db,$tblprefix,$timestamp;
		$curuser = cls_UserMain::CurUser();
		$currencys = cls_cache::Read('currencys');
		if(empty($this->info['mid'])) return;
		if(empty($crids) || !is_array($crids)) return;
		
		foreach($crids as $k => $v){
			if(!$v || ($k && empty($currencys[$k]))) continue;
			if($this->info['mchid'] != 2 && $k == 2) continue;
			
			$this->updatefield("currency$k",$this->info["currency$k"] + $v);
			$db->query("INSERT INTO {$tblprefix}currency$k SET
					value='$v',
					mid='".$this->info['mid']."',
					mname='".$this->info['mname']."',
					fromid='".$curuser->info['mid']."',
					fromname='".$curuser->info['mname']."',
					createdate='$timestamp',
					mode='$mode',
					remark='".($remark ? $remark : '其它原因')."'");
		}
		$this->autogroup();
		$updatedb && $this->updatedb();
	}
	
	function delete(){
		
		global $db,$tblprefix;
        if(!$this->info['mid'] || $this->info['isfounder']) return false;
		$mid = $this->info['mid']; 
		
		/*  === 注意 ==========================================
		如下 1,2 部分：需要根据应用系统 具体情况设置
		======================================================= */
		
		// 1. 删除-文档 ( --- 根据应用系统需要设置 )
		// 2-出租,3-二手房,9-求租,10-求购,101,设计师,102-装修案例,103-商品,104-公司动态,106-问答,108-招聘
		$chids = array(2,3,9,10,101,102,103,104,106,108); //一定要删除部分,115,116,117,118,119,120
		// 5-团购活动,7-楼盘相册,11-户型,13-开发商,(原则,与楼盘相关资料,一般不删)
	
		$_channel = cls_cache::Read("channels");
		$arc = new cls_arcedit;
		foreach($chids as $chidx){
			if(empty($_channel[$chidx])) continue;//模型不存在则跳过，防止客户删除了某个文档模型表
			$query = $db->query("SELECT aid FROM {$tblprefix}".atbl($chidx)." WHERE mid='$mid' AND chid='$chidx'");			
			while($r = $db->fetch_array($query)){
				$arc->set_aid($r['aid'],$chidx);
				$arc->arc_delete();		
			}
		}	
		
		// 2. 删除-交互 ( --- 接收的交互，应用系统需要设置 )	
		$cuids = array(5,11,31,32,34); //11-commu_dsc-店铺收藏
		foreach($cuids as $v){
			if($commu = cls_cache::Read('commu',$v)){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE tomid='$mid'",'UNBUFFERED');
			}	
		}

		// 3. 删除-交互 ( --- 所有发布的交互 )
		$commus = cls_cache::Read('commus');
		foreach($commus as $k => $v){
			if(in_array($k,array(50))) continue; //50-佣金提款信息(不删除...) 49-分销推荐信息
			$db->query("DELETE FROM {$tblprefix}$v[tbl] WHERE mid='$mid'",'UNBUFFERED');
		}
		
		// *. 保留/待定
		//积分/支付日志 - 保留 cms_currency0,cms_currency1 
		//??? 问吧 - 最佳答案，？！--- 没有特殊处理 - 类似的是否要特殊处理？
		
		$this->_delete();
		return true;
		
	}
}