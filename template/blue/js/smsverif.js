//获取确认码
var mobObj=$("[name='fmdata[lxdh]']"),codeObj=$("#msgcode");
//是否开启手机免费短信
$.getJSON(CMS_ABS + uri2MVC("ajax=sms_msend&mod="+isOpenMob+"&act=init&datatype=json"), function(info){
	$("#stloading").hide();
	if(info.error=='close'){
		$("#sendtophone").hide();
		$("#closephtip").show();
	}else{
		$("#sendtophone").show();
	}
});
function sendverCode(os){
	$.getJSON(CMS_ABS + uri2MVC("ajax=sms_msend&mod="+isOpenMob+"&act=code&tel="+mobObj.val()+"&datatype=json"), function(info){
		if(info.error){
			$.jqModal.tip(info.message,'error');
		}else{
			countdown(os);
			mobObj.prop("readonly","readonly");
			$.jqModal.tip('已发送，1分钟后可重新获取','succeed');
			$("#stampinfo").val(info.stamp);
		}
	});
}

var stime;
function countdown(senconds){
	if(senconds>0){
	    senconds--;
		$("#vcode").html('<span class="fcr" id="getminut">60</span>秒后重新获取').prop("disabled","disabled").css("cursor","no-drop");
		if(senconds<10) senconds='0'+senconds;
		$("#getminut").html(senconds);
		stime=setTimeout("countdown("+senconds+")",1000);
		//$("#subsmsbnt").prop("disabled","").removeClass('graybtn');
	}else{
		$("#vcode").html("点击获取确认码").prop("disabled","").css("cursor","pointer");
		mobObj.prop("readonly","");
		//$("#subsmsbnt").prop("disabled","disabled");
	}
}
// mobObj.focus(function(){
// 	if(codeObj.next().hasClass("pass")){
// 		$.jqModal.tip('成功通过手机验证，若想重输入号码，请刷新页面重写！','succeed');
// 		$("#vcode").remove();
// 		mobObj.prop("readonly","readonly");
// 	}
// });