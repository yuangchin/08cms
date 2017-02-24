
var SelectInfoList = $id('SelectInfoList'),TempInfoList = $id('TempInfoList'),RelativeKey = $id('RelativeKey'),RelativeTypeSubject = $id('RelativeTypeSubject'),RelativeTypeKey = $id('RelativeTypeKey'),relatedchid = $id('relatedchid');  //aj=Ajax("HTML","loading"),

if(!relatedaid){
	relatedaid = document.createElement('INPUT');
	relatedaid.type = 'hidden';
	relatedaid.name = relatedaid.id = 'relatedaid';
	relatedaid.value = aids;
	SelectInfoList.parentNode.appendChild(relatedaid);
}

var autorelated1 = $id('_autorelated1'),autorelated0 = $id('_autorelated0');
if(autorelated1 && autorelated0)
	autorelated1.onclick = autorelated0.onclick = function(){
		$id('related').style.display = this.id == '_autorelated1' ? 'none' : '';
	}

$id('relativeButton').onclick = function(){
   (typeof CMS_ABS == 'undefined') && (CMS_ABS = '');
	var searchstr = '';
	if(!RelativeTypeSubject.checked && !RelativeTypeKey.checked){alert('请选择查找方式如：标题、关键字Tag。');return false;}
	if(relatedchid.value == '0'){alert('请选择文档类型。');return false;}
	var kwd = RelativeKey.value ? RelativeKey.value : ' '; //组成完整url，为空则使参数补全导致跨域提示
	if(RelativeTypeSubject.checked){searchstr += '&subject=' + encodeURIComponent(kwd);}
	if(RelativeTypeKey.checked){searchstr += '&keywords=' + encodeURIComponent(kwd);}
	$.get(CMS_ABS+uri2MVC("ajax=relateditem&chid="+relatedchid.value+searchstr),function(data){
	//aj.get(ajaxurl + relatedchid.value + searchstr,function(data){
		$id('TempInfoList').length = 0;
		if(!data.length){alert('没有搜索到相关信息。'); return false;}
		for(var i=0;i<data.length;i++){
			var Option = document.createElement("option");
			Option.appendChild(document.createTextNode(data[i].subject));
			Option.setAttribute('value',data[i].aid);
			$id('TempInfoList').appendChild(Option);
		}
	});
	
}
function addaid(){
	var tempaid = '',oldaids = '',inoldaids = false;
	relatedaid.value = relatedaid.value.replace('，',',');//中文，转英文,
	oldaids = relatedaid.value.split(',');
	for(var i=0;i< SelectInfoList.length;i++){
		inoldaids = false;
		for(var j=0;j< oldaids.length;j++){//判断是否已经存在
			if(oldaids[j] == SelectInfoList[i].value){inoldaids = true;break;}
		}
		if(!inoldaids) tempaid += ',' + SelectInfoList[i].value;
	}
	relatedaid.value += oldaids != '' ? tempaid : tempaid.substr(1);
}
function moveaid(tvalue){
	var tempaid = '',oldaids = '';
	tempaid = oldaids = relatedaid.value.split(',');
	for(var i=0;i<oldaids.length;i++){
		if(oldaids[i]==tvalue) tempaid.splice(i,1);
	}
	relatedaid.value = tempaid.join(',');
}
$id('RAddButton').onclick = function(){
	var tempArr = [];
	for(var i=0;i<TempInfoList.length;i++){
		if(TempInfoList[i].selected){
			tempArr.push(TempInfoList[i]);
		}
	}
	for(var i=0;i<tempArr.length;i++){
		var isselected = false;
		for(var j=0;j<SelectInfoList.length;j++){
			if(tempArr[i].value==SelectInfoList[j].value) isselected = true;
		}
		if(!isselected){
			SelectInfoList.appendChild(tempArr[i]);
		}else{
			TempInfoList.removeChild(tempArr[i]);
		}
	}
	addaid();
}
$id('RAddMoreButton').onclick = function(){
	var tempArr = [];
	for(var i=0;i<TempInfoList.length;i++){
		tempArr.push(TempInfoList[i]);
	}
	for(var i=0;i<tempArr.length;i++){
		var isselected = false;
		for(var j=0;j<SelectInfoList.length;j++){
			if(tempArr[i].value==SelectInfoList[j].value) isselected = true;
		}
		if(!isselected){
			SelectInfoList.appendChild(tempArr[i]);
		}else{
			TempInfoList.removeChild(tempArr[i]);
		}
	}
	addaid();
}
$id('RDelButton').onclick = function(){
	for(var i=SelectInfoList.length-1;i>=0;i--){
		if(SelectInfoList[i].selected){
			moveaid(SelectInfoList[i].value);
			SelectInfoList.removeChild(SelectInfoList[i]);
		}
	}
}
$id('RDelMoreButton').onclick = function(){
	for(var i=SelectInfoList.length-1;i>=0;i--){
		moveaid(SelectInfoList[i].value);
		SelectInfoList.removeChild(SelectInfoList[i]);
	}
}

if ( window.jQuery )
{
    $ = window.jQuery;
}