if(typeof _08cms == 'undefined')_08cms = {};var testArr = [];
if(!_08cms.templet)_08cms.templet = function(window){
	var document, prev, next, isReady = 0, done = 1, delay, interval;
	var data, node, parser = /<js:(\w+)((?:\s+\w+\s*=\s*"[^"]*")*)\s*\/?>/ig;///<js:(\w+)((?:\s+\w+\s*=\s*(?:"[^"]*"|'[^']*'|\d+))*)\s*\/?>/ig
	var i, l, attr, ids, key, end, pos, regatr = /\sname="(\w+)"/;
	var string, nodes = [], query = {};
	var CONST = {
		INSERT	: 0,
		INDEX	: 1,
		FIELD	: 2,
		TEXT	: 3
	};
//	var _timer = (new Date).getTime(), _count = 0;
//	function onload(){isReady = 1}
//	if(window.addEventListener){
//		window.addEventListener('load', onload, false);
//	}else if(window.attachEvent){
//		window.attachEvent('onload', onload);
//	}
	function ajaxData(){
		for(key in query){
			end = '';
			for(i in query[key].fds)end += ',' + i;
			ids = '';
			for(i in query[key].sub)ids += ',' + i;

			string = 'querydata='+key+':'+end.slice(1)+':'+ids.slice(1);
			//string = 'querydata='+key+':'+end.slice(1)+':'+ids.slice(1);

			end = document.createElement('SCRIPT');
			end.type = 'text/javascript';
			end.src = uri2MVC('ajax=floor&callback=_08cms.templet&' + string);
		//	end.src = '/tools/ajax.php?action=floor&callback=_08cms.templet&' + string;
			document.getElementsByTagName('HEAD')[0].appendChild(end);
		}

		for(i = 0, l = nodes.length; i < l; i++){
			node = nodes[i].dom;
			string = '<' + (key = node.tagName);
			attr = node.attributes;
			for(var j = 0; j < attr.length; j++)
				if(attr[j].specified && attr[j].name != 'js_floor')string += ' ' + attr[j].name + '="' + attr[j].value + '"';
			node = nodes[i].fds;
			if(node[0][0] == CONST.TEXT){
				node[0][1] = string + '>' + node[0][1];
			}else{
				node.unshift([CONST.TEXT, string + '>']);
			}
			if(node[j = node.length - 1][0] == CONST.TEXT){
				node[j][1] += '</' + key + '>';
			}else{
				node.push([CONST.TEXT, '</' + key + '>']);
			}
		}
		done = 0;
	}
	function display(query){
		if(done)return setTimeout(function(){display(query);}, 100);
		function floor(i, n){
			var ii, fields;
			do{fields = data[node.ids[i]]}while(!fields && i-- > 0);
			if(!fields)return;
			fn++;
			fields['cid'] = node.ids[i];
			for(ii = 0; ii < pos; ii++){
				switch(node.fds[ii][0]){
				case CONST.INSERT:
					i > 0 && floor(i - 1, n + 1);
					break;
				case CONST.INDEX:
					string += fn - n;
					break;
				case CONST.FIELD:
					string += fields[node.fds[ii][1]] || '';
					break;
				case CONST.TEXT:
					string += node.fds[ii][1];
					break;
				}
			}
		}
		var fn, data, node, i = 0, l = nodes.length;
		while(i < l){
			node = nodes[i++];
			if(data = query[node.tab]){
				fn = 0;
				string = '';
				pos = node.fds.length;
				floor(node.ids.length - 1, 0);
				node.dom.innerHTML = string;
				node.dom.parentNode.replaceChild(node.dom.firstChild, node.dom);
			}
		}
	}
	function parse(curr){
		if(curr.nodeType == 1){
			if((attr = curr.getAttribute('js_floor')) != null){
				attr = attr.split(':');
				if(attr[1]){
					ids = attr[1].split(',');
					if(!query[attr[0]]){
						query[attr[0]] = {
							fds : {},
							sub : {}
						};
					}
					for(i = 0, l = ids.length; i < l; i++)query[attr[0]].sub[ids[i]] = true;
					node = {
						tab : attr[0],
						dom : curr,
						ids : ids,
						fds : []
					};
					ids = query[attr[0]].fds;
					end = 0;
					parser.lastIndex = 0;
					data = curr.innerHTML;
					curr.innerHTML = '\u6570\u636e\u52a0\u8f7d\u4e2d...';
					while(attr = parser.exec(data)){
						pos = parser.lastIndex - attr[0].length;
						if(pos > end)node.fds.push([CONST.TEXT, data.slice(end, pos)]);
						end = parser.lastIndex;
						switch(attr[1] = attr[1].toUpperCase()){
						case 'FIELD':
							if(attr[2] && (attr = attr[2].match(regatr))){
								node.fds.push([CONST.FIELD, attr[1]]);
								ids[attr[1]] = true;
							}
							break;
						default:
							node.fds.push([CONST[attr[1]]]);
							break;
						}
					}
					if(end > 0)node.fds.push([CONST.TEXT, data.slice(end)]);
					nodes.push(node);
				}else{
					curr.style.display = 'none';
				}
			}else if(curr.firstChild){
				parse(next = curr.firstChild);
			}
		}
		curr.nextSibling && parse(next = curr.nextSibling);
	}
	function runtime(){
		if((document = window.document) && document.body){
			prev = next || document.body;
			next = document.body;
			while(next.lastChild)next = next.lastChild;
			if(next != prev){
				parse(prev);
				delay = interval = 13;
			}else{
				prev = delay;
				delay += interval;
				interval = prev;
				if(isReady || delay > 1000){
					alert(testArr)
					parse(next);
					return;
				}
			}
		}
		setTimeout(runtime, delay)
	}
//	setTimeout(runtime, 13);
	display.parse = function(){
		document = window.document;
		parse(document.body);
		ajaxData();
	}
//	window.addEventListener('load', display.parse, false);
	return display;
}(this);