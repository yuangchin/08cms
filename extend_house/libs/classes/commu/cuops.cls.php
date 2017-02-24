<?php
defined('M_COM') || exit('No Permission');
class cls_cuops extends cls_cuopsbase{
	//用于楼盘订阅的删除
	protected function user_del_lpdy($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除订阅';
			return $this->input_checkbox($key,$cfg['title'],1,'onclick="deltip()"');
		}elseif($mode == 2){//数据		
			$this->db->delete($this->table())->where('mid='.$this->actcu['cid'])->exec();
		}
	}
	
	//用于楼盘订阅的发送邮件
	protected function user_send_email($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '发送邮件';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据		
			$timestamp = TIMESTAMP; 
			$tblprefix = cls_env::getBaseIncConfigs('tblprefix');	
			$mid = 	$this->actcu;
			$content = '';
			$modearr = array('new' => '新房动态','old' => '二手房源','rent' => '出租房源',);
			$query = $this->db->query("SELECT cu.*,cu.createdate AS ucreatedate,a.initdate,a.caid,a.chid,a.customurl,a.nowurl,a.subject FROM {$tblprefix}commu_gz cu INNER JOIN {$tblprefix}".atbl(4)." a ON a.aid=cu.aid WHERE cu.mid='$mid'");
			while($r = $this->db->fetch_array($query)){
				cls_ArcMain::Url($r,-1);
				$content .= "\n[$r[subject]]";
				foreach($modearr as $k => $v){
					$url = $k == 'new' ? $r['arcurl'] : ($k == 'old' ? $r['arcurl8'].'&fang=mai' : $r['arcurl8'].'&fang=zhu');
					$r[$k] && $content .= "&nbsp; >><a href=\"$url\" target=\"_blank\">$v</a> ";
				}
			}
			if($content){ 
				$au = new cls_userinfo;
				$au->activeuser($mid);
				if(@mailto($au->info['email'],'dingyue_subject','dingyue_content',array('mid' => $mid,'mname' => $au->info['mname'],'content' => $content))){
					$this->db->query("UPDATE {$tblprefix}commu_gz SET senddate='$timestamp' WHERE mid='$mid'");		
				}
			}
		}
	}
    
    
    
   	//用于问答答案删除
	protected function user_deleteAnswer($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除';
			return $this->input_checkbox($key,$cfg['title'],1,'onclick="deltip()"');
		}elseif($mode == 2){//数据	
            $answertype = $cfg['answertype'];
            $chid = 106;
            $aid = $this->actcu['aid'];
            $cid = $this->actcu['cid'];   
            $tblprefix = cls_env::getBaseIncConfigs('tblprefix');          
		    if($answertype == 1){
				$this->db->query("UPDATE {$tblprefix}".atbl($chid)." set stat0=stat0-1 WHERE aid='$aid'");       
                $this->db->delete('#__commu_answers')->where("tocid = $cid")->exec();
			}
            $this->db->delete('#__commu_answers')->where("cid = $cid")->exec();
			//查找该问题是否还有最佳答案，没的话，把问题设为未处理
            $bestAnswer = $this->db->select('COUNT(*) as num')->from('#__commu_answers')
              ->where("aid = $aid")
              ->_and("isanswer=1")
              ->exec()->fetch();
			if(empty($bestAnswer)){
                $this->db->update('#__archives22', array('ccid35' => '3035','answercid' => 0))->where("aid = $aid")->_and("ccid35=3036")->exec();
			}
		}
	}

    
   	//用于问答答案审核
	protected function user_checkAnswer($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '审核';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据		
            $answertype = $cfg['answertype'];
            $chid = 106;
            $aid = $this->actcu['aid'];
            $cid = $this->actcu['cid']; 
            $tblprefix = cls_env::getBaseIncConfigs('tblprefix');      
            if(empty($this->actcu['checked'])){
                $answertype == 1 && $this->db->query("UPDATE {$tblprefix}".atbl($chid)." set stat0=stat0+1 WHERE aid=$aid");
                $this->db->update('#__commu_answers', array('checked' => '1'))->where("cid = $cid")->exec();
            }	
		}
	}    

   	//用于问答答案解审
	protected function user_uncheckAnswer($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '解审';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据		
            $answertype = $cfg['answertype'];
            $chid = 106;
            $aid = $this->actcu['aid'];
            $cid = $this->actcu['cid'];         
            $tblprefix = cls_env::getBaseIncConfigs('tblprefix'); 
            $isBestAnswer = $this->actcu['isanswer']; 
            $isBestAnswer && cls_message::show('最佳答案不能解审！只能删除。',axaction(1,M_REFERER));
            if(!empty($this->actcu['checked'])){
                $answertype == 1 && $this->db->query("UPDATE {$tblprefix}".atbl($chid)." set stat0=stat0-1 WHERE aid=$aid");
			    $this->db->update('#__commu_answers', array('checked' => '0'))->where("cid = $cid")->exec();
            }   
		}
	} 
    /*
     * 楼盘意向短信群发功能
     * */
	protected function user_issms($mode = 0){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$sms = new cls_sms();
		if(empty($this->cucfgs['issmshout']) || $sms->isClosed()) return;
		if(!$mode){//初始化
		}elseif($mode == 1){//显示
			trbasic('发送短信&nbsp;<input type="checkbox" value="1" name="arcdeal[issms]" class="checkbox">','arcissms','','textarea',array('w'=>240,'h' => 80,'guide'=>'在此输入短信内容,不超过180字，建议控制在70个字内；超过70个字符，约按每70字扣一条短信费用'));
		}elseif($mode == 2){//数据		  
		   $smscon = $GLOBALS[$this->A['opre'].$key];
		   if($smscon== '') cls_message::show('短信内容不能为空',axaction(1,M_REFERER));
		   $_tel = $this->actcu['sjhm'];
		   $msg = $sms->sendSMS($_tel,$smscon,'sadm');
		//cls_message::show((empty($msg) ? "短信接口未打开" : ($msg[0] == 1 ? '通知短信已发送': '通知短信未发送')),axaction(1,M_REFERER));
		}
	}
   	//用于设置最佳答案
	protected function user_isanswer($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '最佳答案';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
            $aid = $this->actcu['aid'];
            $cid = $this->actcu['cid'];  
            $bestAnswer = $this->db->select('COUNT(*) as num')->from('#__commu_answers')
              ->where("aid = $aid")
              ->_and("isanswer=1")
              ->exec()->fetch();
            empty($this->actcu['checked']) && cls_message::show('请先审核，再进行最佳答案操作。',axaction(1,M_REFERER));
            if(empty($bestAnswer['num'])){
                $this->db->update('#__commu_answers', array('isanswer' => '1'))->where("cid = $cid")->exec();
                $this->db->update('#__archives22', array('ccid35' => '3036','answercid' => $cid))->where("aid = $aid")->exec();        		
            }
		}
	}
    
   	//用于取消最佳答案
	protected function user_noanswer($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '取消最佳';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据	    
            $aid = $this->actcu['aid'];
            $cid = $this->actcu['cid'];  	
            $this->db->update('#__commu_answers', array('isanswer' => '0'))->where("cid = $cid")->exec();
            $this->db->update('#__archives22', array('ccid35' => '3035','answercid' => 0))->where("aid = $aid")->_and("ccid35=3036")->exec(); 
        }
	}  
    
   	//用于举报答案的删除恶意操作
	protected function user_deleteVicious($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除恶意';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据      
            $commu = cls_cache::Read('commu', $this->A['cuid']);
            $cid = $this->actcu['cid'];  	
            $mid = $this->actcu['mid'];
            $user = new cls_userinfo;
            if(!empty($mid)){
                $user->activeuser($mid);
                if(!$user->isadmin() && !empty($commu['ccurrency'])) $user->updatecrids(array(1=>-max(0,$commu['ccurrency'])),1,$commu['cname'].'是恶意信息，或垃圾信息。');
            }           
            $this->db->delete('#__commu_jbask')->where("cid = $cid")->exec();
            unset($user);
        }
	} 
    	

}
