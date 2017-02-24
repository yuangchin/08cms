
function favorites(aid){
	if(aid == '' || !/^\d+$/.test(aid)){
		alert('参数出错！');
		return false;
	}
	var aj = new Ajax();
	$.getScript(CMS_ABS + uri2MVC("ajax=sc_wenda&aid="+aid),function(msg){
		switch(data){
			case(1):
        		$.jqModal.tip('请指定收藏对象','warn')
				break;
			case(2):
        		$.jqModal.tip('请先登录会员','warn')
				break;
			case(3):
        		$.jqModal.tip('当前功能关闭','warn')
				break;
			case(4):
        		$.jqModal.tip('您没有关注权限','warn')
				break;
			case(5):
        		$.jqModal.tip('亲，您已经收藏了','warn')
				break;
			case(6):
        		$.jqModal.tip('收藏成功','succeed')
				break;
		}
	});
	return false;
}
function chk_supplementary(form){
	var e = form.elements;
	if(e['added'].checked){
		if(e['fmdata[content]'].value == ''){
			alert('补充问题不能为空！');
			e['fmdata[content]'].focus();
			return false;
		}
	}
	if(e['addreward'].checked){
		if(e['rewardpoints'].value > parseInt(document.getElementById('jifens').innerHTML)){
			alert('你没有足够的积分追加悬赏！');
			return false;
		}
	}
	return true;
}