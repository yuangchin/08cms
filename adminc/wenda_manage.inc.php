<?PHP
/*
** 后期把这个文件拆分为文档与交互 
** 拆分后可以删除  arccols.cls.php 文件 里面的function type_cid()函数
				   archives.cls.php 文件 里面的function  sv_o_cumu_all()函数

** 
*/
/* 参数初始化代码 */
$chid = 106;//必须定义，不接受从url的传参
$cuid = 37;
$caid = 516;
$actext = empty($actext)?'qget':$actext;
$aid = empty($aid)?'':max(0,intval($aid));
$aidstr =  empty($aid)?'':"&aid=$aid";
$cid = empty($cid)?'':max(0,intval($cid)); 
$ajax = empty($ajax)?'':$ajax; 
$my_q = empty($my_q)?'':max(0,intval($my_q)); 
$isa = empty($isa)?'':$isa; 
$info= array('cuid'=>$cuid,'actext'=>$actext,'aid'=>$aid,'action'=>$action);


$prestr = '';
$selectstr = '';
$fromstr = '';
$wherestr = '';
if(in_array($actext,array('qget','qout'))){
	$selectstr = "a.*,b.currency";
	$fromstr = "{$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid b ON a.aid=b.aid ";
}else{
	$selectstr = " cu.*,cu.createdate AS ucreatedate,a.createdate,a.initdate,a.caid,a.chid,a.customurl,a.nowurl,a.subject ";
	$fromstr = "{$tblprefix}commu_answers cu INNER JOIN {$tblprefix}".atbl($chid)." a ON a.aid=cu.aid";
}
$actext == 'qget'   && $wherestr = " a.tomid='$memberid' AND a.chid='$chid' AND a.checked='1' ";
$actext == 'qout'   && $wherestr = " a.mid='$memberid' AND a.chid='$chid' ";
if($actext == 'answer'){
	$aid || $wherestr = " cu.mid='$memberid'";
	$aid && $wherestr = "a.aid='$aid' AND cu.toaid='0' and cu.mid='$memberid'";
}


#-----------------

$oL = new cls_archives(array(
'chid' => $chid,//模型id，必填
'url' => "?action=$action&actext=$actext$aidstr",//表单url，必填，不需要加入chid及pid
'pre' => "",//默认的主表前缀
'where' => $wherestr,//sql中的初始化where，限定为自已的文档
'from' => $fromstr,//sql中的FROM部分
'select' => $selectstr,//sql中的SELECT部分
'cols' => 0,//默认为0，设为大于1则为多列文档模式，如图片列表(设定一个元素不需要索引行)
));
//头部文件及缓存加载
$oL->top_head();

//搜索项目 ****************************
//s_additem($key,$cfg)
$aid || $oL->s_additem('keyword',array('fields' => array('a.subject' => '标题','a.aid' => '文档ID'),));//keys留空则默认为array('a.subject' => '标题','a.mname' => '会员','a.aid' => '文档ID')
$aid || $oL->s_additem('indays');
$aid || $oL->s_additem('outdays');

//搜索sql及filter字串处理
$oL->s_deal_str();

//批量操作项目 ********************
$oL->o_additem('delete');//删除

