<?php
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
if(!$modeSave){
	#$mtag = _tag_merge(@$mtag,@$mtagnew);
	$htmlarr = array(
		'0' => '不作处理',
		'clearhtml' => '清除Html标签',
		'disablehtml' => '仅显示Html标签',
		'safehtml' => '保护性过滤Html',
#		'html_cleara' => '仅删除超链接',
		'html_decode' => 'HTML反编码',
		//'html_keepa' => '仅保留超链接',
	);
	trbasic('* 指定内容来源','mtagnew[setting][tname]',isset($mtag['setting']['tname']) ? $mtag['setting']['tname'] : '','text',array('guide' => '输入格式：字段名aa、变量$a[b]等。'));
    $tag_string = '';
    foreach (_08_HTML::getDealHtmlTagsMap() as $key => $tag)
    {
        if (isset($mtag['setting']['dealhtml_tags']) && is_string($mtag['setting']['dealhtml_tags']))
        {
            $mtag['setting']['dealhtml_tags'] = explode('|', $mtag['setting']['dealhtml_tags']);
            $mtag['setting']['dealhtml_tags'] = array_fill_keys($mtag['setting']['dealhtml_tags'], array('on'));
        }
        if (!empty($mtag['setting']['dealhtml_tags']) && array_key_exists($key, $mtag['setting']['dealhtml_tags']))
        {
            $checked = ' checked="checked"';
        }
        else
        {
        	$checked = '';
        }
        $tag_string .= ('<li style="width: 160px;display:block; float:left;"><input type="checkbox" id="mtagnew[setting][dealhtml_tags][' . $key . ']" name="mtagnew[setting][dealhtml_tags][' . $key . ']" style="vertical-align: middle;" ' . $checked . '/> <label for="mtagnew[setting][dealhtml_tags][' . $key . ']">' . htmlspecialchars($tag)) . '</label></li>';
    }
    $str_tags = <<<TAG
    <div id="_08_tags_box" style="border:1px #134d9d solid; float:left; padding: 10px; margin-top:10px; background-color:#f1f7fd; display:none">
        <ul>
            {$tag_string}
            <li style="width: 160px;display:block; float:left;"><input type="checkbox" id="checkedAll" name="" style="vertical-align: middle;" /> <label for="checkedAll" style="font-weight: bold; background-color:#134d9d; color:#FFF">全 选</label></li>
        </ul>
    </div>
TAG;
	trbasic('处理Html代码','mtagnew[setting][dealhtml]',makeoption($htmlarr,empty($mtag['setting']['dealhtml']) ? '0' : $mtag['setting']['dealhtml']),'select', array('validate' => 'onchange="selectTags(this);"', 'addstr' => $str_tags));
	trbasic('文本长度剪裁','mtagnew[setting][trim]',isset($mtag['setting']['trim']) ? $mtag['setting']['trim'] : 0,'text',array('guide' => '输入字节长度,如为空或0值表示不剪裁，中文按两个字节,utf-8也是这样'));
	trbasic('文本剪裁省略符','mtagnew[setting][ellip]',isset($mtag['setting']['ellip']) ? $mtag['setting']['ellip'] : '','text',array('guide' => '如果文本被剪裁，加上此字符表示省略。留空为不加'));
	trbasic('颜色设置来源','mtagnew[setting][color]',empty($mtag['setting']['color']) ? '' : $mtag['setting']['color'],'text',array('guide' => '输入格式：字段名aa、变量$a[b]或如#FF6633等颜色值。留空为不处理颜色'));
	trbasic('过滤不良词','mtagnew[setting][badword]',empty($mtag['setting']['badword']) ? '0' : $mtag['setting']['badword'],'radio',array('guide'=>'替换或过滤后台设置的不良词(敏感关键字)。'));
	trbasic('处理关联链接','mtagnew[setting][wordlink]',empty($mtag['setting']['wordlink']) ? '0' : $mtag['setting']['wordlink'],'radio',array('guide'=>'给文本中出现了后台设置的热门关键词加上搜索链接。'));
	trbasic('多行文本换行','mtagnew[setting][nl2br]',empty($mtag['setting']['nl2br']) ? '0' : $mtag['setting']['nl2br'],'radio',array('guide'=>'只用于多行文本字段处理，把多行文本中的[回车]替换为html的&lt;br&gt;。'));
	trbasic('添加混淆字串','mtagnew[setting][randstr]',empty($mtag['setting']['randstr']) ? '0' : $mtag['setting']['randstr'],'radio',array('guide'=>'在文本的空白换行处添加隐藏的随机文字，用于防采集。'));
	trbasic('按js规则格式化','mtagnew[setting][injs]',empty($mtag['setting']['injs']) ? '0' : $mtag['setting']['injs'],'radio',array('guide' => '过滤文本中的单引号\n\r等字符，使其可用于js代码中'));
	tabfooter();
	tabheader('图片属性设置');
	trbasic('手机版图片宽度','mtagnew[setting][maxwidth]',isset($mtag['setting']['maxwidth']) ? $mtag['setting']['maxwidth'] : '','text',array('guide' => '在手机版中，如果设置此项将对html字段中的图片进行自定义裁剪（留空默认640宽度）。'));
	trbasic('去掉图片高宽属性','mtagnew[setting][noimgwh]',empty($mtag['setting']['noimgwh']) ? '0' : $mtag['setting']['noimgwh'],'radio',array('guide' => '主要在手机版中，如果设置此项将去掉图片标签中的width/height属性（具体显示可由css控制）。'));	
	trbasic('处理表情符号','mtagnew[setting][face]',empty($mtag['setting']['face']) ? '0' : $mtag['setting']['face'],'radio',array('guide'=>'只用于多行文本字段处理，如把{:face13:}替换成一个小表情图片。'));
	tabfooter();
	if(empty($_infragment)){
		tabheader('标识分页设置');
		trbasic('启用列表分页','mtagnew[setting][mp]',empty($mtag['setting']['mp']) ? 0 : $mtag['setting']['mp'],'radio');
		trbasic('是否简易的分页导航','mtagnew[setting][simple]',empty($mtag['setting']['simple']) ? '0' : $mtag['setting']['simple'],'radio');
		trbasic('分页导航的页码长度','mtagnew[setting][length]',isset($mtag['setting']['length']) ? $mtag['setting']['length'] : '');
		tabfooter();
	}
    echo <<<JS
    <script type="text/javascript">
        function selectTags(ele)
        {
            var _08_tags_box_obj = jQuery('#_08_tags_box');
            if (ele.value == 'clearhtml')
            {
                _08_tags_box_obj.show();
            }
            else
            {
                _08_tags_box_obj.hide();
            }
        }
    	
        selectTags(document.getElementById('mtagnew[setting][dealhtml]'));
        jQuery('#checkedAll').click(function(){
            var items = jQuery(this).parent().prevAll().find('input[type=checkbox]');
            items.prop('checked', jQuery(this).is(':checked'));
        })
    </script>
JS;
}else{
	$mtagnew['setting']['tname'] = trim($mtagnew['setting']['tname']);
	if(empty($mtagnew['setting']['tname']) || !preg_match("/^[a-zA-Z_\$][a-zA-Z0-9_\[\]]*$/",$mtagnew['setting']['tname'])){
		mtag_error('内容来源设置不合规范');
	}
	$mtagnew['setting']['color'] = trim($mtagnew['setting']['color']);
	$mtagnew['setting']['trim'] = max(0,intval($mtagnew['setting']['trim']));
	$mtagnew['setting']['length'] = max(0,intval($mtagnew['setting']['length']));
}
?>
