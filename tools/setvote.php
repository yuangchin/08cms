<?php
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
include_once M_ROOT."include/adminm.fun.php";
$alangs = cls_cache::Read('alangs');
$langs = &$alangs;
if((empty($mode) || $mode == 'a')) $mode = '';
$chid = empty($chid) ? 0 : cls_string::ParamFormat($chid);
if(empty($chid) || empty($field) || !($field = cls_cache::Read($mode.'field', $chid, $field))) exit();
$settings = "{
	count : $field[min],
	items : $field[max]
}";
_header();
?>
<?php
	if(isset($cms_top) && $cms_top && (isset($domain) && $domain == 'domain=1'))
	echo '<script type="text/javascript"> document.domain = "'.$cms_top.'"; </script>';
?>
<script type="text/javascript">
function addOption(title, votenum){
	var option, item, index = stack.options.length, i = 0;
	if(!title && index >= settings.items){
		alert('已达到最大投票选项个数。');
		return false;
	}
	if(!voteOption){
		voteOption = document.getElementById('voteOption');
		voteOption.table = voteOption.parentNode;
		voteOption.table.removeChild(voteOption);
		voteOption.style.display = '';
	}
	option = voteOption.cloneNode(true);
	voteOption.table.appendChild(option);
	option.items = option.getElementsByTagName('INPUT');
	if(!option.items.title)while(item = option.items[i++])option.items[item.name] = item;//谷歌浏览器不支持 name 直接访问
	option.items.title.value = title || '';
	option.items.title.focus();
	option.items.votenum.value = votenum || 0;
	option.items.votenum.name = 'votenum';
	stack.options[option.index = index] = option;
}

