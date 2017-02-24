/**
* 产品对比功能包
* @package product compare
* @author wiki <2009-02-15>
* @since 1.3
*/

function $$(id){
	return document.getElementById(id) || false;
}

function writeCookie(name, value, hours){
	var expire = "";
	if(hours != null)  {
		expire = new Date((new Date()).getTime() + hours * 3600000);
		expire = "; expires=" + expire.toGMTString();
	}
	document.cookie = name + "=" + escape(value) + expire + "; path=/;";
}

function readCookie(name){
	var cookieValue = "";
	var s_search = name + "=";
	if(document.cookie.length > 0){
		offset = document.cookie.indexOf(s_search);

		if (offset != -1){
			offset += s_search.length;
			end = document.cookie.indexOf(";", offset);
			if (end == -1) end = document.cookie.length;
			cookieValue = unescape(document.cookie.substring(offset, end))
		}
	}
	return cookieValue;
}

var comp = {};

//初始化对象
comp.init = function (sub_id) {
	if(!sub_id) sub_id = 0;//全局搜索用
	this.create_box('comp_box');
	this.counter = this.counterContainer.innerHTML = 0;
	this.sub_id = sub_id;
	this.last_sub_id = 0;//标记最后一次插入的产品SUBID
	this.subid_obj.value = this.sub_id;
	this.cookie_name = 'comp_pro_str_'+this.sub_id;
	this.pro_arr = {};
	//this.hasOnBeforeUnload = ('onbeforeunload' in window);
	this.hasOnBeforeUnload = false;
	var pro_str = readCookie(this.cookie_name);
	if(!pro_str) {
		this.hidden();
		return;
	}
	items_arr = pro_str.split('@@@');
	pro_num = items_arr.length;
	if(pro_num) {
		for(var i =0; i<pro_num; i++) {
			one_item_arr = items_arr[i].split('|');
			pro_id = one_item_arr[0];
			this.add_item(one_item_arr);
		}
	} else {
		this.hidden();
	}
}

comp.create_box = function (comp_id) {
	if ($$(comp_id)) {
		this.comp_box = $$(comp_id);
		this.comp_form = this.comp_box.getElementsByTagName('form')[0];
		this.comp_top = $$('comp_top');
		this.subid_obj = $$('subid_obj');
		this.comp_info = this.comp_top.getElementsByTagName('span')[0];
		this.counterContainer = $$('comp_num');
		this.itemContainer = $$('comp_items');
		this.comp_boot = $$('comp_boot');
	} else {
		this.comp_box = document.createElement('div');
		this.comp_box.id = comp_id;

		this.comp_form = document.createElement('form');
		with(this.comp_form) {
			action = comp_action;
			method = 'get';
			target = "_blank";
			onsubmit = function () {if(comp.exec())makeUrl();return false};
		}

		this.comp_top = document.createElement('div');
		this.comp_top.id = 'comp_top';
        var otit=chid==4?'楼盘':'房源';
		var comp_box_name = document.createTextNode(otit+'对比');
		var comp_info = document.createElement('span');
		var left_mb = document.createTextNode('[');
		var right_mb = document.createTextNode('/5]');
		this.counterContainer = document.createElement('b');
		this.counterContainer.id = 'comp_num';
		this.closeBtn = document.createElement('a');
		with(this.closeBtn) {
			className = 'close ico08';
			href="javascript:comp.hidden();";
			innerHTML = '&#xe614;';
		}
		var comp_top_l = document.createElement('div');
		comp_top_l.className = 'top_l';
		comp_top_l.appendChild(comp_box_name);
		comp_top_l.appendChild(left_mb);
		comp_top_l.appendChild(this.counterContainer);
		comp_top_l.appendChild(right_mb);
		comp_top_l.appendChild(comp_box_name);

		this.comp_top.appendChild(this.closeBtn);
		this.comp_top.appendChild(comp_top_l);

		this.itemContainer = document.createElement('ul');
		this.itemContainer.id = 'comp_items';

		this.comp_boot = document.createElement('div');
		with (this.comp_boot) {
			id = 'comp_boot';
			innerHTML = [
				'    <input type="submit" class="prosubmit" value="'+otit+'对比" /><a class="clear" href="javascript:comp.remove_all()"><i class="ico08">&#xf014;</i>清空'+otit+'</a>',
			].join('\r\n');

		}

		this.subid_obj = document.createElement('input');
		with (this.subid_obj) {
			id = 'subid_obj';
			value = this.subcatid;
			name = 'subcatid';
			type = 'hidden';
		}
		this.comp_boot.appendChild(this.subid_obj);

		this.comp_form.appendChild(this.comp_top);
		this.comp_form.appendChild(this.itemContainer);
		this.comp_form.appendChild(this.comp_boot);
		this.comp_box.appendChild(this.comp_form);
		document.body.appendChild(this.comp_box);
	}
}

