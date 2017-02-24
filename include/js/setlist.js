
/*
 * 添加/修改 收起/展开:高级设置
 * e: 设置的元素节点id
 * mc: 是否会员中心
 */
function setMoerInfo(id,mc){
	var setButton = $id('setMore_'+id);
	var tab = setMoerTable(setButton,mc);
	var show = tab.style.display;
	if(show=='none'){ 
		tab.style.display = '';
		setButton.innerHTML = '收起设置&#x21D1; &nbsp; ';
	}else{ 
		tab.style.display = 'none';
		setButton.innerHTML = '展开设置&#x21D3; &nbsp; ';
	}
}

/*
 * 获得id下，父table
 * e: 设置的元素节点
 * mc: 是否会员中心
 */
function setMoerTable(e,mc){
	if(mc==1){ 
		var p = e.parentNode; 
		if(p.tagName.toUpperCase()!='TABLE') return p;
		else return setMoerTable(e);
	}else{ 
		var tempObj = e.parentNode.nextSibling;
		//FireFox中包含众多空格作为文本节点，因此在使用nextSibling时就会出现问题,所以做循环判断
		while (tempObj.nodeType != 1 ) {
			tempObj = tempObj.nextSibling;
		}            
		return tempObj;	
	}
}

/*会员中心扩展设置
*
*/

function ex_hidspan(__js,fmdata,obj){
	if(obj.innerHTML=='收起设置')  
		obj.innerHTML='展开设置';
	else 
		obj.innerHTML= '收起设置';
	 hidspan(__js,fmdata);
}

/*会员中心
*添加修改字段隐藏设置
*@param __js 要隐藏的表单项
*@param fmdata 表单项前缀
*/
function hidspan(__js,fmdata){
	var t=[];
	t=__js.split(",");
	for(i=0;i<t.length;i++){   
        var o=document.getElementById(fmdata+"["+t[i]+"]");
		if(o){ o.parentNode.parentNode.style.display=="none" ? o.parentNode.parentNode.style.display="" : o.parentNode.parentNode.style.display="none";}
    }
}

//移动层的类
function aListSetMove(){
    this.Move = function(DivID,Evt){
        if(DivID == "") return;
        var DivObj = document.getElementById(DivID);
        evt = Evt?Evt:window.event;
        if(!DivObj) return;
        var DivW = DivObj.offsetWidth;
        var DivH = DivObj.offsetHeight;
        var DivL = DivObj.offsetLeft;
        var DivT = DivObj.offsetTop;
        var TemDiv = document.createElement("div");
        TemDiv.id = DivID + "tem";
        document.body.appendChild(TemDiv);
        TemDiv.style.cssText = "width:"+DivW+"px;height:"+DivH+"px;top:"+DivT+"px;left:"+DivL+"px;position:absolute; border:#ff0000 1px dotted;z-index:500";
        this.MoveStart(DivID,evt);
    }
    
    this.MoveStart = function(DivID,Evt){
		var TemDivObj = document.getElementById(DivID+"tem");
        if(!TemDivObj) return;
        evt = Evt?Evt:window.event;
        var rLeft = evt.clientX - TemDivObj.offsetLeft;
        var rTop = evt.clientY - TemDivObj.offsetTop;
        //if (!window.captureEvents){
            //TemDivObj.setCapture();
        //}else{
            //window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP);
        //}
        
        document.onmousemove = function(e)
        {
            if (!TemDivObj) return;
			aListSetUnSelect();
            e = e ? e : window.event;
            if (e.clientX - rLeft <= 0){
                TemDivObj.style.left = 0 +"px";
            }else if(e.clientX - rLeft >= document.documentElement.clientWidth - TemDivObj.offsetWidth - 2){
                TemDivObj.style.left = (document.documentElement.clientWidth - TemDivObj.offsetWidth - 2) +"px";
            }else{
                TemDivObj.style.left = e.clientX - rLeft +"px";
            }
            if (e.clientY - rTop <= 1){
              
            }else{
                TemDivObj.style.top = e.clientY - rTop +"px";
            }
        }
        
        document.onmouseup = function()
        {
            if (!TemDivObj){return;}
            //if (!window.captureEvents){
                //TemDivObj.releaseCapture();
            //}else{
                //window.releaseEvents(Event.MOUSEMOVE|Event.MOUSEUP);
            //}
            var DivObj1 = document.getElementById(DivID);
            if (!DivObj1) return;
            var l0 = TemDivObj.offsetLeft;
            var t0 = TemDivObj.offsetTop;
            
            DivObj1.style.top = t0 + "px";
            DivObj1.style.left = l0 + "px";
            
            document.body.removeChild(TemDivObj);
            TemDivObj = null;
        }   
    }
}
function mouseMove(ev) {
	ev2 = ev || window.event;
	mousePos = mouseCoords(ev2);
}
function mouseCoords(ev) {
	if (ev.pageX || ev.pageY) {
		return { x: ev.pageX, y: ev.pageY };
	}
	try{ //IE6下,在开窗中再打开设置窗,显示错误,但不影响功能。
	  return {  
		x: ev.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft),
		y: ev.clientY + (document.documentElement.scrollTop || document.body.scrollTop)
	  };
	}catch(e){}
}
var aListSetMoving = new aListSetMove();//创建移动层的实例
var mousePos; document.onmousemove = mouseMove;
function aListSetUnSelect(){
	try{ document.selection.empty(); }
	catch(e){ window.getSelection().removeAllRanges();}
}

