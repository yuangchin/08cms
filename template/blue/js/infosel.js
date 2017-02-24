//根据手机号查询信息
function phonePost(fm){
	var numObj=mobObj.val(),
	urlbase={
	'ajax': 'pageload_arcdel', 
	'aj_model'     : 'a,2,3,9,10',
	'aj_pagenum'   : 1,
	'aj_pagesize'  : 20,
	'aj_ainfo'  : 1,
	'aj_nodemode': 0,
	'aj_whrfields'  : 'lxdh,%3D,'+numObj,
	'code'  : codeObj.val(),
	'tel'  : mobObj.val(),
	'datatype'     : 'json',
	'jsoncallback' : '?'
	}
	$("#Step01").hide();
	$("#selloading").show();
	$("#getnum").html(numObj);
	$.getJSON(CMS_ABS + uri2MVC(urlbase),function(info){
		$("#selloading").hide();
		if(info.length>0){
			for(var i=0;i<info.length;i++){
				$("#insertinfo").append(readData(info[i]));
			}
			$("#showinfo").show();
		}else{
			$("#noselinfo").show().html('<div class="noinfo">很抱歉！暂无<span>"'+numObj+'"</span>号码查询相关信息,请<a href="'+CMS_ABS+'info.php?fid=121">重新查询</a></div>');
	  	}
	});
	timeOut(1800);
	return false;
}
//查询数据
function readData(o){
	var tpl= '<li>'
			+ '<input type="checkbox" name="pinfo" id="sel'+o.aid+'" value="'+o.aid+'"/>'
			+ '<label for="sel'+o.aid+'"><a href="'+o.arcurl+'"  target="_blank" title="'+o.subject+'">['+o.catalog+']'+o.subject+'<span>['+getLocalTime(o.createdate)+']</span></a></label>'
			+ '</li>';
	return tpl;
}

//是否全选信息
$("#allsel").click(function(){
	if($(this).is(":checked"))
	$("#insertinfo input").prop("checked","checked");
	else
	$("#insertinfo input").prop("checked","");
});

//确认删除信息
function delphinfo(){
	var tck='',checkObj=$("#insertinfo :checked");
	for(var i=0; i<checkObj.length; i++){
		tck+=','+checkObj.eq(i).val();
	}
	var delurl={
	'ajax': 'sms_arcdel', 
	'mod'     : 'arcxdel', 
	'act'   : 'send',
	'code'  : codeObj.val(),
	'tel'  : mobObj.val(),
	'ids': tck.substr(1),
	'datatype'     : 'json',
	'jsoncallback' : '?'
	}
	$.getJSON(CMS_ABS+uri2MVC(delurl),function(info){
		if(info.error){
			$.jqModal.tip(info.message,'error');
		}else{
			$("#insertinfo :checked").parent().remove();
			$.jqModal.tip(info.message,'succeed');
			if($("#insertinfo li").length==0){
				$.cookie($ckpre+'smscode_'+delurl.mod, null, { expires: -1, path:'/' }); // 删除cookie	
				$.jqModal.tip('已删除该号码相关信息，即将返回查询页','warn');
				window.location.href=CMS_ABS+"info.php?fid=121";
			}
		}
	});
}

//设置停留时间
function timeOut(senconds){
	senconds--;
	setTimeout("timeOut("+senconds+")",1000);
	if(senconds==0) {
		$.jqModal.tip('抱歉！您停留时间过长，即将返回查询页','warn');
		window.location.href=CMS_ABS+"info.php?fid=121";
		}
	}
