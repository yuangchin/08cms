var i,html,ajax = new Ajax('HTML'),showDictionary = $id('showDictionary'),dictionary = $id('dictionary'),dictionaryPage = $id('dictionaryPage'),datacache,
pagecount,page = 1,pagesize = 10,pagehtml;
if($id('fmdata[lpmc]'))$id('fmdata[lpmc]').onkeyup = function(){
	if(this.value){
		var urlfull = $cms_abs + uri2MVC('ajax=ajaxloupan&keywords=' + encodeURIComponent(this.value));
		//console.log(urlfull); // &datatype=js|json 
		ajax.post(urlfull,'',function(re){
			eval("var data = "+re+";");
			if(data.length){
				showDictionary.style.display = "";
				pagecount = Math.ceil(data.length/pagesize);
				datacache = data;
				doPage(1);
				setdisabled(0);
			}else{
				showDictionary.style.display = "none";
				setdisabled(1);
			}
			selectFloor('','','','','','','','');
		});
	}else{ 
		showDictionary.style.display = "none"; 
	}
}
function doPage(p){
	html = '<div class="hd"><b>选择小区</b></div>';
	for(var i = (p - 1)*pagesize;i < datacache.length && i < p*pagesize;i++){
		html += '<div class="item"><a onclick="javascript:selectFloor(\'' + datacache[i]['aid'] + '\',\'' + datacache[i]['subject'] + '\',\'' + datacache[i]['address'] + '\',\'' + datacache[i]['dt'] + '\',\'' + datacache[i]['ccid1'] + '\',\'' + datacache[i]['ccid2'] + '\',\'' + datacache[i]['ccid3'] + '\',\'' + datacache[i]['ccid14'] + '\');" href="javascript:;" class="orange"><font color="#BD2928">' + datacache[i]['subject'] + '</font></a><font color="#565e53">' + datacache[i]['address'] + '</font></div>';
	}
	dictionary.innerHTML = html;
	pagehtml = '<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center"><tbody><tr><td height="30" align="right"><ul class="r">'
	+ (p > 1 ? '<li class="l"><a href="javascript:doPage(' + (p - 1) + ')" class="prev-page">上一页</a>&nbsp;</li>' : '')
	+ (p < pagecount ? '<li class="l">&nbsp;<a href="javascript:doPage(' + (p + 1) + ')" class="next-page">下一页</a></li>' : '')
	+ '</ul></td></tr></tbody></table>';
	dictionaryPage.innerHTML = pagehtml;
}
function selectFloor(aid,subject,address,dt,ccid1,ccid2,ccid3,ccid14){
	if(subject){
		$id('fmdata[lpmc]').value = subject;
		showDictionary.style.display = "none";
	}
	$id('fmdata[pid]').value = aid;
	$id('fmdata[address]').value = address;
	$id('fmdata[dt]').value = dt;
	$id('fmdata[ccid1]').value = ccid1;
	$id('fmdata[ccid2]').value = ccid2;
	$id('fmdata[ccid3]').value = ccid3;
	$id('fmdata[ccid14]').value = ccid14;
}
function setdisabled(d){
	$id('hidesel').style.display = d ? "" : "none";
	$id('selccid1').disabled = d ? false : true;
	$id('fmdata[ccid1]').disabled = d ? true : false;
}


/**
  *pram string  weituo_mids用来存放已经选区的经纪人（cookie）
  *pram int     cid  委托房源信息的id

*/

function chkwt(mid,cid){	
	var wtbox = document.getElementsByName('wtcheckbox[]'),wtnum = 0;
	var checked_mids_val = getcookie('weituo_mids');
	if(input_checked_mids.value.indexOf("," + mid) == -1){//不存在某个经纪人ID，点击的时候则添加经纪人ID
			$id('wtbtn' + mid).className = 'qxwtbtn';
			$id('wtchk' + mid).checked = true;
			input_checked_mids.value = checked_mids_val + "," + mid ;
	}else{//已选取某个经纪人，再次点击，则删除该经纪人ID
		$id('wtchk' + mid).checked = false;
		$id('wtbtn' + mid).className = 'wtbtn';			
		input_checked_mids.value = checked_mids_val.replace("," + mid,"");
	}	
	
	mids_num = input_checked_mids.value.split(',');
	mids_num.splice(0,1);//将数组mids_num从0开始，删除1个元素
	if(mids_num.length > 5){
		input_checked_mids.value = checked_mids_val.replace("," + mid,"");
		$id('wtchk' + mid).checked = false;
		$id('wtbtn' + mid).className = 'wtbtn';
		alert('最多允许选择5个经纪人。');		
		return;
	}
	setcookie('weituo_mids',input_checked_mids.value, 864000000);
}
function chkboxnum(){
	if(input_checked_mids.value == ''){
		alert('请至少选择一个经纪人。');
		return false;
	}
	return true;	
}