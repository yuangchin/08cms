var userAgent = navigator.userAgent.toLowerCase(),
	is_webtv = userAgent.indexOf('webtv') != -1,
	is_kon = userAgent.indexOf('konqueror') != -1,
	is_mac = userAgent.indexOf('mac') != -1,
	is_saf = userAgent.indexOf('applewebkit') != -1 || navigator.vendor == 'Apple Computer, Inc.',
	is_opera = userAgent.indexOf('opera') != -1 && opera.version(),
	is_moz = (navigator.product == 'Gecko' && !is_saf) && userAgent.substr(userAgent.indexOf('firefox') + 8, 3),
	is_ns = userAgent.indexOf('compatible') == -1 && userAgent.indexOf('mozilla') != -1 && !is_opera && !is_webtv && !is_saf,
	is_ie = (userAgent.indexOf('msie') != -1 && !is_opera && !is_saf && !is_webtv) && userAgent.substr(userAgent.indexOf('msie') + 5, 3),

	ctrlobjclassName,cssloaded=[],ajaxdebug,Ajaxs=[],AjaxStacks=[0,0,0,0,0,0,0,0,0,0],attackevasive=isUndefined(attackevasive) ? 0 : attackevasive,ajaxpostHandle=0,loadCount=0,hiddenobj=[],floatscripthandle=[],InFloat='';

function $id(d){return typeof d == 'string' ? document.getElementById(d) : d}
function $ce(tag){return document.createElement(tag)}

function empty(val){
	var i,ret = !val;
	if(!ret){
		if(typeof val == 'string')
			ret =/^[\s|0]*$/.test(val);
		else if(val instanceof Array)
			ret = !val.length;
		else if(val instanceof Object){
			ret = true;
			for(i in val){ret = false;break}
		}
	}
	return ret;
}

function in_array(needle, haystack){
	if(typeof needle == 'string'){
		for(var i in haystack){
			if(haystack[i] == needle){
					return true;
			}
		}
	}
	return false;
}

function checkall(form, prefix, checkall){
	checkall = checkall ? checkall : 'chkall';
	for(var i = 0; i < form.elements.length; i++){
		var e = form.elements[i];
		if(e.name != checkall && (!prefix || !e.name.indexOf(prefix))){
			e.checked = form.elements[checkall].checked;
		}
	}
}
function checkallvalue(form, value, checkall){
	checkall = checkall ? checkall : 'chkall';
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.type == 'checkbox' && e.value == value) {
			e.checked = form.elements[checkall].checked;
		}
	}
}

function redirect(url) {
	window.location.replace(url);
}

function trim(str) {
	return str?str.replace(/^\s+|\s+$/,''):'';
}

