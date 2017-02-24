//微信相关js
function wxCfgsetInit(){
	$id('ftype_1').checked = true;
	wxCfgsetType('mid');
}
function wxCfgsetType(type){
	var url = $id('mconfigsnew[weixin_url]');
	urla = url.value.split('?');
	url.value = urla[0] + '?/weixin/init/'+type+'/{'+type+'}/';
	$id('mconfigsnew[weixin_fromid]').value = '';
}

function wxCfgsetID(e){
	var url = $id('mconfigsnew[weixin_url]'); 
	var val = parseInt(e.value); 
	if(!val){
		alert('请输入>0的数字');
		e.value = '';
		return;
	}
	val = url.value.replace(/\{\d+\}/g,e.value);
	val = url.value.replace('{mid}',e.value);
	val = url.value.replace('{aid}',e.value);
	url.value = val;
}
function wxMenuClearWin(imuid){
	var _einput = $id('catalogsnew['+imuid+'][val]');
	$(_einput).val('');	
}
function wxMenuPickWin(imuid){
	var _string = '', _num = 0;
	for(var i in mu_pics){
		_num++;
		_string += ('<a href="javascript:wxMenuPickDo(\'' + mu_pics[i][1] + '\', ' + imuid + ');">' + (_num) + '、'+mu_pics[i][0]+' ('+mu_pics[i][1]+') </a>');	
	}
	var _this = $id('cupick_'+imuid+'');
	if ( _string == '' ){ _string = '<a>暂无可关联的系统菜单</a>'; }
	$.layer({
		type: 1,
		fix: false,
		title: '请选择要关联的系统菜单',
		area: ['360px', '180px'],
		shade: [0],
		offset: [jQuery(_this).offset().top - jQuery(window).scrollTop() + 'px', jQuery(_this).offset().left - 420 + 'px'],
		page: {
			html: '<div id="catalogsnew_body">' + _string + '</div>'
		}, success: function(layero){
			var _index = layero.attr('times');
			if ( _index > 2 )
			{
				layer.close(_index - 1);
			}
			layero.children('.xubox_main').children('.xubox_page').css({overflowY: 'auto', width: '100%', height: '144px'});
		}
	});
}

function wxMenuPickDo(val, imuid){
	var _einput = $id('catalogsnew['+imuid+'][val]');
	$(_einput).val(val);
	$('div[id^="xubox_layer"]').find('div[id^="xubox_border"]').parent().remove();
}

function wxSetDebugType(key,init){
	var kch3 = key.substr(0,3); //console.log(kch3);
	
	$('#debugtable tr').each(function(index, element) {
        var html = $(this).find('td:first').html(); //console.log(html);
		if(html.indexOf('--,')>0){ 
			 $(this).hide();
		}
		if(html.indexOf(','+kch3+',')>0){ 
			 $(this).show();
		}
    });
	if(init){
		$(':radio[name="deubg[type]"]').last().attr("checked",true);
	}
}

function wxmcUserFrame(type,msg){
	var htab = '<table border="0" cellpadding="0" cellspacing="1" class="black tabmain marb10">';
	var head = '<tr class="header"><td colspan="10"><b>关注者管理</b></td></tr>';
	var itip = '<tr><td class="item2" colspan="10">加载中……<br>如果出现错误，请检查公众号配置或刷新页面；<br>如果翻页中出现错误，请重新点翻页。</td></tr>';
	if(type==0) return htab+head+itip+'</table>';
	if(type==2 && msg){
		return htab+head+'<tr><td class="item2" colspan="10">'+msg+'</td></tr></table>';	
	}
}
function wxmcInit(){
	$('#wx_utable').html(wxmcUserFrame(0));
	wxGetUserPage();
}
function wxSetUserPageMC(data){
	data = data.user_info_list;
	var s = '<table border="0" cellpadding="0" cellspacing="1" class="black tabmain marb10">';
	s += '<tr class="category" align="center">';
	s += "<td>昵称</td>";
	//s += "<td>OpenId</td>";
	s += "<td>分组ID</td>";
	s += "<td>城市</td>";
	s += "<td>头像</td>";
	s += "<td>性别</td>";
	s += "<td>关注时间</td>"; 
	s += "<td>发信息</td>";
	s += "</tr>";
	for(var i=0;i<data.length;i++){
		var itm = data[i];
		var cheadimg = "<a href='"+itm.headimgurl+"' target='_blank'>头像</a>";
		var csex = itm.sex==1 ? '男' : '女';
		var ctime = wxFmtLocalTime(itm.subscribe_time);
		s += "<tr>";
		s += "<td class='item'>"+itm.nickname+"</td>";
		//s += "<td class='item'>"+itm.openid+"</td>";
		s += "<td class='item'>"+itm.groupid+"</td>";
		s += "<td class='item'>"+itm.city+"</td>";
		s += "<td class='item'>"+cheadimg+"</td>";
		s += "<td class='item'>"+csex+"</td>";
		s += "<td class='item'>"+ctime+"</td>"; 
		s += "<td class='item'><a href='"+wu_msgurl+itm.openid+"' onclick=\"return floatwin('open_reply',this)\">发信息</a></td>";
		s += "</tr>";
	}
	s += "</table>";
	return s;
}

