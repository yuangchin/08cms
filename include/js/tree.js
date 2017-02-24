function TreeList(a){
	//beeline/branch/turning/spread
	var cls=[
				['item','icon','body'],
				['spac','lin0'],
				['lin1','lin2'],
				['add1','add2'],
				['sub1','sub2'],
				['ico0','ico1','ico2'],
				['box0','box1','box2'],
				['over','out']
	],b=E('tbody'),r=E('tr'),o=new T(),$=o.$.dom,ck=a.cookie;
	function A(p,c){p.appendChild(c)}
	function C(d,c){d.className=c}
	function D(d){d.parentNode.removeChild(d)}
	function E(i,c){var d=document.createElement(i);if(c)C(d,c);return d}
	function CK(k,v){k=encodeURIComponent(k);var r=(new RegExp('(?:;\s*)?'+k+'=(.+?)(?:;|$)')).exec(document.cookie);if(v!==undefined)document.cookie=k+'='+encodeURIComponent(v)+';expires=Fri, 31 Dec 2038 00:00:00 UTC';return r?decodeURIComponent(r[1]):r}
	function MI(){C(this,cls[0][0]+' '+cls[7][0])}
	function MO(){C(this,cls[0][0]+' '+cls[7][1])}
	function T(){
		var t=this;
		t.$={dom:{line:[],item:E('table',cls[0][0]),body:E('div')},ckl:0};
		with(t.$.dom.item){cellPadding=0;cellSpacing=0}
		t.childs=[];
		t.add=function(d){
			var c=new T(),l=t.$.last,$=t.$.dom;
			c.data=d;
			c.parent=t;
			c.offset=l;
			if($.load){$.load.del();$.load=0}
			c.$.cid=(t.$.cid?(t.$.cid+'_'):'')+t.childs.length;
			if(ck&&d.show===undefined)eval('d.show='+CK(ck+c.$.cid));
			t.childs.push(t.$.last=c);
			if(t.right){
				l.left=c;
				l.icon();
				l.line(1);
			}else{
				t.right=c;
				if(o!=t)t.icon();
			}
			c.make();
			return c;
		};
		t.del=function(){
			var i,l,p=t.parent,c=p?p.childs:0,$=t.$.dom;
			D($.item);D($.body);
			if(c){
				for(i=0,l=c.length;i<l;i++)if(c[i]==t){delete c[i];c.splice(i,1);break}
				if(!c.length){
					p.right=0;
					if(p!=o)p.icon();
				}
			}
		};
		t.loading=function(s){
			var $=t.$.dom;
			if($.load){$.load.del();$.load=0}
			return $.load=t.add({text:s?s:'loading...',noicon:1});
		};
		t.line=function(i){
			var c=t.right,$;
			if(c){
				do{
					$=c.$.dom.line;
					C($[$.length-i],cls[1][1]);
					c.line(i+1)
				}while(c=c.left)
			}
		};
		t.icon=function(){
			var $=t.$.dom,i=t.left?0:1,d=t.data,k=d.show?1:0,c=!d.noicon;
			if(t.right){
				if(c)C($.icon,cls[5][1+k]);
				C($.exp,cls[3+k][i]);
			}else{
				if(c)C($.icon,cls[5][0]);
				C($.exp,cls[2][i]);
			}
			$.body.style.display=k?'':'none';
		}
		t.click=function(){
			var c=t.data.show;
			if(c){
				if(t.onclose)if(false===t.onclose())return;
			}else{
				if(t.onopen)if(false===t.onopen())return;
			}
			if(t.onclick)if(false===t.onclick())return;
			t.data.show=!c;
			if(ck)CK(ck+t.$.cid,!c);
			t.icon();
			if(t.onend)t.onend();
		};
		t.make=function(){
			var s=[],d=t.data,p=t.parent,$=t.$.dom,o=p.$.dom,l;
			do{s.push(p)}while(p=p.parent);s.pop();
			r=E('tr');
			while(p=s.pop()){
				l=E('td',cls[1][p.left?1:0]);
				$.line.push(l);
				A(r,l)
			}
			p=t.parent;
			A(r,$.exp=E('td'));
			$.exp.onclick=t.click;
			if(!d.noicon)A(r,$.icon=E('td'));
			t.icon();
			$.text=E('td',cls[0][2]);$.text.innerHTML=d.html;
			A(r,$.text);
			A(b=E('tbody'),r);
			A($.item,b);
			listen($.item,'mouseover',MI);
			listen($.item,'mouseout',MO);
			A(o.body,$.item);
			A(o.body,$.body);
		};
	}
	$.icon=E('td',cls[0][1]);
	A(r,$.icon);
	if(a.html){
		$.text=E('td');$.text.innerHTML=a.html;
		A(r,$.text);
	}
	A(b,r);
	A($.item,b);
	A(a.dom,$.item);
	A(a.dom,$.body)
	return o;
}