/**
* 删除所有对比产品
*/
comp.remove_all = function () {
	var items = this.itemContainer.getElementsByTagName('li');
	var items_num = items.length;
	var pro_id_arr = [];
	if(items_num) {
		for (var k=0; k<items_num; k++) {
			pro_id_arr[k] = items[k].id.substr(3);
		}
		for (var i=0; i<pro_id_arr.length; i++) {
			pro_id = pro_id_arr[i];
			this.remove(pro_id);
		}
	}
}

comp.remove = function (id) {
	var checkbox = $$('pro_'+id);
	var cp_id = 'cp_' + id;
	var remvoe_item= $$(cp_id);
	delete comp.pro_arr['pro_'+id];
	this.itemContainer.removeChild(remvoe_item);
	this.counter --;
	this.counterContainer.innerHTML --;
	if(checkbox) checkbox.checked = false;
	this.hasOnBeforeUnload || this.destruct();//不支持onbeforeunload事件的话，现在就处理COOKIE
}

comp.add_item = function (pro_id, pro_title, pro_link, sub_id) {
	if(typeof(pro_id) == 'object') {
		var option = pro_id;
		var pro_id = option[0];
		var pro_title = option[1];
		var pro_link = option[2];
		var sub_id = option[3];
	} else {
		var option = [pro_id, pro_title, pro_link, sub_id];
	}
	if (this.last_sub_id) {
		if (sub_id != this.last_sub_id) {
			this.last_sub_id = sub_id;
			this.subid_obj.value = this.sub_id;
			this.remove_all();
		}
	} else {
		this.last_sub_id = sub_id;
	}

	if (this.pro_arr['pro_'+pro_id]) return;
	var this_item = document.createElement('li');
	with(this_item) {
		id = 'cp_'+pro_id;
		innerHTML =	[
			'      <a class="icon ico08" href="javascript:comp.remove(\''+pro_id+'\');">&#xe614;</a>',
			'      <p class="title"><a href="'+pro_link+'" title="'+pro_title+'" target="_blank">'+pro_title+'</a></p>',
			'      <input type="hidden" name="pro_id[]" value="'+pro_id+'">'
		].join('\r\n');
	}
	this.pro_arr['pro_'+pro_id] = option;//缓存变量
	this.counter ++
	this.counterContainer.innerHTML ++;
	this.itemContainer.appendChild(this_item);
	if ($$('pro_'+pro_id) && !$$('pro_'+pro_id).checked) {
		$$('pro_'+pro_id).checked = true;
	}
	fade(this_item, 0, 100);

	this.hasOnBeforeUnload || this.destruct();//不支持onbeforeunload事件的话，现在就处理COOKIE

	return this_item;
}
comp.hidden = function () {
	this.comp_box.style.display = 'none';
}

comp.show = function () {
	this.comp_box.style.display = 'block';
}

comp.destruct = function () {
	var pro_join = [];
	if (this.pro_arr) {
		for (k in this.pro_arr) {
			pro_join.push(this.pro_arr[k].join('|'));
		}
		var pro_str = pro_join.join("@@@");
		writeCookie(this.cookie_name, pro_str);
	}
}

//执行对比
comp.exec = function () {
	if (this.counter < 2) {
		alert('至少选择两项对比~');
		return false;
	}
	if (this.counter >5) {
		alert('最多两项对比~');
		return false;
	}
	return true;
}
/**
* 透明渐变
* @author wiki <2009-02-14>
* @param element 元素
* @param opacity_from 渐变透明开始值
* @param opacity_to 渐变透明结束值
* @param callbackfunc 回调函数
* @return void
*/
function fade(element, opacity_from, opacity_to, callbackfunc) {
	var reduceOpacityBy = 5;
	var rate = 20;
	if (opacity_from <= 100 && opacity_from >= 0) {
		if (opacity_from < opacity_to) {//渐显
			opacity_from += reduceOpacityBy;
			if (opacity_from > opacity_to) opacity_from = opacity_to;
		} else if (opacity_from > opacity_to) {
			opacity_from -= reduceOpacityBy;
			if (opacity_from < opacity_to) opacity_from = opacity_to;
		}
		if (opacity_from > 100) opacity_from = 100;
		if (opacity_to > 100) opacity_to = 100;

		if (element.filters) {
			try {
				element.filters.item("DXImageTransform.Microsoft.Alpha").opacity = opacity_from;
			} catch (e) {
				element.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + opacity_from + ')';
			}
		} else {
			element.style.opacity = opacity_from / 100;
		}
	}
	if (opacity_from > 0 && opacity_from <100) {
		setTimeout(function () {
			fade(element, opacity_from, opacity_to, callbackfunc);
		}, rate);
	} else {
		typeof(callbackfunc) == 'function' && callbackfunc();
	}
}

