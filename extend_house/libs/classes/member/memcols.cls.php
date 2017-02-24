<?php
class cls_memcols extends cls_memcolsbase{
	//会员所在区域
	protected function type_szqy($key = '',$mode = 0,$data = array()){		
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			if(empty($cfg['title'])) $cfg['title'] = $field['cname'];
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$_coclasses = cls_cache::Read('coclasses',1);
			return empty($data[$key]) || empty($_coclasses[$data[$key]]) ? '-':$_coclasses[$data[$key]]['title'];
		}
	}
	
	//会员注册IP
	protected function type_regip($key = '',$mode = 0,$data = array()){		
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = $field['cname'];
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{			
			return empty($data[$key]) ? '-':$data[$key];
		}
	}
	
	//静态空间
	protected function user_static($mode = 0,$data = array()){
		global $mspacepmid;
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'C';
			isset($cfg['view']) || $cfg['view'] = 'S';
			empty($cfg['width']) && $cfg['width'] = '40';
			if(empty($cfg['title'])) $cfg['title'] = '空间';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内
			return in_array($data['mchid'],array(1,13))?'':"<a href=\"?entry=extend&extend=memberstatic&mid={$data['mid']}\" onclick=\"return floatwin('open_mem$key',this)\">".($mspacepmid && !cls_Permission::noPmReason($data,$mspacepmid) ? '<b>静态</b>' : '设置')."</a>";			
		}
	}
    
    
	//会员注册IP
	protected function type_ssgs($key = '',$mode = 0,$data = array()){	
	    $db = _08_factory::getDBO();
        $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = '所属公司';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{		  
            $re = '';
            if(!empty($data['pid4'])){
                $row = $db->select('m.cmane')->from('#__members_3 m')
                  ->where("m.mid = $data[pid4]")        
                  ->exec()->fetch();
                $re = $row['cmane'];
            }else{
                $re = '-';
            }
			empty($cfg['len']) && $cfg['len'] = '32';
			$re = cls_string::CutStr($re,$cfg['len']);
			return $re;
		}
	}
    
    /**
     * 后台经纪公司旗下员工
     */
    protected function user_employee($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);		
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = '公司员工';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
 	        $db = _08_factory::getDBO();
        	$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
        	$pid4 = intval($data['mid']);        	 
            $row = $db->select('COUNT(*) as num')->from('#__members m')
              ->where("m.pid4 = $pid4")
              ->_and(array('m.incheck4'=> '1'))       
              ->exec()->fetch();    
			return "<a href=\"".key_replace($cfg['url'],$data)."\" onclick=\"return floatwin('open_arc$key',this)\">[".$row['num']."]</a>";
		}
	}
}
