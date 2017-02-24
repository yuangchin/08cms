<?php
defined('M_COM') || exit('No Permission');
class cls_cuedit extends cls_cueditbase
{
    //价格趋势当前月的参考价
    public function fm_reference_price($field, $chid, $info = array()){
        if (empty($info) || !in_array($chid, array(
            2,
            3,
            4)))
            return;
        $db = _08_factory::getDBO();
        $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
        if (date('m', $info['month']) == date('m')) {
            //价格统计(全部的楼盘（均价）/二手房（总价）/出租（单价）进行价格统计，求平均值)
            $reference_price = $db->result_one("SELECT AVG($field) AS price FROM {$tblprefix}" .
                atbl($chid) . " WHERE $field > 0 ");
            $reference_price = $chid == 3 ? round($reference_price, 2) : round($reference_price);
            trbasic('参考', 'fmdata[clicks]', $reference_price, 'text', array('guide' =>
                    '最新价格参考值', 'w' => '20'));
        }
    }
    
    /**
     * 解决出租委托,出售委托公用一个交互，分开显示售价(zj)字段。
     **/
	public function user_zj($a,$b){
        global $chid;
	    if($b=='fm' && $this->fields[$a]['type']=='cu' && $this->fields[$a]['tpid']==36 && $chid==2){	      
	       trbasic(($this->fields[$a]['notnull']?'<font color="red">':'').' * </font>租金', $this->fmpre.'['.$a.']', $this->predata[$a], 'text',array('addstr'=>'<font class="gray">元/月</font>','w'=>'20'));
	    }else{
	       return 'undefined';
	    }
	}

    //对楼盘评论进行回复
    public function fm_replay($info = array()){
        if (empty($info))
            return;
        trbasic('留言内容', 'comment', $info['comment'], 'textarea');
        trhidden('fmdata[aid]', $info['aid']);
        trhidden('fmdata[tocid]', $info['cid']);
        trbasic('回复', 'fmdata[reply]', '', 'textarea');
    }

    //保存楼盘评论的回复
    public function sv_replay(){
        global $onlineip;
        $this->sv_set_fmdata(); //设置$this->fmdata中的值
        $fmdata = $this->fmdata;
        if (empty($fmdata))
            return;
        $db = _08_factory::getDBO();
        $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
        $timestamp = TIMESTAMP;
        $curuser = cls_UserMain::CurUser();
        $mid = $curuser->info['mid'];
        $mname = $curuser->info['mname'];
        $db->insert("{$tblprefix}commu_lppl",
            'tocid, aid, mid, mname, createdate, checked, ip, cuid, reply', array(
            $fmdata['tocid'],
            $fmdata['aid'],
            $mid,
            $mname,
            $timestamp,
            1,
            $onlineip,
            2,
            $fmdata['reply']))->exec();
        $this->sv_finish();
    }

    //新房团购活动中的订购户型
    public function fm_dghx($aid=0){
        $db = _08_factory::getDBO();
        $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
        $chid = 11;
        $str = "";
        if($this->isadd){
            $aid = empty($aid)?0:max(1,intval($aid));
        }else{
            $info = $this->predata;
            $aid = $info['aid'];
        }
       	$fromsql = "FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}aalbums b ON b.inid=a.aid"; 
    	$wheresql = "WHERE b.pid='$aid' "; 
        $db->select('a.aid,a.subject')->from("{$tblprefix}" . atbl($chid) . " a ")->innerJoin("{$tblprefix}aalbums b")->
            _on('b.inid=a.aid')->where(array('b.pid' => $aid))->exec();
        $num = 0;        
        while($r = $db->fetch()) {
            $checked = '';
            $num ++;    
            if(!$this->isadd)$checked = preg_match("/^($r[aid])$|^($r[aid])\t|\t($r[aid])\t|\t($r[aid])$/", $info['dghx']) ? 'checked="checked"' : '';
            $str .= "<input type='checkbox' name='fmdata[dghx][]' value='$r[aid]' id='dghx_$r[aid]' $checked />$r[subject]\n";
            if($num%5==0)$str .= "<br/>";
        } 
		$str || $str = "(暂无户型)";
        trbasic('订购户型', '', $str, '');
    }