/**
* 滚动跟随类
* @author wiki <2009-02-16>
*/
function scroll(settings) {
	var self = this;
	this.settings = settings;
	this.delta = this.settings.delta || 0.05;//滚动系数
	this.rate = this.settings.rate || 30;//速度
	this.items = [];
	this.addItem = function (element, x, y) {
		with(element.style) {
			position = 'absolute';
            zIndex ='9999';
			left = typeof(x) == 'string' ? eval(x) : x;
			top = typeof(y) == 'string' ? eval(y) : y;
		}
		var newItem = {};
		newItem.obj = element;
		newItem.x   = x;
		newItem.y   = y;
		this.items[this.items.length] = newItem;
	}
	this.play = function () {
		for(var i = 0; i < self.items.length; i++) {
			var this_item   = self.items[i];
			var this_item_x = typeof(this_item.x) == 'string' ? eval(this_item.x) : this_item.x;
			var this_item_y = typeof(this_item.y) == 'string' ? eval(this_item.y) : this_item.y;
			var doc = document.documentElement, body = document.body;
			var doc_left = (doc && doc.scrollLeft || body && body.scrollLeft || 0);
			var doc_top = (doc && doc.scrollTop || body && body.scrollTop || 0);

			if (this_item.obj.offsetLeft != (doc_left+this_item_x)) {
				var dx = (doc_left+this_item_x - this_item.obj.offsetLeft) * self.delta;
				dx = (dx > 0 ? 1 : -1) * Math.ceil(Math.abs(dx));
				this_item.obj.style.left = (this_item.obj.offsetLeft + dx) + 'px';
			}
			if (this_item.obj.offsetTop != (doc_top+this_item_y)) {
				var dy = (doc_top+this_item_y - this_item.obj.offsetTop) * self.delta;
				dy = (dy > 0 ? 1 : -1) * Math.ceil(Math.abs(dy));
				this_item.obj.style.top = (this_item.obj.offsetTop + dy) + 'px';
			}
		}
		if(this_item.obj.style.display == 'block' || this_item.obj.style.display == '')
			window.setTimeout(function(){self.play()}, self.rate);
	}
}

/**
* checkbox 添加对比产品
*/
function add_comp(obj, pro_name, pro_link, pro_id) {
	if (comp.comp_box.style.display == 'none') {
		comp.comp_box.style.display = 'block';
		comp_scroll.play();
	};
	if (obj.checked) {//添加
		if(comp.counter>=5) {
			obj.checked = false;
			alert('最多选择5套~');
			return false;
		}
		var result_obj = comp.add_item(pro_id, pro_name, pro_link, chid);
		if (typeof(result_obj) != 'object') {
			//obj.checked = false;
			obj.checked = true;
			alert('该房源已经在对比栏中了~');
			return false;
		}
	} else {//删除
		comp.remove(pro_id);
	}
}

/**
* 对比已钩选的产品
*/
function do_comp() {
	comp.exec() && makeUrl();
	return false;
}

window.onload = function () {
	//var chid = comp_action.match(/\bchid=(\d+)/);
	//chid = chid ? chid[1] : 0; 页面上直接定义了chid不用这一步
    comp_boxMXY[1]= comp_boxMXY[0]?'document.documentElement.clientWidth-174-'+comp_boxMXY[1]:comp_boxMXY[1];
	comp.init(chid);
	comp_scroll = new scroll({delta:0.3, rate: 50});
	comp_scroll.addItem(comp.comp_box,comp_boxMXY[1],comp_boxMXY[2]);
	comp_scroll.play();
	//离开页面时触发
	window.onbeforeunload = function() {
		comp.destruct();
	}
}

function makeUrl(){
	var k,ret='';
	for(var k in comp.pro_arr)ret+=','+k.substr(4);
	window.open(comp_action+ret.substr(1),'_blank')
}
