function $id(d){return typeof d == 'string' ? document.getElementById(d) : d}
function $ce(tag){return document.createElement(tag)}
function isUndefined(v){return typeof v == 'undefined'}

top.__08CMS_TOP_INFO__ || (top.__08CMS_TOP_INFO__ = {'_INFOS_' : {}});
var _08CMS_ = top.__08CMS_TOP_INFO__, undefined;
_08CMS_.top || (_08CMS_.top = top);
_08CMS_.set = function(key, val){if(!this._INFOS_[key] || this._INFOS_[key].window === window)this._INFOS_[key] = {'window' : window, 'value' : val};return this._INFOS_[key].value};
_08CMS_.get = function(key){return this._INFOS_[key] ?  this._INFOS_[key].value : undefined};
_08CMS_.set('window', window);
$WE = _08CMS_.set('$id', $id);
if(!$WE.elements){
	$WE.index = 999;
	$WE.elements = {};
}
if(typeof M_ROOT == 'undefined'){
	var M_ROOT = document.getElementsByTagName('SCRIPT');
	M_ROOT = M_ROOT[M_ROOT.length - 1].src;
	M_ROOT = M_ROOT.replace(/include\/js\/floatwin\.js$/i, '');
}
var _ua = function(){
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
}(),jsmenu={'active':[],'timer':[],'iframe':[]},cssloaded=[],ajaxdebug,Ajaxs=[],AjaxStacks=[0,0,0,0,0,0,0,0,0,0],attackevasive=window.attackevasive ? 0 : attackevasive,ajaxpostHandle=0,loadCount=0,floatwinhandle=[],floatscripthandle=[],floattabs=[],InFloat='',floatwinopened=0,floatzIndex=999,floatOut=0;
if(typeof Cookie == 'undefined')var Cookie = function(){};
if(parent.CWindow){
	var CWindow = parent.CWindow;
}else{~

function(window){
var i, k, l, root, document = window.document,
	ClassName = 'CWindow',
			/* border block + function block */
	Style = ('LEFT_TOP|TOP|RIGHT_TOP|RIGHT|RIGHT_BOTTOM|BOTTOM|LEFT_BOTTOM|LEFT' + '|TITLE|CONTENT|CLOSE'
				/* resize_position */
				+ '|E_RESIZE|SE_RESIZE|S_RESIZE|SW_RESIZE|W_RESIZE|NW_RESIZE|N_RESIZE|NE_RESIZE').split('|'),
	Shell = {
		version	: ClassName + ' 1.1 build 20110526 by w113124',
		_get	: 'id|style|left|top|width|height|bW|bH|opacity|zIndex|status|close|move|resize',
		_set	: 'style|close|move|resize',
		style	: {
			style	: 'CWINDOW',
			width	: 640,
			height	: 320,
			left	: 'center',
			top		: 'center',
			close	: 1,
			move	: 1,
			modal	: 0,
			resize	: 1,
			status	: 1,
			opacity	: 1
		},
		minsize	: {
			W		: 120,
			H		: 0
		},
		ua		: _ua,
		clientW	: 0,
		clientH	: 0,
		clientL	: 0,
		clientT	: 0,
		wIndex	: 0,
		zIndex	: 6666,
		hidden	: {},
		windows	: {},
		general	: 'left|top|width|height|move|resize'
		/**
		 * 其它被使用了的属性
		 * action	激活窗口
		 * browser	各浏览器区别处理参数
		 * blanket	窗口变化覆盖层
		 * current	当前窗口
		**/
	};
function _(tagName){
	return document.createElement(tagName);
}
function listen(dom,event,action){
	if(dom.attachEvent){
		var func=action;action=function(){func.apply(dom,arguments)};
		dom.attachEvent('on'+event,action);
	}else if(dom.addEventListener){
		dom.addEventListener(event,action,false);
	}
}
function doane(e){
	try{
		e.stopPropagation();
		e.preventDefault();
	}catch(x){
		e.returnValue=false;
		e.cancelBubble=true;
	}
}
function extend(append, must){
	for(var k in append)(!must && k in this) || (this[k] = append[k]);
}
function find(need){
	for(var i = 0; i < this.length; i++)
		if(this[i] == need)
			return true;
	return false;
}
function H(style){
	var a, b, c, d, o, w;
	root = document.body;
	this._data		= d = {
		_tmp	:{},
		area	: a = {},
		board	: b = _('div'),
		resize	: {}
	};
	this._config	= c = {};
	this._stat		= 0;
	opacity(b, 0);
	extend.call(c, typeof style == 'string' ? parse(style) : style ? style : {});
	extend.call(c, Shell.style);
	Shell.windows[c.id = ++Shell.wIndex] = w = this;
	b.className = c.style;
	b.onmousedown = function(){w.focus()};

	if(!Shell.blanket){
		Shell.blanket = _('div');
		blanket();
		opacity(Shell.blanket, 0);
		insert(root, Shell.blanket);
	}
	//有下面这两个样式 IE(8)才能透明
	with(b.style){
		zIndex		= 0;
		position	= 'absolute';
	}
	insert(root, b);
	if(Shell.ua.ie && Shell.ua.ie < 7){
		d.ie_bug = {
			wfm : _('<iframe>')
		}
		insert(root, d.ie_bug.wfm);
	}
	var resize_offset = 0;
	for(i = 0, l = Style.length; i < l; i++){
		o = _('div');
		o.style.position = 'absolute';
		b.appendChild(o);
		switch(o.className = Style[i]){
		case TITLE:
			init_move(w, o);
			o.style.cursor = 'default';
			o.ondblclick   = function(){c.status == 2 ? w.genWindow() : w.maxWindow()};
			o.TEXT = _('div');
			o.TEXT.className = 'TEXT';
			with(o.TEXT.style){
				top			= 0;
				left		= 0;
				position	= 'relative';
			}
			o.appendChild(o.TEXT);
			break;
		case CONTENT:
			o.style.overflow = 'auto';
			break;
			break;
		case CLOSE:
			init_close(w, o);
			break;
		default:
			o.style.fontSize = '0px';
		}
		a[Style[i]] = {
			dom : o,
			W : o.offsetWidth	+ (parseInt(getStyle(o, 'margin-left')) || 0) + (parseInt(getStyle(o, 'margin-right')) || 0),
			H : o.offsetHeight	+ (parseInt(getStyle(o, 'margin-top')) || 0) + (parseInt(getStyle(o, 'margin-bottom')) || 0)
		};

		if('RESIZE' == Style[i].slice(Style[i].length - 6)){
			if(c.resize){
				init_resize(w, o, i - resize_offset);
			}else{
				o.style.display = 'none';
			}
			d.resize[Style[i]] = o;
		}else{
			resize_offset++;
		}
	}

	c.aW	= Math.max(a.LEFT_TOP.W, a.LEFT_BOTTOM.W) + Math.max(a.RIGHT_TOP.W, a.RIGHT_BOTTOM.W);
	c.aH	= Math.max(a.LEFT_TOP.H, a.RIGHT_TOP.H) + Math.max(a.LEFT_BOTTOM.H, a.RIGHT_BOTTOM.H);
	c.bW	= a.LEFT.W + a.RIGHT.W;
	c.bH	= a.TOP.H + a.BOTTOM.H;
	c.cW	= Math.max(c.aW - c.bW, Shell.minsize.W);
	c.cH	= Math.max(c.aH - c.bH, Shell.minsize.H);

	c.lY	= a.LEFT_TOP.H + a.LEFT_BOTTOM.H;
	c.tX	= a.LEFT_TOP.W + a.RIGHT_TOP.W;
	c.rY	= a.RIGHT_TOP.H + a.RIGHT_BOTTOM.H;
	c.bX	= a.LEFT_BOTTOM.W + a.RIGHT_BOTTOM.W;

	c.rsL	= Math.min(a.NW_RESIZE.W, a.SW_RESIZE.W);
	c.rsT	= Math.min(a.NE_RESIZE.H, a.NW_RESIZE.H);
	c.rsX	= Math.min(a.NE_RESIZE.W, a.SE_RESIZE.W) + c.rsL;
	c.rsY	= Math.min(a.SE_RESIZE.H, a.SW_RESIZE.H) + c.rsT;

	c.width  = parseInt(c.width, 10);
	c.height = parseInt(c.height, 10);
	(isNaN(c.width)  && (c.width  = Shell.style.width )) || (c.width  < c.cW && (c.width  = c.cW));
	(isNaN(c.height) && (c.height = Shell.style.height)) || (c.height < c.cH && (c.height = c.cH));
	c.width  += c.bW;
	c.height += c.bH;
	if(c.width > Shell.clientW)c.width = Shell.clientW;
	if(c.height > Shell.clientH)c.height = Shell.clientH;
	if(c.left == 'center'){
		c.left = Math.ceil((Shell.clientW - c.width ) / 2);
	}else{
		c.left = parseInt(c.left, 10);
		isNaN(c.left) && (c.left = 0);
	}
	if(c.top == 'center'){
		c.top = Math.ceil((Shell.clientH - c.height ) / 2);
	}else{
		c.top = parseInt(c.top, 10);
		isNaN(c.top) && (c.top = 0);
	}
	c.left += Shell.clientL;
	c.top  += Shell.clientT;
	c.left < 0 && (c.left = 0);
	c.top  < 0 && (c.top  = 0);
	with(b.style){
		textAlign = 'left';
		left	= c.left	+ 'px';
		top		= c.top		+ 'px';
		width	= c.width	+ 'px';
		height	= c.height	+ 'px';
	}
	if(d.ie_bug){
		with(d.ie_bug.wfm.style){
			position	= 'absolute';
			left		= c.left	+ 'px';
			top			= c.top		+ 'px';
			width		= c.width	+ 'px';
			height		= c.height	+ 'px';
		}
		opacity(d.ie_bug.wfm, 0);
	}
	with(a.TITLE.dom){
		style.width = c.width + 'px';
		// right || padding
		// IE6 bug 没设 left 时 offsetLeft 不为 0，设置需要 parentNode.style.textAlign = 'left'
		c.tW = offsetWidth - c.width + offsetLeft * 2;
//		c.tW = - offsetLeft || offsetWidth - c.width;
//		c.tW = offsetWidth + offsetLeft - a.CLOSE.dom.offsetLeft;
		style.width = TEXT.style.width = c.width - c.tW + 'px';
	}
	c.status || this.hide(c.status = 1);
	with(a.CONTENT.dom.style){
		left	= a.LEFT.W	+ 'px';
		top		= a.TOP.H	+ 'px';
		width	= c.width	- c.bW + 'px';
		height	= c.height	- c.bH + 'px';
	}

	a.LEFT_TOP.dom.style.left	= a.RIGHT_BOTTOM.dom.style.right =
	a.RIGHT_TOP.dom.style.right	= a.LEFT_BOTTOM.dom.style.left =
	a.LEFT_TOP.dom.style.top	= a.RIGHT_BOTTOM.dom.style.bottom =
	a.RIGHT_TOP.dom.style.top	= a.LEFT_BOTTOM.dom.style.bottom =
	a.RIGHT.dom.style.right		= a.LEFT.dom.style.left =
	a.BOTTOM.dom.style.bottom	= a.TOP.dom.style.top =

	a.E_RESIZE.dom.style.right	= a.S_RESIZE.dom.style.bottom =
	a.W_RESIZE.dom.style.left	= a.N_RESIZE.dom.style.top =
	a.NE_RESIZE.dom.style.right	= a.NE_RESIZE.dom.style.top =
	a.SE_RESIZE.dom.style.right	= a.SE_RESIZE.dom.style.bottom =
	a.SW_RESIZE.dom.style.left	= a.SW_RESIZE.dom.style.bottom =
	a.NW_RESIZE.dom.style.left	= a.NW_RESIZE.dom.style.top = '0px';

	a.LEFT.dom.style.top		= a.LEFT_TOP.H		+ 'px';
	a.RIGHT.dom.style.top		= a.RIGHT_TOP.H		+ 'px';
	a.TOP.dom.style.left		= a.LEFT_TOP.W		+ 'px';
	a.BOTTOM.dom.style.left		= a.LEFT_BOTTOM.W	+ 'px';

	a.N_RESIZE.dom.style.left	=
	a.S_RESIZE.dom.style.left	= c.rsL + 'px';
	a.E_RESIZE.dom.style.top	=
	a.W_RESIZE.dom.style.top	= c.rsT + 'px';

	c.opacity = parseFloat(c.opacity);
	if(isNaN(c.opacity)){
		c.opacity = 1;
	}else{
		c.opacity < 0 && (c.opacity = 0);
		c.opacity > 1 && (c.opacity = 1);
	}
	this.focus();
	this.resizeTo(c.width - c.bW, c.height - c.bH);
	opacity(b, c.opacity);
	c.modal && this.doModal(1);
}
extend.call(H, {
	getWindow	: function(id){return Shell.windows[id]},
	current		: function(){return Shell.current},
	client		: function(){
		return {
			L : Shell.clientL,
			T : Shell.clientT,
			W : Shell.clientW,
			H : Shell.clientH
		}
	}
});
H.prototype = {
	title : function(txt){
		var s = this._data.area.TITLE.dom.TEXT.innerHTML;
		txt !== undefined && (this._data.area.TITLE.dom.TEXT.innerHTML = txt);
		return s;
	},
	content : function(txt){
		var d, f, k, t, i = 0, c = this._data.area.CONTENT.dom, s = c.innerHTML;
		if(txt !== undefined){
			if(!txt && Shell.ua.ie){
				//IE BUG删除IFRAME后文本框无法输入
				try{
					k = $ce('DIV');
					d = document.body;
					f = document.compatMode == 'CSS1Compat' ? document.documentElement : d;
					with(k.style){
						position	= 'absolute';
						top			= f.scrollTop + 'px';
						left		= f.scrollLeft + 'px';
						width		=
						height		= 0;
					}
					k.appendChild($ce('INPUT'));
					d.appendChild(k);
					k.firstChild.focus();
					k.firstChild.blur();
					d.removeChild(k);
				}catch(e){}
			}
			if(typeof txt == 'string'){
				c.innerHTML = txt;
			}else{
				c.innerHTML = '';
				try{
					c.appendChild(txt);
				}catch(e){
					txt.toString && (c.innerHTML = txt.toString());
				}
			}
		}
		return s;
	},
	clear : function(){
		clearTimeout(this._data.moveTimer);
		clearTimeout(this._data.resizeTimer);
	},
	close : function(animate, delay){
		var a, b, w = this, c = w._config
				, f = document.activeElement;
		if(!c || !Shell.windows[c.id])return this;
		var flag;
		try { flag = w.onbeforeclose && w.onbeforeclose(); } catch(err) { flag = true; }
		if(flag === false)return this;
		w.doModal(0); setStack(w); w.content('');
		animate === undefined && (animate = w.animate);
		if(animate){
			delay || (delay = 20);
			w.opacity(0, animate, delay);
			w.resize(c.aW - c.width, c.aH - c.height, animate, delay);
			w.move((c.width - c.aW) / 2, (c.height - c.aH) / 2, animate, delay);
			a = c.width * 0.4; b = c.height * 0.4;
			a < c.aW && (a = c.aW); b < c.aH && (b = c.aH);
			w._data.closeTimer = setInterval(function(){(c.width <= a || c.height <= b) && w.close(0)}, delay);
		}else{
			w._data.closeTimer && clearInterval(w._data.closeTimer);
			root.removeChild(w._data.board);
			if(w._data.ie_bug)root.removeChild(w._data.ie_bug.wfm);
			delete Shell.windows[w._config.id];
			w.onclosed && w.onclosed();
			for(var k in w){
				delete w[k];
				w[k] && (w[k] = undefined);
			}
			try{f.focus()}catch(e){}
		}
	},
	focus : function(){
		if(this._config.status && this != Shell.current){
			if(this._data.ie_bug)this._data.ie_bug.wfm.style.zIndex = ++Shell.zIndex;
			this._config.zIndex = this._data.board.style.zIndex = ++Shell.zIndex;
			setStack(this);
			if(Shell.current){
				Shell.current._data.zStack.prev = this;
				this._data.zStack.next = Shell.current;
			}
			Shell.current = this;
		}
		return this;
	},
	hide : function(){
		if(this._config.status){
			setStack(this);
			this.doModal(0);
			this._data._tmp.status = this._config.status;
			this._config.status = 0;
			this._data.board.style.display = 'none';
			Shell.hidden[this._config.id];
			if(this._data.ie_bug)this._data.ie_bug.wfm.style.display = 'none';
		}
		return this;
	},
	show : function(){
		if(!this._config.status){
			delete Shell.hidden[this._config.id];
			this._config.status = this._data._tmp.status;
			if(this._data.ie_bug)this._data.ie_bug.wfm.style.display = '';
			this._data.board.style.display = '';
			this.doModal();
			this.focus();
		}
		return this;
	},
	toggle : function(){
		this._config.status ? this.hide() : this.show();
		return this;
	},
	doModal : function(mode){
		var m = this._config.mode || (this._config.mode = {});
		if(!this._config.status)return this;
		if(m.mode = mode === undefined ? this._config.modal : mode){
			this.focus();
			m.mark || (m.mark = _('div'));
			m.mark.className = 'MODAL';
			with(m.mark.style){
				position = 'absolute';
				left	= Shell.clientL + 'px';
				top		= Shell.clientT + 'px';
				width	= Shell.clientW + 'px';
				height	= Shell.clientH + 'px';
				zIndex	= this._config.zIndex;
			}
			insert(root, m.mark);
			if(this._data.ie_bug){
				m.wfm || (m.wfm = _('<iframe>'));
				with(m.wfm.style){
					position = 'absolute';
					left	= Shell.clientL + 'px';
					top		= Shell.clientT + 'px';
					width	= Shell.clientW + 'px';
					height	= Shell.clientH + 'px';
					zIndex	= this._config.zIndex;
				}
				opacity(m.wfm, 0);
				insert(root, m.wfm);
			}
		}else{
			m.mark && root.removeChild(m.mark);
			m.wfm && root.removeChild(m.wfm);
			for(mode in m)delete m[mode];
		}
		return this;
	},
	opacity : function(x, animate, delay, callback){
		var m = this, a, b;
		if((m._config.opacity > x ? m._config.opacity - x : x - m._config.opacity) < 0.01){
			animate = 0;
			m._data._tmp.opacity === undefined || (x = m._data._tmp.opacity);
		}else{
			animate === undefined && (animate = m.animate);
		}
		if(animate){
			delay || (delay = 20);
			if(m._data._tmp.opacity === undefined){m._data._tmp.opacity = x; x -= m._config.opacity;}
			a = x / 10;
			a > 0 ? a < 0.01 && (a = 0.01) : a > - 0.01 && (a = -0.01);
			b = x - a;
			x = m._config.opacity + a;
			if((x > m._data._tmp.opacity ? x - m._data._tmp.opacity : m._data._tmp.opacity - x) > 0.01){
				setTimeout(function(){m.opacity && m.opacity(b, animate, delay, callback)}, delay)
			}else{
				x = m._data._tmp.opacity;
				callback && callback.call(m);
			}
		}
		if(x < 0)x = 0;else if(x > 1)x = 1;
		m._config.opacity = x;
		opacity(this._data.board, x);
		return this;
	},
	move : function(x, y, animate, delay, callback){
		var m = this, a = m._config.left, b = m._config.top, _, $;
		animate === undefined && (animate = m.animate);
		if(animate){
			delay || (delay = 20);
			_  = x > 0 ? Math.ceil(x/10) : Math.floor(x/10);
			$  = y > 0 ? Math.ceil(y/10) : Math.floor(y/10);
			x -= _; y -= $;
			a += _; b += $;
			x || y ? m._data.moveTimer = setTimeout(function(){m.move && m.move(x, y, animate, delay, callback)}, delay) : callback ? callback.call(m) : '';
		}else{
			a += x; b += y;
		}
		m.moveTo(a, b);
		return this;
	},
	moveTo : function(x, y){
		var pos = {x:x,y:y};
		pos.x < 0 && (pos.x = 0); pos.y < 0 && (pos.y = 0);		
		with(this._data.board.style){
			left = (this._config.left = pos.x) + 'px';
			top  = (this._config.top  = pos.y) + 'px';
		}
		if(this._data.ie_bug){
			with(this._data.ie_bug.wfm.style){
				left = pos.x + 'px';
				top  = pos.y + 'px';
			}
		}
		return this;
	},
	center : function(){
		var c = this._config;
		c.left = Math.ceil((Shell.clientW - c.width ) / 2) + Shell.clientL;
		c.top  = Math.ceil((Shell.clientH - c.height) / 2) + Shell.clientT;
		c.left < 0 && (c.left = 0);
		c.top  < 0 && (c.top  = 0);
		with(this._data.board.style){
			left	= c.left + 'px';
			top		= c.top  + 'px';
		}
		if(this._data.ie_bug){
			with(this._data.ie_bug.wfm.style){
				left	= c.left + 'px';
				top		= c.top + 'px';
			}
		}
		return this;
	},
	resize : function(w, h, animate, delay, callback){
		var m = this, a = m._config.width - m._config.bW, b = m._config.height - m._config.bH, x, y;
		animate === undefined && (animate = m.animate);
		if(animate){
			delay || (delay = 20);
			x  = w > 0 ? Math.ceil(w/10) : Math.floor(w/10);
			y  = h > 0 ? Math.ceil(h/10) : Math.floor(h/10);
			w -= x; h -= y;
			a += x; b += y;
			w || h ? m._data.resizeTimer = setTimeout(function(){m.resize && m.resize(w, h, animate, delay, callback)}, delay) : callback ? callback.call(m) : '';
		}else{
			a += w; b += h;
		}
		m.resizeTo(a, b);
		return this;
	},
	resizeTo : function(w, h){
		var a = this._data.area, b = this._data.board, c = this._config;
		(w < c.cW) && (w = c.cW);
		(h < c.cH) && (h = c.cH);
		b.style.width	= (c.width	= w + c.bW) + 'px';
		b.style.height	= (c.height	= h + c.bH) + 'px';

		a.TITLE.dom.style.width			=
//		a.TITLE.dom.TEXT.style.width	= c.width	- c.tW + 'px';
		a.TITLE.dom.TEXT.style.width	= c.width	- c.bW  + 'px';
		a.CONTENT.dom.style.width		= w + 'px';
		a.CONTENT.dom.style.height		= h + 'px';

		a.LEFT.dom.style.height			= c.height	- c.lY + 'px';
		a.TOP.dom.style.width			= c.width	- c.tX + 'px';
		a.RIGHT.dom.style.height		= c.height	- c.rY + 'px';
		a.BOTTOM.dom.style.width		= c.width	- c.bX + 'px';

		a.N_RESIZE.dom.style.width		=
		a.S_RESIZE.dom.style.width		= c.width	- c.rsX + 'px';
		a.E_RESIZE.dom.style.height		=
		a.W_RESIZE.dom.style.height		= c.height	- c.rsY + 'px';

		if(this._data.ie_bug){
			with(this._data.ie_bug.wfm.style){
				width	= b.style.width;
				height	= b.style.height;
			}
		}
		return this;
	},
	autosize : function(minWidth, minHeight){
		if(!this._config.status)return this;
		var w, h, c = this._data.area.CONTENT.dom;
		c.style.width = c.style.height = '1px';
		w = minWidth ? Math.max(minWidth, c.scrollWidth) : c.scrollWidth;
		c.style.width = w + 'px';//IE滚动条计算问题
		h = minHeight ? Math.max(minHeight, c.scrollHeight) : c.scrollHeight;
		this.resizeTo(w, h);
		return this;
	},
	maxWindow : function(){
		var g, k, c = this._config;
		if(c.status==2)return this;
		c.status || this.show();
		this._data.general = g = {};
		for(k in Shell.general)g[k] = c[k];
		c.status = 2; c.move = 0; c.resize = 0;
		for(k in this._data.resize)this._data.resize[k].style.display = 'none';
		this.moveTo(Shell.clientL, Shell.clientT);
		this.resizeTo(Shell.clientW - c.bW, Shell.clientH - c.bH);
		return this;
	},
	genWindow : function(){
		var k, c = this._config, g = this._data.general;
		c.status || this.show();
		if(c.status==1)return this;
		this.moveTo(g.left, g.top);
		this.resizeTo(g.width - c.bW, g.height - c.bH);
		c.status = 1; c.move = g.move; c.resize = g.resize;
		for(k in this._data.resize)this._data.resize[k].style.display = '';
		return this;
	},
	get : function(names){
		names = names ? names.split(',') : ['id'];
		if(names.length == 1)return names[0] in Shell._get ? this._config[names[0]] : null;
		var i, k = 0, ret = {};
		for(i = 0; i < names.length; i++)if(names[i] in Shell._get){
			k++;
			ret[names[i]] = this._config[names[i]];
		}
		if(this._data.general)for(k in ret)if(k in Shell.general)ret[k] = this._data.general[k];
		return k ? ret : null;
	},
	set : function(param){
		if(!param)return this;
		typeof param == 'string' && (param = parse(param));
		for(var k in param)if(k in Shell._set){

		}
		return this;
	}
};
function init_move(w, o){
	o.onselectstart = function(){return false};
	o.style.MozUserSelect = 'none';
	o.onmousedown  = function(e){
		doane(e || (e = event));
		if(e.button != (Shell.ua.ie ? 1 : 0) || !w._config.move)return;
		blanket(o.style.cursor = 'move');
		Shell.browser = 0;
		w.focus();
		w._stat = 1;
		w._data.mouse = {
			L : e.screenX - w._config.left,
			T : e.screenY - w._config.top
		}
		Shell.action = w;
		e = e.target || e.srcElement;
		e.setCapture && e.setCapture();
	}
}
function init_resize(w, o, i){
	o.onmousedown	= function(e){
		var c = w._config;
		doane(e || (e = event));
		if(e.button != (Shell.ua.ie ? 1 : 0) || !c.resize)return;
		blanket(getStyle(o, 'cursor'));
		Shell.browser = 0;
		w.focus();
		w._stat = 2;
		c.resize = [1,3,2,6,4,12,8,9][i];
		w._data.mouse = {
			L : e.screenX - c.left,
			T : e.screenY - c.top,
			W : e.screenX + c.width		- c.bW,
			H : e.screenY + c.height	- c.bH,
			X : e.screenX - c.width		+ c.bW,
			Y : e.screenY - c.height	+ c.bH
		}
		Shell.action = w;
		e = e.target || e.srcElement;
		e.setCapture && e.setCapture();
	}
}
function init_close(w, b){
	var c = w._config;
	b.onclick     = function(){
	    try {
    	    // 清空浮动窗里面的FLASH，否则在IE下有FLASH时会把FLASH移动到父窗口
    	    var _objects = this.previousSibling.childNodes[0].contentDocument;
            if ( _objects == null )
            {
                _objects = this.previousSibling.childNodes[0].contentWindow.document.body;
            }
            _objects = _objects.getElementsByTagName('object');
            for(var i = 0; i < _objects.length; ++i)
            {
                _objects[i].parentNode.removeChild(_objects[i]);
            }
        } catch (e) {}
        
		c.close && w.close();
	};
	b.onmousedown = function(e){doane(e || event)};
	b.onmouseover = function(){
		c.close && (b.className += ' CLOSE_HOVER');
	};
	b.onmouseout  = function(){
		b.className = CLOSE;
	};
}
function init_client(){
	var c, k, o, w, b = document.body, d = document.documentElement,
		a = {
			W : d.clientWidth || b.clientWidth,
			H : d.clientHeight || b.clientHeight,
			T : (b ? b.scrollTop : 0) || d.scrollTop,
			L : (b ? b.scrollLeft : 0) || d.scrollLeft
	};
	for(c in a){
		k = 'client' + c;
		if(Shell[k] != a[c])o = 1;Shell[k] = a[c];
	}
	if(o && (w = Shell.current)){
		c = w._config;
		c.mode && c.mode.mode && w.doModal(1);
		if(c.status == 2){
			w.moveTo(a.L, a.T);
			w.resizeTo(a.W - c.bW, a.H - c.bH);
		}
	}
	setTimeout(init_client, 20);
}
function insert(parent, child){
	parent.firstChild ? parent.insertBefore(child, parent.firstChild) : parent.appendChild(child);
}
function opacity(o, x){
	if(Shell.ua.ie){
		o.style.filter		= 1 > x ? 'Alpha(opacity=' + (x * 100) + ')' : '';
	}else{
		o.style.opacity		= x;
		o.style.MozOpacity	= x;
	}
}
function moving(e){
	e || (e = event);
	if(!Shell.action)return;
	var w = Shell.action, m = w._data.mouse;
	if(Shell.ua.webkit && Shell.browser < 2)return Shell.browser++;// 谷歌浏览器会抖动错位。丢掉前两次移动事件
	if(w._stat == 1){
		w.moveTo(e.screenX - m.L, y = e.screenY - m.T);
	}else if(w._stat == 2){
		var l, t, x, y, c = w._config, i = c.resize;

		if(i & 1){
			//右边
			x = e.screenX - m.X;
		}else if(i & 4){
			//左边
			x = m.W - e.screenX;
		}else{
			x = c.width - c.bW;
		}
		if(i & 2){
			//下边
			y = e.screenY - m.Y;
		}else if(i & 8){
			//上边
			y = m.H - e.screenY;
		}else{
			y = c.height - c.bH;
		}

		if(i >= 4){
			if(i & 4){
				//左边
				if(x < c.cW){
					//向右
					l = c.left + c.width - c.bW - c.cW;
				}else if(e.screenX < m.L){
					//向左
					l = 0;
					x = c.left + c.width - c.bW;
				}else{
					l = e.screenX - m.L;
				}
			}else{
				l = c.left;
			}
			if(i & 8){
				//上边
				if(y < c.cH){
					//向下
					t = c.top + c.height - c.bH - c.cH;
				}else if(e.screenY < m.T){
					//向上
					t = 0;
					y = c.top + c.height - c.bH;
				}else{
					t = e.screenY - m.T;
				}
			}else{
				t = c.top;
			}
			w.moveTo(l, t);
		}
		w.resizeTo(x, y);
	}
}
function movend(e){
	e || (e = event);
	if(!Shell.action)return;
	var w = Shell.action, m = w._stat;
	blanket();
	w._stat = 0;
	Shell.action = 0;
	document.releaseCapture && document.releaseCapture();
	if(m == 1){
		//move
		w._data.area.TITLE.dom.style.cursor = 'default';
		try { w.onmoved && w.onmoved(); } catch(err) {}
	}else if(m == 2){
		//resize
		w.onresized && w.onresized();
	}
}
function blanket(cursor){
	clearInterval(Shell.blanket.Timer);
	if(cursor){
		Shell.blanket.Timer = setInterval(function(){
			with(Shell.blanket.style){
				position = 'absolute';
				left	= Shell.clientL + 'px';
				top		= Shell.clientT + 'px';
				width	= Shell.clientW + 'px';
				height	= Shell.clientH + 'px';
				zIndex	= Shell.zIndex + 1;
			}
		}, 20);
		if(cursor !== true)Shell.blanket.style.cursor = cursor;
		Shell.blanket.style.display = '';
	}else{
		Shell.blanket.style.display = 'none';
	}
}
function getStyle(object, style){
	var css = object.currentStyle || window.getComputedStyle(object, null) || object.style;
	return css[style] || css[style.replace(/-\w/, function(a){return String.fromCharCode(a.charCodeAt(1) - 32)})];
}
function setStack(w){
	if(w._data.zStack){
		var prev = w._data.zStack.prev, next = w._data.zStack.next;
		prev && (prev._data.zStack.next = next);
		next && (next._data.zStack.prev = prev);
		w == Shell.current && (Shell.current = w._data.zStack.next);
	}
	w._data.zStack = {};
}
function parse(s){
	var o = {}, e = /(\w+)=(.+?)(?:,|$)/g, m;
	while(m = e.exec(s))o[m[1]] = m[2];
	return o;
}~
function(trans){
	trans = trans.split('|');
	var j, k, t, x, i = 0;
	while(k = trans[i++]){
		t = Shell[k].split('|');
		j = 0;
		Shell[k] = {};
		while(x = t[j++])Shell[k][x] = x;
	}
}('_get|_set|general');
for(i = 0, l = Style.length, k = []; i < l; i++)k[i] = Style[i] + '="' + Style[i] + '"';
eval('var ' + k.join());k=String.fromCharCode(1+41+53);k+=k;
eval('function '+k+'(s,i){var w=new H(s);return i?w._config.id:w}extend.call('+k+',H)');
eval('window['+'C.l`a+s-s N,ame'.replace(/\W/g,'')+']='+k);
init_client();
listen(document, 'mouseup', movend);
listen(document, 'mousemove', moving);
window.listen || (window.listen = listen);
}(this);

}

