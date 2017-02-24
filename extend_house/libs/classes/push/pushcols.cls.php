<?php
class cls_pushcols extends cls_pushcolsbase{
	
	// 推送位中显示楼盘名称
	protected function type_lpname($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = empty($cfg['title']) ? '所属楼盘' : $cfg['title'];
		}else{
			$pid = 0;
			if(!empty($data['pid3'])){
				$pid = $data['pid3'];
			}elseif(!empty($data['pid36'])){
				$pid = $data['pid36'];
			}
			$arc = new cls_arcedit;
			$arc->set_aid($pid,array('au'=>0,'ch'=>0));
			return empty($arc->archive['subject']) ? $pid : $arc->archive['subject']; 
		}
	}
	
}
