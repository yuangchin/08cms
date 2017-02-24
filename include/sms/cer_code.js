
/**
 * 根据会员认证类型id,mchid,认证字段 是否重复；后续考虑可兼容文档
 * eid:表单项ID
 * mctid:认证类型ID
 * mchid:会员模型ID
 * esend:提交按钮element
 * ehidden:扩展处理的element
 * msg:(存在时)自定义信息
 */
function checkUnique(eid,mctid,mchid,esend,ehidden,msg){
	var eform = $id(eid);
	aj = new Ajax('XML');
	aj.get(CMS_ABS+ uri2MVC({'ajax' : 'checkUnique', 'mctid' : mctid, 'mchid' : mchid, 'val' : eform.value, '__rnd' : (new Date).getTime()}), function(info){
		if(info.msg=='Exists'){ //重复,提示
			alert(msg + '\n\n请修改后继续…');
			esend.disabled = true;
			try{ehidden.style.display = 'none';}catch(ex){}
		}else if(info.msg=='OK'){ //不重复,不提示
			esend.disabled = false;
			try{ehidden.style.display = '';}catch(ex){}
		}else{ // 其它信息，显示就是
			alert(info.msg);
		} 
	});
}

/**
 * 用js调用tools/ajax.php?action=memcert 发送认证码的公用函数
 * 某些文档,交互,不给会员随意删除，只有vip等高级会员才可删除
 * @param  string mob 输入 手机号码的表单项ID；
 * @return string mctid 输入 手机认证类型ID 或 短信模块启用中设置的模块ID如[register]，通过这个ID，在ajax.php中找到相关的[短信内容模版]
 
 * @return null
 */
function sendCerCode(mobid,mctid,repid){
	var mob = $id(mobid);
	var aj, tmp, step = 1;
	if(mob.value.length<10) return alert('手机号码格式错误');
	if(!mob.value.match(/^\d{3,4}[-]?\d{7,8}$/))return alert('手机号码格式错误');
	
	// check 手机号码重复
	try{
		var mchk = mob.nextSibling;
		while (mchk.nodeType != 1 ) {
			mchk = mchk.nextSibling;
		} 
		if(mchk.className){
			if(mchk.className.indexOf('warn')>0) return alert('手机号码重复,请更换...');
		}
	}catch(ex){}
	//console.log(mchk.className); return alert('11手机号码重复');
	
	var ckname = ((typeof($ckpre)=="undefined") ? '_fix_sendCerCode_' : $ckpre)+'_'+mobid.replace('[','').replace(']','')+'_'+mctid;
	var ckval = parseInt(getcookie(ckname)); //console.log(ckname+':'+ckval);
	if(ckval>0){
		return alert('请不要重复提交，请耐心等待！');
	}
	
	aj = new Ajax('XML');
	aj.get(CMS_ABS + uri2MVC('ajax=memcert&datatype=xml&mctid='+mctid+'&option=msgcode&mobile='+mob.value+'&__rnd='+(new Date).getTime()), function(info){
		
		if(!info.text){
			var now = new Date(); var nowTime = now.getTime();
			setcookie(ckname, 12321, 60*1000);
			alert('确认码已发送到您手机，请注意查收。');
			if(repid) sendDelay(repid);
		}else{ //错误信息
			alert(info.text);
		}
		
	});

}

// sendDelay延时设置，
// (ids)ID规范：id:原始ID,id_rep:替换的ID,id_rep_in替换ID内的计数, html类似如下：
// <a id="tel_code" href="javascript:" onclick="sendCerCode('$varname','$mctid');">【点击获得确认码】</a>
// <a id="tel_code_rep" style="color:#CCC; display:none"><span id="tel_code_rep_in">60</span>秒后重新获取</a> 
function sendDelay(id){ //alert('xxx');    
	org = $id(id);
	rep = $id(id+'_rep');
	rin = $id(id+'_rep_in');
	sec = parseInt(rin.innerHTML);
	if(sec>0){ 
		org.style.display = 'none';
		rep.style.display = '';
		rin.innerHTML--;
		setTimeout("sendDelay('"+id+"')",1000);
	}else{
		rin.innerHTML = 60; //重设延时计数
		org.style.display = '';
		rep.style.display = 'none'; 
	}    
}
