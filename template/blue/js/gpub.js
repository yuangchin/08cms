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
			}else{
				showDictionary.style.display = "none";
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
	//auto_fillx();
	$id('fmdata[pid3]').value = aid;
	$id('fmdata[address]').value = address;
	//$id('fmdata[dt]').value = dt;
	$id('fmdata[ccid1]').value = ccid1;
	//$id('fmdata[ccid2]').value = ccid2;
	//$id('fmdata[ccid3]').value = ccid3;
	//$id('fmdata[ccid14]').value = ccid14;
}

function auto_fillx(){
	var auto_fields = 'shi|ting|wei'.split('|');
	var auto_fnames = '室|厅|卫'.split('|');
	//var isadd = '{$this->isadd}';
		var tmp0 = $id('fmdata[lpmc]').value,tmpx='';
		for(i=0;i<auto_fields.length;i++){
			var fid = auto_fields[i];
			var elm = $id('fmdata['+fid+']');  
			if(elm && elm.value!='100'){
				tmpx += elm.value + auto_fnames[i]; 
			}
		}
		tmp0 += ' ' + tmpx;
		elm = $id('fmdata[mj]'); 
		if(elm && elm.value>'0'){
			tmp0 += ' ' + elm.value + '㎡';
		}
		var asubj = $id('fmdata[subject]');
		if(asubj.value.length==0) asubj.value = tmp0; 
}