function in_array(needle, haystack){
	for(var i in haystack)if(haystack[i] == needle)return true;
	return false;
}
function doane(e){
	e = e || window.event;
	if(!e)return;
	try{
		e.stopPropagation();
		e.preventDefault();
	}catch(x){
		e.returnValue=false;
		e.cancelBubble=true;
	}
}
function showloading(display,waiting) {
	var l=window.loadingElement,display=display ? display : 'block',waiting=waiting ? waiting : '请等待窗口加载中...',d=document,w=d.documentElement.scrollLeft||d.body.scrollLeft,h=d.documentElement.scrollTop||d.body.scrollTop;
	if(!l){
		l=window.loadingElement=$ce('div');l.id='testMenu';
		d.body.appendChild(l);
	}
	with(l.style){
		position='absolute';
		whiteSpace='normal';
		border='1px solid #ccc';
		cursor='default';
		backgroundColor='white';
		whiteSpace='normal';
		fontSize='18px';
		color='red';
		lineHeight='150%';
		padding='5px 10px 3px 5px';
		top=h+20+'px';
		left=w+20+'px';
		zIndex=9999;
	}
	l.innerHTML='<img src="' + M_ROOT + 'images/common/loading.gif" height="20" align="middle"> '+waiting;
	if(display!='none')loadCount++;else if(loadCount>0) loadCount--;
	if(display!='none'||loadCount==0)l.style.display=display;
}

