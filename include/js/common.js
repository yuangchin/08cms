var scripts=document.getElementsByTagName("script"),
script=scripts[scripts.length-1];  //因为当前dom加载时后面的script标签还未加载，所以最后一个就是当前的script
src=script.src.split('?');
if(typeof _08cms == 'undefined')_08cms = {};
if(!_08cms.stack)_08cms.stack = {};
function $id(d){return typeof d == 'string' ? document.getElementById(d) : d}
top.__08CMS_TOP_INFO__ || (top.__08CMS_TOP_INFO__ = {'_INFOS_' : {}});
var _08CMS_ = top.__08CMS_TOP_INFO__, undefined;
_08CMS_.set = function(key, val){if(!this._INFOS_[key] || this._INFOS_[key].window === window)this._INFOS_[key] = {'window' : window, 'value' : val};return this._INFOS_[key].value};
_08CMS_.get = function(key){return this._INFOS_[key] ?  this._INFOS_[key].value : undefined};
$WE = _08CMS_.set('$id', $id);
if(!$WE.elements){
	$WE.index = 999;
	$WE.elements = {};
}
(typeof CMS_ABS == 'undefined') && (CMS_ABS = '');
(typeof CMS_URL == 'undefined') && (CMS_URL = '/');
_08cms._ua = function(){
	var E={ie:0,opera:0,gecko:0,webkit:0,mobile:null,air:0,caja:0},F=navigator.userAgent,D;
	if((/KHTML/).test(F)){E.webkit=1}
	D=F.match(/AppleWebKit\/([^\s]*)/);
	if(D&&D[1]){
		E.webkit=parseFloat(D[1]);
		if(/ Mobile\//.test(F)){E.mobile="Apple"}else{
			D=F.match(/NokiaN[^\/]*/);
			if(D){E.mobile=D[0]}
		}
		D=F.match(/AdobeAIR\/([^\s]*)/);
		if(D){E.air=D[0]}
	}
	if(!E.webkit){
		D=F.match(/Opera[\s\/]([^\s]*)/);
		if(D&&D[1]){
			E.opera=parseFloat(D[1]);
			D=F.match(/Opera Mini[^;]*/);
			if(D){E.mobile=D[0]}
		}else{
			D=F.match(/MSIE\s([^;]*)/);
			if(D&&D[1]){E.ie=parseFloat(D[1])}else{
				D=F.match(/Gecko\/([^\s]*)/);
				if(D){
					E.gecko=1;
					D=F.match(/rv:([^\s\)]*)/);
					if(D&&D[1]){E.gecko=parseFloat(D[1])}
				}
			}
		}
	}
	D=F.match(/Caja\/([^\s]*)/);
	if(D&&D[1]){E.caja=parseFloat(D[1])}
	return E
}();

_08cms.event = function(){
	if (!this._ua.ie && !this._ua.opera){
		var $, _ = arguments.callee.caller;
		while(_){
			$ = _.arguments[0];
			if ($ && ($.constructor == Event || $.constructor == MouseEvent))return $;
			_ = _.caller
		}
		return null
	}
	return event
};

_08cms.elem = function(){
	var e = _08cms.event();
	return e ? e.srcElement || e.target : e
};

_08cms.each = function(object, callback){
	var key, value, length = object.length;
	if(length === undefined || object instanceof Function){
		for(key in object)if(callback.call(value = object[key], key, value) === false)break;
	}else{
		var i = -1;
		while(object[++i] && callback.call(value = object[i], i, value) !== false);
	}
};

function redirect(url){
	if(location.assign){
		location.assign(url);
	}else{
		location.replace(url);
	}
}

function Cookie(key, value, expires, path, domain, secure){
	key = encodeURIComponent(key);
	var t = expires, r = (new RegExp('(?:^|;)\\s*' + key + '=(.*?)(?:;|$)')).exec(document.cookie), e, f;
	if(value !== undefined){
		if(t && !(t instanceof Date)){
			e = t;t = new Date();
			if(value === null){
				value = '';
			}else{
				e = /^([+-]?)(\d+)([YMWDHIS]?)$/i.exec(e) || [,,0];
				e[3] && (e[3] = e[3].toUpperCase());
				e[3] == 'W' && (e[3] = 'D') && (e[2] *= 7);
				f = {Y : 'FullYear', M : 'Month', D : 'Date', H : 'Hours', I : 'Minutes', S : 'Seconds'}[e[3] || 'I'];
				eval('t.set' + f + '(t.get' + f + '()' + (e[1] || '+') + e[2] + ')')
			}
		}
		document.cookie = key + '=' + encodeURIComponent(value)
						+ (t ? ';expires=' + t.toGMTString() : '')
						+ '; path=' + (typeof path != 'string' ? '/' : path)
						+ (domain ? '; domain=' + domain : '')
						+ (secure ? '; secure' : '')
	}
	return r ? decodeURIComponent(r[1]) : r
}

