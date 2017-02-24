<?php

/* #################################################### 
#类目菜单 --- +首字母+搜索
$cocsmenus = array (
	#coid
	12 => array (
		'label' => '地区',
		'first_letter' => '1', //显示首字母,不要此效果不要这一行
		'letter_search' => '1', //首字母快搜,不要此效果不要这一行
		'items' => array(
			#ccid => aurl id
			12 => '1,2',
			13 => '1,3',
			14 => '2,3',
			15 => '1,2,3',
		),
		'aurls' => array(
			
			// 无ccid参数
			1 => array(
				'name' => '汽车管理',
				'link' => '?entry=home#extend&extend=cararchives',
			),
			2 => array(
				'name' => '品牌添加',
				'link' => '?entry=extend&extend=coclass1&action=coclassadd&coid=1',
			),
			3=> array(
				'name' => '车款合并',
				'link' => '?entry=extend&extend=carunion&caid=32',
			),
			4=> array(
				'name' => '手动维护', //操作
				'link' => '?entry=extend&extend=updateautoparams',
			),
			5=> array(
				'name' => '车库更新',
				'link' => '?entry=autodatas',
			),
			
			6=> array(
				'name' => '车类目管理',
				'link' => '?entry=extend&extend=coclass1&action=coclassedit',
			),
			7=> array(
				'name' => '推荐车型',
				'link' => '?entry=extend&extend=coclass1&action=coclass_hot',
			),
			
			// ccid参数-品牌
			11 => array(
				'name' => '品牌编辑',
				'link' => '?entry=extend&extend=coclass1&action=coclassdetail&coid=1',
			),
			12 => array(
				'name' => '厂商添加',
				'link' => '?entry=extend&extend=coclass1&action=coclassadd&coid=1',
			),

			// ccid参数-厂商
			21 => array(
				'name' => '厂商编辑',
				'link' => '?entry=extend&extend=coclass1&action=coclassdetail&coid=1',
			),
			22 => array(
				'name' => '车型添加',
				'link' => '?entry=extend&extend=coclass1&action=coclassadd&coid=1',
			),
		),
	),
);

//临时设定参数
$arr = cls_cache::Read('coclasses',12);
$char = array();
// 1~5, 11~12, 21~22, 31~38
$level = array(
	0=>'1,11,12', 
	1=>'1,21,22',
	2=>'1,31,32,33,34,35,36,37,38',
);
foreach($arr as $k=>$v){ 
	$items[$k] = $level[$v['level']];
	//无首字母快搜,以下4行可不要
	if($v['level']==0){
		$ltter = $v['letter'];
		if(!empty($ltter)) $char[$ltter] = $ltter;
	}
}
$cocsmenus[12]['items'] = array(0=>'1,6,7,2,3,4,5')+$items; // 31,32, ,33,34,35,36,37,38
//print_r($cocsmenus);


//无首字母快搜,以下4行可不要
asort($char); // for 字母头
global $cms_abs; $ccid12_letter = '';
foreach($char as $k=>$v){ 
	$ccid12_letter .= "<option value='$k'>$k</option>";
}
$ccid12_letter = '<script type="text/javascript" x_src="'.$cms_abs.'include/js/ccid12_select2.js"></script>
  <select name="c1s_Letter" id="c12s_Letter" style="width:40px" x_onchange="ccid12_leftMenuScrooll(1)">'.$ccid12_letter.'</select>
  <input type="text" name="c1i_word" id="c1i_word" style="width:40px;border-bottom:1px solid #999;" x_onkeydown="ccid12_leftMenuDown(this,event)" title="按回车确认" />
  <input type="button" name="button" id="button" value=" 搜 " xonclick="ccid12_leftMenuScrooll(2)" />';
$cocsmenus[12]['search_item'] = "<li id='leftMenuLi_CcidSearch12'>$ccid12_letter</li>"; 
// 注意：li id='leftMenuLi_CcidSearch1' 一定要用[leftMenuLi_CcidSearch]开头做id,与include/js/aframe.js中函数initaMenu(Ul,ck)的一致
#################################################### */


// ======================================================================


/* #################################################### 
#类目菜单
$cocsmenus = array (
	#coid
	2 => array (
		'label' => '产品管理',
		'items' => array(
			#ccid => aurl id
			12 => '1,2',
			13 => '1,3',
			14 => '2,3',
			15 => '1,2,3',
		),
		'aurls' => array(
			#aurl id
			1 => array(
				'name' => '管理产品',
				'link' => '?entry=extend&extend=news_s',
			),
			2 => array(
				'name' => '添加产品',
				'link' => '?entry=extend&extend=news_a',
			),
			3 => array(
				'name' => '删除产品',
				'link' => '?entry=extend&extend=news_d',
			),
		),
	),
);
#################################################### */