    /**
     * 后台委托房源的详情中显示委托数据
     */
    public function fm_wt_info($cid){
        $db = _08_factory::getDBO();
        $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
        $db->select('a.owerstatus,a.jjrstatus,a.weituodate,m.mname ')->from("{$tblprefix}weituos a ")->innerJoin("{$tblprefix}members m")->_on('a.tmid=m.mid')->where(array('a.cid' => $cid))->exec();
  		$cy_arr[] = '受委托方';	
		$cy_arr[] = '委托状态';
		$cy_arr[] = '委托时间';
		trcategory($cy_arr);
        while($r = $db->fetch()){
 			$fover = $r['owerstatus'];
			$fsget = $r['jjrstatus'];
			if($fover == 1){
				$statusstr = '被业主取消';
			}elseif($fover == 2){
				$statusstr = '成功委托';	
			}elseif($fsget == 1){
				$statusstr = "已拒绝";
			}elseif($fsget == 2){
				$statusstr = '接受委托';
			}else{
				$statusstr = '待接受';
			}
			$mname = $r['mname'];
			$time = date('Y-m-d H:i:s',$r['weituodate']);
			echo "<tr>\n";
			echo "<td class=\"txtC\">$mname</td>\n";		
			echo "<td class=\"txtC\">$statusstr</td>\n";
			echo "<td class=\"txtC\">$time</td>\n";
			echo "</tr>\n";
        }
    }
    
    //家装>>商品>>修改脚本中的状态显示
    public function fm_state(){
        $info = $this->predata;
        $select_arr =  array('0'=>'未处理','1'=>'已处理');
        trbasic('处理状态','',makeradio('fmdata[state]',$select_arr,$info['state']),'');
    }
    
    //家装>>商品>>修改脚本中的状态修改
   	function sv_state($cfg = array()){	
		$this->sv_set_fmdata();//设置$this->fmdata中的值
        //修改只能修改状态，其他的不能修改，因此重新赋值this->fmdata      
        $this->db->update($this->table(), array('state' => $this->fmdata['state']))->where('cid='.$this->cid)->exec();
		$this->sv_finish($cfg);//结束时需要的事务，包括操作记录、成功提示等
		
	}
    
  /**
   * 楼盘价格编辑跳转链接
   */
  public function fm_dj_edit_url(){
    	trbasic('价格编辑','',"<a onclick=\"return floatwin('open_arcdj',this)\" href=\"?entry=extend&amp;extend=jiagearchive&aid=".$this->predata['aid']."&isnew=1\" class=\"scol_url_pub\"><font color='blue'>>>编辑楼盘价格</font></a>",'html22',array('guide'=>'点此可以进入楼盘价格编辑页面'));
  }

	//分销 佣金提取情况
	public function fma_fxyongjin($field){
		$s = $this->predata['status'];
		if($s!='3') return;
		$a = array('yjbase'=>'佣金提取','yjextra'=>'上级提取');
		$v = $this->predata[$field];
		$re = empty($v) ? '<span style="">未提取</span>' : '<span style="color:#00F">已提取</span>';
		trbasic($a[$field],'',$re,'');
	}

	//分销 楼盘名称列表
	public function fma_fxlpnames($exfenxiao,$mode='0'){
		$aids = $this->predata['aids']; $aida = explode(',',$aids);
		$ayjs = $this->predata['ayjs']; $ayja = explode(',',$ayjs); 
		$slps = '<table border="0"><tr><td>分销标题</td><td>佣金(元)</td><td>楼盘名称</td><td>确认项</td></tr>'; $no = 0;
		$yjhid = '<input name="fmdata[okayj]" id="fmdata[okayj]" type="hidden" value="'.(empty($this->predata['okayj']) ? '' : $this->predata['okayj']).'" rule="text" must="1">';
		foreach($aida as $k=>$aid){
			if(empty($aid)) continue;
			$pinfo = $this->getPInfo('a',$aid,1);
			if(!empty($pinfo['lpmc'])){
				$no++; 
				if(count($aida)==3){ // 只有一个有效的id
					$checked = 'checked';
					$yjhid = str_replace('value=""','value="'.$ayja[$k].'"',$yjhid);
				}else{
					$checked = $this->predata['okaid']==$aid ? 'checked' : '';	
				}
				$yjadd = $no==1 ? $yjhid : '';
				$slps .= "<tr><td> {$pinfo['subject']} </td><td> {$ayja[$k]} </td><td>".$pinfo['lpmc']." </td><td><label onClick=\"fillYj('$ayja[$k]')\"><input class='radio' type='radio' name='fmdata[okaid]' id='_fmdata[okaid]{$no}' $checked value='{$aid}'>确认</label>$yjadd</td></tr>"; 
			}
		}
		trbasic('楼盘及佣金','',"$slps</table><script>function fillYj(yj){\$id('fmdata[okayj]').value=yj;}</script>",'');
		if($this->predata['status']=='3'){
			trbasic('确认时间','',date('Y-m-d H:i',$this->predata['oktime']),'');
		}
	}
	