(function(window){
var load, start = true, end, stack = {};
function listen(dom,event,action){
	if(event == 'ready')return ready.add(dom, action);
	if(dom.attachEvent){
		var func=action;action=function(){func.apply(dom,arguments)};
		dom.attachEvent('on'+event,action);
	}else if(dom.addEventListener){
		dom.addEventListener(event,action,false);
	}
}
function ready(){
	var id, dom, has, flag, func, parent;
	for(id in stack){	   
	    if ( typeof debug != 'undefined' )
        {
            debug.innerHTML = (new Date).getTime();
        } 
		if(!(dom = document.getElementById(id))){
			has = true;
			continue;
		}
		if(!load && !dom.nextSibling){
			flag = false;
			parent = dom.parentNode;
			while(parent){
				if(parent.nextSibling){
					flag = true;
					break;
				}
				parent = parent.parentNode;
			}
			if(!flag){
				has = true;
				continue;
			}
		}
		while(func = stack[id].pop())func.apply(dom);
		delete stack[id];
	}
	if(load || end || !has){
		load && !end && (stack = {});
		start = true;
	}else{
		setTimeout(ready, 20);
	}
}
ready.add = function(id, func){
	end = true;
	var timer = setInterval(function(){
		if(!start)return;
		clearInterval(timer);
		start = end = false;
		if(id)stack[id] ? stack[id].push(func) : stack[id] = [func];
		ready();
	}, 20);
};
listen(window, 'load', function(){load = true;ready.add()});
window.listen = listen;
})(this);

function trim(str) {
	return str?str.replace(/^\s+|\s+$/,''):'';
}

function display(id) {
	$id(id).style.display=$id(id).style.display=='' ? 'none' : '';
}

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

function strlen(str){
	var tmp =window.charset == 'utf-8' ? '***' : '**';
	return str.replace(/[^\x00-\xff]/g, tmp).length;
}

function init_clear(a){
	var a = a instanceof Array ? a : arguments, l = a.length, r = '', i;
	if(l){
		for(i = 0; i < l; i++)r += 'delete $WE.elements[' + a[i] + '];';
		r = 'try{' + r + '}catch(e){}';
	}
	return r;
}

function boxTo(){
	var ipt, max, me = _08cms.elem(), box = me.parentNode.getElementsByTagName('INPUT'), value = [];
	if(me.type != 'checkbox')return;
	_08cms.each(box, function(){
		if(this.type == 'checkbox'){
			this.checked && value.push(this.value);
		}else if(!ipt && this.name){
			ipt = this;
		}
	});
	if(ipt){
		if((max = parseInt(ipt.getAttribute('max'))) && value.length > max){
			me.checked = false;
		}else ipt.value = value.join(',');
	}
}

