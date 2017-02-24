<?php
/**
 * 通用ajax列表处理
 *
 * @example   请求范例URL：?/ajax/pageload_base/aj_model/a,3,1/caid/33/ccid20/1298/aj_thumb/thumb,120,90/aj_pagesize/2/aj_pagenum/2/domain/192.168.1.11/
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_pageload_Base extends _08_Models_Base{
	
	public $mcfgs = array();//a/m/cu/co,3,1 (类型,模型id,模型表) (explode结果)
	public $_ajda = array();//_da配置
	public $sqlarr = array('select','from','where','order','limit');
    public $fieldcfg = array('from_tables'=>array(),'vaild_fields'=>array());//字段相关参数,from_tables:字段来源table; vaild_fields:有效字段;  
	public $comkey = array(//通用参数
		'aj_model',     //模型信息(a-文档/m-会员/cu-交互/co-类目,3,1-模型表; 如:a,3,1)
		'aj_check',     //是否审核(0/1或不设置,默认为1,交互下设置-1表示所有)
		'aj_vaild',     //是否有效(1或不设置)
		'aj_arids',     //属于合辑arid,pmod,pid -=> 如：1,4,23567
		'aj_ids',       //ids(如:123,456,32,954)
		'aj_mid',       //mid(如:)
		'aj_pagenum'  , //当前分页(数字,默认2)
		'aj_pagesize',  //分页大小(数字,默认10)
		'aj_thumb',     //缩略图处理(格式:图片字段,宽,高; 如:thumb,240,180)
		'aj_unsets',    //unset字段(提高传输速度,节约流量)
		'aj_nodemode',  //是否手机版url; 默认1,
        'aj_deforder',  //默认排序(orderby为空时使用的默认排序)
        'aj_whrfields', //组条件的字段(格式见方法)
        'aj_minfo',     //同时返回会员资料; 交互,文档适用
        'aj_ainfo',     //同时返回文档资料; 交互适用
		'caid',         //栏目(文档用)
		'searchword',   //关键字(具体搜索字段,在扩展中设置)
		'orderby',      //排序字段
		'ordermode',    //排序模式
	);
	
    public function __toString(){
		//初始化及模拟da处理
		$this->_initDa(); 
        //加载模版函数
        include_once(cls_tpl::TemplateTypeDir('function').'utags.fun.php');
		//常规sql条件
		$this->_getSql(); 
        $this->_getOrder();
		foreach($this->sqlarr as $k){
			$$k = $this->$k;
		} 
		//全部sql及结果
		$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit"; //echo "\n$sql\n<br>\n";
		$result = $this->_getData($sql); 
        return $result; 
    }
	
    public function _initDa($exkeys=array()){
		//inti
		$_ajkeys = array_merge($this->comkey,$exkeys); 
		foreach($_ajkeys as $key){
			$_ajda[$key] = isset($this->_get[$key]) ? $this->_get[$key] : '';
		} 
		$_ajda['aj_nodemode'] = strlen($_ajda['aj_nodemode'])==0 ? 1 : intval($_ajda['aj_nodemode']);
		// 模型属性
		$mcfgs = explode(',',$_ajda['aj_model']); //a/m/cu,3,1 (类型,模型id,模型表) /co
		if($mcfgs[0]=='a' && !empty($mcfgs[1])){ //文档
			$_ajda['aj_fdata'] = 'a';
			$_ajda['aj_keyid'] = 'aid';
		}elseif($mcfgs[0]=='m' && !empty($mcfgs[1])){
			$_ajda['aj_fdata'] = 'm';
			$_ajda['aj_keyid'] = 'mid';
		}elseif($mcfgs[0]=='cu' && !empty($mcfgs[1])){
			$_ajda['aj_fdata'] = 'cu';
			$_ajda['aj_keyid'] = 'cid';
		}elseif($mcfgs[0]=='co' && !empty($mcfgs[1])){
			$_ajda['aj_fdata'] = 'co';
			$_ajda['aj_keyid'] = 'ccid';
		}else{ //这里不停止,在查询数据库时才停止
			die("Error:".$_ajda['aj_model']);	
		}
		// 
		$this->mcfgs = $mcfgs;
		$this->_ajda = $_ajda;
	}
	
    public function _getSql(){
		$mcfgs = $this->mcfgs;
		$_ajda = $this->_ajda;
		$tblprefix = $this->_tblprefix;
		//select
		$select = "{$_ajda['aj_fdata']}.*".($_ajda['aj_fdata']=='m' ? ",s.*" : '');
		if(in_array($mcfgs[0],array('a','m')) && !empty($mcfgs[2])){
			$select .= ',c.*';
		}
		//from
		$from = "";
		if($mcfgs[0]=='a'){ //文档
			$atbl = atbl($mcfgs[1]); //
			if(empty($atbl)) die("Error:chid=$mcfgs[1]");
			$from .= "{$tblprefix}$atbl a";
            $this->fieldcfg['from_tables'][] = $atbl;
			if(!empty($mcfgs[2])){
				$from .= " INNER JOIN {$tblprefix}archives_{$mcfgs[1]} c ON c.aid=a.aid";
                $this->fieldcfg['from_tables'][] = "archives_{$mcfgs[1]}";
			}
		}elseif($mcfgs[0]=='m'){
			$mchannels = cls_cache::Read('mchannels');
			if(empty($mchannels[$mcfgs[1]])) die("Error:mchid=$mcfgs[1]");
			$from .= "{$tblprefix}members m";
			$from .= " INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid";
            $this->fieldcfg['from_tables'][] = "members";
            $this->fieldcfg['from_tables'][] = "members_sub";
			if(!empty($mcfgs[2])){
				$from .= " INNER JOIN {$tblprefix}members_{$mcfgs[1]} c ON c.mid=m.mid";
                $this->fieldcfg['from_tables'][] = "members_{$mcfgs[1]}";
			}
		}elseif($mcfgs[0]=='cu'){
			$cucfgs = cls_cache::Read('commu',$mcfgs[1]);
			if(empty($cucfgs['tbl'])) die("Error:cuid=$mcfgs[1]");
			$from .= "{$tblprefix}{$cucfgs['tbl']} cu";
            $this->fieldcfg['from_tables'][] = $cucfgs['tbl'];
		}elseif($mcfgs[0]=='co'){
			$cocfgs = cls_cache::Read('coclasses', $mcfgs[1]);
			if(empty($cocfgs)) die("Error:coid=$mcfgs[1]");
			$from .= "{$tblprefix}coclass{$mcfgs[1]} co";
            $this->fieldcfg['from_tables'][] = "coclass{$mcfgs[1]}";
		}else{
			//die("Error:".$_ajda['aj_model']);	
		}
        //vaild_fields
        foreach($this->fieldcfg['from_tables'] as $tab){
            $this->fieldcfg['vaild_fields'] = array_merge($this->fieldcfg['vaild_fields'],$this->getFields("{$tblprefix}$tab"));
        } // 过滤重复?  按类型过滤?
		//where
		$where = $this->_getWhere();
        $where = $this->_whrFields($where);
		//order(默认的排序)
		$order = "{$_ajda['aj_fdata']}.{$_ajda['aj_keyid']} DESC";
		//limit
		$aj_pagesize = empty($_ajda['aj_pagesize']) ? 10 : intval($_ajda['aj_pagesize']);
		$aj_pagenum = empty($_ajda['aj_pagenum']) ? 2 : max(1,intval($_ajda['aj_pagenum']));
		$aj_pageflag = ($aj_pagenum-1)*$aj_pagesize;
		$limit = "$aj_pageflag,$aj_pagesize";
		foreach($this->sqlarr as $k){
			$this->$k = $$k;
		}
	}
	
    public function _getWhere(){
		$mcfgs = $this->mcfgs;
		$_ajda = $this->_ajda;
		//where : 'aj_check','aj_vaild','aj_ids','aj_mid'
		$where = "";
		if($mcfgs[0]=='a'){ //文档专门处理
			//caid
			if(!empty($_ajda['caid'])){
				$ids = sonbycoid(intval($_ajda['caid']), 0, 1); 
				$where .= (empty($where) ? '' : ' AND ')."a.caid IN(".implode(',',$ids).")";
			}
			//vaild
			if(!empty($_ajda['aj_vaild'])){
				$where .= (empty($where) ? '' : ' AND ')."(a.enddate=0 OR a.enddate>'".TIMESTAMP."')";
			}
			//aj_arids/arid,pmod,pid -=> 1,4,23567 (aj_arids参数后续需要用于会员)
			if(!empty($_ajda['aj_arids'])){
				$arstr = $this->_getArWhr($_ajda['aj_arids'],$mcfgs[1]);
				$arstr && $where .= (empty($where) ? '' : ' AND ').$arstr;
			}
			
		}
		if(!empty($_ajda['aj_ids'])){
			$ids = preg_replace('/[^\d|\,]/', '', $_ajda['aj_ids']);
			$ida = array_filter(explode(',',$ids));
			if(count($ida)>200) array_splice($ida,200); //最多100个
			$ids = empty($ida) ? '0' : implode(',',$ida); 
			$where .= (empty($where) ? '' : ' AND ')."{$_ajda['aj_fdata']}.{$_ajda['aj_keyid']} IN($ids)";
		}
		if(!empty($_ajda['aj_mid']) && $mcfgs[0]!='co'){ //文档交互适用(排除类目)
			$aj_mid = intval($_ajda['aj_mid']);
			$where .= (empty($where) ? '' : ' AND ')."{$_ajda['aj_fdata']}.mid='$aj_mid'";
		}
		$aj_check = strlen($_ajda['aj_check']) ? intval($_ajda['aj_check']) : '1'; //0/1:算条件,默认空('')则为1处理
		$close = empty($aj_check) ? 1 : 0;
		if($mcfgs[0]=='cu' && $aj_check==-1){ //文档/会员:一定是审核的...,交互不要checked条件
			$where .= (empty($where) ? '' : ' AND ')."1=1";
		}else{
			$where .= (empty($where) ? '' : ' AND ')."{$_ajda['aj_fdata']}.".($mcfgs[0]=='co' ? "closed=$close" : "checked=$aj_check")."";
		}
		return $where;
	}
    
	/** 
     $whrfix : aj_whrfields参数, 格式：如：
     .../aj_whrfields/field1,op1,v1;field2,op2,v2;field3,op3,v3.../... 每一项用[;]分开,单项中的属性用[,]分开
     fieldX: 数据库中的字段;关键字搜索可用[-]分开多个字段
             .../aj_whrfields/subject,like,东/...                           -=> subject LIKE '%东%'
             .../aj_whrfields/subject-address,like,dong/...                 -=> subject LIKE '%东%' OR address LIKE '%东%'
     opX: 条件对比类型,如 like,=,>,<,>-,auto,mso1,inlike
             .../aj_whrfields/ccid12,auto,127/...                           -=> jiage>5 AND jiage<=10
             .../aj_whrfields/jiage,>=,10.5/...                             -=> jiage>=10.5  op可为 >,<,=,>=,<=
			 .../aj_whrfields/fromaddress,in,4004,20/..                     -=> fromaddress IN(123,456,789,55...) 4004是类系20的一个顶级类别
             .../aj_whrfields/ccid1,in,123-456/...                          -=> ccid1 IN(123,456)   用[-]分开多个值
             .../aj_whrfields/ccid2,in,123/...                              -=> ccid1 IN(123,124)   类别123及子类别
             .../aj_whrfields/mianccid1,inlike,26,1/...                     -=> (主营mianccid1为多选；类系为1; 所有顶级类别26及子类别下的资料)   AND (CONCAT(',',mianccid1,',') LIKE '%,3001,%' OR CONCAT(',',mianccid1,',') LIKE '%,3004,%')   类别123及子类别
             .../aj_whrfields/lcs,mso1,3/...                                -=> 楼层(多选字段); CONCAT('\t',lcs,'\t') LIKE '%\t3\t%'
             .../aj_whrfields/ccid12,mcos,2/...                             -=> ccid12=',1,2,4,'(多选类系); ccid12 LIKE '%,2,%'
     综合:   .../aj_whrfields/subject,like,东;lcs,mso1,3/...                -=> subject LIKE '%东%' AND CONCAT('\t',lcs,'\t') LIKE '%\t3\t%'
     每一项如[field1,op1,v1]的第三个属性为值,可按上面在[,]后直接加上; 或在url中 由.../fieldN/valN/.... 获取; 前者值优先
     如：    .../aj_whrfields/leixing,in,0,1;subjectstr,like;lcs,mso1/subjectstr/dong/lcs/2...
     ???
     多选类系:ccid12
	*/        
    public function _whrFields($whrfix){
		$mcfgs = $this->mcfgs;
		$_ajda = &$this->_ajda;
        $_whrstr_paras = array();
        $searchword = isset($this->_get['searchword']) ? $this->_get['searchword'] : ''; 
        $searchword = trim(cls_string::iconv('utf-8',cls_env::getBaseIncConfigs('mcharset'),$searchword));
        $where = '';
		if(!empty($_ajda['aj_whrfields'])){
            $_itms = explode(';',$_ajda['aj_whrfields']);
            foreach($_itms as $itm){ //echo "\n$itm,";
                $_ia = explode(',',$itm); $_ik = $_ia[0]; 
                $_iop = empty($_ia[1]) ? '=' : $_ia[1]; 
                $_iv = empty($_ia[2]) ? ((strstr($_ik,'subject') || strstr($_ik,'company')) ? $searchword : @$this->_get[$_ik]) : cls_string::iconv('utf-8',cls_env::getBaseIncConfigs('mcharset'),$_ia[2]);
                if(empty($_ik) || empty($_iv)) continue;             
                if(strstr($_ik,'-')){
                    $_iks = '';
                    $_ika = explode('-',$_ik);
                    foreach($_ika as $_ikn){
                        if(in_array($_ikn,$this->fieldcfg['vaild_fields'])){
                            $_iks .= (empty($_iks) ? '' : ' OR ')."$_ikn ".sqlkw($_iv); 
                        }
                    }
                    $where .= (empty($_iks) ? '' : ' AND (')." $_iks) "; 
                }elseif($_iop=='auto'){ // 自动条件类系
                    $coid = intval(str_replace('ccid','',$_ik));
                    if(empty($coid) || ($mcfgs[0]!='a')) continue;
                    $splitbls = cls_cache::Read('splitbls'); 
                    if(!in_array($coid,$splitbls[str_replace('archives','',atbl($mcfgs[1]))]['coids'])) continue; //要判断是否可用
                    $_tmp = cnsql($coid,$_iv);
                    $_tmp && $where .= " AND ".$_tmp;
                }else{
                    if(!in_array($_ik,$this->fieldcfg['vaild_fields'])) continue;
                    $_fmt = empty($_ia[3]) ? '' : intval($_ia[3]); //类系
                    if($_iop=='like'){ //关键字
    					$where .= " AND $_ik ".sqlkw($_iv);
    				}elseif(in_array($_iop,array('>','>=','<','<='))){ //数字比较
    					$where .= " AND $_ik$_iop'$_iv'";
    				}elseif(in_array($_iop,array('notnull','isnull'))){ // field!='' 或 field=''
    					$where .= " AND $_ik".($_iop=='isnull' ? "=" : "!=")."''";
    				}elseif($_iop=='in'){ //分类,自分类; 查sonbycoid()子栏目/类目, caid IN(sonbycoid($caidx1))
    					$coid = isset($_ia[3]) ? $_ia[3] : intval(str_replace('ccid','',$_ik)); //caid 为0
    					$ids = sonbycoid($_iv, $coid, 1); //echo "\n===$coid,$_ik,$_iv";
    					if(strstr($_iv,'-')){
    					    $where .= " AND $_ik ".multi_str(explode(',',str_replace('-',',',$_iv)));
    					}elseif($ids){
							$ids = preg_replace("/[^0-9\.]/i","",$ids);
							$where .= " AND $_ik IN(".implode(',',$ids).")"; //echo "(($where))";
    					}
    				//*
                    }elseif($_iop=='inlike'){ //多选字段,分类有子分类, CONCAT(',',mianccid1,',') LIKE '%\t$ccid1\t%' OR (...)
                        $coid = empty($_ia[3]) ? 0 : $_ia[3]; 
    					$ids = sonbycoid($_iv, $coid, 1); $itmp = ''; 
    					if($ids){
    						foreach($ids as $id){
    							$itmp .= (empty($itmp) ? '' : ' OR ')."CONCAT(',',$_ik,',') LIKE '%,$id,%'";	
    						}
    					}
                        $itmp && $where .= " AND (".$itmp.")";
    				}elseif(in_array($_iop,array('mso1'))){ //多选字段搜1个([tab键]分开)
    					$where .= " AND CONCAT('\t',$_ik,'\t') LIKE '%\t$_iv\t%'";
                    }elseif(in_array($_iop,array('mcos'))){ //多选类系等搜1个([,]分开)
    					$where .= " AND $_ik LIKE '%,$_iv,%'";                      
    				}else{ //其它情况呢?! 
    					$where .= " AND $_ik='$_iv'"; 
    				}   
                }
            }
		}
        //print_r($_ajda); print_r($_whrstr_paras); //die();
        $where = $whrfix . (empty($where) ? '' : $where); //print_r("\nC:$where");
        return $where;       
	}
	
	//辑内审核排序未考虑...
	public function _getArWhr($arCfgs,$mod,$filed='aid'){
		$arids = explode(',',$arCfgs); //1,4,23567
		$abrel = cls_cache::Read('abrel',$arids[0]); //print_r($abrel);
		if(!empty($arids[1]) && !empty($arids[2]) && !empty($abrel) && in_array($arids[1],$abrel['tchids']) && in_array($mod,$abrel['schids'])){
			if($abrel['tbl']){ 
				$ids = cls_DbOther::SubSql_InIds('inid', "{$abrel['tbl']}", "pid='".intval($arids[2])."'"); 
			}else{ 
				$ids = cls_DbOther::SubSql_InIds($filed, "{$this->A['tbl']}", "pid{$abrel['tbl']}='".intval($arids[2])."'");	
			} 
			return substr($filed,0,1).".$filed IN($ids)";	
		}else{
			return '';	
		}
	}
	
    public function _getOrder(){
		$mcfgs = $this->mcfgs;
		$_ajda = $this->_ajda;
        $order = '';
		if(!empty($_ajda['orderby'])){
			$order = $_ajda['orderby'];
            $order .= ($_ajda['ordermode'] ? '' : ' DESC');
		}elseif(!empty($_ajda['aj_deforder'])){ // ccid41 DESC,vieworder ASC
            $order = $_ajda['aj_deforder'];	
		} 
        // 检查字段存在 和 修正前缀 --- 
        if($order){
            $_ord_a = explode(',',$order); $order = '';
            foreach($_ord_a as $oitm){ 
                if($oitm){
                    $_ord_b = explode(' ',str_replace('+',' ',$oitm)); $ofield = trim($_ord_b[0]); 
                    foreach(array('a','c','m','s','cu') as $_fix) $ofield = str_replace("{$_ajda['aj_fdata']}.","",$ofield);                  
                    if(empty($ofield) || count($_ord_b)>2 || !in_array($ofield,$this->fieldcfg['vaild_fields'])) continue;
                    if($ofield==$_ajda['aj_keyid']) $ofield = $_ajda['aj_fdata'].".$ofield";  
                    $_omode = @trim(strtoupper($_ord_b[1])); $_omode = in_array($_omode,array('ASC','DESC')) ? $_omode : '';
                    $order .= (empty($order) ? '' : ',')." $ofield $_omode "; 
                }
            } 
        } 
        $this->order = empty($order) ? $this->order : $order; 
        return $this->order;  
	}
	
    public function _getData($sql){
		$mcfgs = $this->mcfgs;
		$_ajda = $this->_ajda;
		$query = $this->_db->query($sql);
		$result = array();
		if($mcfgs[0]=='a'){ //文档
			$ufields = cls_cache::Read('fields',$mcfgs[1]);
		}elseif($mcfgs[0]=='m'){
			$ufields = cls_cache::Read('mfields',$mcfgs[1]);
		}elseif($mcfgs[0]=='cu'){
			$ufields = cls_cache::Read('cufields',$mcfgs[1]);
		}elseif($mcfgs[0]=='co'){
			$ufields = cls_cache::Read('cnfields',$mcfgs[1]);
		}
		while($r = $this->_db->fetch_array($query)){ 
			//分情况处理row
			if($mcfgs[0]=='a'){ //文档
				$r['nodemode'] = $_ajda['aj_nodemode']; //1:手机版url
				cls_ArcMain::Parse($r); 
			}elseif($mcfgs[0]=='m'){
				$r['murl'] = @cls_Mspace::IndexUrl($r); //网页版使用
			}elseif($mcfgs[0]=='cu'){
				//;
			}elseif($mcfgs[0]=='co'){
				$node = cls_node::cnodearr("ccid{$mcfgs[1]}=$r[ccid]",$_ajda['aj_nodemode']); //其它链接靠扩展
				$r['def_courl'] = empty($node['indexurl']) ? '#' : $node['indexurl'];
			}else{
				//die("Error:".$_ajda['aj_model']);	
			}
			//处理通用thumb
			if(!empty($_ajda['aj_thumb'])){ // aj_thumb=thumb,240,180
				$tharr = explode(',',$_ajda['aj_thumb']);
				$r['thumbOrg'] = $r['thumb'] = preg_replace("/\#\d*/",'',$r[$tharr[0]]); 
				$r['thumbOrg'] = cls_url::tag2atm($r['thumbOrg']);				
				if(!empty($tharr[1]) && !empty($tharr[2])&&is_file($r['thumb'])){
					$r['thumb'] = cls_atm::thumb($r['thumb'],$tharr[1],$tharr[2],1,1); 
				}else{
					$r['thumb'] = $r['thumbOrg'];
				}
				
			}
            if(!empty($_ajda['aj_minfo']) && in_array($mcfgs[0],array('cu','a'))){ //同时返回会员资料; 交互,文档适用
    			$user = new cls_userinfo;
    			$user->activeuser($r['mid']); //,$detail
    			$r['aj_minfo'] = $user->info;
            }
            if(!empty($_ajda['aj_ainfo']) && in_array($mcfgs[0],array('cu')) && !empty($r['aid'])){ //同时返回文档资料; 交互适用
    			$arc = new cls_arcedit;
    			$arc->set_aid($r['aid'],array('au'=>0)); //,'ch'=>$detail
    			$r['aj_ainfo'] = cls_ArcMain::Parse($arc->archive);	
            }
			/*/处理通用日期 (前端js处理))
			foreach(array('createdate','updatedate','refreshdate','enddate','regdate','lastvisit','lastactive') as $k){
				if(!isset($r[$k])){
					;//
				}elseif(!empty($r[$k])){
					$r[$k] = date('Y-m-d H:i:s',$r[$k]);
				}else{
					$r[$k] = '-';	
				}
			}*/
			//处理unset
            if(empty($_ajda['aj_unsets']) && $mcfgs[0]!='cu') $_ajda['aj_unsets'] = 'abstract,content,nowurl';
			$_ajda['aj_unsets'] .= ",password,alipay,alipid,alikeyt,tenpay,tenkeyt";
			$arr = explode(',',$_ajda['aj_unsets']);
			foreach($arr as $k){
				unset($r[$k]);
				unset($r['aj_minfo'][$k]);
			}
			foreach($r as $k=>$v){
				if(isset($ufields[$k]) && in_array($ufields[$k]['datatype'],array('select','mselect'))){
					$arr = cls_field::options($ufields[$k]); 
					$re2 = ''; $ids = explode(',', str_replace(array(", ","\t",",,"),',',$v));
					foreach($ids as $k2){
						if(isset($arr[$k2])){ 
							$v2 = $arr[$k2]; 
							$re2 .= (empty($re2) ? '' : ', ').(is_array($v2) ? $v2['title'] : $v2);
						}
					}
					$r["{$k}title"] = $re2; //cls_uviewbase::field_value($v, $k, $mcfgs[1], '');	
				}
			}
			$r['_timestamp'] = TIMESTAMP; //返回服务器时间（_timestamp）。
			//一条记录
			$result[] = $r; // $r[$_ajda['aj_keyid']] (使用aj_keyid,json会自动排序...)
		} //echo "<pre>"; //print_r($result); echo "</pre>"; die();
		return $result;
	}
	
	function getFields($fulltable){ 
		// 没有考虑是否支持sqli，后续确认
		$query = $this->_db->query("SHOW FULL COLUMNS FROM $fulltable",'SILENT');
		$a = array();
		while($row = $this->_db->fetch_array($query)){
			$a[] = $row['Field'];
		}
		return $a;
	}    
}