if($cid){
	if(!($commu = cls_cache::Read('commu',$cuid))) cls_message::show('不存在的交互项目。');
	if(!($row = $db->fetch_one("SELECT * FROM {$tblprefix}$commu[tbl] WHERE cid='$cid'"))) cls_message::show('指定的咨询记录不存在。');
	$arc = new cls_arcedit;
	$arc->set_aid($row['aid'],array('au'=>0));
	if(!$arc->aid) cls_message::show('指定的文档不存在。');	
	if($my_q){
		if($my_q != $arc->archive['aid']) cls_message::show('请指定自已收到的回答。');
	}elseif($memberid != $row['mid'])cls_message::show('请指定自已提交的回答。');
	
	$fields = cls_cache::Read('cufields',$cuid);
	if(!submitcheck('bsubmit')){
		tabheader("问题的回答 &nbsp;<a href=\"".cls_ArcMain::Url($arc->archive)."\" target=\"_blank\">>>{$arc->archive['subject']}</a>",'newform',"?action=$action&cid=$cid",2,1,1);
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
			$a_field->trfield('fmdata');
		}
		unset($a_field);
		tabfooter('bsubmit');
	}else{//数据处理
		$sqlstr = '';
		$c_upload = new cls_upload;	
		$a_field = new cls_field;
		foreach($fields as $k => $v){
			if(isset($fmdata[$k])){
				if($isa && !in_array($k,array('huida'))) continue;
				if(!$isa && in_array($k,array('huida'))) continue;
				$a_field->init($v,isset($row[$k]) ? $row[$k] : '');
				$fmdata[$k] = $a_field->deal('fmdata','mcmessage',axaction(2,M_REFERER));
				$sqlstr .= ",$k='$fmdata[$k]'";
				if($arr = multi_val_arr($fmdata[$k],$v)) foreach($arr as $x => $y) $sqlstr .= ",{$k}_x='$y'";
			}
		}
		unset($a_field);
		$isa && $fmdata['huida'] && $sqlstr .= ",amid='$memberid',aname='{$curuser->info['mname']}',dafutime='$timestamp'";
		$sqlstr = substr($sqlstr,1);
		$sqlstr && $db->query("UPDATE {$tblprefix}$commu[tbl] SET $sqlstr  WHERE cid='$cid'");
		$c_upload->closure(1,$cid,"commu$cuid");
		$c_upload->saveuptotal(1);
		cls_message::show('咨询记录编辑完成',axaction(6,M_REFERER));
	}
}else{	
	if(!submitcheck('bsubmit')){	
		//$aid是列表中；链接传递过来参数
		//头部选择区域
		$aid || backnav('kuaiwen',$actext);		
		
		//搜索区域 ******************
		$oL->s_header();
		$aid || $oL->s_view_array();
		$aid || $oL->s_footer();
		
	
		//显示列表区头部 ***************
		$oL->m_header();
		

		
		//设置列表项目，如果列表项中包含可设置项，需要在数据储存时，加入设置项的处理
		//分组，在先出现的列配置中加入：'group' =>'item,内容分隔符,索引分隔符',内容分隔符留空直接连接,索引行标题的分隔符留空则只使用第一个标记
		
		$actext == 'answer' || $oL->m_additem('selectid');
		$actext == 'answer' && $oL->m_additem('cid',array('type'=>'cid'));
		$oL->m_additem('subject',array('len' => 40,'title'=>'问题名称'));
		if(in_array($actext,array('qget','qout'))){
			$oL->m_additem('checked',array('type'=>'bool','title'=>'审核','len' => 40,));
			//$oL->m_additem('close',array('title'=>'状态',));
			$oL->m_additem('currency',array('title'=>'悬赏分','mtitle'=>'{currency}分'));
			$oL->m_additem('close',array('type'=>'close','title'=>'状态','width'=>35,));			
			$oL->m_additem('clicks',array('title'=>'点击','mtitle'=>'{clicks}'));
			
			$oL->m_additem('createdate',array('type'=>'date','title'=>'添加时间','mtitle'=>'{createdate}','url'=>"?action=archiveinfo&aid={aid}",'width'=>40,));
		}
		if($actext == 'qget'){			
			$oL->m_additem('stat_1',array('type'=>'url','title'=>'答案','mtitle'=>'[回答]','url'=>"etools/answer.php?aid={aid}&isfull=1&mid=$memberid"));				
		}
		if($actext == 'answer'){
			$oL->m_additem('content',array('type'=>'other','title'=>'回答内容','len'=>10));
			$oL->m_additem('isanswer',array('type'=>'bool','title'=>'最佳答案',));
			$oL->m_additem('tocid',array('type'=>'ShowContent','title'=>'问答形式'));
			$oL->m_additem('ucreatedate',array('type'=>'date','title'=>'提交时间',));
			//$oL->m_additem('detail',array('type'=>'url','title'=>'编辑','mtitle'=>'详情','url'=>"?action=wenda_manage&cid={cid}&my_q={aid}",'width'=>40,));
		}


		
		
		//$oL->m_mcols_style("{selectid} &nbsp;{subject}<br>{shi}/{ting]/{chu}");//多列文档模式定义显示项目的组合样式,默认为："{selectid} &nbsp;{subject}"
		
		//显示索引行，多行多列展示的话不需要
		$oL->m_view_top();
		
		//全部列表区处理，如果需要定制，尽量使用类中的细分方法
		$oL->m_view_main();
		
		//显示列表区尾部
		$oL->m_footer();
		
		//显示批量操作区************
		$oL->o_header();
		
		//显示单选项
		//$oL->o_view_bools('单行标题',array('bool1','bool2',));
		$oL->o_view_bools();
		
		//显示整行项
		$oL->o_view_rows();
		
		$oL->o_footer('bsubmit');
		$oL->guide_bm('','0');		
	}else{	
		//预处理，未选择的提示
		$oL->sv_header();
		$info['selectid'] = $selectid;
		$info['arcdeal'] = $arcdeal;
		
		//列表区中设置项的数据处理
	//	$oL->sv_e_additem('clicks',array());
	//	$oL->sv_e_all();
		
		//批量操作项的数据处理
		$actext == 'answer' || $oL->sv_o_all();
		$actext == 'answer' && $oL->sv_o_cumu_all($info);
		
		//结束处理
		$oL->sv_footer();
	}
}
?>