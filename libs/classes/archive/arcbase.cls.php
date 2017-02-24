<?PHP
defined('M_COM') || exit('No Permission');
class cls_arcbase{
	var $aid = 0;
	var $archive = array();
	var $detailed = 0;
	var $auser = '';
	var $tbl = 'archives';
	var $coids = array();
	var $channel = array();
	var $arc_tpl = array();
	var $updatearr = array();
	function init(){
		$this->aid = 0;//文档id
		$this->archive = array();//文档信息
		$this->auser = '';//文档作者对象
		$this->tbl = 'archives';//主表名称
		$this->coids = array();//主表中可用的类系id
		$this->detailed = 0;//是否读取了模型表的内容
		$this->channel = array();//文档模型
		$this->arc_tpl = array();//文档模板方案
		$this->updatearr = array();//需要更新的内容
	}
	function set_aid($aid,$param = array()){//指定aid，实例化一个文档
		global $db,$tblprefix;
		$this->init();
		if(!($aid = max(0,intval($aid)))) return 0;
		is_array($param) || $param = array();
		$param += array('chid'=>0,'au'=>0,'ch'=>0,'ttl'=>0,'nodemode'=>0);//读取时指定chid可以减少一个查询,au读取作者资料,ch读取模型资料，ttl设置查询缓存，nodemode是否手机版
		extract($param, EXTR_OVERWRITE);
		if(!($chid = max(0,intval($chid)))) $chid = achid($aid);
		if(!$chid || !($ntbl = atbl($chid))) return 0;
		$sqlstr = $ch ? "SELECT a.*,c.* FROM {$tblprefix}$ntbl a INNER JOIN {$tblprefix}archives_$chid c ON c.aid=a.aid" : "SELECT a.* FROM {$tblprefix}$ntbl a";
		if($this->archive = $db->fetch_one($sqlstr." WHERE a.aid='$aid'",$ttl)){
			$this->aid = $aid;
			$this->tbl = $ntbl;
			$this->channel = cls_channel::Config($this->archive['chid']);
			$this->arc_tpl = cls_tpl::arc_tpl($this->archive['chid'],$this->archive['caid'],$nodemode);
			if($nodemode) $this->archive['nodemode'] = 1;//设置手机版标志
			
			$splitbls = cls_cache::Read('splitbls');
			$this->coids = empty($splitbls[$this->channel['stid']]['coids']) ? array() : $splitbls[$this->channel['stid']]['coids'];
			$ch && $this->detailed = 1;
			$au && $this->arcuser();
		}
		return $this->aid;
	}
	function detail_data($auser=1,$ttl=0){//读取模型表
		global $db,$tblprefix;
		if(empty($this->aid) || $this->detailed) return;
		if($r = $db->fetch_one("SELECT * FROM {$tblprefix}archives_".$this->archive['chid']." WHERE aid='".$this->aid."'",$ttl)){
			$this->archive = array_merge($r,$this->archive);
			unset($r);
		}
		$auser && $this->arcuser();
		$this->detailed = 1;
	}
	//在手机版及常规版之间切换，重设arc_tpl
	function ChangeNodeMode($nodemode = 0){
		if($nodemode != @$this->archive['nodemode']){
			$this->arc_tpl = cls_tpl::arc_tpl($this->archive['chid'],$this->archive['caid'],$nodemode);
			$this->archive['nodemode'] = $nodemode;//设置手机版标志
		}
	}
	function arcuser(){//读取作者对象
		if(!$this->auser){
			$this->auser = new cls_userinfo;
			$this->auser->activeuser($this->archive['mid']);
		}
	}

    /**
     * 记录托管人信息
     *
     * @param string $sql 要拼接的SQL语句
     * @since 1.0
     */
    public static function recordTrusteeship(&$sql) {
		$curuser = cls_UserMain::CurUser();
        // 记录托管人信息
        if(!empty($curuser->info['atrusteeship']))
        {
            $from_id = (int)$curuser->info['atrusteeship']['from_mid'];
            $from_mname = trim($curuser->info['atrusteeship']['from_mname']);
            if($from_id) {
                $sql .= ", `from_mid` = $from_id";
            }
            if($from_mname) {
                $sql .= ", `from_mname` = '$from_mname'";
            }
        }
    }