   // 分销推荐检测: 登录的经纪人,黑名单,推荐上限
   public function fm_fenxiao_check($exfenxiao){
		$curuser = cls_UserMain::CurUser(); $curuser->detail_data();
		$db = $this->db;
		if(empty($curuser->info['mid']) || $curuser->info['mchid']!=2){ 
			$this->message('推荐客户 请登录为经纪人！');
		}elseif(!empty($curuser->info['blacklist'])){
			$this->message('你已被列入黑名单, 不能再推荐客户！<br>');
		}
		$mid = $curuser->info['mid'];
		$cnt = $db->select('COUNT(*)')->from($this->table())
			->where(array('mid'=>$mid))->exec()->fetch(); // ->_and(array('createdate'=>'1370270040'))
		$cnt = $cnt['COUNT(*)']; //var_dump($cnt); echo "{$exfenxiao['pnum']}";
		if(intval($cnt)>=intval($exfenxiao['pnum'])){
			$this->message('推荐客户已达到上限['.$exfenxiao['pnum'].'], 不能再推荐客户！<br>');
		} 
		return $cnt;
   }

   public function sv_fenxiao_satus($exfenxiao){
		$fmdata = $this->fmdata;
		$db = $this->db;
		if($fmdata['status']=='3'){ //确认时间,//已成交(预定) 条数(可编辑)
			$this->sv_excom('oktime',TIMESTAMP); 
			#$db->query("UPDATE {$tblprefix}".atbl(113)." SET yds = yds + 1 WHERE aid='{$fmdata['okayj']}'");
			$db->update('#__'.atbl(113), "yds=yds+1")->where("aid={$fmdata['okayj']}")->exec();
		}
		if($fmdata['status']=='4'){ //无效客户（手动）检查黑名单
			$mid = $this->predata['mid'];
			$cnt = $db->select('COUNT(*)')->from($this->table())
				->where(array('mid'=>$mid))->_and(array('status'=>4))->exec()->fetch(); // ->_and(array('createdate'=>'1370270040'))
			$cnt = $cnt['COUNT(*)']; // var_dump($cnt); echo "{$exfenxiao['pnum']}";
			//echo "$cnt,".$exfenxiao['unvnum'];
			if(intval($cnt)>=intval($exfenxiao['unvnum'])){
				$db->update('#__members_2', array('blacklist' => 1))->where("mid = $mid")->exec();
			} 
		}
   }

   public function sv_fenxiao_check($exfenxiao){
		$fmdata = $this->fmdata;
		//检查该电话号码是否是无效用户
		$dianhua = $fmdata['dianhua']; if(empty($dianhua)) $this->message('提交资料[联系电话]错误！',M_REFERER);
		$this->db->select('*')->from($this->table())->where(array('dianhua'=>$fmdata['dianhua']));
		$dhchk = $this->db->_and(array('createdate'=>TIMESTAMP+$exfenxiao['vtime']*86400),'<')->exec()->fetch();
		if($dhchk) $this->message("推荐失败：<br>联系电话[{$dianhua}]<br>在{$exfenxiao['vtime']}天内已经有人推荐过！<br>",M_REFERER);
		#var_dump($dhchk); print_r($dhchk); die('xxx');
		//检测分销资源是否有效
		$aida = explode(',',$fmdata['aids']);
		$said = ''; $sayj = '';
		foreach($aida as $aid){
			if(empty($aid)) continue;
			$pinfo = $this->getPInfo('a',$aid,1);
			if(!empty($pinfo['yj'])){
				$said .= (empty($said) ? '' : ',').$aid; 
				$sayj .= (empty($sayj) ? '' : ',').$pinfo['yj'];
			}
		}
		if(empty($said) || empty($sayj)) $this->message('提交资料[楼盘数据]错误！',M_REFERER);
		return array('said'=>$said,'sayj'=>$sayj);
   }

}