/* 回调函数：uploadwin.callback = function(){}, 还可以把 callback 代替 element 参数直接传递*/
function uploadwin(mode, element, mincount, maxcount, player, wmid, float, width, height, url, ckeditor){
	width = width || 630;
	height = height || 460;
    ckeditor = ckeditor || 0;
    if ( (typeof(ck_edit_config) == 'object') && ck_edit_config.upload_plugin_area )
    {
        upload_plugin_area = ck_edit_config.upload_plugin_area;
    }
    else
    {
    	upload_plugin_area = '';
    }
    
	function _()
    {
	   //return CMS_URL + 'tools/upload.php?win_id='+wid+
	   return CMS_URL + uri2MVC('upload=post' + '&win_id='+wid+
              '&domain='+src[1]+
              '&type='+mode+
              '&mincount='+mincount+
              '&maxcount='+maxcount+
              '&wmid='+(wmid||0)+
              '&field_id='+i+(empty(player)?'':'&player=1')+
              '&upload_plugin_area=' + upload_plugin_area +
              '&is_ckeditor=' + ckeditor);
    }
	var i,p,ifr,win,wid='uploadwin';
	//p = $id(element);有时候p为null
	p = (typeof(element)=='function') ? element : $id(element);
	i = p.fwex = p.fwex ? p.fwex : ++$WE.index;
	$WE.elements[i] = p;
	if(window.floatwin && (float||float===undefined)){
		floatwin.remember = 0;
		floatwin.style = {width : width, height : height, modal : 1, resize : 0};
		floatwin('open_' + wid, -1);
		showloading();
		try { wid = _08CMS_.get('floatwin').fcwid; } catch(e) { wid = parseInt(Cookie('wid')) + 1; }
        Cookie('wid', wid);
		win = CWindow.getWindow(wid);
		win.content('<iframe id="' + wid + '_iframe" name="' + wid + '_iframe" onload="showloading(\'none\');CWindow_frame_onload(this,' + wid + ')" width="100%" height="100%" frameborder="0" scrolling="auto"></iframe>');
		ifr=$WE(wid + '_iframe');
		listen(ifr,'load',function(){floatwin_title(win, this.contentWindow.document.title)});
		ifr.src=(url == '' || url == undefined ? _() : url);
	}else{
		var left=(screen.width-width)/2,top=(screen.height-height)/2;
		win = window.open((url == '' || url == undefined ? _() : url), wid, 'scrollbars=no,resizable=yes,statebar=no,width='+width+',height='+height+',left='+left+',top='+top);
		win.focus();
	}
	win.uploadCallback = uploadwin.callback;
	uploadwin.callback = null;
}

