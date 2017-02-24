<?php
/**
 * ajax提交POST表单，通用处理代码
 *
 * @author    Peace@08cms
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_cuAjaxPost_Base extends _08_Models_Base
{
    protected $cuid = 0;
	protected $cutype = 0;
	
	protected $aid = 0;
	protected $tomid = 0;
	protected $tocid = 0;
	
	protected $cucfgs = array();
	protected $exfields = array();
	protected $pinfo = array();
	
	protected $verify = '';
	protected $regcode = '';
	protected $fmpre = '';
	protected $cucbs = ''; //多选字段:传输时用,分开;保存时用tab键分开
	protected $cucbvals = array(); //多选储存值
	
	//protected $cid = 0; //添加时生成? 
    
	// 不适合需要，就扩展
	// 扩展注意继承本类：extends _08_M_Ajax_cuAjaxPost_Base
    //  'aj_minfo',     //同时返回会员资料
    //  'aj_ainfo',     //同时返回文档资料
    //  'aj_func',      //处理cuedit的sv_Favor(),sv_Mood(),sv_Vote()
    public function __toString()
    {
		$this->cuaj_post_init();
		$oA = new cls_cuedit($this->defCfgs());  
		$oA->add_init($this->defPid(),'',array('setCols'=>1)); 
		$this->pinfo = $oA->pinfo; 
		
        if($this->aj_func && in_array($this->aj_func,array('Favor','Mood','Vote'))){
            $re = $this->cu_funcs($oA, $this->aj_func); 
            return array('error'=>'', 'message'=>'提交完成！', 'result'=>$re); //, 'cu_data'=>$fmdata
        }
        
		$oA->sv_repeat($this->repCookie(), 'both'); // array('aid'=>$aid,'tocid'=>$tocid)
		$oA->sv_set_fmdata();//设置$this->fmdata中的值 
		$oA->sv_items();//保存数据到数组，此时未执行数据库操作
		$oA->sv_regcode("commu$this->cuid"); //认证码靠后,如果之前出现问题返回,认证码还有效
		$this->cid = $oA->sv_insert($this->extFields());//array('aid'=>$aid,'tocid'=>$tocid,'ip'=>$onlineip,)
		#$oA->sv_upload();//上传处理
		//附加操作, 发短信, 自定义操作..... 
		return $oA->sv_ajend('提交成功！',array('aj_ainfo'=>$this->aj_ainfo,'aj_minfo'=>$this->aj_minfo));//结束时需要的事务
        //'';//$contents;
    }
    
    //处理cuedit的sv_Favor(),sv_Mood(),sv_Vote()
	public function cu_funcs($oA=null, $func='')
    {
		// sv_Favor($pfield='aid')
        // sv_Mood($pfield='aid', $fix='opt', $no='1', $nos='1,2', $add=1)
        // sv_Vote($cid,          $fix='opt', $no='1', $nos='1,2', $add=1)
		$dbfields = $oA->getFields(); //print_r($dbfields);
        foreach(array('pfield','fix','no','tocid') as $k){
			$$k = empty($this->_get[$k]) ? '' : $this->_get[$k];	
		}
        $_fkeys = array('pfield'); //print_r($pfield);
        if($func=='Favor' && $pfield && in_array($pfield,$dbfields)){
            if(empty($oA->pinfo)) return 'Error';
            return $oA->sv_Favor($pfield);
        }
        if($func=='Mood' && $pfield && in_array($pfield,$dbfields)){ 
            if(in_array($fix,$dbfields)){
				$no = $nos = '';
			}else{
				$nos = $no;		
			} //echo "($fix$no)"; 
			return in_array("$fix$no",$dbfields) ? $oA->sv_Mood($pfield,$fix,$no,$nos) : 'Error';
        }
        if($func=='Vote' && $tocid){ 
            return in_array("$fix$no",$dbfields) ? $oA->sv_Vote($tocid,$fix,$no,"$no") : 'Error';
        }
        return 'Error';
    }
    
	// init初始化(常用参数)
	protected function cuaj_post_init(){
		$a1 = array('cuid','aid','tomid','tocid'); 
		foreach($a1 as $k){
			$this->$k = empty($this->_get[$k]) ? 0 : floatval($this->_get[$k]);	
		}
		$a2 = array('cutype','verify','regcode','fmpre','cucbs','aj_minfo','aj_ainfo','aj_func'); //'cureval',
		foreach($a2 as $k){
			$this->$k = empty($this->_get[$k]) ? '' : $this->_get[$k];
			cls_env::SetG($k, $this->$k);
		}
		if(empty($this->cutype) && $this->aid) $this->cutype = 'a'; //有aid默认为针对文档
		if(empty($this->cutype) && $this->tomid) $this->cutype = 'm'; //有tomid默认为针对会员
		$this->cucfgs = cls_cache::Read('commu',$this->cuid);
		$this->cufields = cls_cache::Read('cufields',$this->cuid);
		if(empty($this->cuid)) cls_message::show('错误！');
		empty($this->fmpre) && $this->fmpre = 'fmdata';
		$this->ip = cls_env::OnlineIP();
		cls_env::SetG('inajax', 1); //设置:ajax提交 $this->_get['inajax'] ? 1 : 0
		//这里ajax里面用$this->_get获取值, 到了cubasic里面要用cls_env::SetG才能获取
		$fmdata = @$this->_get[$this->fmpre];
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$cucba = empty($this->cucbs) ? array(-1) : explode(',',str_replace(array($this->fmpre,'[',']'),'',$this->cucbs));
		if(!empty($fmdata)){
			foreach($fmdata as $k=>$v){ //print_r($cucba);  //echo " --- $k \n";
				$fmdata[$k] = @cls_string::iconv("utf-8",$mcharset,$v);
				// 多选字段处理：用字符串传过来,转化为数组给fields.cls使用
				// 在ajax里面数组会转化,这里绕开,使用本类$this->cucbvals处理 
				if(in_array($k,$cucba)){ 
					$this->cucbvals[$k] = str_replace(",","\t",$fmdata[$k]);
					unset($fmdata[$k]); //$fmdata[$k] = explode(",",$fmdata[$k]); 
				}
				if(!isset($this->cufields[$k])){
					@$this->exfields[$k] = cls_string::SafeStr($fmdata[$k]);
				}
			} 
		}else{
			;//	
		}
		cls_env::SetG($this->fmpre, $fmdata);
	}
	// 默认init的cfgs
	protected function defCfgs(){
		$_init = array(
			'cuid' => $this->cuid,
			'ptype' => $this->cutype,
			'pchid' => (empty($this->cutype) ? 0 : 1), 
			'url' => '', 
		);
		if($this->fmpre != 'fmdata') $_init['fmpre'] = $this->fmpre;
		return $_init;
	}
	// 默认的Pids
	protected function defPid(){
		$pid = empty($this->cutype) ? 0 : ($this->cutype=='m' ? $this->tomid : $this->aid);
		return $pid;
	}
	// Cookie检查项目(repeat)
	protected function repCookie(){
		$a = array(); //array('aid'=>$aid,'tocid'=>$tocid)
		if(!empty($this->aid)){
			$a['aid'] = $this->aid;
			if(!empty($this->tocid)) $a['tocid'] = $this->tocid;
		}else{
				
		}
		if(!empty($this->tomid)){
			$a['tomid'] = $this->tomid;
		}
		return $a;
	}
	// 扩展字段项()
	protected function extFields(){
		$a = array('ip'=>$this->ip); //array('aid'=>$aid,'tocid'=>$tocid,'ip'=>$onlineip,)
		if(!empty($this->aid)){
			$a['aid'] = $this->aid;
			if(!empty($this->tocid)) $a['tocid'] = $this->tocid;
		}else{
				
		}
		if(!empty($this->tomid)){
			$a['tomid'] = $this->tomid;
			$a['tomname'] = @$this->pinfo['mname'];
		}

		if(!empty($this->exfields)){
			$a = array_merge($a,$this->exfields);
		}
		if(!empty($this->cucbvals)){
			$a = array_merge($a,$this->cucbvals);
		}

		return $a;
		// tocid,ip可能有些交互没有...要到insert里面去判断。
	}

}