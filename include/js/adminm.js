var Head = document.getElementsByTagName('head')[0],
	cata_operates = [], curr_operate;
function $id(d){return typeof d == 'string' ? document.getElementById(d) : d}
function doane(e){
	if(!e)return;
	try{
		e.stopPropagation();
		e.preventDefault();
	}catch(x){
		e.returnValue=false;
		e.cancelBubble=true;
	}
}
function redirect(url) {
	window.location.replace(url);
}


function initaMenu(ul, ck){
	var oa, o, s = [];
	function C(k,v){k=encodeURIComponent(k);var r=(new RegExp('(?:;\s*)?'+k+'=(.+?)(?:;|$)')).exec(document.cookie);if(v!==undefined)document.cookie=k+'='+encodeURIComponent(v)+';expires=Fri, 31 Dec 2038 00:00:00 UTC';return r?decodeURIComponent(r[1]):r}
	function F(i, ul){
		var x, k;
		i.onclick = function(e){
			e = e || event;
			doane(e);
			var me = e.target || e.srcElement, a;
			me.blur();
			while(me.tagName.toLowerCase()!='li'){
				if(me.href){
					if(!e.nohref)main.location.replace(me.href.replace(/[?&]isframe\b[^&]*$|([?&])isframe\b[^&]*&/g,'$1'));
					a = 1;
				}else if(a && me.tagName.toLowerCase()=='em'){
					i.em = me;
					if(!me.className.match(/\bdj0/))me.className += ' dj0';
				}
				me = me.parentNode;
			}
			if(a){
				if(o && o.className != 'jia')o.className = o == i ? 'dian0' : o.oc ? o.oc : '';
				if(o && o != i && o.em)o.em.className = o.em.className.replace(/\s?dj0/,'');
				currItem = o = i;
				i.className = 'dian0';
			}
			if(!a && i == me && ul){
				if(i.className != 'dian0')i.className = !x ? '' : 'jia';else i.oc = !x ? '' : 'jia';
				ul.style.display = x ? 'none' : '';
				x = !x;
				if(ck)C(k, x ? 1 : 0);
			}
		};
		if(ul){
			if(ck){
				k = ck + '_' + s.join('_');
				x = C(k) == 1 ? 0 : 1;
			}else{
				x = 1;
			}
			i.onclick({target:i});
		}
		i.onmouseover = function(e){
			doane(e || event);
			if(i.className != 'dian0'){
				i.oc = i.className;
				i.className += ' hover0';
			}
		};
		i.onmouseout = function(e){
			doane(e || event);
			i.className = i.className.replace(/ ?hover0/,'');
		};
	}
	function G(ul){
		var a = [], i, z, x, cu, li = ul.childNodes;
		for(i = li.length-1; i >= 0; i--)if(li[i].nodeType == 1)a.push(li[i]);
		x = a[0];
		while(i = a.pop()){
			cu = i.getElementsByTagName('ul');
			s.push(a.length);
			if(cu.length){
				F(i,cu[0]);
				G(cu[0]);
			}else{
				F(i);
			}
			s.pop();
		}
	}
	G(ul);
}
var initMenus = [], currMenu, currSub, currItem;
function setMenu(id, no){
	var s ,i = -1, k = !/^\d+$/.test(id), a = $id((k ? '' : 'mainmenu_') + id), lm = $id('leftmenu'), oi = $id('operateitem');
	if(a)a.blur();
	$id('urlmenus').style.display = 'none';
	if(!no && !window.main) return alert('请等待窗口加载完成');
	if(currMenu){
		if(currMenu == a.parentNode)return;
		currMenu.className = '';
	}
	if(a)(currMenu = a.parentNode).className = 's1';
	if(currSub){
		if(currItem && currItem.className != 'jia')currItem.className = '';
		if(currItem && currItem.em)currItem.em.className = currItem.em.className.replace(/\s?dj0/,'');
		currSub.style.display = 'none';
	}
	a = $id((k ? '' : 'submenus_') + id).getElementsByTagName('a');
	while(++i < a.length)if(s = a[i].href.match(/^javascript:\/\/(\w+)/)){s = s[1]; break;}i = -1;
	switch(s){
	case 'content':
		curr_operate = null;
		oi.style.display = '';
		lm.className = 'col1';
		(currSub = $id('catamenu')).style.display = '';
		a = currSub.childNodes;
		while(++i < a.length)if(a[i].nodeType == 1 && a[i].tagName.toLowerCase() == 'li'){a = a[i].getElementsByTagName('a')[0];break}
		a.onclick();
		break;
	default:
		oi.style.display = 'none';
		lm.className = 'col2';
		if(!initMenus[id])initaMenu(initMenus[id] = $id((k ? '' : 'submenus_') + id));
		(currSub = initMenus[id]).style.display = '';
		a = currSub.getElementsByTagName('a')[0];
	}
	if(!no && a.href && a.href.charAt(0) != '&' && !a.href.match(/\/&/))a.parentNode.parentNode.onclick({target:a});
}

function showdv(index){   
	for(i in divarr){
		var j = divarr[i];
		var e = $id("dv"+ j);
		var ev = $id("dvShow"+ j);
		if( e == null || ev == null ) continue;
		if(j == index){
			if(ev.style.display == "none"){
				ev.style.display = "";
				e.className="left2_on";
			}else{   
				ev.style.display = "none";
				e.className="left2_out";
			}
		}else{
			e.className="left2_out";
			ev.style.display="none";
		}
	}
}

function SpaceClass(divname){
	$id(divname).className="aclick";
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
function alterview(tname){
	if($id(tname)!=null){
		if($id(tname).style.display=='none'){
			$id(tname).style.display='';
		}else{
			$id(tname).style.display='none';
		}
	}
}

function redirect(url) {
	window.location.replace(url);
}

function checkByCell(e){
	e = e || event;
	if(e.button == 2)return;
	e = e.target || e.srcElement;
	if(in_array(e.tagName, ['INPUT','TEXTAREA','A','IMG']))return;
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
listen(document.documentElement, 'mousedown', checkByCell);