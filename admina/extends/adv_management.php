<?php
/**
 * 广告位管理
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('farchive')) cls_message::show($re);
empty($action) && $action = 'adv_tpl';

$fcaid = cls_fcatalog::InitID(@$fcaid);
if(empty($fcaid)) cls_message::show('参数错误！');


if($action != 'recache') {
    backnav('adv', $action);
    $ttype = 'advtag';
    $mtagnew['ename'] = 'adv_' . $fcaid;
    $tclass = 'advertising';
    $advertising = _08_Advertising::getAdvConfig($fcaid);
    trhidden('_sclass', $advertising['chid']);
    trhidden('mtagnew[tclass]', 'farchives');
    include_once M_ROOT . _08_ADMIN . DS . 'mtags' . DS . '_taginit.php';
}
switch(strtolower(trim($action)))
{
    case 'adv_tpl' : {
        if(!$advertising) cls_message::show('请指定正确的广告。');
        include_once dirname(dirname(__FILE__)) . "/mtags/advertising.php";
        if(!submitcheck('bsubmit')){
            tabheader('调用方法：');
            $values = $strings = array();
           /* $values['call_function1'] = (empty($advertising['title']) ? '' : "<!--{$advertising['title']}-->") . "<script type=\"text/javascript\" src=\"{\$cms_abs}api/adv.php?fcaid={$fcaid}".(empty($advertising['params']) ? '' : ('&' . str_replace(',', '&', $advertising['params'])))."\"></script>";*/
            $values['call_function2'] = (empty($advertising['title']) ? '' : "<!--{$advertising['title']}-->") . "<script type=\"text/javascript\" src=\"{$cms_abs}api/adv.php?fcaid={$fcaid}".(empty($advertising['params']) ? '' : ('&' . str_replace(',', '&', $advertising['params'])))."\" charset=\"$mcharset\"></script>";
            
            // 把原本格式的参数转为JSON格式参数
            $params = explode(',', $advertising['params']);
            $paramArray = array();
            foreach ( $params as $param ) 
            {
				if($param) {
					list($key, $value) = explode(':', $param);
					$paramArray[trim($key)] = trim($value);
				}
            }
            
            $values['call_function1'] = (empty($advertising['title']) ? '' : "<!--{$advertising['title']}-->") . "<div id=\"_08_adv_{$fcaid}\"".
                                        (empty($advertising['params']) ? '' : (' params=\'' . json_encode($paramArray) . '\'')) . "></div>";
                                        
            $strings['call_function1'] = $call_function = _08_HTML::createCopyCode('call_function1', $values['call_function1'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
            $strings['call_function2'] = $call_function = _08_HTML::createCopyCode('call_function2', $values['call_function2'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
            trbasic("站内调用：{$strings['call_function1']}",'call_function1', $values['call_function1'],'textarea',array('w' => 600,'h' => 45));
            trbasic("站外调用：{$strings['call_function2']}",'call_function2', $values['call_function2'], 'textarea',array('w' => 600,'h' => 45));
            tabfooter();
			echo "<script>var copySwf_cbackID=1;</script>";
        } else {
    		adminlog('设置广告内容调用模板');
            $flag = _08_Advertising::setFcatalog($adv, $fcaid);
            _08_Advertising::setAdvCache($mtagnew);
            if(!empty($flag)) {
                cls_message::show('广告调用模板设置完成',axaction(6,"?entry=$entry&extend=$extend&action=$action&src_type=other&fcaid=$fcaid"));
            } else {
                cls_message::show('广告调用模板设置失败，请稍候再试。',"?entry=$entry&extend=$extend&action=$action&src_type=other&fcaid=$fcaid");
            }
        }
    } break;
    case 'view' : {
        // 预览
        if(!$advertising) cls_message::show('请指定正确的广告。');
    	if(!empty($advertising['params']) && (false === strpos($advertising['params'], '='))){
    	    $na = array_filter(explode(',', $advertising['params']));
    		tabheader('设置广告的预览变量','advdetail',"{$cms_abs}api/adv.php",2,0,1,0,'get');
    		foreach($na as $k) {
    		    //if(false !== strpos($advertising['params'], '=')) continue;
				if(!strpos($k, ':')) continue;
				$ka = explode(':',$k); $kk = $ka[0];
				if(!empty($advertising['farea']) && $kk=='farea'){ 
					trbasic('地区','',cls_fcatalog::areaShow($fcaid,'','Search','farea',"-请选择-"),'');
				}else{
               		trbasic("$k 变量的值",$kk,'','text',$kk == 'charset' ? array('guide' => '指定内容的编码，取值gbk/big5/utf-8') : array());
				}
    		}

            echo <<<EOT
                <tr><td colspan="2">
                    <a href="{$cms_abs}api/adv.php?fcaid={$fcaid}&adv_view=1" onclick="showAdv(this); return floatwin('open_show_adv',this)">
                        <input class="btn" type="button" name="bsubmit" value="预览" />
                    </a>
                    <script type="text/javascript">
                        function showAdv(obj) {
                            var inputs = document.getElementsByTagName("input");
                            var len = inputs.length;
                            var patt1;
                            obj.href = '{$cms_abs}api/adv.php?fcaid={$fcaid}&adv_view=1';
                            for(var i = 0; i < len; ++i)
                            {
                                if(inputs[i].type == 'text') {
                                    obj.href += '&' + inputs[i].name + '=' + inputs[i].value;
                                }
                            }
							var farea = \$id("farea"); 
							if(farea){
								obj.href += '&farea='+farea.value; 
							} 
                        }
                    </script>
                </td></tr>
EOT;
?><?PHP
    		tabfooter();
    	} else {
    	    echo '<iframe name="show_adv" id="show_adv" src="'.$cms_abs.'api/adv.php?fcaid='.$fcaid.'&adv_view=1" style="width:100%;height:450px;border:1px #ccc solid;" scrolling="auto" frameborder="0"></iframe>';
    	}
     } break;
     case 'recache': {
         if(_08_Advertising::cleanTag($fcaid)) {
             cls_message::show('更新缓存成功！', axaction(6,M_REFERER));
         } else {
             cls_message::show('更新缓存失败，请稍候再试！', axaction(6,M_REFERER));
         }
     } break;
}