function listTree(dom,name,data,val){
	function addNode(a,e){
		var i,l,s,p;
		for(i=0,l=a.length;i<l;i++){
			s=!a[i][2]?('<span class="cant">'+a[i][1]+'</span>'):a[i][0]==val?('<a class="selected">'+a[i][1]+'</a>'):('<a class="option">'+a[i][1]+'</a>');
			s=tmp[e].add({html:s});
			if(a[i].length==4){
				tmp.push(s);
				s.onend=autosize;
				arguments.callee(a[i][3],e+1);
				tmp.pop();
			}
			if(a[i][2]){
				p=s;
				s=s.$.dom.text.firstChild;
				s.onclick=selected;
				s.value=a[i][0];
				if(s.value==val){
					while(p=p.parent)if(p.data)p.click();
					sct=s;
					txt.innerHTML=s.innerHTML;
					init(s.cloneNode(true));
				}
			}
		}
	}
	function selected(){
		if(sct)sct.className='option';
		sct=this;
		sct.className='selected';
		ipt.value=sct.value;
		txt.innerHTML=sct.innerHTML;
		txt.style.width=sct.offsetWidth+12+'px';
		txt.style.backgroundPosition=sct.offsetWidth+'px -51px';
		win.style.display='none';
		document.listTree=0;
	}
	function max(p){
		var i,l,t,s=0;
		for(i=0,l=p.length;i<l;i++){
			if(p[i].data.show)t=arguments.callee(p[i].childs);else t=p[i].$.dom.item.offsetWidth;
			if(s<t)s=t;
		}
		return s;
	}
	function autosize(e){
		var p=this,w,h,i=0,c;
/*		if(!e&&p.data.show){
			while(p=p.parent)i++;
			i--;p=this;
			do{if(opt[i]==p)break}while(p=p.parent);
			if(!p)while(opt.length>i){p=opt.pop();p.click()}
			opt[i]=this
		}
*/		if(this.$&&this.$.dom){
			c=this.$.dom.item;
			i=c.className;
			c.className='';
		}
		if(ifr)lst.style.width='9999px';else win.style.width='9999px';
		tree.style.width='';
		w=max(tmp[0].childs);
		p=tree.offsetHeight>350;
		h=p?350:(tree.offsetHeight);
		if(ifr){
			ifr.style.width=w+(p?23:7)+'px';
			ifr.style.height=h+'px';
			lst.style.width=w+(p?21:5)+'px';
			lst.style.height=h+'px';
			win.style.width=w+(p?23:7)+'px';
		}else{
			win.style.width=w+(p?21:5)+'px';
		}
		tree.style.width=w+5+'px';
		win.style.height=h+'px';
		if(c)c.className=i;
	}
	function init(p){
		function X(){
			dom.appendChild(p);
			var s=p.offsetWidth;
			dom.removeChild(p);
			txt.style.width=s+12+'px';
			txt.style.backgroundPosition=s+'px -51px';
		}
		listen(window,'load',X);
		X();
	}
	var sct,ifr,lst,tmp=[],opt=[],txt=document.createElement('div'),ipt=typeof name=='object'?name:document.createElement('input'),win=document.createElement('div'),tree=document.createElement('div'),doc=document.documentElement;
	tree.className='listTree';
	win.hasScroll=1;
	tmp.push(TreeList({dom:tree}));
	txt.className='text';
	txt.innerHTML=dom.innerHTML||'&nbsp;';
	dom.className='listTree';
	dom.innerHTML='';
	with(win.style){
		position='absolute';
		backgroundColor='#fff';
		zIndex=9999;
		display='none';
	}
	if(is_ie){
		ifr=document.createElement('<iframe></iframe>');
		with(ifr.style){
			position='absolute';
			left='0px';
			top='0px';
			zIndex=8;
			filter='progid:DXImageTransform.Microsoft.Alpha(opacity=0,finishOpacity=100,style=0)';
		}
		lst=document.createElement('div');
		with(lst.style){
			position='absolute';
			border='1px solid black';
			overflow='auto';
			left='0px';
			top='0px';
			zIndex=9;
		}
		win.appendChild(ifr);
		win.appendChild(lst);
		lst.appendChild(tree);
	}else{
		with(win.style){
			border='1px solid black';
			overflow='auto';
		}
		win.appendChild(tree);
	}
	if(typeof name!='object'){
		ipt.type='hidden';
		ipt.id=ipt.name=name;
		ipt.style.display='none';
	}else{
		name=ipt.id?ipt.id:ipt.name;
	}
	if(val)ipt.value=val;else val=ipt.value;
	addNode(data,0);
	document.getElementById('append_parent').appendChild(win);
	dom.appendChild(txt);
	dom.appendChild(ipt);
//	dom.id='menu'+name;
//	win.id='menu'+name+'_menu';
//	dom.appendChild(win);
	listen(dom,'click',function(){
/*		var p=dom;
		while(p=p.offsetParent){if(/^floatwin_/.test(p.id)){InFloat=p.id;break}}
		showMenu(dom.id);
*/		if(!win.style.display){win.style.display='none';return}
		var p=dom,w=p.offsetLeft,h=p.offsetTop+p.offsetHeight;
		while(p=p.offsetParent){
			w+=p.offsetLeft;
			h+=p.offsetTop;
		}
		p=dom;while(p=p.parentNode)
			if(/_content$/.test(p.id)){
				w-=p.scrollLeft;
				h-=p.scrollTop;
			}
		if(w-doc.scrollLeft+tree.offsetWidth>doc.clientWidth){
			w-=tree.offsetWidth;
			if(w<doc.scrollLeft)w=doc.scrollLeft;
		}
		if(h-doc.scrollTop+tree.offsetHeight>doc.clientHeight){
			h-=tree.offsetHeight;
			if(h<doc.scrollTop)h=doc.scrollTop;
		}
		with(win.style){
			left=w+'px';
			top=h+'px';
			display='';
		}
		autosize(1);
		setTimeout(function(){document.listTree=win},200);
	});
}

