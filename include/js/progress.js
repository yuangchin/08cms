function Progress(){
	var doc = window.document, $, border;
	$ = this.$_dom = {};
	$.border = border = doc.createElement('div');
	$id('append_parent').appendChild(border);
	with(border.style){
		position = 'absolute';
		textAlign = 'left';
		padding = '5px';
		left = '5px';
		top = '5px';
		width = '350px';
		hieght = '60px';
		lineHeight = '20px';
		backgroundColor = '#fff';
		border = '1px solid #999';
		zIndex = 9999;
	}
	border.innerHTML = '<p><a href="?entry=gmissions&action=break" onclick="var m;if(m=/[&?](infloat=[^&]+)/i.exec(window.location.href))this.href+=\'&\'+m[1];" style="color:blue">' + '要中止操作请点这里<<'+ '</a></p><div></div>';
	$.content = border.lastChild;
	this.$_data = {
		rateWidth : border.offsetWidth,
		
		pagecount	: 0,
		linkcount	: 0,
		content		: 0,
		output		: 0,
		usetime		: 0,
		
		timestamp : new Date(),
		lang : {
			hours : '小时',
			minutes : '分钟',
			seconds : '秒',
			string : '采集正在进行中。。。<br />共请求了 {$pagecount} 个页面<br />并获得了 {$linkcount} 个链接<br />采集到了 {$content} 条内容<br />入库完成 {$output} 条记录<br />持续时间 {$usetime}'}
	};
	var me = this;
	listen(window, 'load', function(){me.hide()});
}

Progress.prototype = {
	show : function(){
	},
	hide : function(){
		var b = this.$_dom.border;
		b.parentNode && b.parentNode.removeChild(b);
	},
	rate : function(rate){
		this.$_dom.rate.style.width = Math.round(rate / 100 * this.$_data.rateWidth) + 'px';
	},
	pagecount : function(num){
		this.$_data.pagecount += num;
		this.refurbish();
	},
	linkcount : function(num){
		this.$_data.linkcount += num;
		this.refurbish();
	},
	content : function(num){
		this.$_data.content += num;
		this.refurbish();
	},
	output : function(num){
		this.$_data.output += num;
		this.refurbish();
	},
	refurbish : function(){
		var data = this.$_data;
		data.usetime = this.timeFormat((new Date()) - data.timestamp);
		this.$_dom.content.innerHTML = this.$_data.lang.string.replace(/\{\$(\w+)\}/g, function(a, b){return data[b]});
	},
	timeFormat : function(timestamp){
		var H, i, s, ms;
		ms = timestamp % 1000;
		timestamp = (timestamp - ms) / 1000;
		s = timestamp % 60;
		timestamp = (timestamp - s) / 60;
		i = timestamp % 60;
		H = (timestamp - i) / 60;
		time = H ? H + this.$_data.lang.hours : '';
		time += (i ? i : '0') + this.$_data.lang.minutes;
		time += s ? s : '0';
		time += ms ? '.' + ms : '';
		return time + this.$_data.lang.seconds;
	}
};
