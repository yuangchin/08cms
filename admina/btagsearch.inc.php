<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
foreach(array('btagnames','channels','fchannels') as $k) $$k = cls_cache::Read($k);
aheader();
backnav('btags','search');
$bclasses = array(
	'common' => '通用信息',
	'archive' => '文档相关',
	'cnode' => '类目相关',
	'freeinfo' => '副件相关',
	'commu' => '交互相关',
	'member' => '会员相关',
	'other' => '其它',
);
$datatypearr = array(
	'text' => '单行文本',
	'multitext' => '多行文本',
	'htmltext' => 'Html文本',
	'image' => '单图',
	'images' => '图集',
	'flash' => 'Flash',
	'flashs' => 'Flash集',
	'media' => '视频',
	'medias' => '视频集',
	'file' => '单点下载',
	'files' => '多点下载',
	'select' => '单项选择',
	'mselect' => '多项选择',
	'cacc' => '类目选择',
	'date' => '日期(时间戳)',
	'int' => '整数',
	'float' => '小数',
	'map' => '地图',
	'vote' => '投票',
	'texts' => '文本集',
);
tabheader('搜索原始标识  >><a id="btags_update" href="?entry=btags&action=update" onclick="return showInfo(this.id,this.href)">更新</a>','btagsearch','?entry=btagsearch');
trbasic('标识ID含字串','bsearch[ename]',empty($bsearch['ename']) ? '' : $bsearch['ename']);
trbasic('标识名称含字串','bsearch[cname]',empty($bsearch['cname']) ? '' : $bsearch['cname']);
trbasic('标识分类','bsearch[bclass]',makeoption(array('' => '不限') + $bclasses,empty($bsearch['bclass']) ? '' : $bsearch['bclass']),'select');
tabfooter('bbtagsearch','搜索');
if(submitcheck('bbtagsearch')){
	$ename = trim(strtolower($bsearch['ename']));
	$cname = trim($bsearch['cname']);
	$bclass = trim($bsearch['bclass']);
	if(empty($ename) && empty($cname) && empty($bclass)) cls_message::show('请输入搜索字串');
	tabheader('原始标识搜索结果列表','','','8');
	trcategory(array('序号','标识名称',array('使用样式'.'1','txtL'),array('使用样式'.'2','txtL'),array('使用样式'.'3','txtL'),'标识类别','详细分类','字段类型'));
	$i = 1;
	foreach($btagnames as $k => $v){
		if((!$ename || in_str($ename,$v['ename'])) 
			&& (!$cname || in_str($cname,$v['cname']))
			&& (!$bclass || $v['bclass'] == $bclass)){
			$sclasses = array();
			if($v['bclass'] == 'archive'){
				foreach($channels as $chid => $channel){
					$sclasses[$chid] = $channel['cname'];
				}
			}elseif($v['bclass'] == 'cnode'){
				$sclasses = array(
					'catalog' => '栏目',
					'coclass' => '分类',
				);
			}elseif($v['bclass'] == 'freeinfo'){
				foreach($fchannels as $chid => $channel){
					$sclasses[$chid] = $channel['cname'];
				}
			}elseif($v['bclass'] == 'commu'){
				$sclasses = array(
					'comment' => '评论',
					'purchase' => '购买',
					'answer' => '答疑',
				);
			}elseif($v['bclass'] == 'other'){
				$sclasses = array(
					'attachment' => '附件',
					'vote' => '投票',
				);
			}
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w40\">$i</td>\n".
				"<td class=\"txtL\">$v[cname]</td>\n".
				"<td class=\"txtL\">{<b>$v[ename]</b>}</td>\n".
				"<td class=\"txtL\">{\$<b>$v[ename]</b>}</td>\n".
				"<td class=\"txtL\">{\$<b>v[$v[ename]]</b>}</td>\n".
				"<td class=\"txtC w80\">".@$bclasses[$v['bclass']]."</td>\n".
				"<td class=\"txtC w80\">".(empty($sclasses[$v['sclass']]) ? '-' : $sclasses[$v['sclass']])."</td>\n".
				"<td class=\"txtC w80\">".$datatypearr[$v['datatype']]."</td>\n".
				"</tr>";
			$i ++;
		}
	}
	tabfooter();
}
?>