function formatList(arr){
	var i,l,ret=[],dict={};
	for(i=0,l=arr.length;i<l;i++){
		if(arr[i][0]){
			if(dict[arr[i][0]]){
				if(dict[arr[i][0]].length!=4)dict[arr[i][0]][3]=[];
				dict[arr[i][1]]=dict[arr[i][0]][3][dict[arr[i][0]][3].push([arr[i][1],arr[i][2],arr[i][3]])-1];
			}
		}else{
			dict[arr[i][1]]=ret[ret.push([arr[i][1],arr[i][2],arr[i][3]])-1];
		}
	}
	return ret;
}

function listen(dom,event,action){
	if(dom.attachEvent){
		var func=function(){return action.apply(dom)};
		try{dom.detachEvent('on'+event,func)}catch(e){};
		dom.attachEvent('on'+event,func);
	}else if(dom.addEventListener){
		try{dom.removeEventListener(event,action,true)}catch(e){};
		dom.addEventListener(event,action,false);
	}else{
		if(!dom.listens)dom.listens=[];
		var x,e=dom.listens[event];
		if(e){
			for(x in e)if(e[x]==action)return;
		}else{
			e=dom.listens[event]=[];
			if(dom['on'+event])e.push(dom['on'+event]);
			dom['on'+event]=function(m){
				for(var i=0,l=e.length;i<l;i++)e[i].call(dom,m);
			}
		}
		e.push(action);
	}
}

listen(document,'mousedown',function(e){e=e||event;var p=e.srcElement||e.target;do{if(p==this.listTree)return}while(p=p.parentNode);if(this.listTree)this.listTree.style.display='none';this.listTree=0});