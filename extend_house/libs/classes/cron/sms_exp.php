<?php
defined('M_COM') || exit('No Permission');
//  ### 会员过期 短信提醒
class cron_sms_exp extends cron_exec{   
	public function __construct(){
		parent::__construct();
        $this->main();

    }
	public function main(){

		$sms = new cls_sms(); 
		if($sms->isClosed()) return; //关闭了，直接退出
		
		$smscfgsets = cls_cache::exRead('smsregcodes');
        $smscfgsave = cls_cache::Read('smsconfigs');
		$tpl = empty($smscfgsave['membexp']['tpl']) ? '' : $smscfgsave['membexp']['tpl']; 
		if(empty($smscfgsave['membexp']['cfgs']['ugids']) || empty($tpl)){ 
			return; //配置为空，直接退出
		}
		$days = empty($smscfgsave['membexp']['cfgs']['days']) ? 3 : $smscfgsave['membexp']['cfgs']['days'];
		
        $ugida = array( //会员模型,手机字段(各扩展系统请自行设置)
			'14_8'   => array('2', 'lxdh'), 
			'31_102' => array('11','lxdh'), 
			'32_104' => array('12','lxdh'), 
        ); 
		
		foreach($smscfgsave['membexp']['cfgs']['ugids'] as $_v){
			$_a = explode('_',$_v); 
			$ugid = intval($_a[0]); $ugval = @intval($_a[1]); 
			if(empty($ugid) || empty($ugval)) continue;
			$_b = $ugida[$_v];
			$mchid = @$_b[0]; $field = @$_b[1];
			$sql = "SELECT $field,grouptype{$ugid}date FROM {$this->tblprefix}members m INNER JOIN {$this->tblprefix}members_sub s ON s.mid=m.mid ";
			$sql .= "WHERE grouptype$ugid='$ugval' AND grouptype{$ugid}date>'".TIMESTAMP."' AND grouptype{$ugid}date<'".(TIMESTAMP+86400*$days)."'";
			$q = $this->db->query($sql);
			$re = array(); //按天分组，群发
			while($r = $this->db->fetch_array($q)){
				$phone = $r[$field];
				$sdate = date('Y-m-d',$r["grouptype{$ugid}date"]);
				$phone && $re[$sdate] = $phone;
			}
			$ugname = @$smscfgsets['membexp']['ugcfgs'][$_v];
			$ctpl = str_replace('{$groupname}',$ugname,$tpl);
			foreach($re as $date=>$arr){
				$content = str_replace('{$expdate}',$date,$ctpl);
				$sms->sendSMS($arr,$content,'sadm');
				//echo "<hr>$content<br>"; print_r($arr);
			}
			//echo "<br>$_v;<br>$sql;<br>$content;";	
		}
		//echo "<hr>$days<br>$tpl<br>"; print_r($smscfgsave['membexp']['cfgs']['ugids']);
		//return;

	}
}