	function arcadd($chid = 0,$caid = 0,$aid = 0){//添加一个文档
		global $db,$tblprefix,$timestamp;
		
		if(!$chid || !$caid || !($ntbl = atbl($chid))) return 0;
		$curuser = cls_UserMain::CurUser();
		$db->query("INSERT INTO {$tblprefix}archives_sub SET " . ($aid ? "aid=$aid," : '') . "chid='$chid'");
		if($aid || $aid = $db->insert_id()){
		    $sql = "INSERT INTO {$tblprefix}$ntbl SET aid='$aid',chid='$chid',caid='$caid',mid='{$curuser->info['mid']}',mname='{$curuser->info['mname']}',initdate='$timestamp',refreshdate='$timestamp',createdate='$timestamp'";
            self::recordTrusteeship($sql); // 记录托管人信息
			$db->query($sql);
			$db->query("INSERT INTO {$tblprefix}archives_$chid SET aid='$aid'");
			$this->set_aid($aid,array('chid'=>$chid,'ch'=>1,));
			$this->set_arcurl(1);
			
			$this->auser = $curuser;
			$cur_ruler = "archive"; //处理当前会员-添加文档积分
			$currencys = cls_cache::Read('currencys');
			foreach($currencys as $k => $v){
				if(isset($v['bases']["archive$chid"])){
					$cur_ruler = "archive$chid";	
					break; //找到了一个，就退出
				}
			}
			$this->auser->basedeal($cur_ruler,1,1,'发布'.$this->channel['cname']);
			//$this->auser->updatefield('archives',$this->auser->info['archives'] + 1,'members_sub');
			$this->auser->updatedb();
			return $aid;
		}else return 0;
	}
	function set_arcurl($force = 0){//初始化文档静态格式，及生成初始静态跳转文件
		//1、force强制使用新规则重写，并将已生成的静态页初始化。2、如果启用新规则或文档当前规则为空，会将规则更新到文档
		global $enablestatic;
		if(!$this->aid) return false;
		if(!($au = $this->arc_format_w(!$force))) return false;
		if(empty($enablestatic)) return false;
		for($i = 0;$i <= @$this->arc_tpl['addnum'];$i++){
			if(!empty($this->arc_tpl['cfg'][$i]['static'])) continue;
			$arcfile = M_ROOT.cls_url::m_parseurl($au,array('addno' => arc_addno($i,@$this->arc_tpl['cfg'][$i]['addno']),'page' => 1,));
			if($force || !is_file($arcfile)){
				str2file(_08_HTML::DirectUrl("archive.php?aid={$this->aid}".($i ? "&addno=$i" : '')),$arcfile);
			}
		}
	}
	function auto($updatedb=0){
		if(!$this->aid) return;
		$this->autocolor();
		$this->autoauthor();
		$this->autoletter();
		$this->autoabstract();
		$this->autokeyword();
		$this->autothumb();
		$updatedb && $this->updatedb();
	}
	function autocolor(){//设置自动颜色//????????????后续要改进这个方法
		global $color;
		if($color){
			$this->updatefield('color',$color == '#' ? '' : $color);
		}
	}
	function autorelated(){//设置自动关联
		global $autorelated,$relatedaid;
		if(empty($autorelated) && !empty($relatedaid)){
			$this->updatefield('relatedaid',$relatedaid);
		}else{
			$this->updatefield('relatedaid','');
		}
	}
	function autoauthor($updatedb=0){
		if(($field = cls_cache::Read('field',$this->archive['chid'],'author')) && empty($this->archive['author'])){
			$this->updatefield('author',addslashes($this->archive['mname']));
		}
		$updatedb && $this->updatedb();
	}
	function autoletter($updatedb=0){
		$this->detail_data();
		$this->channel['autoletter'] && $this->updatefield('letter',autoletter($this->archive[$this->channel['autoletter']]));
		$updatedb && $this->updatedb();
	}
	function autokeyword($updatedb=0){
		$this->detail_data();
		if(($field = cls_cache::Read('field',$this->archive['chid'],'keywords')) && $this->channel['autokeyword'] && empty($this->archive['keywords'])){
			$this->updatefield('keywords',cls_string::keywords(addslashes(autokeyword(@$this->archive[$this->channel['autokeyword']]))));
		}
		$updatedb && $this->updatedb();
	}
	function autoabstract($updatedb=0){
		$this->detail_data();
		if(($field = cls_cache::Read('field',$this->archive['chid'],'abstract')) && $this->channel['autoabstract'] && empty($this->archive['abstract'])){
			$this->updatefield('abstract',addslashes(autoabstract($this->archive[$this->channel['autoabstract']])));
		}
		$updatedb && $this->updatedb();
	}
	function autothumb($updatedb=0){
		$c_upload = cls_upload::OneInstance();
		$this->detail_data();
		$fields = cls_cache::Read('fields',$this->archive['chid']);
		if(isset($fields['thumb']) && $this->channel['autothumb'] && empty($this->archive['thumb']) && !empty($this->archive[$this->channel['autothumb']])){
			$thumb = $c_upload->thumb_pick($this->archive[$this->channel['autothumb']],$fields[$this->channel['autothumb']]['datatype'],$fields['thumb']['rpid']);
			$this->updatefield('thumb',addslashes($thumb));
		}
		$updatedb && $this->updatedb();
	}
	function autopush(){ //自动推送
		$pa = cls_pusher::paidsarr('archives',$this->archive['chid'],$this->archive['caid']);
		foreach($pa as $paid=>$paname){ 
			$pusharea = cls_PushArea::Config($paid);
			if(!empty($pusharea['autopush'])){ //不用返回值
				cls_pusher::push($this->archive,$paid,21); 
			}
		}
	}
	function autoclick(){ //默认点击数(仅发布时使用), mclicks,wclicks要不要同步更新?
		$dmin = intval(@$this->channel['click_defmin']); 
		$dmax = intval(@$this->channel['click_defmax']); //print_r($this->updatearr); die();
		if($dmin && $dmax && empty($this->updatearr[$this->tbl]['clicks'])){ 
			$defclick = rand($dmin,$dmax); 
			$this->updatefield('clicks',$defclick);
		}
	}
	