function parseurl(str, mode) {
	var x='([^>=\]"\'\/]|^)((((https?|ftp):\/\/)|www\.)([\w\-]+\.)*[\w\-\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@\':+!]*)+\.(jpg|gif|png|bmp))',y='([^>=\]"\'\/]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k):\/\/)|www\.)([\w\-]+\.)*[\w\-\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@\':+!#]*)*)';
	str = str.replace(new RegExp(x,'ig'), mode == 'html' ? '$1<img src="$2" border="0">' : '$1[img]$2[/img]');
	str = str.replace(new RegExp(y,'ig'), mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
	str = str.replace(/([^\w>=\]:"']|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig, mode == 'html' ? '$1<a href="mailto:$2">$2</a>' : '$1[email]$2[/email]');
	return str;
}
function fullurl(url){
	if(!/[0-9_a-z]+:/i.test(url)){
		var u=location.href;
		u=u.substr(0,u.indexOf('?')<0?u.length:u.indexOf('?'));
		if(url.substr(0,1)=='?')
			url=u+url;
		else{
			var d=/([0-9_a-z]+:\/*[^\/]+)/i.exec(u),h=d[1],d=u.lastIndexOf('/');
			d=d==0?'':u.substring(h.length+1,d+1);
			if(url.substr(0,1)=='/'){
				url=h+url;
			}else{
				var i=1,s=url.indexOf('?');u=s<0?url:url.substr(0,s);s=s<0?'':url.substr(s);
				u=(d+u).split('/');
				while(i<u.length){
					if(u[i]=='..'){
						u.splice(--i,2);
						if(i<1)i=1;
					}else{
						i++;
					}
				}
				i=0;
				while(u[i]=='..')i++;
				u.splice(0,i);
				url=h+'/'+u.join('/')+s;
			}
		}
	}
	return url;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}
function findtags(parentobj, tag) {
	if(!isUndefined(parentobj.getElementsByTagName)) {
		return parentobj.getElementsByTagName(tag);
	} else if(parentobj.all && parentobj.all.tags) {
		return parentobj.all.tags(tag);
	} else {
		return null;
	}
}
function alterview(tname){
	if($id(tname)!=null){
		if($id(tname).style.display=='none'){
			$id(tname).style.display='';
		}else{
			$id(tname).style.display='none';
		}
	}
}
function clearalerts(form){
	tags = findtags(form,'div');
	if(!tags) return;
	var reg = /^alert_/;
	for(k in tags){
		if(reg.test(tags[k].id)){
			try{
				var div = document.createElement('div');
				div.id =tags[k].id;
				div.className = tags[k].className;
				tags[k].parentNode.replaceChild(div,tags[k]);
			}catch(e){
				tags[k].innerHTML = '';
			}
		}
	}
}
function strlen(str){
	var tmp =window.charset == 'utf-8' ? '***' : '**';
	return str.replace(/[^\x00-\xff]/g, tmp).length;
}
function isdate(str){
	var ret = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
	if(ret == null) return false;
	ret[2] --;
	var d = new Date(ret[1],ret[2],ret[3]);
	return d.getFullYear() == ret[1] && d.getMonth() == ret[2] && d.getDate() == ret[3];
}
function isnumber(str){
	var reg = /^(-?\d+)(\.\d+)?$/;
	return reg.test(str);
}
function isnumberletter(str){
	var reg = /^\w+$/;
	return reg.test(str);
}
function istagtype(str){
	var reg = /^[a-zA-Z]+\w*$/;
	return reg.test(str);
}
function isletter(str){
	var reg = /^[a-zA-Z]+$/;
	return reg.test(str);
}
function isint(str){
	var reg = /^-?\d+$/;
	return reg.test(str);
}
function isemail(str){
	var reg = /([\w|_|\.|\+]+)@([-|\w]+)\.([A-Za-z]{2,4})/;
	return reg.test(str);
}
function strmatch(str,matchstr){
	return matchstr.test(str);
}
function mtagcodecopy(obj) {
	obj.focus();
	obj.select();
	if(document.all){
		obj.createTextRange().execCommand("Copy");
	}
}
function opennewwin(url,wname,width,height){
	if(is_ie){
		var posLeft = window.event.clientX-100;
		var posTop = window.event.clientY;
	}else{
		var posLeft = 100;
		var posTop = 100;
	}
	window.open(url,wname,"scrollbars=yes,resizable=yes,statebar=no,width=" + width + ",height=" + height + ",left=" + posLeft + ", top=" + posTop);
}

function checkidsarr(value,vvalue,idsname){
	var o = $id('mselect_' + idsname + '_area') || $id('mselect_' + idsname);
	if(value == vvalue){
		$id(idsname).style.visibility = 'visible';
		o.style.display = '';
	}else{
		$id(idsname).style.visibility = 'hidden';
		o.style.display = 'none';
	}
}
function initidscheckboxtoi(I){
	var S = 'mselect_' + I.id,
		O = (document.getElementById(S + '_area') || document).getElementsByTagName('INPUT'),
		L = S.length,
		T = O.length;
	I.checkbox = [];
	for(var i = 0; i < T; i++)
		O[i].type == 'checkbox' && O[i].id.slice(0, L) == S && I.checkbox.push(O[i]);
	return I.checkbox;
}
function setidswithi(I, type){
	var X = I.value, K = '_$_oldValues_$_', O , S , T;
	if(type){
		O = I.checkbox || initidscheckboxtoi(I);
	}else{
		O = $id('mselect_' + I.id).options;
	}
	T = setInterval(function (){
		if(I.value != X){
			X = I.value;
//			X = I.value.replace(/,+/g, ',').replace(/^,/, '');
			var A = {}, m = X.split(','), v = [], i = 0, l = m.length;
			while(i < l){
//				m[i] in A || v.push(m[i]);
				if(/^\d+$/.test(m[i]))A[m[i]] = 1;
				i++;
			}
//			X = I.value = v.join(',');
			if(type)
				for(var i = 0, l = O.length; i < l; i++)O[i].checked = O[i].value in A;
			else
				for(var i = 0, l = O.length; i < l; i++)O[i].selected = O[i].value in A;
			I[K] = []
			for(v in A)I[K].push(v);
		}
	}, 50);
	I.onblur = function(){
		setTimeout(function(){
			clearInterval(T);
		}, 50);
	};
}

function setidswiths(S, type){
	var I = S.id.match(/^mselect_(.+?)(?:_\d+)?$/); if(!I)return; I = $id(I[1]);
	var X = I.value.replace(/,+$/, ''), O = type ? I.checkbox || initidscheckboxtoi(I) : S.options, K = '_$_oldValues_$_',A = {}, o = I[K],n = [], a = [], i =0, l = O.length, e, j, k, p;
	if(!o){
		o = [];
		while(i < l){
			(O[i].defaultSelected || O[i].defaultChecked) && o.push(O[i].value);
			(O[i].selected || O[i].checked) && n.push(O[i].value);
			i++;
		}
	}else{
		while(i < l){
			(O[i].selected || O[i].checked) && n.push(O[i].value);
			i++;
		}
	}
	I[K] = n;
	for(i = 0, l = n.length, k = o.length; i < l; i++){
		p = 0;
		for(j = 0; j < k; j++){
			if(n[i] == o[j]){
				p = 1;
				break;
			}
		}
		if(p){
			o.splice(j,1);
			k--;
		}else{
			a.push(n[i]);
		}
	}
	X = X.replace(/^,+|,+$/g, '').split(',');
	for(i = 0, l = X.length; i < l; i++)A[X[i]] = 1;
	delete A[''];
	for(i = 0, l = o.length; i < l; i++)delete A[o[i]];
	X = [];
	for(k in A)X.push(k);
	I.value = X.concat(a).join(',');
}

function setIdWithI(I){
	var S = $id('mselect_' + I.id), K = '_$_oldValues_$_', X = I.value, T, i;
	if(!S[K]){
		S[K] = {};
		for(i = 0; i < S.options.length; i++)S[K][S.options[i].value] = i;
	}
	T = setInterval(function (){
		if(I.value != X){
			X = I.value;
			S.selectedIndex = S[K][X] ? S[K][X] : -1;
		}
	}, 50);
	I.onblur = function(){
		setTimeout(function(){
			clearInterval(T);
		}, 50);
	};
}

function setIdWithS(S){
	var I = $id(S.id.substr(8));
	I.value = S.options[S.selectedIndex].value;
}

function single_list_set(radio, same, diff){
	function O(v){var k = same + '$' + diff;if(v)window[k] = v;else{return window[k]}}
	var o = O(), n = radio.value, a, i, l, p;
	if(o === undefined){
		p = radio.form[radio.name];
		for(i = 0, l = p.length; i < l; i++)
			if(p[i].defaultChecked){
				o = p[i].value;
				break;
			}
	}
	a = [$id(same + o), $id(same + n)];
	if(diff)a = a.concat([$id(diff + n), $id(diff + o)]);
	i = 0;
	l = a.length;
	while(i < l && (p = a[i++]));
	if(p){
		O(n);
		i = 0;
		while(i < l)a[i].style.display = i++ % 2 ? '' : 'none';
	}else{
		O(o);
		p = radio.form[radio.name];
		for(i = 0, l = p.length; i < l; i++)
			p[i].checked = p[i].value == o;
		alert('页面尚未加载完成，请稍候操作！');
	}
}

function AC_GetArgs(args, classid, mimeType) {
	var ret = new Object();
	ret.embedAttrs = new Object();
	ret.params = new Object();
	ret.objAttrs = new Object();
	for (var i = 0; i < args.length; i = i + 2){
		var currArg = args[i].toLowerCase();
		switch (currArg){
			case "classid":break;
			case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
			case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
			case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
			case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
			case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
			case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
			case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
			case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
			case "id":ret.objAttrs[args[i]] = args[i+1];break;
			case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
			case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
			default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if(mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}

function AC_FL_RunContent() {
	var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
	var str = '';
	if(is_ie && !is_opera) {
		str += '<object ';
		for (var i in ret.objAttrs) {
			str += i + '="' + ret.objAttrs[i] + '" ';
		}
		str += '>';
		for (var i in ret.params) {
			str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
		}
		str += '</object>';
	} else {
		str += '<embed ';
		for (var i in ret.embedAttrs) {
			str += i + '="' + ret.embedAttrs[i] + '" ';
		}
		str += '></embed>';
	}
	return str;
}

function doane(event) {
	e=event ? event : window.event;
	if(is_ie) {
		e.returnValue=false;
		e.cancelBubble=true;
	} else if(e) {
		e.stopPropagation();
		e.preventDefault();
	}
}

function mb_strlen(str) {
	var len=0;
	for(var i=0; i < str.length; i++) {
		len +=str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset=='utf-8' ? 3 : 2) : 1;
	}
	return len;
}

function mb_cutstr(str,maxlen,dot) {
	var i,len=0,ret='';
	dot=!dot ? '...' : '';
	maxlen=maxlen - dot.length;
	for(i=0; i < str.length; i++) {
		len +=str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset=='utf-8' ? 3 : 2) : 1;
		if(len > maxlen)break;
	}
	ret =str.substr(0,i+1)+(i==str.length?'':dot);
	return ret;
}

function sz_substr(str,maxlen,dot){
	var i,len=0,ret='';
	dot=!dot?'...':'';
	maxlen=maxlen-dot.replace(/[^\x00-\xff]/g,'**').length;
	for(i=0;i<str.length&&len<maxlen;i++)len+=str.charCodeAt(i)<0||str.charCodeAt(i)>255?2:1;
	return str.substr(0,i+1)+(i==str.length?'':dot);
}

function choose(e,obj) {
	var links=obj.getElementsByTagName('a');
	if(links[0]) {
		if(is_ie) {
			links[0].click();
			window.event.cancelBubble=true;
		} else {
			if(e.shiftKey) {
				window.open(links[0].href);
				e.stopPropagation();
				e.preventDefault();
			} else {
				window.location=links[0].href;
				e.stopPropagation();
				e.preventDefault();
			}
		}
		hideMenu();
	}
}

function display_opacity(id,n) {
	if(!$id(id)) {
		return;
	}
	if(n >=0) {
		n -=10;
		$id(id).style.filter='progid:DXImageTransform.Microsoft.Alpha(opacity=' + n + ')';
		$id(id).style.opacity=n / 100;
		setTimeout('display_opacity(\'' + id + '\',' + n + ')',50);
	} else {
		$id(id).style.display='none';
		$id(id).style.filter='progid:DXImageTransform.Microsoft.Alpha(opacity=100)';
		$id(id).style.opacity=1;
	}
}

var evalscripts=new Array();
function evalscript(s) {
	if(s.indexOf('<script')==-1) return s;
	var p=/<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr=new Array();
	while(arr=p.exec(s)) {
		var p1=/<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1=new Array();
		arr1=p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1],'',arr1[2],arr1[3]);
		} else {
			p1=/<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1=p1.exec(arr[0]);
			appendscript('',arr1[2],arr1[1].indexOf('reload=') !=-1);
		}
	}
	return s;
}

function appendscript(src,text,reload,charset) {
	var id=hash(src + text);
	if(!reload && in_array(id,evalscripts)) return;
	if(reload && $id(id)) {
		$id(id).parentNode.removeChild($id(id));
	}

	evalscripts.push(id);
	var scriptNode=$ce("script");
	scriptNode.type="text/javascript";
	scriptNode.id=id;
	scriptNode.charset=charset ? charset : (is_moz ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src=src;
		} else if(text){
			scriptNode.text=text;
		}
		$id('append_parent').appendChild(scriptNode);
	} catch(e) {}
}

function stripscript(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig,'');
}

function hash(string,length) {
	var length=length ? length : 32;
	var start=0;
	var i=0;
	var result='';
	filllen=length - string.length % length;
	for(i=0; i < filllen; i++){
		string +="0";
	}
	while(start < string.length) {
		result=stringxor(result,string.substr(start,length));
		start +=length;
	}
	return result;
}

function stringxor(s1,s2) {
	var s='';
	var hash='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max=Math.max(s1.length,s2.length);
	for(var i=0; i<max; i++) {
		var k=s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s +=hash.charAt(k % 52);
	}
	return s;
}

function checkByCell(e){
	e = e || event;
	if(e.button == 2)return;
	e = e.target || e.srcElement;
	if((e.tagName == 'IMG' && e.onclick)
		|| in_array(e.tagName, ['INPUT','TEXTAREA','A']))return false;
	while(e && e.tagName != 'TR')e = e.parentNode;
	if(!e)return;
	var b = e.getElementsByTagName('INPUT'), fix = window._clickprefix ? window._clickprefix : 'selectid', len = fix.length, i, x;
	if(b.length>1 && b[1].name && b[1].name.substr(0,len)==fix) return; //多列显示,多个selectid忽略;
	for(i = 0; i < b.length; i++)if(b[i].name && b[i].name.substr(0, len) == fix){
		x = b[i];
		while(x && x.tagName != 'TR')x = x.parentNode;
		if(x != e)continue;
		b[i].checked = !b[i].checked;break
	}
}

function resizeBox(){
	var cel, tmp, size, value,
		i = -1, box = document.getElementsByTagName('TEXTAREA');
	function init(cel, tmp, box, isc){
		var	iw = box.offsetWidth,
			ih = box.offsetHeight;
		isc && listen(window, 'resize', function(){
			tmp.style.left = (cel.offsetWidth - tmp.offsetWidth) / 2 + 'px';
		});
		return function(e){
			doane(e || (e = event));
			resizeBox.W = iw;
			resizeBox.H = ih;
			resizeBox.L = isc;
			resizeBox.X = e.screenX;
			resizeBox.Y = e.screenY;
			resizeBox.width = box.offsetWidth;
			resizeBox.height = box.offsetHeight;
			resizeBox.cel = cel;
			resizeBox.tmp = tmp;
			resizeBox.box = box;
			document.body.onselectstart = function(){return false};
			document.body.style.MozUserSelect = 'none';
			document.body.style.cursor = box.style.cursor = 'se-resize';
		};
	}
	function auto(box, cel, tmp, size){
		var timer = setInterval(function(){
			var w = box.offsetWidth, h = box.offsetHeight, l = box.offsetLeft, isc = (cel.offsetWidth - w) / 2 == l;
			if(w || h){
				clearInterval(timer);
				size.onmousedown = init(cel, tmp, box, isc);
				box.parentNode.insertBefore(tmp, box);
				tmp.appendChild(box);
				tmp.appendChild(size);
				with(tmp.style){
					position = 'relative';
					textAlign = 'left';
					if(isc)left = l - 5 + 'px';
					width = w + 'px';
					height = h + 'px';
				}
			}
		}, 20);
	}
	while(++i < box.length){
		if(!box[i].className.match(/\bjs-resize\b/))continue;
		cel = box[i].parentNode;
		tmp = document.createElement('DIV');
		size = document.createElement('DIV');
		with(size.style){
			right = '1px';
			bottom = '1px';
			width = 9 + 'px';
			height = 9 + 'px';
			cursor = 'se-resize';
			position = 'absolute';
			background = "url('images/admina/resize.gif')";
		}
		auto(box[i], cel, tmp, size);
	}
}

resizeBox.moving = function(e){
	if(resizeBox.box){
		e || (e = event);
		var	x = e.screenX - resizeBox.X,
			w = (resizeBox.L ? x * 2 : x) + resizeBox.width,
			h = e.screenY - resizeBox.Y + resizeBox.height;
		if(w < resizeBox.W)w = resizeBox.W;
		if(h < resizeBox.H)h = resizeBox.H;
		with(resizeBox.box.style){
			width = w - 6 + 'px';
			height = h - 6 + 'px';
		}
		with(resizeBox.tmp.style){
			if(resizeBox.L && (x = resizeBox.cel.offsetWidth - w) > 0)left = x / 2 - 5 + 'px';
			width = w + 'px';
			height = h + 'px';
		}
	}
}

resizeBox.movend = function(){
	if(resizeBox.box){
		document.body.style.cursor = resizeBox.box.style.cursor = 'default';
		document.body.style.MozUserSelect = '';
		resizeBox.box = document.body.onselectstart = null;
	}
}
function tableTree(object){//{data:,ckey:,step:,html:}//one page, one tree
var assoc = {
		aocs : {},								//assoc [pid] = [a1, a2, ...]
		cids : {},								//childTable ids in makeTree function
		exts : {},								//exists tr in makeTree function
		imgs : {},								//images before tree in makeTree function
		keys : {},								//{word : last find word, last : last id position}
		pids : {},								//assoc [id] = pid
		hide : document.createElement('DIV'),	//hide dom container
		ckey : object.ckey,						//cookies key prefix
		step : parseInt(object.step) || 0,		//step with setting
												//cell [2,4]
												//find finded dom
		turn : object.turn === false ? [] : object.turn || '#5595EA,#72A6ED,#8DB8F1,#A0C4F3,#B4CFF5,#C7DCF8,#D0E1F9,#DAE8FA,#E3EDFB,#E3EDFB'.split(',')
												//gradient background
};
function setChildBox(input){
	var tr, td, childs, check, i = 0;
	input || (input = _08cms.elem());
	if(input && (tr = input.parentNode.parentNode.nextSibling) && (td = tr.firstChild).colSpan > 1){
		if(!input._checked){
			input._checked = (check = input.checked) ? 1 : 2;
		}else if(input.checked){
			check = input._checked == 2;
			input._checked = check ? 1 : 2;
		}else{
			return true;
		}
		childs = td.getElementsByTagName(input.tagName);
		while(childs[i]){
			childs[i].checked = check;
			arguments.callee(childs[i++]);
		}
	}
}
function setTreeNode(ico, p, spread){
	assoc.find = null;
	var c = spread ? 0 : assoc.ckey ? Cookie(assoc.ckey + p) == 1 : ico.src.match(/\bsub\d/), row = ico.parentNode.parentNode;
	row = row.parentNode.rows[row.rowIndex + 1];
	if(row.firstChild.colSpan != assoc.cell[2] || row.getAttribute('rel'))return false;
	assoc.ckey && Cookie(assoc.ckey + p, c ? 0 : 1, '9Y');
	ico.src = ico.src.replace(/\b(add|sub)(\d)/, c ? 'add$2' : 'sub$2');
	if(!spread && !row.firstChild.firstChild)row.firstChild.innerHTML = makeTree(p, assoc.imgs[p], 0);
	row.style.display = c ? 'none' : '';
}
function setTreeNext(self, start){
	assoc.find = null;
	var e = setTreeNext.caller.arguments[0] || event, d = e.target || e.srcElement, p = t = parseInt(self.getAttribute('rel'));
	return d.tagName == 'INPUT' || start > assoc.aocs[p].length - 1 || makeTree(p, assoc.imgs[p] || [], start < 0 ? 0 : start, self);
}
function setTreeOffset(self){
	var a, h, i, l, v, x, e = setTreeOffset.caller.arguments[0] || event, d = e.target || e.srcElement
		, k = e.keyCode || e.charCode, p = parseInt(self.getAttribute('rel'));
	if(d.tagName == 'INPUT' && k == 13){
		v = d.value.replace(/^\s+|\s+$/g, '');
		if(!v)return false;
		assoc.find = null;
		switch(d.getAttribute('rel')){
		case 'mc':
			if(assoc.keys.word != d.value){
				//多出空白也算改过，只能比较d.value
				assoc.keys = {
					last : -1,
					word : d.value
				}
			}
			if(x = v.charAt(0) == '^')v = v.slice(1);
			for(l in assoc.aocs){
				//chrome for in的读出顺序会不会有问题
				i = 0;
				while(a = assoc.aocs[l][i]){
					if((x ? a[2].slice(0, v.length) == v : a[2].indexOf(v) > -1)){
						if(assoc.keys.last == -1){
							assoc.keys.last = a[0];
							break;
						}else if(assoc.keys.last == a[0]){
							assoc.keys.last = -1;
						}
					}
					i++;
				}
				if(a){
					break;
				}
			}
			if(!a){
				alert('已搜索到列表结尾！');
				return false;
			}
			v = a[1];
		case 'id':
			v = parseInt(v);
			if(isNaN(v)){
				alert('请输入一个有效的数字！');
				return false;
			}
			a = [v];
			while(v && p != assoc.pids[v])a.push(v = assoc.pids[v]);
			if(!v){
				alert('没有找到指定的ID！');
				return false;
			}
			while(self.tagName != 'TABLE')self = self.parentNode;
			if(!self){
				alert('程序错误，没有找到接收的容器！');
				return false;
			}
			l = 0;
			while(assoc.pids[v]){
				l++;
				v = assoc.pids[v];
			}
			while(true){
				i = 0;
				v = a.pop();
				while(v != assoc.aocs[p][i][1])i++;
				if(h = makeTree(p, assoc.imgs[p] || [], i, self)){
					e.innerHTML = h;
					self = e.firstChild;
					e = 0;
					while(x = self.rows[e++])(h = x.getAttribute('rev')) && (assoc.exts[h] = x);//for assoc.turn
				}
				x = p ? 1 : 2;
				if(!assoc.step || assoc.aocs[p].length <= assoc.step){
					x--;
					while(i-- > 0){
						x++;
						assoc.aocs[assoc.aocs[p][i][1]] && x++;
					}
				}
				self = self.rows[x];
				if(!a.length)break;
				setTreeNode(self.cells[assoc.cell[0]].childNodes[l++], v, 1);
				e = self.nextSibling.firstChild;
				self = e.firstChild;
				p = v;
			}
			x = {
				x : assoc.find = assoc.exts[v],
				s : assoc.find.style,
				k : 'onkeypress',
				m : 'onmouseover'
			};
			i = 0;
			x.c = x.s.background;
			while(a = ['k', 'm'][i++]){
				if(typeof x[a] != 'string')continue;~
				function(k, a){
					x[k] = x.x[a];
					x.x[a] = function(){
						if(this[a] = x[k])x[k].call(this);
						assoc.find = null;
					}
				}(a, x[a]);
			}
			i = 0;
			l = assoc.turn.length;
			assoc.turn && function(flag){
				try{
					if(x.x && (a = assoc.turn[flag ? --i : i++])){
						if(i == 0 || i == l)flag = !flag;
						x.x.style.background = a;
						a = arguments.callee;
						if(i != l || x.x == assoc.find){
							setTimeout(function(){a(flag)}, 52);
						}else{
							x.s.background = x.c || '';
						}
					}else{
						x.s.background = x.c || '';
					}
				}catch(e){}
			}();
			setTimeout(function(){
				var h = 0, i = x.x;
				d.focus();
				d.value=d.value;/*IE BUG*/
				while(i){
					h += i.offsetTop;
					i = i.offsetParent;
				}
				i = document.documentElement
				if(!i || !i.clientHeight)i = document.body;
				if(h > i.clientHeight)i.scrollTop = h - x.x.clientHeight;
			}, 13);
			break;
		case 'ex':
			v = parseInt(v);
			if(isNaN(v)){
				alert('请输入一个有效的数字！');
				return false;
			}
			l = assoc.aocs[p].length;
			if(v < 1)v = 1;
			if(v > l)v = l;
			makeTree(p, assoc.imgs[p] || [], --v < 0 ? 0 : v, self);
			d.value = '';
		}
		return false;
	}
}
function modified(tr){
	var i, k = 0, inputs = tr.getElementsByTagName('INPUT');
	while(i = inputs[k++]){
		switch(i.type.toLowerCase()){
		case 'radio':
		case 'checkbox':
			if(i.checked != i.defaultChecked)return true;
			break;
		case 'text':
		case 'image':
		case 'button':
		case 'hidden':
		case 'textarea':
			if(i.value != i.defaultValue)return true;
		}
	}
	return false;
}
function makeTree(pid, imgs, start, container){
	var a, c, x, tr, div, ico, flag, e = assoc.aocs[pid].length
		,n = start = assoc.step && e > assoc.step ? start : 0, end = assoc.step ? n + assoc.step : 0, nav = {}, html = '', img = imgs.join('');
	function over(container, pid){
		var a, x, tr;
		if(nav[pid]){
			//保留第一次的头，其它的使用第二次的
			nav[pid].length = pid ? 0 : 1;
		}
		if(!pid){
			tr = container.rows[0];
			nav[0] || (nav[0] = [tr]);
			tr.parentNode.removeChild(tr);
		}
		while(tr = container.rows[0]){
			if(a = tr.getAttribute('rev')){
				assoc.exts[a] = tr;
				x = tr.nextSibling;
				if(x && x.firstChild.colSpan == assoc.cell[2] && !x.getAttribute('rel')){
					tr.assoc = x;
					x.firstChild.firstChild && over(x.firstChild.firstChild, a);
					x.parentNode.removeChild(x);
				}
			}else{
				nav[pid] ? nav[pid].push(tr) : (nav[pid] = [tr]);
			}
			tr.getAttribute('rel') || object.callback(tr) === false ? tr.parentNode.removeChild(tr) : assoc.hide.appendChild(tr);
		}
	}
	function make(container, pid){
		var a, x, tr, i = 0, l = assoc.cids[pid].length;
		pid || container.tBodies[0].appendChild(nav[pid].shift());
		nav[pid] && container.tBodies[0].appendChild(nav[pid][0]);
			while(i < l){
				a = assoc.cids[pid][i++];
				tr = assoc.exts[a];
				container.tBodies[0].appendChild(tr);
				if(x = tr.assoc){
					container.tBodies[0].appendChild(x);
					x.firstChild.firstChild && make(x.firstChild.firstChild, a);
				}
			}
		nav[pid] && container.tBodies[0].appendChild(nav[pid][1]);
	}
	if(container){
		while(container.tagName != 'TABLE')container = container.parentNode;
		if(assoc.hide.tagName == 'DIV'){
			assoc.hide.style.display = 'none';
			assoc.hide.innerHTML = '<table><tbody></tbody></table>';
			container.parentNode.insertBefore(assoc.hide, container);
			assoc.hide = assoc.hide.firstChild.firstChild;
		}
		over(container, pid);
	}
	assoc.cids[pid] = [];
	while(a = assoc.aocs[pid][n++]){
		assoc.cids[pid].push(x = a[1]);
		if(!(x in assoc.exts)){
			if(flag = x in assoc.aocs){
				//Have children
				c = assoc.ckey ? Cookie(assoc.ckey + x) == 1 : 0;
				ico = '<img onclick="tableTree.setTreeNode(this,' + x + ')" src="images/admina/'
					+ (c ? 'sub' : 'add') + (e != n ? 2 : 3) + '.gif" width="32" height="32" class="md" />';
			}else{
				ico = '<img src="images/admina/line' + (e != n ? 2 : 3) + '.gif" width="32" height="32" class="md" />';
			}
			html	+= '<tr class="txt" rev="' + x + '">'
					+ object.html.rows.replace(/%(\w+)%/g, function($, _){
						if(_.match(/^\d+$/))return a[_];//0:offset;1:id;2:title;3:order|...
						switch(_){
						case 'ico':
							$ = img + ico;
							break;
						case 'order':
							$ = n;
							break;
						default:
							try{eval('$=' + _ + '.call(a,n,img+ico)')}catch(e){}
						}
						return $;
					}) + '</tr>';
		}
		if(flag){
			html += '<tr' + (c ? '' : ' style="display:none"') + '><td class="nb" colspan="' + assoc.cell[2] + '" style="padding:0px;border:none">';
			imgs.push('<img src="images/admina/' + (e != n ? 'line1' : 'blank') + '.gif" width="32" height="32" class="md" />');
			assoc.imgs[x] = imgs.concat();
			c && (html += makeTree(x, assoc.imgs[x], 0));
			imgs.pop();
			html += '</td></tr>';
		}
		if(end && n == end)break;
	}
	if(assoc.step && e > assoc.step){
		x = start - assoc.step;
		html  = '<tr class="txt moreTree" rel="' + pid + '" onclick="tableTree.setTreeNext(this,'
			 + x + ')" onkeypress="return tableTree.setTreeOffset(this)"' + (start	 ? '' : 'style="display:none"')
			 + ' onselectstart="return false" title="单击本行上移本层"><td colspan="' + assoc.cell[0] + '"></td><td class="txtL" colspan="5">'
			 + img + '<img src="images/admina/upp.gif" width="32" height="32" class="md" />本层位置：'
			 + '<input rel="ex" style="width:36px" value="' + (start + 1) + '" title="回车键确认"/> / ' + e + '</td></tr>' + html
			 + '<tr class="txt moreTree" rel="' + pid + '" onclick="tableTree.setTreeNext(this,'
			 + n + ')" onkeypress="return tableTree.setTreeOffset(this)"' + (e > end ? '' : 'style="display:none"')
			 + ' onselectstart="return false" title="单击本行下移本层"><td colspan="' + assoc.cell[0] + '"></td><td class="txtL" colspan="5">'
			 + img + '<img src="images/admina/dnp.gif" width="32" height="32" class="md" />本层位置：'
			 + '<input rel="ex" style="width:36px" value="' + (e < n ? e : n) + '" title="回车键确认"/> / ' + e + '</td></tr>';
	}
	if(pid){
		html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">' + html + '</table>';
	}else{
		html = '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tb0 tb2 bdbot">'
				+ '<tr class="txt" rel="head">'
				+ object.html.head.replace(/%(\w+)%/g, function($, _){
					switch(_){
					case 'code':
						$ = assoc.step && e > assoc.step ? ' rel="0" onkeypress="return tableTree.setTreeOffset(this)"' : '';
						break;
					case 'input':
						$ = assoc.step && e > assoc.step ? 'ID<input rel="id" style="width:36px" title="回车键确认"/>或 名称<input rel="mc" style="width:80px" title="回车键确认，用^表示从开头匹配"/>' : '';
						break;
					}
					return $;
				}) + '</tr>' + html + '</table>';
	}
	if(container){
		div = document.createElement('DIV');
		div.innerHTML = html;
		over(div.firstChild, pid);
		make(container, pid);
	}else{
		return html;
	}
}
assoc.cell = object.html.cell || [2,4];
assoc.cell[2] = assoc.cell[0] + assoc.cell[1] + 1;
object.callback = object.callback === true ? modified : object.callback || function(){};
var p, i = l = 0, tmp = 'setChildBox,setTreeNode,setTreeNext,setTreeOffset'.split(',');
while(p = tmp[i++])eval('tableTree[p]=' + p);
for(i = 0, tmp = [0]; a = object.data[i]; i++){
	tmp[l = a[0]] = a[1];
	p = assoc.pids[a[1]] = l ? tmp[l - 1] : 0;//pid
	a[0] = i;//position
	p in assoc.aocs ? assoc.aocs[p].push(a) : (assoc.aocs[p] = [a]);
}
return makeTree(0, [], 0);
}
listen(document.documentElement, 'mousedown', checkByCell);
listen(window, 'load', resizeBox);
listen(document.documentElement, 'mousemove', resizeBox.moving);
listen(document.documentElement, 'mouseup', resizeBox.movend);



/**
 * 打开创建选中文本
 *
 * @param string id    文本框ID
 * @param string types 操作类型，update为修改, add 为增加插入
 * @author Wilson
 * 2012-10-11
 */
var floatwin_id = getUriValue('floatwin_id');
var entry = getUriValue('entry');
function openCreateSelectText(id, types) {
    var src_value = src_type = '';
    var fn, len, select_object;
    select_object = get_select_object(id, types); 
    // 插入复合标识与区块标识
    if(types == 'insert') {
        return openCreateInsertText(id, select_object.CaretPos, 'mtpls&action=mtagcode');
    }
    
    // 插入原始标识
    if(types == 'insert_') 
    {
        return openCreateInsertText(id, select_object.CaretPos, 'btags' + getRequestURI());
    }

    // 获取父窗口处理类型与值，如果父窗口不是模板处理则视为标签处理
    try {
        if(getUriValue('tplname') == null && getUriValue('action') != 'tpl') {
            src_type = 'tname';
        } else if(getUriValue('action') == 'tpl') {
            src_type = 'ename';
        } else {
            src_type = 'tplname';
        }
        
        if(
            types == 'restore' || 
            getUriValue('entry') == 'tags_restore' || 
            getUriValue('src_type') == 'other' ||
            getUriValue('action') == 'mtagadd'
        ) {
            src_value = 'restore';
        } else {
            src_value = getUriValue(src_type).replace(/[^A-Z0-9_-]/i, '_');
        }
    } catch (err) {
        alert('页面URI不存在tplname或是ttype等值，导致：' + err);
        return false;
    }

    fn = src_type + '_' + src_value + '_' + select_object.CaretPos;
    if(src_value == null) {
        alert('类型值不能为空！');
        return false;
    }
    	
	if(!select_object) {
        alert('该代码未含有标识！');
        return false;
    }	
	len = select_object.text.length;
    if(len == 0) {
        alert('请先选择内容！');
        return false;
    }

    $.post(CMS_URL + uri2MVC("ajax=save_tag_cache"),  "createrange="+select_object.text + '&fn=' + fn, function (s) {
   		if(s.length != 0) {
		  alert(s);
		} else {
			var wid = (document.CWindow_wid != undefined ? document.CWindow_wid : getUriValue('handlekey'));
			floatwin(
				'open_mtagcode',
				'?entry=mtpls&action=mtagcode&fn=' + fn + '&types=update&textid=' + id + '&floatwin_id=' + wid + getRequestURI(),
				800,
				600
			);
		}
   	});

}


/**
 * 获取标识还原时请求的URI参数
 */
function getRequestURI()
{
    var tclass = getUriValue('tclass');
    var bclass = getUriValue('bclass');
    var tclass2 = getUriValue('mtagnew\\[tclass\\]');
    var sclass = getUriValue('sclass');
    var bclass_str = '';
    try {
        if(document.getElementById('mtagnew[tclass]') != null)
        {
            tclass = document.getElementById('mtagnew[tclass]').value;
        }
        if(document.getElementById('_sclass') != null)
        {
            sclass = document.getElementById('_sclass').value;
        }
    } catch(err){};
    if(bclass != null) bclass_str += ('&bclass=' + bclass);
    if(sclass != null) bclass_str += ('&sclass=' + sclass);
    if(tclass != null) {
        bclass_str += ('&bclass=' + tclass);
    } else if(tclass2 != null) {
        bclass_str += ('&bclass=' + tclass2);
    }    
    return bclass_str;
}

/**
 * 设置坐标到COOKIE
 */
function setCaretpos(id, types)
{
    var select_object = get_select_object(id, types);
    setcookie('caretPos_' + floatwin_id + '_' + entry, select_object.CaretPos);
}

/**
 * 获取选中对象
 *
 * @param string  id            要获取的对象ID
 * @param string  types         当前要获取的类型
 * @retrun object select_object 获取到的选中对象
 */
function get_select_object(id, types)
{
    if(types == 'restore') {
        select_object = getTextInfo(id);
        if(select_object.text == '') {
            //alert('该代码未含有标识！');
            return false;
        }
    } else {
        select_object = getSelectText(id);
    }
    return select_object;
}

/**
 * 以打开窗口方式来创建要插入的新标签文本
 *
 * @param string id    文本框ID
 * @param int    pos   传递光标位置
 */
function openCreateInsertText(id, caretpos, entry)
{
    var wid = (document.CWindow_wid != undefined ? document.CWindow_wid : getUriValue('handlekey'));
    floatwin(
        'open_mtagcode',
        '?entry=' + entry + '&caretpos=' + caretpos + '&types=insert&textid=' + id + '&floatwin_id=' + wid,
        800,
        600
    );
}

/**
 * 插入新建标签信息
 *
 * @param string id      文本框ID
 * @param string new_str 新标签信息
 * @param int    pos     要插入的光标位置
 */
function insertTagStr(id, new_str, pos)
{
    var select_field = document.getElementById(id);
    var tops = select_field.scrollTop;    
    var caretPos = getcookie('caretPos_' + floatwin_id + '_' + entry);
    if(caretPos != null && caretPos != undefined) {
        pos = caretPos;
        setcookie('caretPos_' + floatwin_id + '_' + entry, ''); // IE
        setcookie('caretPos_' + floatwin_id + '_' + entry, parseInt(caretPos) + parseInt(new_str.length));
    }
    setCursorPosition(id, pos);
    // IE
    if (document.selection) {
        var sel = document.selection.createRange();
        sel.text = new_str;
    } else if (select_field.selectionStart || select_field.selectionStart == '0') {
        var startPos = select_field.selectionStart,
            endPos = select_field.selectionEnd,
            cursorPos = startPos,
            tmpStr = select_field.value;
        select_field.value = tmpStr.substring(0, startPos) + new_str + tmpStr.substring(endPos, tmpStr.length);
        cursorPos += new_str.length;
        select_field.selectionStart = select_field.selectionEnd = cursorPos;
    }
    select_field.scrollTop = tops;
}

/**
 * 修改标签
 *
 * @param string id      文本框ID
 * @param string old_str 选中的内容
 * @param string new_str 修改后的新内容
 */
function updateTagStr(id, old_str, new_str, pos)
{
    var obj = document.getElementById(id);
    var tops = obj.scrollTop;
    new_str = new_str.replace(/\[!!!\]/g, '\r\n').replace(/\[!-!\]/g, '\n').replace(/\[!!-\]/g, '\r');
    obj.value = obj.value.substring(0,pos) + obj.value.substring(pos).replace(old_str, new_str);
    obj.scrollTop = tops;
}


/**
 * 设置光标位置
 *
 * @param  string id  要获取选中文本所在节点的对象ID
 * @return string pos 光标要移动到的位置
 */
function setCursorPosition(id, pos){
    pos = parseInt(pos);
    var select_field = document.getElementById(id);
	if(select_field.setSelectionRange){
        select_field.focus();
        select_field.setSelectionRange(pos,pos);
    } else if (select_field.createTextRange) {
        var range = select_field.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}

/**
 * 获取选中文本数据信息
 *
 * @param  string 要获取选中文本所在节点的对象ID
 * @return JSON   返回选中JSON对象
 */
function getSelectText(id)
{
    var select_field = document.getElementById(id);
    var select_object= { text:'', CaretPos:0 };
    // IE
    if (document.selection)
    {
        var sel = document.selection.createRange();
        if (sel.text.length > 0) {
		  //  sel.moveStart ('character', - select_field.value.length);
            select_object.text = encodeURIComponent(sel.text);
        }
    }
    // Other
    else if (select_field.selectionStart || select_field.selectionStart == '0')
    {
        var select_start = select_field.selectionStart;
        var select_end = select_field.selectionEnd;
        if (select_start != select_end) {
            select_object.text = encodeURIComponent(select_field.value.substring(select_start, select_end));
        }
    }
    select_object.CaretPos = getPositionForTextArea(select_field);
    return select_object;
}

/**
 * 获取第一个内容标签信息
 *
 * @param  string id 要获取的内容所在的元素ID
 * @return JSON      返回选中JSON对象
 */
function getTextInfo(id) {
    var select_field = document.getElementById(id);
    var select_object= { text:'', CaretPos:0 };
    var patt = new RegExp(/\{(u|c|p)\$([^\s]+)\s*(.*?)\}([\s\S]*?)\{\/\1\$\2\}/);
    var result = patt.exec(select_field.value);
    // 如果以上获取的不是非封装标识则判断是否是封装标识
    if(result == null) {
        patt = new RegExp(/\{(u|c|p|tpl)\$(.+?)(\s|\})/);
        result = patt.exec(select_field.value);
    }

    if(result == null) {
        select_object.CaretPos = 0;
        select_object.text = '';
    } else {
        select_object.CaretPos = result.index
        select_object.text = encodeURIComponent(result[0]);
    }

    return select_object;
}

/**
 * 获取光标位置
 * @param object ctrl   要获取选中文本所在节点的对象
 * @return int CaretPos 返回光标位置
 */
function getPositionForTextArea(ctrl) {
    var CaretPos = 0;
    if(document.selection) {// IE Support
        ctrl.focus();
        var Sel = document.selection.createRange();
        var Sel2 = Sel.duplicate();
        Sel2.moveToElementText(ctrl);
        var CaretPos = -1;
        while(Sel2.inRange(Sel)){
            Sel2.moveStart('character');
            CaretPos++;
        }
    }else if(ctrl.selectionStart || ctrl.selectionStart == '0'){// Firefox support
        CaretPos = parseInt(ctrl.selectionStart);
    }
    return (CaretPos);
}

function clickSap(obj) {
    alert(obj);
    return false;
}

// 权限设置-动态显示权限方案连接
function setPermBar(fmid){
	var os = $id(fmid);
	var oa = $id('spBar_g5'+fmid);
	var oval = os.value.toString();
	var omsg = os.options[os.selectedIndex].text;
	if(Math.abs(oval)>0 && omsg!='全部自动审核'){
		var msg = "<a href='?entry=permissions&action=permissionsdetail&pmid="+oval.replace('-','')+"' onclick=\"return floatwin('open_permcase',this)\">"+omsg+"</a>";
	}else{
		var msg = "(无对应权限方案)";
	}
	oa.innerHTML = msg;
}

/**
 * 采集字段规则替换信息：导入类系
 * @param string ename   要获取选中文本所在节点的id部分字符串
 * @param string fromstr 类系标题组成的字符串
 * @param string tostr 	 类系id组成的字符串
 */
function export_ccid(ename,fromstr,tostr){		
	document.getElementById(ename + "_from").value = fromstr;
	document.getElementById(ename + "_to").value = tostr;
}

/**
 * 采集字段规则替换信息：清空类系
 * @param string ename   要获取选中文本所在节点的id部分字符串
 */
function clear_ccid(ename){
	document.getElementById(ename + "_from").value = '';
	document.getElementById(ename + "_to").value = '';
}
/*
 *采集：字段内容采集模印中选中的内容替换成(*)或(?)
 * @param object e   用作替换文本所在节点的对象
 * @param string id  被替换文本所在节点的id
*/
function replace_html(e,id){ 
	var select_object = getSelectText(id);
	select_object.text = decodeURIComponent(select_object.text);
	select_object.text && updateTagStr(id, select_object.text, e.innerHTML, select_object.CaretPos);
}

/*
 *采集：替换信息来源内容输入框中两边都添加(|)
 * @param string ename   要获取选中文本所在节点的id部分字符串
*/
function add_html(ename){ 
	document.getElementById(ename + "_from").value += '(|)';
	document.getElementById(ename + "_to").value += '(|)';
}

/**
 *后台>>推送管理>>全部推送位>>更新排序：每个分类增加“全选”功能
 *
 */
function chooseall(e){            
    var input_object = $(e).parent().next().find('input[type!=hidden]');    
    if(e.checked == true){
        for(i=0;i<input_object.length;i++){
            input_object[i].checked = true;                    
        }
    }else{
        for(i=0;i<input_object.length;i++){
            input_object[i].checked = false;   
        }
    }
}