function saveVote(form){
	var i, k, add, date, field;
	var options = [];
	var key = object.key + '[' + object.fid + ']';
	if(!voteForm || object.fid == undefined)return false;
	try{opener._08cms.stack}catch(e){return false}
	for(i = 0; i < stack.options.length; i++){
		add = stack.options[i].items.title.value + '|' + stack.options[i].items.votenum.value;
		empty(add) || options.push(add);
	}
	if(empty(voteForm.subject.value)/* || empty(voteForm.content.value)和说明*/ || options.length < 2){
		alert('投票标题必填，投票选项大于等于两个！');
		return false;
	}

//set or add subject
	if(object.addnew){
		add = object.btn;
		while(add && (add.nodeType != 1 || add.getAttribute('vote') != 'item'))add = add.previousSibling;
		if(!add){
			field = opener.document.createElement('DIV');
			field.innerHTML = '<div vote="item" style="display:none"><span vote=\"subject\">{subject}</span>&nbsp;'
				+ '[<a href="javascript://" onclick="_08cms.vote.editVote(this,\'' + object.key + '\',\'' + object.chid + '\',\'' + object.mode + '\',{index})"><?='编辑'?></a>]&nbsp;'
				+ '[<a href="javascript://" onclick="_08cms.vote.delVote(this,\'' + object.key + '\',{index})"><?='删除'?></a>]</div>';
			add = field.firstChild;
		}
		add = add.cloneNode(true);
		add.style.display = '';
		try{
			add.removeAttribute('vote');
		}catch(e){
			add.setAttribute('vote', null);
		}
		add.innerHTML = add.innerHTML.replace(/\{index\}/g, object.fid).replace(/\{subject\}/g, voteForm.subject.value.replace(/&|<|"/g, function(a){return a=='&' ? '&amp;' : a=='<' ? '&lt;' : '&quot;'}));
		add.id = key;
		object.btn.parentNode.insertBefore(add, object.btn);
		add = add.lastChild;
	}else{
		add = object.btn;
	}
	while(add && (add.nodeType != 1 || add.getAttribute('vote') != 'subject'))add = add.previousSibling;
	if(add)add.innerHTML = voteForm.subject.value.replace(/&|<|"/g, function(a){return a=='&' ? '&amp;' : a=='<' ? '&lt;' : '&quot;'});

//create hidden fields

	for(i = voteForm.elements.length - 1; i >= 0; i--){
		field = voteForm.elements[i];
		field.value = trim(field.value);
		if(field.name && field.name != 'title' && field.name != 'votenum'){
			if(!(add = stack.elements[field.name])){
				add = stack.elements[field.name] = opener.document.createElement('INPUT');
				add.type = 'hidden';
				add.name = key + '[' + field.name + ']';
				object.form.appendChild(add);
			}
			switch(field.name){
			case 'ismulti':
				add.value = voteForm.ismulti[0].checked ? 1 : 0;
				break;
			case 'enddate':
				date = 0;
				if(k = field.value.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/)){
					date = new Date();
					date.setTime(0);
					date.setFullYear(k[1]);
					date.setMonth(parseInt(k[2]) - 1);
					date.setDate(k[3]);
					date = Math.floor(date.getTime() / 1000);
				}
				add.value = date;
				break;
			default:
				add.value = field.value;
			}
		}
	}
	for(i = 0; i < options.length; i++){
		if(!empty(options[i])){
			var arr = options[i].split('|');
			for(var k in arr){
				field = key + '[options][' + i + '][' + (k == 1 ? 'votenum' : 'title') + ']';
				add = stack.elements.options[i];
				if(!add || !add[k == 1 ? 'votenum' : 'title']){
					add = opener.document.createElement('INPUT');
					add.type = 'hidden';
					add.name = field;
					object.form.appendChild(add);
				}
				add.value = arr[k];					
				object.form.elements[field].value = add.value; 
			}
		}
	}
	options = stack.elements.options;
	while(i < options.length){
		for(k in options[i])options[i][k].parentNode.removeChild(options[i][k]);
		i++;
	}
	if(object.form.elements[object.key]){
		object.form.elements[object.key].parentNode.removeChild(object.form.elements[object.key]);
		delete object.form.elements[object.key];
	}
	window.close();
}

function moveUp(a){
	var c, i;
	if(a = getOption(a)){
		if(a.index == 0)return;
		i = stack.options[a.index - 1];
		stack.options[a.index--] = i;
		stack.options[i.index++] = a;
		i.parentNode.insertBefore(a, i);
//		if(a.index == 1){
//		}else{
//		}
	}
}

function moveDown(a){
	if(a = getOption(a)){
		if(a.index == stack.options.length - 1)return;
		var i = stack.options[a.index + 1];
		stack.options[a.index++] = i;
		stack.options[i.index--] = a;
		i.parentNode.removeChild(i);
		a.parentNode.insertBefore(i, a);
//		if(a.index == stack.options.length - 2){
//		}else{
//		}
	}
}

function delOption(a){
	if(a = getOption(a)){
		if(confirm('确定要删除第 ' + (a.index + 1) + ' 个选项么？')){
			a.parentNode.removeChild(a);
			stack.options.splice(a.index, 1);
			for(var i = a.index; i < stack.options.length; i++)stack.options[i].index = i;
		}
	}
}

function getOption(a){
	while(a && a.tagName != 'TR')a = a.parentNode;
	return a;
}

window.onload = function(){
	var key, len, options, field, name, date, i;
	voteForm = document.forms.voteDetail;
	if(!voteForm || !object){
		alert('没有找到 Form 表单！');
		window.close();
		return;
	}
	if(object.fid == undefined){
		key = object.key;
		len = key.length;
		object.fid = -1;
		options = {};
		for(i = object.form.elements.length - 1; i >= 0; i--){
			field = object.form.elements[i];
			if(field.name && field.name.slice(0, len) == key){
				name = field.name.slice(len).match(/^\[(\d+)\]/);
				if(name){
					options[name[1]] = true;
					if(parseInt(name[1]) > object.fid)object.fid = parseInt(name[1]);
				}
			}
		}
		len = 0;
		for(i in options)len++;
		if(len >= settings.count){
			alert('最多只能添加 ' + settings.count + ' 个投票。');
			window.close();
		}
		object.fid++;
		object.addnew = true;
	}else{
		key = object.key + '[' + object.fid + ']';
		len = key.length;
		options = [];
		for(i = object.form.elements.length - 1; i >= 0; i--){
			field = object.form.elements[i];
			if(field.name && field.name.slice(0, len) == key){
				name = field.name.slice(len).match(/^\[(\w+)\](?:\[(\d+)\](?:\[(\w+)\])?)?$/);
				if(!name)continue;
				switch(name[1]){
				case 'ismulti':
					voteForm.ismulti[0].checked = field.value == '1';
					break;
				case 'enddate':
					if(!empty(field.value) && field.value.match(/^\d+$/)){
						date = new Date();
						date.setTime(parseInt(field.value) * 1000);
						voteForm.enddate.value = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
					}
					break;
				case 'options':
					if(!options[name[2]]){
						options[name[2]] = {};
						stack.elements.options[name[2]] = {};
					}
					options[name[2]][name[3]] = field.value;
					stack.elements.options[name[2]][name[3]] = field;
					continue;
				default:
					if(voteForm[name[1]])voteForm[name[1]].value = field.value;
				}
				stack.elements[name[1]] = field;
			}
		}
		for(i = 0; i < options.length; i++)addOption(options[i].title, options[i].votenum);
	}
	window.focus();
};

var stack = '<?=$stack?>', _08cms = opener._08cms, object = _08cms.stack.object[stack],
	 voteForm, voteOption, undefined;
var settings = <?=$settings?>;
stack = {
	options : [],
	elements : {
		options : []
	}
};
setInterval(function(){try{opener._08cms.stack}catch(e){window.close()}}, 50);//When the opener refresh
</script>
	<form name="voteDetail" method="get" onsubmit="saveVote(this);return false" style="margin:10px">
		<table border="0" cellpadding="0" cellspacing="1" class="tabmain">
			<tr class="header">
				<td colspan="2"><b><?='编辑投票'?></b></td>
			</tr>
			<tr>
				<td width="25%" class="item1"><b><?='投票标题'?></b></td>
				<td class="item2"><input type="text" size="25" id="subject" name="subject" value="">
					<div id="alert_subject" name="alert_subject" class="red"></div></td>
			</tr>
			<tr>
				<td width="25%" class="item1"><b><?='投票说明'?></b></td>
				<td class="item2"><textarea rows="4" name="content" id="content" cols="60"></textarea>
					<div id="alert_content" name="alert_content" class="red"></div></td>
			</tr>
			<tr>
				<td width="25%" class="item1"><b><?='投票结束日期'?></b></td>
				<td class="item2"><input type="text" size="15" id="enddate" name="enddate" value="" class="Wdate" onfocus="WdatePicker({readOnly:true,minDate:'<?=date('Y-m-d')?>'})">
					<div id="alert_enddate" name="alert_enddate" class="red"></div></td>
			</tr>
			<tr>
				<td width="25%" class="item1"><?='是否多项选择'?></td>
				<td class="item2"><input id="ismulti1" name="ismulti" value="1" type="radio" class="radio" />
					<label for="ismulti1"><?='是'?></label> &nbsp; &nbsp;
					<input id="ismulti0" name="ismulti" value="0" checked="checked" type="radio" class="radio" />
					<label for="ismulti0"><?='否'?></label></td>
			</tr>
		</table>
		<table border="0" cellpadding="0" cellspacing="1" class="tabmain">
			<tr class="header">
				<td colspan="4"><b><?='投票选项'?> -- >> <a href="javascript://" onclick="addOption();"><?='添加投票选项'?></a></b></td>
			</tr>
			<tr class="category" align="center">
				<td class="item2"><?='选项标题'?></td>
				<td class="item2"><?='票数'?></td>
				<td><?='排序'?></td>
				<td><?='删除'?></td>
			</tr>
			<tr id="voteOption" style="display:none">
				<td class="item2"><input size="40" name="title" /></td>
				<td class="item2"><input size="10" name="votenum" /></td>
				<td><a href="javascript://" onclick="moveUp(this)"><?='上移'?></a> <a href="javascript://" onclick="moveDown(this)"><?='下移'?></a></td>
				<td><a href="javascript://" onclick="delOption(this)"><?='删除'?></a></td>
			</tr>
		</table>
		<br />
		<br />
		<input class="submit" type="submit" value="  <?='确认'?>  ">
	</form>
</div>
</body>
</html>