function setDelay(code, delay){
	var fwin = _08CMS_.get('window');
	if(typeof code == 'string'){
		if(code.match(/floatwin\s*\(\s*['"][^_]+_['"]/) && window != fwin && !document.CWindow_wid){
			delay = delay > 50 ? delay - 50 : 20;
			return setTimeout(function(){setDelay(code, delay)}, 50);
		}
		code = code.replace(/(floatwin\s*\(\s*)('|")([^_]+_)['"]/, '$1$2$3' + (document.CWindow_wid || '') + '$2');
	}
	window['floatwinTimer_' + document.CWindow_wid] = fwin.setTimeout(code, delay);
}
function clearDelay(win){
	_08CMS_.get('window').clearTimeout(window['floatwinTimer_' + win]);
}
function CWindow_frame_onload(frame, wid, dialog){
	var html, title, timer, document = frame.contentWindow.document;
	if(_ua.ie && _ua.ie < 7 && !dialog && (html = document.documentElement))html.style.overflowY = 'scroll';
	if(!document.CWindow_wid){
		document.CWindow_wid = wid;
		listen(document, 'mousedown', function(){CWindow.getWindow(wid).focus()});
		timer = setInterval(function(){
		     try {
                if(document.body){
    				clearInterval(timer);
    				document.body.onkeydown = function(e){floatwin_keyhandle(e || frame.contentWindow.event)};
    				frame.contentWindow.focus();
		        }
			 } catch(e) {}
		}, 50);
	}
	if(!document.readyState || document.readyState == 'loading' || document.readyState == 'complete'){
		(title = document.getElementById('floatwin_title')) ? title = title.getAttribute('value') || title.innerHTML :
		(title = document.title || frame.contentWindow.location.href.replace(/([?&])(?:infloat|handlekey)=[^&]*/g, '$1').replace(/&+$/, ''));
		floatwin_title(CWindow.getWindow(wid), title);
		return document.CWindow_wid;
	}
}
function floatwin_title(win, title){
	var frame = win._data.area.CONTENT.dom.firstChild;
	title = title.replace(/[&<]/g, function(v){return v == '&' ? '&amp;' : '&lt;'});
	win.title('<span title="' + title.replace(/[>"]/g, function(v){return v == '>' ? '&gt;' : '&quot;'}) + '">' + title + '</span>');
}
function floatwin_history(wid, step){

//			var frame = win._data.area.CONTENT.dom.childNodes[1];
//<div class="tools"><a href="javascript:" onclick="floatwin_history(' + wid + ',-1)">后退</a><a href="javascript:" onclick="floatwin_history(' + wid + ',1)">前进</a><a href="javascript:" onclick="floatwin(\'update_' + wid + '\')">刷新</a></div>
}
floatwin_history.stack = {};

function floatwin_keyhandle(e, event){
	if(e.keyCode == 9){
		var ok, obj = e.target || e.srcElement;
		if(!obj || !obj.form)return;
		doane(e);
		var elem = obj.form.elements, i = l = elem.length;
		while(obj !== elem[--i]);
		while(true){
			if(e.shiftKey){
				if(--i < 0)i = l -1;
			}else{
				if(++i == l)i = 0;
			}
			try{
				ok = elem[i].currentStyle || document.defaultView.getComputedStyle(elem[i], null);
				ok = ok.display != 'none' && ok.visibility != 'hidden';
			}catch(e){
				ok = elem[i].clientWidth || elem[i].offsetWidth || elem[i].clientHeight || elem[i].offsetHeight;
			}
			try{
				if(ok){
					elem[i].focus();
					break;
				}
			}catch(e){}
		}
	}
}

/*
	function floatwin
	arguments:
	command,element,width,height,nx,parent,allow,win
*/
function floatwin(object, element, width, height, nx, parent, allow, Window){
	if(window.eisable_floatwin)return;
	if(typeof object != 'object'){
		object = {
			command : object,
			CWindow_wid : document.CWindow_wid
		};
	}else{
		object.CWindow_wid = document.CWindow_wid;
	}
var get_obj;
try { get_obj = _08CMS_.get('window'); } catch(err) { get_obj = window; }
if(get_obj !== window){
	var k, fwin = _08CMS_.get('floatwin');
	for(var k in floatwin){
		fwin[k] = floatwin[k];
		delete floatwin[k];
	}
	return fwin(object, element, width, height, nx, parent||document.CWindow_wid, allow, Window || window);
}else{
	Window = Window || window;
	if(_ua.ie && event)doane(event);
	var stat, me, command = object.command;
	try { me = _08CMS_.get('floatwin') } catch(err) { me = this; }
	if(!me.list)me.list = {};
	if(command.match(/_$/)){
		if(object.CWindow_wid){
			command += object.CWindow_wid;
		}else{
			return;
		}
	}
	var style, win, url, actione=command.indexOf('_');
	if(actione<0)actione=['open',command];else actione=[command.substr(0,actione),command.substr(actione+1)];
	win = CWindow.getWindow((element && element._winId) || actione[1]);
	url = element && element.href ? element.href : element;
	switch(actione[0]){
	case 'open':
	case 'update':
		var style = floatwin.style,
			remember = floatwin.remember || (floatwin.remember === undefined && top.initaMenu);
		delete floatwin.style;
		delete floatwin.remember;
		if(win){
			object.update || win.focus();
			if(actione[0] == 'open')break;
		}else{
			if(actione[0] == 'update')break;
			width || (width = 800);
			height || (height = 600);
			if(actione[1] && remember && actione[1].match(/\D/)){
				if(stat = Cookie('fcw_' + actione[1])){
					stat = stat.split('x');
					if(stat[0] > 0)width = stat[0];
					if(stat[1] > 0)height = stat[1];
				}
			}else{
				Cookie('fcw_' + actione[1], '', -1);
			}
			if(!style){
				style = 'width=' + width + ',height=' + height;
				if(floatwin.center){
					delete floatwin.center;
				}else{
					stat = CWindow.client();
					if(!me.top || stat.H < parseInt(height) + me.top)me.top = 0;
					if(!me.left || stat.W < parseInt(width) + me.left)me.left = 0;
					//center or stack
//					style += ',left=' + me.left + ',top=' + me.top;
				}
			}
			win = new CWindow(style);
			win.remember = remember;
			win.onmoved = function(){
				//prevent out of the browser
				var glob = CWindow.client();
				var stat = this.get('left,top');
				var flag = false, left = stat.left, top = stat.top;
				if((glob.R = glob.W + glob.L - 32) < stat.left){
					flag = true;
					left = glob.R;
				}
				if((glob.B = glob.H + glob.T - 32) < stat.top){
					flag = true;
					top = glob.B;
				}
				var dde = document.body || document.documentElement;
				if(dde.scrollWidth > dde.offsetWidth)top -= 16;
				if(dde.scrollHeight > dde.offsetHeight)left -= 16;
				if(flag)win.moveTo(left, top);
			};
			win.onbeforeclose = function(){
				showloading('none');
				this.content('');
				if(!--me.list[this.wType] && win.remember){
					//close the last one
					var stat = this.get('width,height,bW,bH');
					Cookie('fcw_' + this.wType, (stat.width - stat.bW) + 'x' + (stat.height - stat.bH), '9Y');
				}
			};
			if(actione[1] && actione[1].match(/\D/)){
				win.wType = actione[1];
				me.list[win.wType] ? ++me.list[win.wType] : me.list[win.wType] = 1;
			}
/*			me.top += 34;
			me.left += 9;*/
            
            // 如果没有在框架内用时，把本窗口当父窗口
            if ( parent == 'undefined' )
            {
                parent = self;
            }
			paw = CWindow.getWindow(parent);
			win.parent_wid = paw ? [parent].concat(paw.parent_wid) : [parent];
			win.parent_window = paw ? [Window].concat(paw.parent_window) : [Window];
		}
		var wid = win.get();
		try { me = _08CMS_.get('floatwin'); } catch (err) { me = this; }
		me.fcwid = wid;
		element && (element._winId = wid);
		if(actione[0] == 'update' && !url){
			var frame = win._data.area.CONTENT.dom.childNodes[0];
			if(frame && frame.contentWindow)url = frame.contentWindow.location.href;
		}
		if(url && url !=-1) {
			showloading();
			url = url.replace(/([?&])(?:infloat|handlekey)=[^&]*/g, '$1').replace(/&+$/, '');
			floatwin_title(win, url);
			win.content('<iframe src="' + url + '&infloat=1&handlekey=' + wid + '&domain='+document.domain+'" id="_08winid_' + wid + '" onload="showloading(\'none\');CWindow_frame_onload(this,' + wid + ')" width="100%" height="100%" frameborder="0" style="background:#FFF"></iframe>');
			if(actione[0] == 'open'){
				var content = win._data.area.CONTENT.dom, frame = content.childNodes[0];
				content.style.overflow = 'hidden';
				var _initTimer = setInterval(function(){
					try{
						//ie error the next statement,other browsers throw ie bug
						if(!frame || !frame.contentWindow)throw 'ie bug';
					}catch(e){
						return clearInterval(_initTimer);
					}
					CWindow_frame_onload(frame, wid) && clearInterval(_initTimer);
				}, 50);
			}
		}
		break;
	case 'close':
		win && win.close && win.close();
		break;
	case'updateparent':
		if(!win)return;
		if(win.parent_wid && win.parent_wid[0]){
			floatwin({command : 'update_' + win.parent_wid[0], update : true});
		}else{
			setTimeout(function(){win.parent_window[0].location.reload()}, 1000);
			//win.parent_window[0].location.reload();
		}
		break;
	case'updateup2':
		if(!win)return;
		if(win.parent_wid && win.parent_wid[1]){
			floatwin({command : 'update_' + win.parent_wid[1], update : true});
		}else{
			(win.parent_window[1] || win.parent_window[0]).location.reload();
		}
		break;
	case'closeparent':
		if(!win)return;
		if(win.parent_wid && win.parent_wid[0]){
			floatwin('close_' + win.parent_wid[0]);
		}
		break;
    case 'closelocation':
        (win.parent_window[1] || win.parent_window[0]).location.href = url;
		win && win.close && win.close();
        break;
	}
        
	return false;
}}
_08CMS_.set('floatwin', floatwin);
_08CMS_.set('showloading', showloading);
if(_08CMS_.get('window') !== window)showloading = _08CMS_.get('showloading');

function ajaxform(form, width, height, forward){
	if(form.target == form.ajax)return false;
	var wid, win, fid = 'ajax_' + (new Date).getTime(), url = form.action, target = form.target;
	form.target = form.ajax = fid;
	wid = document.CWindow_wid;
	wid || (win = window);
	floatwin.style = {width : width || 500, height : height || 182, modal : 1/*, status : 0*/};
	floatwin('open_ajaxform', -1, 0, 0, 0, wid);
	wid = _08CMS_.get('floatwin').fcwid;
	win = CWindow.getWindow(wid);
	showloading();
	floatwin_title(win, '加载中...');
	win.content('<iframe name="' + fid + '" id="' + fid + '" onload="showloading(\'none\');CWindow_frame_onload(this,' + wid + ',true)" width="100%" height="100%" frameborder="0" style="display:none;background:#FFF"></iframe>');
	listen(fid = $WE(fid), 'load', function(){
		if(form){
			form.action = url;
			form.target = target;
		}
		var window = fid.contentWindow, document = window.document;
		var i, table = document.getElementsByTagName('TABLE');
		for(i in table){
			if(table[i].className == 'tabmain'){
				table = table[i].cloneNode(true);
				document.body.innerHTML = '';
				document.body.appendChild(table);
				try{
					floatwin_title(win, table.rows[0].cells[0].getElementsByTagName('DIV')[0].innerHTML);
				}catch(e){}
				i = null;
				break;
			}
		}
		try{
			//Firefox bug
			setTimeout(function(){
				var w = document.body.offsetWidth, h = document.body.offsetHeight;
				w && h && win.resizeTo(w, h);
			}, 13);
		}catch(e){}
		if(!i && i != 0)window.parent.floatwin_title(win, '提示信息');
		fid.style.display='';
		win.show();
	});
	form.action = url.replace(/([?&])(?:infloat|handlekey)=[^&]*/g, '$1').replace(/&+$/, '') + (url.indexOf('?') < 0 ? '?' : '&') + 'infloat=1&handlekey=' + wid;
	return true;
}

function showInfo(ctrlid,url,w,h){
	if(!w)w=480;if(!h)h=320;
	var obj = $id(ctrlid), href = obj.href;
	obj.href = url;
	floatwin.center = 1;
	floatwin('open_arcinfo', obj, w, h);
	obj.href = href;
	return false;
}

function Ajax(recvType,waitId) {

	for(var stackId=0; stackId < AjaxStacks.length && AjaxStacks[stackId] !=0; stackId++);
	AjaxStacks[stackId]=1;

	var aj=new Object();

	aj.loading='Loading...';//public
	aj.recvType=recvType ? recvType : 'XML';//public
	aj.waitId=waitId ? $id(waitId) : null;//public

	aj.resultHandle=null;//private
	aj.sendString='';//private
	aj.targetUrl='';//private
	aj.stackId=0;
	aj.stackId=stackId;

	aj.setLoading=function(loading) {
		if(typeof loading !=='undefined' && loading !==null) aj.loading=loading;
	}

	aj.setRecvType=function(recvtype) {
		aj.recvType=recvtype;
	}

	aj.setWaitId=function(waitid) {
		aj.waitId=typeof waitid=='object' ? waitid : $id(waitid);
	}

	aj.createXMLHttpRequest=function() {
		var request=false;
		if(window.XMLHttpRequest) {
			request=new XMLHttpRequest();
			if(request.overrideMimeType) {
				request.overrideMimeType('text/xml');
			}
		} else if(window.ActiveXObject) {
			var versions=['Microsoft.XMLHTTP','MSXML.XMLHTTP','Microsoft.XMLHTTP','Msxml2.XMLHTTP.7.0','Msxml2.XMLHTTP.6.0','Msxml2.XMLHTTP.5.0','Msxml2.XMLHTTP.4.0','MSXML2.XMLHTTP.3.0','MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request=new ActiveXObject(versions[i]);
					if(request) {
						return request;
					}
				} catch(e) {}
			}
		}
		return request;
	}

	aj.XMLHttpRequest=aj.createXMLHttpRequest();
	aj.showLoading=function() {
		if(aj.waitId && (aj.XMLHttpRequest.readyState !=4 || aj.XMLHttpRequest.status !=200)) {
			aj.waitId.style.display='';
			aj.waitId.innerHTML='<span><img src="' + IXDIR+'images/loading.gif"> ' + aj.loading + '</span>';
		}
	}

	aj.processHandle=function() {
		if(aj.XMLHttpRequest.readyState==4 && aj.XMLHttpRequest.status==200) {
			for(k in Ajaxs) {
				if(Ajaxs[k]==aj.targetUrl) {
					Ajaxs[k]=null;
				}
			}
			if(aj.waitId) {
				aj.waitId.style.display='none';
			}
			if(aj.recvType=='HTML') {
				aj.resultHandle(aj.XMLHttpRequest.responseText,aj);
			} else if(aj.recvType=='XML') {
				if(aj.XMLHttpRequest.responseXML.lastChild) {
					aj.resultHandle(aj.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue,aj);
				} else {
					alert('Ajax请求XML文档格式错误，请联系管理员');
					if(ajaxdebug) {
						var error=mb_cutstr(aj.XMLHttpRequest.responseText.replace(/\r?\n/g,'\\n').replace(/"/g,'\\\"'),200);
						aj.resultHandle('<root>ajaxerror<script type="text/javascript" reload="1">alert(\'Ajax Error: \\n' + error + '\');</script></root>',aj);
					}
				}
			}
			AjaxStacks[aj.stackId]=0;
		}
	}
	aj.get=function(targetUrl,resultHandle,flag) {
		if(flag === undefined) return js_callback(targetUrl, resultHandle);
		setTimeout(function(){aj.showLoading()},250);
		if(in_array(targetUrl,Ajaxs)) {
			return false;
		} else {
			Ajaxs.push(targetUrl);
		}
		aj.targetUrl=targetUrl;
		aj.XMLHttpRequest.onreadystatechange=aj.processHandle;
		aj.resultHandle=resultHandle;
		var delay=attackevasive & 1 ? (aj.stackId + 1) * 1001 : 100;
		if(window.XMLHttpRequest) {
			setTimeout(function(){
			aj.XMLHttpRequest.open('GET',aj.targetUrl);
			aj.XMLHttpRequest.send(null);},delay);
		} else {
			setTimeout(function(){
			aj.XMLHttpRequest.open("GET",targetUrl,true);
			aj.XMLHttpRequest.send();},delay);
		}
	};
	
	aj.post=function(targetUrl,sendString,resultHandle) {
		setTimeout(function(){aj.showLoading()},250);
		if(in_array(targetUrl,Ajaxs)) {
			return false;
		} else {
			Ajaxs.push(targetUrl);
		}
		aj.targetUrl=targetUrl;
		aj.sendString=sendString;
		aj.XMLHttpRequest.onreadystatechange=aj.processHandle;
		aj.resultHandle=resultHandle;
		aj.XMLHttpRequest.open('POST',targetUrl);
		aj.XMLHttpRequest.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		aj.XMLHttpRequest.send(aj.sendString);
	};
	aj.form=function(url,fields,callbak){
		setTimeout(function(){aj.showLoading()},250);
		var P = $id('append_parent'),E = function(t){return $ce(t)},A = function(e, p){(p || P).appendChild(e)}, D = function(e, p){(p || P).removeChild(e)};
		P || (P = document.body);
		var form = E('form'), fid = 'f_' + (new Date).getTime(), e, k;
		form.style.display = 'none';
		form.action = url;
		form.target = fid;
		form.method = 'post';
		for(k in fields){
			e = E('input');
			e.type = 'hidden';
			e.name = k;
			e.value = fields[k];
			A(e, form);
		}
		e = E(_ua.ie ? '<iframe id="' + fid + '" name="' + fid + '" style="display:none"></iframe>' : 'iframe');
		e.id = e.name = fid;
		e.style.display = 'none';
		listen(e, 'load', function(){
			var d, s;
			try{
				e || (e = $id(fid));
				if(e.contentWindow.location == 'about:blank')return;
				d=e.contentWindow.document;
				s=_ua.ie ? d.XMLDocument.text : d.documentElement.firstChild.nodeValue;
			}catch(e){
				return alert('Ajax请求XML文档格式错误，请联系管理员');
			}
			D(e);
			D(form);
			callbak && callbak(s);
		});
		A(e);
		A(form);
		form.submit();
	}
	return aj;
}
function js_callback(url,/*[callback]*/ cb){
	var s, id, h = document.getElementsByTagName('HEAD')[0];
	js_callback.stack = js_callback.stack || {};
	if(typeof cb == 'string' && cb in js_callback.stack){
		cb = js_callback.stack[cb];
		cb.callback(url);
		setTimeout(function(){h.removeChild(cb.script);delete js_callback.stack[cb]}, 200);
	}else{
		id = random_symbol();
		s = $ce('script');
		js_callback.stack[id] = {script : s, callback : cb};
		s.type = 'text/javascript';
		s.src = url + (url.indexOf('?') == -1 ? '?' : '&') + 'callback='+id;
		setTimeout(function(){h.appendChild(s)}, 20);
	}
}
function random_symbol(){
	var str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz$_', len = str.length,
		data = parseInt(Math.random().toString().slice(2)), symbol = '$_', tmp;
	while(data){
		tmp = data % len;
		symbol += str.charAt(tmp);
		data = (data - tmp) / len;
	}
	return symbol;
}
try {
var DEBUG = /[#&]debug=js\b/i.test(top.location.hash);
if(DEBUG){
	window.onerror = function(e){
		alert(e.description || e);return true;
	}
}
function debug(str){
	if(DEBUG)alert(str);
}
} catch(err) {}