function findtags(parentobj, tag) {
	if(parentobj.getElementsByTagName) {
		return parentobj.getElementsByTagName(tag);
	} else if(parentobj.all && parentobj.all.tags) {
		return parentobj.all.tags(tag);
	} else {
		return null;
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
function checksubject(btn, tab, fix){
	var field = btn.form[fix ? fix : 'subject'], val = encodeURIComponent(trim(field.value));
	if(!val || !tab)return alert('请输入标题内容！');
	if(val == field.defaultValue)return alert('无修改动作');
	btn.disabled = true;
	var url=CMS_ABS + uri2MVC({'ajax':'subject', 'datatype':'json', 'table':tab,'subject':val});
    $.ajax({
        type:'get',
        url:url,
        success:function(data){
            if(data){
			btn.disabled = false;
			count = parseInt(data);
			alert(!count ? '标题没有重复!' : count == -1 ? '请输入标题内容！' : '输入的标题已存在！');
           }
        }
    });
}

/**
 * 检查分机号
 * @param {type} btn
 * @param {type} fix
 * @returns {unresolved}
 */
function checkwebcallext(btn, fix){
	
	var field = btn.form[fix ? fix : 'extcode'], val = encodeURIComponent(trim(field.value));
	if( !val )return alert('请输入分机号码！');
	//if(val == field.defaultValue)return alert('无修改动作');
	btn.disabled = true;
	var url=CMS_ABS + uri2MVC({'ajax':'webcallexist', 'datatype':'json', 'extcode':val});
    $.ajax({
        type:'get',
        url:url,
        success:function(data){
            btn.disabled = false;
            if(data=='succeed'){
			alert(data);
           }
           else
           {
               alert(data);
               field.value = '';
           }
        }
    });
}

function checkbadwords(text){
	var bwAjaxUrl = CMS_URL + uri2MVC('ajax=badwords&act=text&domain='+document.domain); 
	$.post(bwAjaxUrl, "content="+encodeURIComponent(text), function (res) {
		if(res.length>0){
			alert('含有不良关键词:\n'+res+'\n\n可能有些地方会屏蔽显示。');
		}else{
			//console.log('b:'+res);	
		}
	});  
}
function check_repeat(key, type){
	var btn = _08cms.elem();
	if(!btn)return;
	var path = '|dirname|frnamesame|mdirname|'.indexOf('|' + type + '|') >= 0 ? 'tools' : 'etools';
	var field = btn.form[key], val = trim(field.value), onfocus = field.onfocus;
	if(!val || !type)return alert('请输入要测试的字符串');
	if(val == field.defaultValue)return alert('无修改动作');
	btn.disabled = true;
	field.onfocus = function(e){
		btn.disabled = false;
		onfocus && onfocus.call(this, e);
	};
    var url=CMS_ABS + uri2MVC({'ajax' : type, 'datatype' : 'json', 'value' : encodeURIComponent(val)});
    $.ajax({
        type:'get',
        url:url,
        success:function(data){
            if(data){
			btn.disabled = false;
		    count = parseInt(data);
		    alert(!count ? '测试的字符串可以正常用' : count == -1 ? '请输入要测试的字符串' : '测试的字符串已存在，请重新输入');
           }    
        }
    });
}
function check_sitemaps_repeat(key, type){
	var btn = _08cms.elem();
	if(!btn)return;
	var field = btn.form[key], val = trim(field.value), onfocus = field.onfocus;
	if(!val || !type)return alert('请输入要测试的字符串');
	if(val == field.defaultValue)return alert('无修改动作');
	btn.disabled = true;
	field.onfocus = function(e){
		btn.disabled = false;
		onfocus && onfocus.call(this, e);
	};
	var url=CMS_URL + uri2MVC({'ajax' : type, 'datatype' : 'json', 'value' : encodeURIComponent(val)});
    $.ajax({
        type:'get',
        url:url,
        success:function(data){
            if(data){
			btn.disabled = false;
			count = parseInt(data);
			alert(!count ? '测试的字符串可以正常用' : count == -1 ? '请输入要测试的字符串' : '测试的字符串已存在，请重新输入');
           }    
        }
    });
}
function faceIcon(box, add){
	function click(face){
		if(add != undefined){
			if(add){
				box.value += face;
			}else{
				box.value = face + box.value;
			}
		}else{
			box.focus();
			if(box.selectionStart != undefined){
				var x = box.selectionStart;
				box.value = box.value.substr(0, x) + face + box.value.substr(box.selectionEnd);
				box.selectionStart = x + face.length;
				box.selectionEnd = x + face.length;
			}else if(document.selection){
				document.execCommand('paste', false, face);
			}else{
				box.value += face;
			}
		}
		return false;
	}
	function init(f){
		if(typeof FACEICONS == 'undefined'){
			setTimeout(function(){init(f)}, 100);
		}else{
			f[0] = a;
			a.box = box;
			f.callee.apply(null, Array.prototype.slice.call(f,0));
		}
	}
	var html, a, i, k, l, o, s, t, z = [];
	if(box.box){
		a = box;
		box = box.box;
	}else{
		t = '__' + (new Date).getTime() + Math.random().toString().substr(2);
		document.write('<div class="fi_div" id="' + t + '"></div>');
		a=document.getElementById(t);
	}
	if(typeof FACEICONS == 'undefined'){
		s = document.createElement('SCRIPT');
		s.type='text/javascript';
		s.src = CMS_URL + 'dynamic/cache/faceicons.js';
		document.getElementsByTagName('HEAD')[0].appendChild(s);
		init(arguments);
		return ;
	}
	typeof box == 'string' && (box = document.getElementById(box));
	html = '';
	k = 0;
	click = add instanceof Function ? add : click;
	while(o = FACEICONS[k++]){
		if(o[1].length){
			s = o[1];
			z.push(o);
			html += '<ul style="display:none">';
			for(i = 0, l = s.length; i < l; i++){
				if(!s[i])break;
				if(s[i][1].indexOf('://')<0)s[i][1] = CMS_ABS + s[i][1];
				html += '<li><a href="#' + s[i][0] + '" onclick="return false"><img src="' + s[i][1] + '" border="0" /></a></li>';
			}
			html += '</ul>';
		}
	}
	a.innerHTML = html;
	t = a.childNodes;
	a.onclick = function(e){
		e = e || event;
		e = (e.target || e.srcElement).parentNode;
		e.tagName.toUpperCase() == 'A' && click(e.href.slice(e.href.indexOf('#') + 1));
		return false;
	};
	if(z.length > 1){
		k = 0;
		s = document.createElement('UL');
		s.className = 'fi_ul';
		t[0].parentNode.parentNode.insertBefore(s, t[0].parentNode);
		while(o = z[k++]){
			i = document.createElement('LI');
			s.appendChild(i);
			i.innerHTML = '<a href="javascript:void(' + k + ');">' + o[0] +'</a>';
		}
		z = 0;
		a = s.childNodes;
		(s.onclick = function(e){
			e = e || event;
			e = e.target || e.srcElement;
			if(e.tagName.toUpperCase() == 'A'){
				a[z].className = '';
				t[z].style.display = 'none';
				z = e.href.match(/\d+/)[0] - 1;
				a[z].className = 'act';
				t[z].style.display = '';
			}
			return false;
		})({target : a[0].firstChild});
	}else{
		t[0].style.display = '';
	}
}

// 字段多项选择,检测是否为空
function resetCheckbox(fmid, init){
	var cbs = document.getElementsByName(fmid);
	if(cbs.length==0) cbs = document.getElementsByName(fmid+'[]');
	var rv = '';
	if(init=='set'){
		for(var i=0;i<cbs.length;i++){
			if(cbs[i].checked) rv += cbs[i].value+',';  
		}
		$id('cbs_'+fmid+'_vals').value = rv;
		return;
	}
	for(var i=0;i<cbs.length;i++){
		if(cbs[i].checked) rv += cbs[i].value+',';
	}
	$id('cbs_'+fmid+'_vals').value = rv;
	for(var i=0;i<cbs.length;i++){
		cbs[i].onchange = function(){
			resetCheckbox(fmid, 'set');
		}	
	}
}

_08cms.random_symbol = function(){
	var str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz$_', len = str.length,
		data = parseInt(Math.random().toString().slice(2)), symbol = '$_', tmp;
	while(data){
		tmp = data % len;
		symbol += str.charAt(tmp);
		data = (data - tmp) / len;
	}
	return symbol;
}
if(!_08cms.map)_08cms.map = {};
_08cms.map.setButton = function(self, action, field, info, zoom, coord){
	var form = self.form, cls = self.className;
	if(!top._08cms)top._08cms = {};
	if(!top._08cms.stack)top._08cms.stack = {};
	if(!top._08cms.stack.object)top._08cms.stack.object = {};
	self.onfocus = null;
	if(!(field = field.nodeType ? field : form[field]))return;
	if(!info){
		info = self.nextSibling;
		while(info.nodeType != 1)info = info.nextSibling;
	}
	self.onfocus	= function(){
		self.className = cls + 'hover';
	};
	self.onmouseout	=
	self.onblur		= function(){
		self.className = cls;
	};
	self.onclick	= function(){
		self.blur();
		_08cms.map['set' + action](self, field, info, zoom, coord);
		return false
	};
	self.onfocus();
};
_08cms.map.setmarker = function(self, field, info, zoom, coord){
    //setDoMain(document.URL);
	var id = _08cms.random_symbol();
	var latlng = field.value ? field.value.split(',') : [];	
	var width = 800, height = 600, left = (screen.width - width) / 2, top = (screen.height - height) / 2, url = CMS_URL + 'tools/marker.html?stack=' + id;
	window.top._08cms.stack.object[id] = window._08cms.stack.object[id] = {
		lat : latlng[0],
		lng : latlng[1],
		btn : self,
		info : info,
		root : CMS_ABS,
		zoom : zoom,
		field : field,
		coord : field.value ? 0 : coord,
		window : window
	};
//	win = window.open(url, 'mapmarker', 'scrollbars=no,resizable=yes,statebar=no,width='+width+',height='+(height-30)+',left='+left+',top='+top);
//	win.focus();
	floatwin('open_mapmarker', url);
};
if(!_08cms.stack.object)_08cms.stack.object = {};
if(!_08cms.vote)_08cms.vote = {
	addVote : function(self, key, chid, mode){
		var id = _08cms.random_symbol();
		var form = self.parentNode;
		while(form && form.tagName != 'FORM')form = form.parentNode;
		if(!form){
			alert('没有找到 Form 表单！');
			return false;
		}
		_08cms.stack.object[id] = {
			form: form,
			btn : self,
			key : key,
			chid: chid,
			mode: mode
		};
		_08cms.vote.window(id);
	},
	editVote : function(self, key, chid, mode, fid){
		var id = _08cms.random_symbol();
		var form = self.parentNode;
		while(form && form.tagName != 'FORM')form = form.parentNode;
		if(!form || !form.elements[key + '[' + fid + '][subject]']){
			alert('没有找到 Form 表单！');
			return false;
		}
		_08cms.stack.object[id] = {
			form: form,
			btn : self,
			key : key,
			chid: chid,
			mode: mode,
			fid : fid
		};

		_08cms.vote.window(id);
	},
	delVote : function(self, key, fid){
		var i, len, obj, item;
		var form = self.parentNode;
		while(form && form.tagName != 'FORM')form = form.parentNode;
		if(!form || !form.elements[key + '[' + fid + '][subject]']){
			alert('没有找到 Form 表单！');
			return false;
		}
		if(!confirm('确定要删除选定的项目吗？'))return false;
		item = key + '[' + fid + ']';
		len = item.length;
		for(i = form.elements.length - 1; i >= 0; i--){
			obj = form.elements[i];
			if(obj.name && obj.name.slice(0, len) == item){
				obj.disabled = true;
				obj.name = '';
				obj.parentNode.removeChild(obj);
			}
		}
		obj = $id(item);
		obj && obj.parentNode.removeChild(obj);

		len = key.length;
		for(i = form.elements.length - 1; i >= 0; i--){
			obj = form.elements[i];
			if(obj.name && obj.name.slice(0, len) == key)return;
		}
		item = document.createElement('INPUT');
		item.name = key;
		item.type = 'hidden';
		form.appendChild(item);
		form.elements[key] = item;
	},
	window : function(id){
		var width = 800, height = 600, left = (screen.width - width) / 2, top = (screen.height - height) / 2;
		var obj = _08cms.stack.object[id];
		var field = obj.key.match(/^(?:\w+\[)?(\w+)\]?$/)[1];
		return window.open(CMS_URL+'tools/setvote.php?stack=' + id + '&chid=' + obj.chid + (obj.mode ? '&mode=' + obj.mode : '') + '&field=' + field + '&domain=' + src[1], 'mapmarker', 'scrollbars=yes,resizable=yes,statebar=no,width='+width+',height='+(height-30)+',left='+left+',top='+top);
	}
}
function ShowColor(e){
	LoadNewDiv(e, CMS_URL + 'images/common/colornew.htm', 'colordlg');
}
function SelectSource(e){
	LoadNewDiv(e, CMS_URL + uri2MVC('ajax=mysource&charset='+charset+'&t='+Math.random()), 'mysource');
}
function SelectAuthor(e){
	LoadNewDiv(e, CMS_URL + uri2MVC('ajax=myauthor&charset='+charset+'&t='+Math.random()), 'myauthor');
}
function SelectKeywords(e){
	LoadNewDiv(e, CMS_URL + uri2MVC('ajax=mykeyword&charset='+charset+'&t='+Math.random()), 'mykeyword');
}
function HideObj(objname){
	var obj = $id(objname);
	if(obj == null) return false;
	obj.style.display = "none";
}
function removeObj(id){
	var obj = $id(id);
	if(obj == null) return false;
	document.body.removeChild(obj);
}
function PutSource(str){
	HideObj('mysource');
	with($id('selSource').parentNode.getElementsByTagName('INPUT')[0])
	{value = str;focus();}
}
function PutAuthor(str){
	HideObj('myauthor');
	with($id('selAuthor').parentNode.getElementsByTagName('INPUT')[0])
	{value = str;focus();}
}
function PutKeyword(str){
	with($id('selkeywords').parentNode.getElementsByTagName('INPUT')[0]){
		value = value == '' ? str : value + ',' + str;
		focus();
	}
}
function LoadNewDiv(e,surl,oname){
    var pxStr = '';
	if(e.pageX){ //_08cms._ua.ie,自IE9开始(少数IE8)很多属性又与Firefox等类似!!!
		var posLeft = e.pageX-20;
		var posTop = e.pageY-30;
        pxStr = 'px';
	}else{
		var posLeft = window.event.clientX-20;
		var posTop = window.event.clientY-30;
        // IE下scrollTop的兼容性问题
        var scrollTop = document.documentElement.scrollTop || window.pageYOffset;
        if(typeof(scrollTop) == 'undefined') scrollTop = document.body.scrollTop;
		posTop += scrollTop;
	}
	posTop += 43; //下移，不要覆盖input
	posLeft = posLeft - 100;
	var newobj = $id(oname);
	if(!newobj){
		newobj = document.createElement("DIV");
		newobj.id = oname;
		newobj.className = oname;
		newobj.style.position = 'absolute';newobj.style.top = posTop + pxStr;newobj.style.left = posLeft + pxStr;
		document.body.appendChild(newobj);
	}
	else{
		newobj.style.display = "block";
	}
	if(newobj.innerHTML.length<10){
		$.ajax({
			type:'get',
			url:surl,
			success:function(html){
				if(html){
				newobj.innerHTML = html;
			   }    
			}
		});
	}
}
function ColorSel(c){
	var tobj = $id('setcolor').parentNode.getElementsByTagName('INPUT')[0];
	var colorobj = $id('color');
	colorobj.value = c;
	tobj.style.color = c;
	$id('colordlg').style.display = 'none';
	return true;
}
function strlen_verify(obj,maxlen) {
	var v = obj.value, charlen = 0, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = v.length;
	for(var i = 0; i < v.length; i++) {
		if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 255) {
			curlen -= charset == 'utf-8' ? 2 : 1;
		}
	}
	if(curlen >= len) {
		try{ $id('inputnum').innerHTML = curlen - len; }
		catch(ex){} //并不是每个subject都有inputnum
	} else {
		obj.value = mbt_cutstr(v, maxlen, true);
	}
}
function mbt_cutstr(str, maxlen, dot) {
	var len = 0,ret = '';
	dot = !dot ? '...' : '';
	maxlen = maxlen - dot.length;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
		if(len > maxlen) {
			ret += dot;
			break;
		}
		ret += str.substr(i, 1);
	}
	return ret;
}
// 对一般的删除input-checkbox，加上title属性，可以改变默认的删除提示信息
// 如：title="您所选条目将取消关联，同类此操不再提示！继续？"	
function deltip(item,ddevelop,cbak){
	if(!item)item = _08cms.elem();
	if(item.nodeName == 'A'){
		if(ddevelop){
			alert('架构保护模式下禁止的操作，请联系创始人');
			return false;
		}else if(!confirm('确定要删除该条目？')){
			return false;
		}else item.href += '&confirm=ok';
	}else{
		if(item.checked && ddevelop){
			alert('架构保护模式下禁止的操作，请联系创始人');
			return item.checked = false;
		}else if(item.checked && !item.form._08cms_vars_deltip){
			item.form._08cms_vars_deltip = 1;
			var msg = item.title ? item.title : '您所选条目将被删除，同类删除不再提示！继续删除？';
			if(!confirm(msg)){
				return item.checked = false;
			}
		}
	}
	if(cbak){
		cbak.apply(null, Array.prototype.slice.call(arguments, 3));
	}
}

