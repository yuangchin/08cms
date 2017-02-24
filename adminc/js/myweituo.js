function weituotab(cid){
	var li = $id('weituoli' + cid),lis = li.parentNode.getElementsByTagName('LI');
	for(var i in lis) lis[i].className = '';
	li.className = 'act';
	var wtobj = $id('weituo' + cid);
	var divs = wtobj.parentNode.getElementsByTagName('DIV');
	for(var i in divs)	if(divs[i].id && divs[i].id.indexOf('weituo') !== -1)	divs[i].style.display = divs[i].id == 'weituo' + cid ? '' : 'none';
}
function delWeituo(cid){
	var li = $id('weituoli' + cid),ul = li.parentNode,div = $id('weituo' + cid),pdiv = div.parentNode;
	ajax.get($cms_abs  + uri2MVC("ajax=delweituo&cid="+cid),function(result){
		if(result == 'SUCCEED'){
			ul.removeChild(li);
			pdiv.removeChild(div);
			var unode = ul.childNodes
			for(var i = 0;i < unode.length;i++) if(unode[i].tagName == 'LI'){
				unode[i].onclick();break;	
			}
		}else alert(result);
	},1);
}
function cancelWeituo(wid,cid){
	ajax.get($cms_abs + uri2MVC("ajax=cancelweituo&wid="+wid),function(result){
		if(result == 'SUCCEED'){
			var trobj = $id('tr'+wid);
			trobj.parentNode.removeChild(trobj);
			$id('hweituonum' + cid).innerHTML = parseInt($id('hweituonum' + cid).innerHTML)-1; 
			$id('nweituonum' + cid).innerHTML = parseInt($id('nweituonum' + cid).innerHTML)+1; 
			if(parseInt($id('nweituonum' + cid).innerHTML) > 0)document.getElementById('wtcontinue').style.display = 'block';
		}else alert(result);
	},1)
}
function modifyPrice(cid){
	if($id('text'+cid)){cancelModify(cid,$id('text'+cid).value);return;}
	var span = $id('price'+cid),price = parseFloat(span.innerHTML);
	span.innerHTML = '<input id="text' + cid + '" value="'+price+'" type="text"><div><a  href="javascript:confirmModify(' + cid + ')">\u786e\u5b9a</a> <a href="javascript:cancelModify(' + cid + ',' + price + ')">\u53d6\u6d88</a></div>';
}
function cancelModify(cid,price){
	$id('price'+cid).innerHTML = price;	
}
function confirmModify(cid){
	var price = parseFloat($id('text' + cid).value);
	ajax.get($cms_abs + uri2MVC("ajax=modifyprice&cid=" + cid + "&zj=" + price),function(result){
		if(result == 'SUCCEED') $id('price'+cid).innerHTML = price;
		else alert(result);
	},1);
}


