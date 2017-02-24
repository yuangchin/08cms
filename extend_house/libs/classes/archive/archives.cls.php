<?php
class cls_archives extends cls_archivesbase{
    /**
     * 显示房东信息的触发JS
     * @param object $curuser 当前会员实例
     * return string $str 
     */
    function landlordClickJs($curuser){
   	    $js = "<script type='text/javascript'>
                    function setfdinfo(e){
                    	eck = e.checked?'':'none'; //true:false;
                    	etr = document.body.getElementsByTagName('tr');
                    	for(i=0;i<etr.length;i++){
                    		id = etr[i].id.toString();
                    		if(id.indexOf('fdinfo_')==0) etr[i].style.display = eck;
                    	}
                    }
               </script>";
    	$str = $curuser->info['mchid']==2?"$js<div style='float:right;padding-right:10px'><label><input class=\"checkbox\" type=\"checkbox\" id=\'fdinfo\' name=\"fdinfo\" value=\"xx\" onclick='setfdinfo(this)'>&nbsp;显示房东信息</label></div>":'';
        return $str;
    }
	
	// 房源-房东信息 显示
	function m_view_main_fy($cfg=array(), $mchid=3){
		$rs = $this->m_db_array();
		foreach($rs as $k => $v){
			echo $this->m_one_row($v, $cfg);
			$fdname = $v['fdname']==''?'-':str_replace(array("'","\r","\n"),array("\'","<br>","<br>"),$v['fdname']);
			$fdtel = $v['fdtel']==''?'-':str_replace(array("'","\r","\n"),array("\'","<br>","<br>"),$v['fdtel']); 
			$fdnote = $v['fdnote']==''?'-':str_replace(array("'","\r\n","\r","\n"),array("\'","<br>","<br>","<br>"),$v['fdnote']); 
			if($mchid==2){
				$rstr = "\n<tr id='fdinfo_$v[aid]' class=\"bg bg2\" style='display:none'><td class=\"item\">&nbsp;</td>\n";
				$rstr .= "<td class=\"item\" colspan='10'>
				  <div style=' width:70px; float:left; text-align:left'>房东姓名</div>
				  <div style=' width:150px; float:left; text-align:left'>$fdname</div>
				  <div style=' width:70px; float:left; text-align:left'>房东电话</div>
				  <div style=' width:180px; float:left; text-align:left'>$fdtel</div>
				  <div style='clear:both'></div>
				  <div style=' width:70px; float:left; text-align:left'>内部备注</div>
				  <div style=' width:650px; float:left; text-align:left'>$fdnote</div>
					  </td>\n
				</tr>\n";
				echo $rstr;
			}
		}
	}
	
	// 重设/忽略 类系处理
	function resetCoids(&$coids){
		$mconfigs = cls_cache::Read('mconfigs');
		$fcdisabled2 = $mconfigs['fcdisabled2'];
		$fcdisabled3 = $mconfigs['fcdisabled3'];
		$skipCoid = array(); //1,2,3,14
		if(!empty($fcdisabled2)) $skipCoid[] = 2;
		if(!empty($fcdisabled3)){ 
			$skipCoid[] = 3;
			$skipCoid[] = 14;
		}
		resetCoids($coids, $skipCoid); 		
	}
	
	//删除问答答案交互数据
	function  sv_o_cumu_all($info){
		global $db,$tblprefix;
		
		$cuid = $info['cuid'];
		$selectid = $info['selectid'];
		$actext = $info['actext'];
		$aid = $info['aid'];
		$action = $info['aid'];
		$arcdeal = $info['arcdeal'];
		
		$commu = cls_cache::Read('commu',$cuid);
		if(empty($arcdeal)) cls_message::show('请选择操作项目。',axaction(1,M_REFERER));
		if(empty($selectid)) cls_message::show('请选择咨询记录。',axaction(1,M_REFERER));
		foreach($selectid as $k){
			if(!empty($arcdeal['delete'])){
				$db->query("DELETE FROM {$tblprefix}$commu[tbl] WHERE cid='$k'",'UNBUFFERED');	
			}
		}
		$aid || cls_message::show('咨询批量操作成功。',"?action=$action&actext=$actext");
		$aid && cls_message::show('咨询批量操作成功。',"?action=$action&actext=$actext&aid=$aid");	
	}
	
	
	function s_footer_ex($url,$orther=array()){
		global $authkey; 
		$where_str = '';
		if(!empty($this->oS->wheres)){
			foreach($this->oS->wheres as $k => $v){
				$where_str .= " AND $v";
			}
		}		
		//除了搜索条件组成的sql，另外需要加入的sql组成部分
		if(!empty($orther) && !empty($orther['sql'])){
			$where_str .= " AND $orther[sql]";
		}
		$where_str = cls_string::urlBase64(trim($where_str));
        $p = md5($where_str.$authkey);//防篡改加密参数,传递参数后，判断$where_str+$authkey加密后的字符串与$p是否一致

		$html = "<a style=\"float:right;text-decoration:none;\" onclick=\"return floatwin('open_arcdetail',this)\" href=\"".$url."&q=".$where_str."&p=".$p."\"><input class='excel_button'  type=\"button\" value=\"EXCEL导出\"></a>";
		if(empty($this->A['MoreSet'])){
			echo strbutton('bfilter','筛选');
			echo $html;
		}else{
			echo $html;
			echo "</div></div>";//高级区结尾
		}
		tabfooter();
		unset($this->oS);
	}

    /**
     *楼盘相册,户型的推送项的批量展示,添加了pid参数
     *
     * @param    string   $key  推送位项目关键字
     * @return   html    返回html字符串
     */
    function o_view_upushs($title = '',$incs = array(),$numpr = 5){
        //$numpr每行显示数量
        $html = '';$i = 0;
        $incs || $incs = array_keys($this->oO->cfgs);
        foreach($incs as $k){
            if($re = $this->o_view_one_push($k)){
                if($numpr && $i && !($i % $numpr)) $html .= '<br>';
                $i ++;
                $html .= $re;
            }
        }
        //$html = str_replace("?entry=extend&","?entry=extend&pid3={$this->A['pid']}&",$html);
        $html = preg_replace("/=push_(\d+)/",'=push_${1}&pid3='.$this->A["pid"],$html);
        if($html){
            $title || $title = '选择推送位';
            trbasic($title,'',$html,'');
        }
    }


}