function wxGetUserPage(){ 
	var pstart = (wu_page-1)*50;
	var pend = pstart+50; if(pend>wu_count) pend=wu_count;
	var arr = wu_list.split(',');
	var ids = '';
	for(var i=pstart;i<pend;i++){
		ids += ','+arr[i];
	}
	var url=CMS_ABS + uri2MVC("ajax=Weixin_Ops&act=getUserInfo&ustr="+ids+"&appid="+wu_appid+""); 
	$.ajax({
		type:'get',
		url:url,
		success:function(data){
			if(data.error){
				var msg = data.error+'<br>'+data.message;
				msg = wu_ismc==1 ? wxmcUserFrame(2,msg) : msg;
				$('#wx_utable').html(msg);
			}else if(data){ //console.log(wu_ismc); //alert(data);
				var stab = wu_ismc==1 ? wxSetUserPageMC(data) : wxSetUserPage(data);
				$('#wx_utable').html(stab);
		   }    
		}
	});
	//console.log(ids);
}
function wxSetUserPage(data){
	data = data.user_info_list;
	var s = "<table width='100%' border='0' cellpadding='0' cellspacing='0' class=' tb tb2 bdbot'>";
	s += "<tr class='title txt'>";
	s += "<td class='title txtC'>昵称</td>";
	s += "<td class='title txtC'>OpenId</td>";
	s += "<td class='title txtC'>分组ID</td>";
	s += "<td class='title txtC'>城市</td>";
	s += "<td class='title txtC'>头像</td>";
	s += "<td class='title txtC'>性别</td>";
	s += "<td class='title txtC'>关注时间</td>"; 
	s += "<td class='title txtC'>发信息</td>";
	s += "</tr>";
	for(var i=0;i<data.length;i++){
		var itm = data[i];
		var cheadimg = "<a href='"+itm.headimgurl+"' target='_blank'>头像</a>";
		var csex = itm.sex==1 ? '男' : '女';
		var ctime = wxFmtLocalTime(itm.subscribe_time);
		s += "<tr class='txt'>";
		s += "<td class='txtC'>"+itm.nickname+"</td>";
		s += "<td class='txtC'>"+itm.openid+"</td>";
		s += "<td class='txtC'>"+itm.groupid+"</td>";
		s += "<td class='txtC'>"+itm.city+"</td>";
		s += "<td class='txtC'>"+cheadimg+"</td>";
		s += "<td class='txtC'>"+csex+"</td>";
		s += "<td class='txtC'>"+ctime+"</td>"; 
		s += "<td class='txtC'><a href='"+wu_msgurl+itm.openid+"' onclick=\"return floatwin('open_reply',this)\">发信息</a></td>";
		s += "</tr>";
	}
	s += "</table>";
	return s;
}
function wxFmtLocalTime(stamp) {     
	return new Date(parseInt(stamp) * 1000).toLocaleString();//.substr(0,17);
} 
function wxGetPageBar(pnow) {
	var pall = Math.ceil(wu_count/50);
	var pmin = pnow-5; if(pmin<1) pmin=1;
	var pmax = pnow+5; if(pmax>pall) pmax=pall;
	var css = '', i=0; //console.log(wu_count + ':'+ pall);
	var sbar = "<a class='p_total'> ("+wu_count+")位关注者 </a>";
	if(pmin>1){
		i=1; css = i==pnow ? 'p_curpage' : 'p_num';
		sbar += "<a class='"+css+"' style='cursor:pointer' onclick='wxGetPageAct("+i+")'>"+i+"...</a>";	
	}
	for(i=pmin;i<=pmax;i++){
		css = i==pnow ? 'p_curpage' : 'p_num';
		sbar += "<a class='"+css+"' style='cursor:pointer' onclick='wxGetPageAct("+i+")'>"+i+"</a>";	
	}
	if(pmax<pall){
		i=pall; css = i==pnow ? 'p_curpage' : 'p_num';
		sbar += "<a class='"+css+"' style='cursor:pointer' onclick='wxGetPageAct("+i+")'>..."+i+"</a>";
	}
	$('#p_bar').html(sbar);
}
function wxGetPageAct(page) {
	wu_page = page;
	wxGetUserPage(); 
	wxGetPageBar(page);
}

function wxKwdsetSence(istext){ 
	var kobj = $id('wxkwdsadd[keyword]'); 
	kobj.value = istext ? '' : 'add_friend_autoreply_info'; 
	var trobj = kobj.parentNode.parentNode;
	trobj.style.display = istext ? '' : 'none';
}

function wxSendType(robj){ 
	//nmsg[ids][]
	$("[name='nmsg[ids][]']").attr('checked',false);
	$("[id^='rowd_']").hide();
	var rowid = $(robj).val();
	$("#rowd_"+rowid).show();
	//$()
	return;
}