function DateControl(configs){
	configs || (configs = {});
	var k, obj, tmp, vars = {
		el : configs.field || _08cms.elem(),
		readOnly : true
	};
	if(tmp = configs.format){
		if(obj = document.getElementById(tmp)){
			tmp = obj.value;
		}else if(obj = document.getElementsByName(tmp)){
			for(k in obj){
				if(obj[k].checked){
					tmp = obj[k].value;
					break;
				}
			}
		}
		if(tmp.toString().match(/^\d+$/)){
			tmp = [
				'yyyy-MM-dd',
				'yyyy-MM-dd HH:mm:ss'
			][tmp];
		}
		if(tmp)vars.dateFmt = tmp;
	}
	return WdatePicker(vars);
}
//户型或者相册列表中，点击图片时，对应的checkbox呈现选取还是未选取状态
function _img_affect_checkbox(aid){
	var name = "selectid[" + aid + "]";
	var obj = document.getElementsByName(name)[0];
	obj.checked = obj.checked?false:true;
}

function getcookie(name) {
    var tmp,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)","gi");
    return (tmp=reg.exec(document.cookie))?(unescape(tmp[2])):'';
	//var cookie_start = document.cookie.indexOf(name);
	//var cookie_end = document.cookie.indexOf(";", cookie_start);
	//return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
}

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	var expires = new Date();
	expires.setTime(expires.getTime() + seconds);
	document.cookie = escape(cookieName) + '=' + escape(cookieValue)
        // 以下修改成兼容IE
		+ ((seconds != null && seconds != '') ? '; expires=' + expires.toGMTString() : '')
		+ ((path != null && path != '') ? '; path=' + path : '; path=/')
		+ ((domain != null && domain != '') ? '; domain=' + domain : '')
		+ ((secure != null && secure != '') ? '; secure' : '');
}