function aListSetIsShow(trID,sCfg,id){ // 检测id第几列状态
	fCok = getcookie(aListSet_ckpre+trID+'_'+id); // (null)H/S/
	fDef = sCfg.split('|')[id]; // (null)H/S/
	if(fCok=='H'){ return 'H'; }
	else if(fCok=='S'){ return 'S'; }
	else{
	  if(fDef=='H')	{ return 'H'; }
	  else { return 'S'; }
	}
}
function aListSetGetTable(trID,flag){ // 找到父级Table/CSS
	if(flag=='td'){
	   	return $id('TR_'+trID).getElementsByTagName('td')[1].className;
	}else{
		var tab = $id('TR_'+trID).parentNode;
		if(tab.tagName.toUpperCase()!='TABLE') tab = tab.parentNode;
		if(flag=='tb') return tab.className;
		else return tab;
	}
}
function XXX_aListSetGetCSS(trID){ // 找到Table CSS
	var tab = aListSetGetTable(trID);
	var objTR = $id('TR_'+trID);
	var fline = 0; if(objTRs[0].getElementsByTagName('td').length==1) fline = 1;
}
function aListSetGetCols(trID,sCfg){ // IE6/IE7下,修正colspan=N
	var objCols = $id('TR_'+trID).getElementsByTagName('td');
	var fcols = 0;
	var aCfg = sCfg.split('|');
	for(var i = 0;i<objCols.length;i++){
	  if(aCfg[i]!='S'){
		  fcols++;
	  }else{
		  iFlg = aListSetIsShow(trID,sCfg,i);
		  if(iFlg=='S'){
			  fcols++;
		  }  
	  }
	}
	return fcols;
}
function aListSetReset(trID,sCfg){ // 初始化Table
	var objTRs = aListSetGetTable(trID).getElementsByTagName('tr');
	var fline = 0; if(objTRs[0].getElementsByTagName('td').length==1) fline = 1;
	var fCols = new Array(); //Chrome下太慢,发现aListSetIsShow很占时间,用这个记录第一次取值 
	//var now1 = new Date(); //测试用时
	for(var i=fline;i<objTRs.length;i++){
		objTD = objTRs[i].getElementsByTagName('td');
		for(var j=0;j<objTD.length;j++){
			fCols[j] = fCols[j] ? fCols[j] : aListSetIsShow(trID,sCfg,j);
			if(fCols[j]=='H'){ 
				//objTD[j].sytle.display = 'none'; //无(width:0px)IE6报错?
				objTD[j].style.cssText = 'width:0px;display:none;';
			}
		}
	}
	//var now2 = new Date(); alert(now1.getSeconds()+'.'+now1.getMilliseconds()+'\n'+now2.getSeconds()+'.'+now2.getMilliseconds());
	objTRs[fline].title = '双击进行详细设置';
	sBtn = '<div id="aListSetBtn'+aListSet_ckpre+'" class="alist_setcols" onclick="aListSetting(\''+trID+'\',\''+sCfg+'\')">列设置</div>';
	if(fline==0){
		var aDiv = document.getElementsByTagName('div'); var flag = false;
		for(i=0;i<aDiv.length;i++){
			if(aDiv[i].className=='conlist1') {
			  if(aDiv[i].innerHTML.indexOf('aListSetBtn'+aListSet_ckpre)<0){ //仅第一次执行,每页仅显示一个
				  aDiv[i].innerHTML = sBtn+aDiv[i].innerHTML; flag = true; // showMap()
			  }
			  break; 
			}
		}
	}else{
		var objTD = aListSetGetTable(trID).getElementsByTagName('tr')[0].getElementsByTagName('td')[0];
		if(objTD.innerHTML.indexOf('aListSetBtn'+aListSet_ckpre)<0){
			objTD.innerHTML = sBtn+objTD.innerHTML; flag = true;
		}
		objTD.setAttribute("colSpan", aListSetGetCols(trID,sCfg));
	}
	/*
	if(flag){ //仅第一次执行
		var row1 = objTRs[0].getElementsByTagName('td'); //alert(row1.length);
		for(j=0;j<row1.length;j++){
		  if(aListSet_tCfg[j]!=''){
			  $iVal = row1[j].innerHTML;
			  if(row1[j].innerHTML.indexOf('"aListSetTDAct(')<0){ 
				  //$iVal = "\n<span id=\"aListSet_TD"+j+"T\" style=\"cursor:pointer;\" onmouseover=\"aListSetTDAct(this,"+j+")\" onmouseout=\"setTimeout('aListSetTDOut(this,"+j+")',1500)\">"+$iVal+"</span>";
				  //$iVal += "\n<span id=\"aListSet_TD"+j+"B\" style=\"cursor:pointer; color:#F00; display:none;\" onclick=\"aListSetColumn("+j+")\">[x]隐藏此列</span>";
				  //row1[j].innerHTML = $iVal;  
			  }
			  //row1[j].title = '双击进行详细设置';
		  }
		}
	}
	*/
	
}	
function aListSetColumn(id,trID,type){ // 隐藏/显示某列
	//aListSetReset();
	if(type=='Show'){ //2^31=2,147,483,648; 1Year=31,536,000S
	  setcookie(aListSet_ckpre+trID+'_'+id, 'S', 321000123); type=''; 
	}else{ 
	  setcookie(aListSet_ckpre+trID+'_'+id, 'H', 321000123); type='width:0px;display:none;'; 
	}
	var objTR = aListSetGetTable(trID).getElementsByTagName('tr');
	for(var i = 0;i<objTR.length;i++){
		objTD = objTR[i].getElementsByTagName('td');
		for(var j = 0;j<objTD.length;j++){
			if(j==id){ 
				//objTD[j].innerHTML = '';
				objTD[j].style.cssText = type;
			}
		}
	}
	aListSetGetTable(trID).style.cssText = 'width:100%';
}

