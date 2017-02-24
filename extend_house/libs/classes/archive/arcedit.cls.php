<?php
class cls_arcedit extends cls_arcbase{
	
	/**
	 * 删除扩展：处理 合辑，交互
	 *
	 * @param  int $isdelbad 输入 是否 扣积分删除
	 */
	function arc_delete($isdelbad=0){
		global $db,$tblprefix;
		if(empty($this->aid)) return false;
		
		/********** 扩展部分Start ***************/
		$chid = $this->archive['chid'];
		$aid = $this->aid;
		
		// 删除楼盘-同时删除 : 新房团购,楼盘相册,户型
		// 放在 删除合辑关系前
		// ?? 成功分销过的楼盘,不删除?! (未处理)
		if($chid == 4){
			$exit = new cls_arcedit;
			$re = $db->query("DELETE FROM {$tblprefix}housesrecords WHERE aid='$aid' ");
			foreach(array(5,7,11) as $h){ //; 2,3,
				$query = $db->query("SELECT aid FROM {$tblprefix}".atbl($h)." WHERE pid3='$aid'");
				while($r = $db->fetch_array($query)){
					$exit->set_aid($r['aid'],array('chid'=>$h));
					$exit->arc_delete(0);
				}
			}
			// 删除对应分销(113)
			$query = $db->query("SELECT aid FROM {$tblprefix}".atbl(113)." WHERE pid33= '$aid'");
			while($r = $db->fetch_array($query)){
				$exit->set_aid($r['aid'],array('chid'=>113));
				$exit->arc_delete(0);
			}
			// chid=107(特价房)
			$query = $db->query("SELECT inid FROM {$tblprefix}aalbums WHERE pid='$aid'");
			while($r = $db->fetch_array($query)){
				$exit->set_aid($r['inid'],array('chid'=>107));
				$exit->arc_delete(0);
			}
			unset($exit);
		}
		// chid=2,3(房源)
		if(in_array($chid,array(2,3))){
			$exit = new cls_arcedit;
			$query = $db->query("SELECT aid FROM {$tblprefix}".atbl(121)." WHERE pid38= '$aid'");
			while($r = $db->fetch_array($query)){
				$exit->set_aid($r['aid'],array('chid'=>121));
				$exit->arc_delete(0);
			}
			unset($exit);
		}
		// chid=121(房源图片)
		$urlpid = cls_env::GetG('pid');
		if(in_array($chid,array(121)) && $urlpid){
			cnt_imgnum($urlpid,'del');
		}

		//删除合辑关系
		$abrels = cls_cache::Read('abrels');
		foreach($abrels as $k=>$abrel){
		if($abrel['available']){
			if(empty($abrel['tchids']) || empty($abrel['schids'])) continue; 
			if(empty($abrel['tbl'])){
				if(in_array($chid,@$abrel['tchids'])){
					foreach($abrel['schids'] as $ch){
						if($ntbl = atbl($ch)){
							$sql = "UPDATE {$tblprefix}".atbl($ch)." SET pid$k='0',inorder$k='0',incheck$k='0' WHERE pid$k='$aid' ";
							$query = $db->query($sql);
						}
					}
				}
			}elseif(in_array($chid,@$abrel['schids'])){
				$query = $db->query("DELETE FROM {$tblprefix}$abrel[tbl] WHERE inid='$aid' ");
			}elseif(in_array($chid,@$abrel['tchids'])){
				$query = $db->query("DELETE FROM {$tblprefix}$abrel[tbl] WHERE pid='$aid' ");
			}
		}}
		
		//删除交互
		$commus = cls_cache::Read('commus');
		foreach($commus as $k => $v){
			// 49,50:分销推荐信息,佣金提款信息(没有aid),需要的化单独处理
			if(in_array($k,array(5,10,11,31,32,34,40,42,49,50))) continue; //这些没有文档交互,10-公司资金,40-网站提问
			$db->query("DELETE FROM {$tblprefix}$v[tbl] WHERE aid='$aid'",'SILENT');
		}
		// 分销推荐信息 .... (成交结算)状态不能提交更新
		#$v = $commus[49]; 
		#$db->query("DELETE FROM {$tblprefix}$v[tbl] WHERE aids LIKE '%,$aid,%'",'SILENT');
		// 佣金提款信息,不删除..???
		
		/********** 扩展部分End ***************/
		
		$this->_arc_delete($isdelbad);
		return true;
	}
	
	function arcadd($chid = 0,$caid = 0,$aid = 0){//添加一个文档
		if($aid = parent::arcadd($chid,$caid,$aid)){
			if($chid == 2){
				$this->auser->updatefield('aczfys',@$this->auser->info['aczfys'] + 1,'members_sub');
			}elseif($chid == 3){
				$this->auser->updatefield('aesfys',@$this->auser->info['aesfys'] + 1,'members_sub');
			}elseif($chid == 9){
				$this->auser->updatefield('aqgs',@$this->auser->info['aqgs'] + 1,'members_sub');
			}elseif($chid == 10){
				$this->auser->updatefield('aqzs',@$this->auser->info['aqzs'] + 1,'members_sub');
			}
			$this->auser->updatedb();
		}
		return $aid;
	}	
}