/**
 * 获取URI值
 *
 * @param  string name 要获取的值名称
 * @return string      返回名称的值
 * @author Wilson
 */
function getUriValue(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

/**
 * 判断是否跨域了，如果是则自动设置域为顶级域
 *
 * @param string url 要获取的页面URL，通常传递当页面URL（即：document.URL）
 */
function setDoMain(url) {
	var reg = new RegExp('.*?([^\.|^//]*\.(com|com\.cn|com\.hk|cn|net|net\.cn|org|org\.cn|gov|gov\.cn|edu|mobi|cc|hk|tv|biz|name|info|so|co|asia|me|uk|fr|jp|tw]))/', 'i');
	var arr = url.match(reg); 
	if(arr==null){ // 如果为IP,则:ma为null // Peace 添加
		return false;
	}else{
		var old_domain = document.domain;
		var new_domain = arr[1];
		if((old_domain != new_domain) && (src[1] == 'domain=1')){
			document.domain = arr[1];
        }
	};
}

/**
 * 预览图片-按比例缩放, Demo: onload="javascript:setImgSize(this,400,300);"
 * 用于: 单图字段，附件预览 等
 *
 * @param object obj 图片Element
 * @param int    w   最大显示宽
 * @param int    h   最大显示高
 */
function setImgSize(obj,w,h){
  img = new Image(); img.src = obj.src; 
  zw = img.width; zh = img.height; 
  zr = zw / zh;
  if(w){ fixw = w; }
  else { fixw = obj.getAttribute('width'); }
  if(h){ fixh = h; }
  else { fixh = obj.getAttribute('height'); }
  if(zw > fixw) {
	zw = fixw; zh = zw/zr;
  }
  if(zh > fixh) {
	zh = fixh; zw = zh*zr;
  }
  obj.width = zw; obj.height = zh;
}
 
function setImgShow(id, style) {
	var div = $id(id+'_view');
	var img = $id(id+'_img'); 
	if($id(id).value.length>12 && !style){
		img.src = $id(id).value; 
		div.style.display = 'block';
		div.style.zIndex = '1';
		div.className = 'image_pview';  
	}else{
		div.style.display = 'none';	
	}
}

/* 实现HTML5的placeholder标签功能 */
function _08_inputOnClick(input, message)
{
    if ( input.value == message )
    {
        input.value = '';
    }
    
    input.style.color = '#666';
}

function _08_inputOnBlur(input, message)
{        
    if ( !input.value )
    {
        input.value = message;
    }
    
    input.style.color = '#ccc';
}
/* 实现HTML5的placeholder标签功能 end */

function _08cms_layer( configs )
{
    // 为了解决与 contentsAdmin.css里的样式冲突增加以下语句
    var parentDocument = parent.document;
    parentDocument.body.style.textAlign='left';
    if ( !configs.width )
    {
        configs.width = '500px';
    }
    
    if ( !configs.height )
    {
        configs.height = '200px';
    }
    
    parent.jQuery.layer({
    	type: configs.type,
    	title: configs.title,
    	offset: ['150px',''],
    	area: [configs.width, configs.height],
    	iframe: {src: configs.url},
        close: function(index) {
            parentDocument.body.style.textAlign='center';
            parent.layer.close(index);
        }
    });
}

/**
 * 把URI转成MVC路由的URI
 * 注：转换后的URI后面如果没有 / 会自动增加一个 /
 * 
 * @param  object uri         要转换的原始URI（JSON格式），但参数一的键和值必需是控制器名和action名
 * @param  bool   addFileName 是否要增加路由文件名称，默认为增加，传递false为不增加
 * @return string             返回转换后的MVC架构URI
 * 
 * @since  nv50
 */
function uri2MVC ( uri, addFileName )
{
    var _split = '/';
    if ( !_08_ROUTE_ENTRANCE )
    {
        var _08_ROUTE_ENTRANCE = 'index.php?/';
    }
    
    (addFileName == undefined) && (addFileName = true);
    var _uri = '';
    if ( typeof uri == 'string' )
    {
        _uri = uri.replace(/&/g, _split).replace(/=/g, _split);
    }
    else
    {
    	for ( var i in uri )
        {
            _uri += (i + _split + uri[i] + _split);
        }
    }
    var _endstr = _uri.charAt(_uri.length - 1);
    if ( _endstr == _split )
    {
        _uri = _uri.substr(0, _uri.length - 1);
    }
    
    var newURI = addFileName ? _08_ROUTE_ENTRANCE + _uri : _uri;
    if ( !/domain/i.test(newURI) )
    {
        newURI += (_split + 'domain' + _split + (self.originDomain || document.domain));
    }
    return newURI;   
}