function aListSetting(trID,sCfg){ // 设置窗口

	aListSetUnSelect();
	
	var alertWidth = 400; var boxID = 'aListSetBox_'+trID; 
	var alertW12 = Math.round(alertWidth/2);
	var winWidth = document.body.clientWidth; 
	//alert(mousePos.y+':'+mousePos.x); 
	var alertTop = mousePos.y + 5; //得到滚动位置，设置对话框顶部位置
    //var alertLeft = mousePos.x - alertW12; //设置对话框居中显示 
	var alertLeft = (document.documentElement.clientWidth-10-alertWidth)/2;
	if(alertLeft<5) alertLeft = 5;
	if(alertLeft+alertWidth>winWidth+5) alertLeft = winWidth - alertWidth - 5;
	try{ document.body.removeChild($id(boxID)); }catch(e){} 
	//if($id(boxID)){ //IE6出错! 用removeChild则不出错.
        //$id(boxID).style.display = '';
		//$id(boxID).style.left = alertLeft+'px';
    //}else{
        var obj = document.createElement("div"); //创建一个div标签，作为自定对话框的容器 
        document.body.appendChild(obj); obj.id = boxID; 
        obj.style.cssText = "width:"+alertWidth+"px; position:absolute; left:"+alertLeft+"px; top:"+alertTop+"px; border:#999999 0px solid; overflow-X:hidden; overflow-Y:hidden; z-index:1000;";//设定对话框容器的样式
	//}
	obj.className = 'aListSet_windowall'; // <div(x)windowall"></div>
	var objTRs = aListSetGetTable(trID).getElementsByTagName('tr');
	var cssTab = aListSetGetTable(trID,'tb');
	var cssTD = aListSetGetTable(trID,'td');
	cssTD = cssTD.replace('txtC','').replace('center',''); //修正对齐 txtL,left
	var fline = 0; if(objTRs[0].getElementsByTagName('td').length==1) fline = 1;
	var oItem = objTRs[fline].getElementsByTagName('td');
    strBox = '<div class="aListSet_wleftop"></div><div class="aListSet_wintitle" style="cursor:move" onmousedown="aListSetMoving.Move(\'aListSetBox_'+trID+'\',event)"><a onclick="aListSetEnd(\''+trID+'\');"  class="aListSet_wclose"></a><span> &nbsp; 详细设置 - 可选显示列设置</span></div><div class="aListSet_wrighttop"></div><div class="aListSet_contentall"><div class="aListSet_wcontent">(text)</div></div><div class="aListSet_wlefbottom"></div><div class="aListSet_wbottom"></div><div class="aListSet_wrightbottom"></div>';
	str = ''; j = 0; k = 0; //strBox = strBox.replace('(x)',' class="aListSet_'); // 不能投机Peace
	str += '<table class="'+cssTab+'" width="100%">';
	var aCfg = sCfg.split('|');
	for(var i = 0;i<oItem.length;i++){
	  if(aCfg[i]!='S'){
		  iVal = oItem[i].innerHTML; if(iVal.indexOf('<input ')>=0) iVal = '[选择]'; //if(i==0)alert(iVal);
		  iFlg = aListSetIsShow(trID,sCfg,i);
		  if(iFlg=='S'){
			  iFlg = 'checked="checked"';
			  k++;
		  }else{
			  iFlg = '';
		  }
		  if(j%2==0) str += '<tr>';
		  str += '<td colspan="2" align="left" class="'+cssTD+'"><label><input class="checkbox" type="checkbox" id="aListSet_Col'+trID+i+'" '+iFlg+'>'+iVal+'</label></td>';
		  if(j%2==1) str += '</tr>';
		  j++;
	  }
	}
	ckd = k==j?' checked="checked"':'';
	str += '<table class="'+cssTab+'" width="100%"><tr>';
	str += '<td width="32%" align="left" class="'+cssTD+'"><label><input id="aListSetAll'+trID+'" class="checkbox" type="checkbox" onclick="aListSetAll(this,\''+trID+'\',\''+sCfg+'\')" '+ckd+'>全选</label></td>';
	str += '<td width="32%" align="left" class="'+cssTD+'"><input class="btn button" type="submit" name="bsubmit2" value="确定" onclick="aListSetOK(\''+trID+'\',\''+sCfg+'\')" ></td>';
	str += '<td align="left" class="'+cssTD+'"><input class="btn button" type="submit" name="bsubmit1" value="重置" onclick="aListSetRE(\''+trID+'\',\''+sCfg+'\')" ></td>';
	str += '</tr></table>'; //absolute/relative/fixed  xheight:500px;
	obj.innerHTML = strBox.replace('(text)',str);
	
	return false;
}
function aListSetAll(e,trID,sCfg){ //全选
	if(e.checked){ eck=true; flg = 'Show'; }
	else         { eck=false; flg = ''; }
	var aCfg = sCfg.split('|');
	for(i =0;i<aCfg.length-1;i++){
		if(aCfg[i]!='S'){
			objCbox = $id('aListSet_Col'+trID+i);
			objCbox.checked = eck; //aListSetColumn(i,flg);
		}
	}
}
function aListSetRE(trID,sCfg){ 
//重置 ('extend_file_s_1','S|S|||H|H|H||||H|||H|H|||S|')
	for(i =0;i<100;i++){
		setcookie(aListSet_ckpre+trID+'_'+i, '', -1024);
	}
	//aListSetOK(trID,sCfg);
	aListSetEnd(trID,sCfg);
}
function aListSetOK(trID,sCfg){ //设置完毕,存Cookie,关设置窗口
	var aCfg = sCfg.split('|');
	for(i =0;i<aCfg.length-1;i++){
		if(aCfg[i]!='S'){
			objCbox = $id('aListSet_Col'+trID+i);
			if(objCbox.checked) {
				aListSetColumn(i,trID,'Show');
			}else{
				aListSetColumn(i,trID);
			}
		}
	}
	aListSetEnd(trID,sCfg);
}
function aListSetEnd(trID,sCfg){ // 关闭对话框函数
	if(sCfg) aListSetReset(trID,sCfg);
	if($id("aListSetBox_"+trID)){
		document.body.removeChild($id("aListSetBox_"+trID));
    }
}




