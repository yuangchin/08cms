<?php
/**发布房源/需求信息; 修改房源; (从etools/gpub_func.php中移植过来...)
 * @example   请求范例URL：index.php?/ajax/addarc/...
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2015 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_addarc extends _08_Models_Base
{
    private $fmpre = 'fmdata';
	
	public function __toString()
    {
		$mcharset = $this->_mcharset;	
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$timestamp = TIMESTAMP; 
		$cms_abs = cls_env::mconfig('cms_abs');
		$curuser = cls_UserMain::CurUser();
		
		$msgcode = @$this->_get['msgcode'];
		$GLOBALS[$this->fmpre] = &$this->_get['fmdata']; // archivebase.cls.php里面是用GLOBALS变量的。
		$fmdata = &$GLOBALS[$this->fmpre];
		$fmdata = cls_string::iconv('utf-8',$mcharset,$fmdata);
			
		include_once _08_INCLUDE_PATH."admin.fun.php";
		
		// ------------------- 检查
		
			$aid = @$this->_get['aid'];
			$actdo = @$this->_get['actdo']; $actdo = empty($actdo) ? "save" : $actdo;
			$caid = @$this->_get['caid'];
			$action = @$this->_get['action']; $action = empty($action) ? "chushou" : $action;
			$ismob = @$this->_get['ismob'];

			if(!empty($ismob)){ //手机版发布
				if(!in_array($caid,array('3','4'))) cls_message::show('参数错误!');
				$chid = $caid==3 ? 3 : 2;
				$names = array('3'=>'二手房','4'=>'出租'); 
			}else{ 
				$chids = array('chushou'=>3,'chuzu'=>2);
				$caids = array('chushou'=>3,'chuzu'=>4);
				$names = array('chushou'=>'二手房','chuzu'=>'出租');
				if(!in_array($action,array('chushou','chuzu'))) cls_message::show('参数错误!');
				$chid = $chids[$action];
				$caid = $caids[$action];
			}
			cls_env::SetG('chid',$chid);
			cls_env::SetG('caid',$caid);
			$isadd = $actdo=='edit' ? 0 : 1; //echo "isadd=$isadd, actdo=$actdo, aid=$aid, ismob=$ismob,";
			if($aid && $ismob){ 
				$curuser = cls_UserMain::CurUser();
				$arc = new cls_arcedit;
				$arc->set_aid($aid,array('au'=>0,'ch'=>1));
				$data = $arc->archive;
				if($data['caid']!=$caid || $data['mid']!=$curuser->info['mid']){
					cls_message::show("参数错误[aid=$aid]! ");
				}
				$actname = '编辑';
				$f2dis = cls_env::mconfig('fcdisabled2');
				$f3dis = cls_env::mconfig('fcdisabled3');
			}else{
				$actname = '发布';	
			}
			$mchid = empty($curuser->info['mchid']) ? 0 : $curuser->info['mchid'];
			if(in_array($mchid,array(1,2))){ // 普通会员与经纪人进入会员中心发布
				if(empty($ismob)){
					header("location:{$cms_abs}adminm.php?action={$action}add");	
				}
			}elseif(!empty($close_gpub)){
				cls_message::show('发布房源，请注册成为普通会员或经纪人！','');	
			}elseif(!empty($mchid)){
				$curuser->info['mid'] = 0;
			}
			
			$oA = new cls_archive();
			$oA->isadd = $isadd;
			//$oA->message("本号码今天发布<span$style>限额已满</span>,不能再发布房源！");
		
			$oA->read_data();
			resetCoids($oA->coids, array(9,19)); 
			
			/* 对以前的代码的兼容,在部分定制代码中，可直接使用以下资料 */
			$chid = &$oA->chid;
			$arc = &$oA->arc;
			$channel = &$oA->channel;
			$fields = &$oA->fields;
			$oA->fields['content']['mode'] = 1;
			
			// 
			$count_gpub = cls_env::mconfig('count_gpub'); //游客发布数量
			$count_gpub = empty($count_gpub) ? 3 : $count_gpub;
			
			$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH); 
			$fyvalid = empty($exconfigs['fanyuan']['fyvalid']) ? 30 : $exconfigs['fanyuan']['fyvalid']; //租售有效期限
			$sms = new cls_sms();
			
		// ------------------- Save-开始
			if($isadd){
				$smskey = 'arcfypub'; $ckkey = 'smscode_'.$smskey; 
				if(empty($ismob) && $sms->smsEnable($smskey)){
					@$pass = smscode_pass($smskey,$msgcode,$fmdata['lxdh']);
					if(!$pass){
						cls_message::show('手机确认码有误', M_REFERER);
					}
					msetcookie($ckkey, '', -3600);
					$tel_checked = 1;
				}else{ //需传入验证码类型，否则默认为'archive' 
					$oA->sv_regcode("archive_fy");
					$tel_checked = 0;
				}
			}
			
			//*/发布数量限制
			$style = " style='font-weight:bold;color:#F00'";
			$sql = "SELECT count(*) FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON c.aid=a.aid WHERE a.mid='0' AND c.lxdh='$fmdata[lxdh]' AND a.createdate>'".($timestamp-85400)."' ";
			$all_gpub = $db->result_one($sql); $all_gpub = empty($all_gpub) ? 0 : $all_gpub;
			if($all_gpub>=$count_gpub){
				$oA->message("本号码今天发布限额已满,不能再发布房源！");
			}//*/
			
			if($isadd && $ismob){ //手机版前台为text,后台为html
				$fmdata = &$GLOBALS[$oA->fmdata];
				$fmdata['content'] = nl2br($fmdata['content']);
			}
			//添加时预处理类目，可传$coids：array(1,2)
			$oA->sv_pre_cns(array());
			
			//分析权限，添加权限或后台管理权限
			//$oA->sv_allow();
			
			//增加一个文档
			//if(!$oA->sv_addarc()){ 
			empty($oA->arc) && $oA->arc = new cls_arcedit;
			if($isadd){
				$oA->aid = $oA->arc->arcadd($oA->chid,$oA->predata['caid']);
				if(!$oA->aid){ 
					//添加失败处理
					$oA->sv_fail();
				} 
			}
			
			//类目处理，可传$coids：array(1,2)
			$oA->sv_cns(array());
			
			//字段处理，可传$nos：array('ename1','ename2')
			$oA->sv_fields(array());
			
			//可选项array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
			//处理多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
			$oA->sv_params(array('createdate','enddate',));
			
			$oA->arc->updatefield('enddate',$timestamp+$fyvalid*86400); //处理上架
			// - 游客发布，不要这个
			//$oA->sv_fyext();
			
			if($isadd){
				// 手机短信认证了默认审核
				$tel_checked && $oA->arc->updatefield('checked',$tel_checked);
			}
			
			//新增字段mchid，存放会员的模型ID，区分是个人发布还是经纪人发布
			$oA->arc->updatefield('mchid',@$curuser->info['mchid']);
			// 区分是否手机发布
			if(!empty($ismob)) $oA->arc->updatefield('ismob',1,"archives_$chid");

			//有效期
			$oA->sv_enddate();
			
			$oA->sv_update();
			
			//上传处理
			#$oA->sv_upload(); //传图不在房源id下,这里不处理; 
			
			//要指定合辑id变量名$pidkey、合辑项目$arid
			$oA->sv_album('pid3',3);
			
			if($isadd){
				$fyimg_count = cls_env::mconfig('fyimg_count');
				$fyimg_count = empty($fyimg_count) ? 20 : $fyimg_count;
				//保存图片
				$fmdata['fythumb'] = cls_env::GetG('fmdata.fythumb'); 
				$imgscfg = array('chid'=>121,'caid'=>623,'pid'=>$oA->aid,'arid'=>38,'max'=>$fyimg_count);
				//$imgscfg['props'] = array(1=>'subject',2=>'lx');
				$mre = $oA->sv_images2arcs($fmdata,'thumb',$imgscfg,'fythumb');
				$db->update('#__'.atbl($chid), array('thumb' => @$mre[1]))->where("aid = $oA->aid")->exec();
			}
			$oA->sv_fyext($fmdata,$chid);
			//自动生成静态
			$oA->sv_static();
			
			//结束时需要的事务，包括自动生成静态，操作记录及成功提示
			//$oA->sv_finish();
			
			$curuser = cls_UserMain::CurUser();
			$checked = $curuser->pmautocheck($channel['autocheck']);
			if($isadd){
				$acname2 = '添加';
				$cmsg = ($checked || $tel_checked) ? "此信息已经由系统[自动审核]！" : "<br>此信息需要管理员审核才能在前台显示"; 
				//echo 'end';
			}else{
				$acname2 = '修改';
				$cmsg = '';	
			}

			if(empty($ismob)){
				$remsg = "{$names[$action]} {$acname2}完成！$cmsg";
			}else{
				$remsg = "{$names[$caid]} {$acname2}完成！$cmsg";	
			}
			return array('error'=>'','message'=>$remsg);
		
		// ------------------- Save-结束
		
	}

}