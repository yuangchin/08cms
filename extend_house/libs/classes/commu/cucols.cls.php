<?php
defined('M_COM') || exit('No Permission');
class cls_cucols extends cls_cucolsbase{
	
	//价格趋势-地区
	protected function type_trendarea($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = '区域';
		}else{ 
			$ccid1 = $data[$key];
			$coclasses1 = cls_cache::Read('coclasses',1);
			$re = isset($coclasses1[$ccid1]) ? $coclasses1[$ccid1]['title'] : (empty($ccid1) ? '(整站)' : $ccid1);
			return $re;
		}
	}
	
	//分销 佣金(成交结算)状态下才有
	protected function user_fxyongjin($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			$cfg['title'] = '佣金(元)';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容	
			$status = $data['status'];
			$okayj = $status ==3 ? $data['okayj'] : '<span style="color:#999">0</span>';
			return "$okayj";
		}
	}
	
	//分销 楼盘名称s
	protected function user_fxlpnames($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			$cfg['title'] = '推荐楼盘';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			$aids = $data['aids'];
			$aida = explode(',',$aids);
			$okaid = $data['okaid'];
			$status = $data['status'];
			$slps = ''; 
			foreach($aida as $aid){
				if(empty($aid)) continue;
				$pinfo = $this->getPInfo('a',$aid,1);
				if(!empty($pinfo['lpmc'])){
					$slps .= (empty($slps) ? '' : ' , ').(($status=='3' && $okaid==$aid) ? "<span style='color:#00F'>{$pinfo['lpmc']}</span>" : $pinfo['lpmc']);
				}
			}
			$slps || $slps = str_replace(array('(,',',)'),array('',''),"<span style='color:#999' title='楼盘ID'>($aids)</span>");
			return "$slps";
		}
	}
	
	//定制时间方法(原有时间(dbkey)加上offset)
	//fmt:时间方法的格式化参数
	//showEnd:按到期时间方式特殊显示(分颜色或显示<永久>), 默认enddate按此方式显示
	protected function type_udate($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		$dbkey = $cfg['dbkey'];
		$offset = $cfg['offset'];
		if($mode){//处理列表区索引行
			$arr = array('cucreate' => '添加时间',);
			if(empty($cfg['title']) && isset($arr[$key])) $cfg['title'] = $arr[$key];
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			// enddate默认按showEnd方式显示，显示<永久>, 或分颜色显示过期如否
			$showEnd = isset($cfg['showEnd']) ? $cfg['showEnd'] : ($dbkey=='enddate' ? 1 : 0);
			$timestamp = TIMESTAMP;
			$null = isset($cfg['empty']) ? $cfg['empty'] : ($showEnd ? '&lt;永久&gt;' : '-');
			$fmt = isset($cfg['fmt']) ? $cfg['fmt'] : 'Y-m-d';
			$sval = date($fmt,intval($data[$dbkey]+$offset));
			if($showEnd){
				$cval = date($fmt,$timestamp);
				if($cval>$sval){ $sval = "<span style='color:#FF0000'>$sval</span>"; } //已经过期:红色
				elseif($cval==$sval){ $sval = "<span style='color:#0000FF'>$sval</span>"; } //当天过期:蓝色
			}
			return empty($data[$dbkey]) ? $null : $sval;
		}
	}
		
	//选择mid     用于楼盘订阅列表
	protected function user_selectmid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = "<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form,'selectid','chkall')\">";
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			return "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$data[mid]]\" value=\"$data[mid]\">";
		}
	}
	
	protected function user_xingbie($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = empty($data[$key]) ? "保密":($data[$key] == 1? "男":"女");			
			return $re;
		}
	}
    //房源举报：列表中的举报类型
    protected function user_leixing($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
        $info = cls_cache::Read("cufields",4);
        $lxstr = $info['leixing']['innertext'];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = '';
            if(!empty($lxstr)){
                $lxarr = explode("\n",$lxstr);
                $arr = array();
			    foreach($lxarr as $v){
					$temparr = explode('=',str_replace(array("\r","\n"),'',$v));
					$temparr[1] = isset($temparr[1]) ? $temparr[1] : $temparr[0];
					$arr[$temparr[0]] = $temparr[1];
				}
                $re = @$arr[$data[$key]];
            }
			return $re;
		}
	}
 
    /**
     * 委托房源中的小区名称显示：（要区分是否关联了小区还是临时小区）
     */
    protected function user_ex_subject($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'L';
			!isset($cfg['view']) && $cfg['view'] = 'S';
			if(empty($cfg['title'])) $cfg['title'] = '标题';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
            if(!empty($data[$key])){
    			$re = (!empty($data['thumb']) ? '<font style="color:red">图&nbsp;</font>' : '');
    			$addno = empty($cfg['addno']) ? 0 : max(0,intval($cfg['addno']));
    			$url = '';
    			if(empty($cfg['url'])){
    				if(!empty($cfg['mc'])){  //会员空间    
    					cls_ArcMain::Url($data,-1);
    					$url = $data['marcurl'];
    				}
    				else $url = cls_ArcMain::Url($data,$addno);
    			}elseif($cfg['url'] == '#'){  // 不需要url链接
    				if(!empty($data['color'])) $re .= "<span style=\"color:{$data['color']}\">";
    				$len = empty($cfg['len']) ? 40 : $cfg['len'];
    				if(!empty($data['thumb'])) $len -= 4;
    				$re .= htmlspecialchars(cls_string::CutStr($data['subject'],$len))."</span>";
    				return $re;
    			}else $url = key_replace($cfg['url'],$data); //可以自定义url格式
    			$re .= "<a ".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_subject'")." href=\"$url\" target=\"_blank\"";
    			
    			if(!empty($data['color'])) $re .= " style=\"color:{$data['color']}\"";
    			
    			$len = empty($cfg['len']) ? 40 : $cfg['len'];
    			if(!empty($data['thumb'])) $len -= 4;
    			$re .= " title=\"".htmlspecialchars($data[$key])."\">".htmlspecialchars(cls_string::CutStr($data[$key],$len))."</a>";
            }else{
                $re = "<font color='#999'>(无小区名称)</font>";          
            }
			return $re;
		}
	}
    
    /**
     * 委托房源中的小区名称显示：（要区分是否关联了小区还是临时小区）
     * $data['cu_chid']  来源于sql查询时 cu.chid as cu_chid
     */
    protected function user_wtlx($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';    
		if($mode){//处理列表区索引行
            empty($cfg['side']) && $cfg['side'] = 'C';
			if(empty($cfg['title'])) $cfg['title'] = '标题';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
            $re = $data['cu_chid']==2?'出租':'出售';
			return $re;
		}
	}
    
    /**
     * 商品购买列表中的处理状态
     */
	protected function user_state($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = empty($data[$key]) ? "未处理":"已处理";			
			return $re;
		}
	}

    /**
     * 问答类型
     */
	protected function user_ask_type($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = (empty($data['toaid']) && empty($data['tocid']) ? '回答' : (!empty($data['toaid']) ? '补充' : ($data['mid'] == $data['twmid'] ? '追问' : '补充')));		
			return $re;
		}
	}

    /**
     * 房源委托中的委托状态
     */
	protected function user_entrusted_state($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
            if(empty($cfg['title'])) $cfg['title'] = '委托状态';
        	$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
            $statusstr = '';
            switch($data['jjrstatus']){
                case 1:
                    $statusstr = "<font color=\"#FF00FF\">已拒绝委托</font>";
                    break;
                case 2:
                    $statusstr = "<font color=\"#006600\">已接受委托</font>";
                    break;
                default:
                    $statusstr = "<font color=\"#FF0000\">等待处理</font>"; 
                    break;                
            }
			return $statusstr;
		}
	}

    /**
     * 房源委托中的小区图
     */
	protected function user_xqimg($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
        $mconfigs = cls_cache::Read('mconfigs');
        $cms_abs  = $mconfigs['cms_abs'];
  
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
            if(empty($cfg['title'])) $cfg['title'] = '小区图片';
        	$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{         
			$row = $this->db->select('thumb')->from('#__archives15 a')
              ->where("a.aid = $data[pid]")
              ->limit(1)        
              ->exec()->fetch();
            $imgpath = empty($row['thumb'])?'images/common/nopic.gif':trim($row['thumb']);            
            return "<img width=\"125\" height=\"75\" src=".$cms_abs.$imgpath.">";
		}
	}


    /**
     * 房源委托中的查看信息
     */
	protected function user_connectinfo($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
        $mconfigs = cls_cache::Read('mconfigs');
        $cms_abs  = $mconfigs['cms_abs'];
  
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
            if(empty($cfg['title'])) $cfg['title'] = '信息';
        	$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
            $re = "<a onclick=\"return floatwin('open_viewweituo',this)\" href=\"?action=delegations&cid=$data[cid]\">".($data['owerstatus'] == 0 && $data['jjrstatus'] == 0 ? "查看信息并处理":"查看信息")."</a>";            
            return $re;
		}
	}
    
     /**
     * 经纪人会员中心，给我的留言的  留言者列显示
     */
	protected function user_mname($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
        $mconfigs = cls_cache::Read('mconfigs');
        $cms_abs  = $mconfigs['cms_abs'];
  
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
            if(empty($cfg['title'])) $cfg['title'] = '留言者';
        	$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
		    $url = cls_Mspace::IndexUrl($data);
			$mnamestr = $data['mid'] ? "<a href=\"$url\" target=\"_blank\">$data[mname]</a>" : $data['mname'];
            return $mnamestr;
		}
	}
    
    
   	/**
	 *栏目处理
	 */
	protected function user_caid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = '栏目';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = ($catalog = cls_cache::Read('catalog',$data['caid'])) ? $catalog['title'] : '';
			$re || $re = isset($cfg['empty']) ? $cfg['empty'] : '-';
			return $re;
		}
	}

}