	function no_auto_static($val = 0){//作废的函数，暂时保留以免引用脚本出错
	}
	function arc_check($check=1,$updatedb=0){//$check执行审核或解审的操作
		if(empty($this->aid)) return;
		if($this->updatefield('checked',$check)){
			$curuser = cls_UserMain::CurUser();
			$this->updatefield('editorid',$curuser->info['mid']);
			$this->updatefield('editor',$curuser->info['mname']);
			$updatedb && $this->updatedb();
		}
	}
	function autocheck(){
		$this->arcuser();
		if($this->auser->pmautocheck($this->channel['autocheck'])) $this->arc_check(1);
	}
	function sale_define($updatedb=0){//作废的函数，暂时保留以免引用脚本出错
	}
	function arc_caid($caid=0,$updatedb=0){//修改栏目
		if(!$caid || $caid == $this->archive['caid'] || !($catalog = cls_cache::Read('catalog',$caid,''))) return;
		if(!in_array($this->archive['chid'],explode(',',$catalog['chids']))) return;
		$this->updatefield('caid',$caid);
		unset($catalog);
		$updatedb && $this->updatedb();
	}
	function set_ccid($mode,$ids,$coid,$date=0,$updatedb=0){//批量设置分类
		//使用重设0、增补1、去除2三种模式进行设置
		$cotypes = cls_cache::Read('cotypes');
		if(!in_array($coid,$this->coids) || !empty($cotypes[$coid]['self_reg'])) return;
		$this->arc_ccid(idstr_mode($mode,$cotypes[$coid]['asmode'],$ids,$this->archive["ccid$coid"],1),$coid,$date,$updatedb);
	}
	function arc_ccid($ids,$coid,$date=0,$updatedb=0){//设置分类
		global $timestamp;
		$cotypes = cls_cache::Read('cotypes');
		if(!in_array($coid,$this->coids) || !empty($cotypes[$coid]['self_reg'])) return;
		if($ids != $this->archive["ccid$coid"]){
			if($ids = array_filter(explode(',',$ids))){
				$oids = array_filter(explode(',',$this->archive["ccid$coid"]));
				foreach($ids as $k => $id){
					if(in_array($id,$oids)) continue;
					if(!($coclass = cls_cache::Read('coclass',$coid,$id)) || !in_array($this->archive['chid'],explode(',',$coclass['chids']))) unset($ids[$id]);
				}
			}
			$this->updatefield("ccid$coid",$ids ? (empty($cotypes[$coid]['asmode']) ? $ids[0] : (','.implode(',',$ids).',')) : '');
		}
		if($cotypes[$coid]['emode']) $this->updatefield("ccid{$coid}date",$ids && $date && cls_string::isDate($date) ? strtotime($date) : 0);
		$updatedb && $this->updatedb();
	}
	function arc_delete($isdelbad=0){
		global $db,$tblprefix;
		if(empty($this->aid)) return false;
		/********** extend_example/libs/xxxx/arcedit.cls.php中同名函数的扩展部分 ***************/
		$this->_arc_delete($isdelbad);
		return true;
	}
	function _arc_delete($isdelbad=0){//执行文档删除时一些必要的操作，省去核心升级时更新到扩展arcedit.cls.php的相应操作
		global $db,$tblprefix;
		if(empty($this->aid)) return;
		$wherestr = "WHERE aid='{$this->aid}'";
		$this->auser || $this->arcuser();

		//删除内容页静态文件
		$this->archive = ArchiveStaticFormat($this->archive);
		if($this->archive['nowurl']){
			for($i = 0;$i <= @$this->arc_tpl['addnum'];$i ++){
				$UrlFormat = cls_url::m_parseurl($this->archive['nowurl'],array('addno' => arc_addno($i,@$this->arc_tpl['cfg'][$i]['addno'])));
				$UrlFormat && m_unlink($UrlFormat);
			}
		}
		
		//删除文档的关联推送信息
		cls_pusher::DelelteByFromid($this->aid,'archives');

		//删除文档表上的记录
		$db->query("DELETE FROM {$tblprefix}archives_".$this->archive['chid']." WHERE aid='{$this->aid}'", 'UNBUFFERED');
		$db->query("DELETE FROM {$tblprefix}archives_sub WHERE aid='{$this->aid}'", 'UNBUFFERED');
		$db->query("DELETE FROM {$tblprefix}".$this->tbl." WHERE aid='{$this->aid}'", 'UNBUFFERED');

		//删除这个文档的附件
		$uploadsize = 0;
		$query = $db->query("SELECT * FROM {$tblprefix}userfiles WHERE aid='{$this->aid}' AND tid=1");
		while($r = $db->fetch_array($query)){
			atm_delete($r['url'],$r['type']);
			$uploadsize += ceil($r['size'] / 1024);
		}
		$this->auser->updateuptotal($uploadsize,1,1);
		$db->query("DELETE FROM {$tblprefix}userfiles WHERE aid='{$this->aid}' AND tid=1", 'UNBUFFERED');
		
		// 处理当前会员-删除文档减积分
		if($isdelbad){
			$cur_ruler = "archive"; 
			$currencys = cls_cache::Read('currencys');
			foreach($currencys as $k => $v){
				if(isset($v['bases']["archive".$this->archive['chid']])){
					$cur_ruler = "archive".$this->archive['chid'];
					break; //找到了一个，就退出
				}
			}
			$this->auser->basedeal($cur_ruler,0,2,'删除'.$this->channel['cname']);
			//$this->auser->updatefield('archives',$this->auser->info['archives'] + 1,'members_sub');
			$this->auser->updatedb();
		}
		$this->init();
	}
	function exit_album($arid = 0,$pid = 0){
		global $db,$tblprefix;
		if(empty($arid)) return false;
		$abrel = cls_cache::Read('abrel',$arid);
		if(empty($abrel['tbl'])){
			$sql = "UPDATE {$tblprefix}{$this->tbl} SET pid$arid='0',inorder$arid='0',incheck$arid='0' WHERE aid='{$this->aid}' LIMIT 1";
		}else{
			$sql = "DELETE FROM {$tblprefix}$abrel[tbl] WHERE inid = '{$this->aid}' AND arid='$arid' AND pid='$pid'";
		}
		$db->query($sql);
		return true;
	}
	function incheck($check = 1,$arid = 0,$pid = 0){//当前文档在指定合辑$pid中有效或无效
		global $db,$tblprefix;
		if(empty($arid)) return false;
		$check = empty($check) ? 0 : 1;
		$abrel = cls_cache::Read('abrel',$arid);
		if(empty($abrel['tbl'])){
			$sql = "UPDATE {$tblprefix}{$this->tbl} SET incheck$arid='$check' WHERE aid='{$this->aid}' LIMIT 1";
		}else{
			$sql = "UPDATE {$tblprefix}$abrel[tbl] SET incheck='$check' WHERE inid='{$this->aid}' AND arid='$arid' AND pid='$pid' LIMIT 1";
		}
		$db->query($sql);
		return true;
	}
	function set_album($pid=0, $arid=0, $arr = array()){#将当前文件归入到 $pid 的合辑中，可能是文档或会员。$arid 为合辑项目id
		global $db, $tblprefix;
		if(empty($pid) || empty($arid))return false;
		$abrel = cls_cache::Read('abrel', $arid);
		!empty($abrel['schids']) && !is_array($abrel['schids']) && $abrel['schids'] = explode(',', $abrel['schids']);
		!empty($abrel['tchids']) && !is_array($abrel['tchids']) && $abrel['tchids'] = explode(',', $abrel['tchids']);
		#合辑项目类型判断
		if(empty($abrel) || empty($abrel['available']) || $abrel['source'] != 0 || ($abrel['target'] == 0 && $pid == $this->archive['aid']))return false;
		#不符合的文档模型
		if(!empty($abrel['schids']) && !in_array($this->archive['chid'], $abrel['schids']))return false;
		if($abrel['target'] == 0){
			#归入到文档
			if(!$row = $db->fetch_one("SELECT chid FROM {$tblprefix}archives_sub WHERE aid='$pid'"))return false;//$文档不存在
			#不符合的文档模型
			if(!empty($abrel['tchids']) && !in_array($row['chid'], $abrel['tchids']))return false;
		}else{
			#归入到会员
			if(!$row = $db->fetch_one("SELECT mchid FROM {$tblprefix}members WHERE mid='$pid'"))return false;//$会员不存在
			#不符合的会员模型
			if(!empty($abrel['tchids']) && !in_array($row['mchid'], $abrel['tchids']))return false;
		}
		if(empty($abrel['tbl'])){
			#判断合辑数量，没有判断本身有没有入到这个辑内
			if(!empty($abrel['maxnums']) && $db->result_one("SELECT COUNT(*) FROM {$tblprefix}".atbl($pid,2)." WHERE pid$arid='$pid'") > $abrel['maxnums'])return false;
			$sql = "UPDATE {$tblprefix}{$this->tbl} SET pid$arid='$pid',inorder$arid=0,incheck$arid=" . (empty($abrel['autocheck']) ? '0' : '1') . " WHERE aid='{$this->archive['aid']}' LIMIT 1";
			            #若自动推送，并且推送模型内有字段pid$arid，则补全pid$arid            	
           	$pa = cls_pusher::paidsarr('archives',$this->archive['chid'],$this->archive['caid']);      
    		foreach($pa as $paid=>$paname){ 
                #查看推送模型是否有pid$arid，并且启用
                $pfields = cls_fieldconfig::InitialFieldArray('pusharea',$paid);
                $pidfield = "pid".$arid;
                if(!empty($pfields[$pidfield]) && !empty($pfields[$pidfield]['available'])){           
        			$pusharea = cls_PushArea::Config($paid); 
                    #要设置了自动推送，并且含有pid$arid 
        			if(!empty($pusharea['autopush']) && !empty($pusharea['sourcefields'])){
        				$ispid = 0;
                        foreach($pusharea['sourcefields'] as $k => $v){
        				    if(strstr($v['from'],'pid')){
        				        $ispid=1;
                                break;
                            }
        				}
                        if($ispid){
                            $db->query("UPDATE {$tblprefix}$paid SET pid$arid='$pid' WHERE fromid='{$this->archive['aid']}' LIMIT 1");
                        }
        			}
                }
    		}			
		}else{
			#判断合辑数量，没有判断本身有没有入到这个辑内
			if(!empty($abrel['maxnums']) && $db->result_one("SELECT COUNT(*) FROM $tblprefix$abrel[tbl] WHERE pid='$pid' AND arid='$arid'") > $abrel['maxnums'])return false;
			if(!empty($abrel['issingle'])
				#如果不允许入多个合辑，删除旧合辑记录，如果存在
				&& $db->result_one("SELECT COUNT(*) FROM $tblprefix$abrel[tbl] WHERE arid='$arid' AND inid='{$this->archive['aid']}'")){
					$sql1 = "UPDATE $tblprefix$abrel[tbl] SET ";
					$sql2 = " WHERE arid='$arid' AND inid={$this->archive['aid']}";
			}else{
					$sql1 = "INSERT INTO $tblprefix$abrel[tbl] SET arid='$arid',inid='{$this->archive['aid']}',";
					$sql2 = '';
			}
			$sql = $sql1 . "pid='$pid',inorder=0,incheck=" . (empty($abrel['autocheck']) ? '0' : '1') . $sql2;
		}
		$db->query($sql);
		return true;
	}
	function push($paid){
		if(cls_pusher::SourceNeedAdv($paid)){
			$this->detail_data();
		}
		return cls_pusher::push($this->archive,$paid);
	}
	function readd($updatedb=0){//刷新或重发布
		global $timestamp;
		if(!$this->aid) return;
		$this->updatefield('refreshdate',$timestamp);
		$updatedb && $this->updatedb();
	}
	function setend($days=0,$updatedb=0){//days:设置多少天有效期,0为即时到期，-1为永不过期
		global $timestamp;
		if(!$this->aid) return false;
		$days = max(-1,intval($days));
		$this->updatefield('enddate',$days == -1 ? 0 : $timestamp + $days * 24 * 3600);
		$updatedb && $this->updatedb();
	}
	function updatefield($fieldname,$newvalue,$tbl='archives'){
		in_array($tbl,array('archives','archives_sub',$this->tbl)) || $this->detail_data();
		//过滤掉无效的字段
		if($this->archive[$fieldname] != stripslashes($newvalue)){
			$this->archive[$fieldname] = stripslashes($newvalue);
			if($tbl == 'archives') $tbl = $this->tbl;
			$this->updatearr[$tbl][$fieldname] = $newvalue;
			return true;
		}else return false;
	}
	//更新会员表存放已刷新次数的字段，用于刷新操作readd
	function update_refreshes($fieldname){
		global $db,$tblprefix,$memberid;
		$db->query("UPDATE {$tblprefix}members SET $fieldname = $fieldname + 1 WHERE mid = '$memberid'");
	}
	function updatedb($chid=''){
            //判断400电话
            if($chid == 4 && isset($this->updatearr['archives_4']['extcode']))
            {
                include_once 'etools/phone4008Service.php';
                $_4008Service = new _08_phone4008Service;
                
                //添加分机号
                $data =array('loginname'=>'CD000888','loginpwd'=>'385000DC','extcode'=>$this->updatearr['archives_4']['extcode'],'tellist'=>$this->updatearr['archives_4']['tel'],'custname'=>'test','workgroupname'=>'test','content'=>'你好，欢迎致电创典房产，请拨分机号','addtion'=>'1,0.13,2,0');
                $return = $_4008Service->AddCustomer($data);

                if(!$return[1]['result'])
                {
                    return FALSE;
                }              
            }
		global $db,$tblprefix,$timestamp;
		if(empty($this->aid)) return;
		if($this->updatearr){
			$this->updatearr[$this->tbl]['updatedate'] = $timestamp;
			$this->updatearr[$this->tbl]['needstatics'] = '';//认为文档修改后需要更新静态页面
		}
		foreach(array($this->tbl,'archives_sub',"archives_{$this->archive['chid']}") as $tbl){
			if(!empty($this->updatearr[$tbl])){
				$sqlstr = '';foreach($this->updatearr[$tbl] as $k => $v) $sqlstr .= ($sqlstr ? "," : "")."`".$k."`='".$v."'";
                if(preg_match('/archives\d+/', $tbl)) {
                    self::recordTrusteeship($sqlstr); // 记录托管人信息
                }
				$sqlstr && $db->query("UPDATE {$tblprefix}$tbl SET $sqlstr WHERE aid={$this->aid}");
			}
		}
                
		$this->updatearr = array();
	}
//----------------------------------------------------------------------------------------------
	function arc_crids($isatm = 0){//计算当前附件下载或播放对当前用户要扣除的积分值。
		$vcps = cls_cache::Read('vcps');
		$curuser = cls_UserMain::CurUser();
		if($curuser->info['mid'] && $curuser->info['mid'] == $this->archive['mid']) return 0;//自已的文章
		if(!$this->archive['checked']) return 0;//未审核的文章
		if($curuser->paydeny($this->aid,$isatm)) return 0;//已经付费或免费订阅的用户
		$crids = array();
		$tax = $isatm ? 'ftax' : 'tax';
		if(($catalog = cls_cache::Read('catalog',$this->archive['caid'])) && !empty($catalog[$tax.'cp']) && !empty($vcps[$tax][$catalog[$tax.'cp']])){
			$cparr = explode('_',$catalog[$tax.'cp']);
			$crids[$cparr[0]] = -$cparr[1];
		}
		unset($catalog);
		return $crids ? $crids : 0;
	}
//-------------------------------------------------------------------------
	function urlpre($addno=0,$filterstr='',$static=0,$kp=1){//分页url模印含{$page}的可变参数//
		global $mobiledir;
		if(!empty($this->archive['nodemode'])){
			$re = $mobiledir.'/archive.php?aid='.$this->aid.($addno ? '&addno='.$addno : '').$filterstr.'&page={$page}';
		}elseif($static){
			$au = $this->arc_format_w($kp);
			$re = $au ? cls_url::m_parseurl($au,array('addno' => arc_addno($addno,@$this->arc_tpl['cfg'][$addno]['addno']))) : '';
		}else{
			$re = cls_url::en_virtual('archive.php?aid='.$this->aid.($addno ? '&addno='.$addno : '').$filterstr.'&page={$page}',@$this->arc_tpl['cfg'][$addno]['novu']);
		}
		return $re ? cls_url::view_url($re) : '';
	}
	function filepre($addno=0,$kp=1){
		$au = $this->arc_format_w($kp);
		return $au ? cls_url::m_parseurl($au,array('addno' => arc_addno($addno,@$this->arc_tpl['cfg'][$addno]['addno']))) : '';
	}
	function arc_format_w($kp=1){
		global $db,$tblprefix;
		$ou = $this->archive['nowurl'];
		$this->archive = ArchiveStaticFormat($this->archive,$kp);
		$au = $this->archive['nowurl'];
		if($ou != $au){
			if($au){
				$db->query("UPDATE {$tblprefix}{$this->tbl} SET nowurl='$au' WHERE aid={$this->aid}");
			}else{
				$au = $ou;//如果即时生成的au为空，则保持原格式
			}
			$this->updatefield('nowurl',$au);
		}
		return $au;
	}
	function tplname($addno = 0){//优先使用文档单独定义的模板
		$arctpls = !empty($this->archive['nodemode']) || empty($this->archive['arctpls']) ? array() : explode(',',$this->archive['arctpls']);
		return !empty($arctpls[$addno]) ? $arctpls[$addno] : (empty($this->arc_tpl['cfg'][$addno]['tpl']) ? '' : $this->arc_tpl['cfg'][$addno]['tpl']);
	}
	function autostatic($kp = 1){
		$this->channel['autostatic'] && $this->arc_static($kp);
	}
	function arc_static($kp = 1){
		if(cls_env::mconfig('enablestatic')){
			for($i = 0;$i <= @$this->arc_tpl['addnum'];$i++) $this->tostatic($i,$kp);
		}
	}
	function tostatic($addno = 0,$kp = 1){//kp保持原静态格式
		$re = cls_ArchivePage::Create(array('arc' => $this,'addno' => $addno,'kp' => $kp,'inStatic' => true));
		return $re;
	}
	function need_static_refresh($addno = 0){//根据文档定义的更新周期，分析是否需要被动更新静态页面
		global $archivecircle,$timestamp;
		if($addno > $this->arc_tpl['addnum']) return false;
		if(!($nss = $this->archive['needstatics'])) return true;
		$nss = explode(',',$nss);
		$period = empty($this->arc_tpl['cfg'][$addno]['period']) ? ($archivecircle ? $archivecircle * 60 : 12*3600) : $this->arc_tpl['cfg'][$addno]['period'] * 60;
		return $timestamp - $period > @$nss[$addno] ? true : false;
	}

}
