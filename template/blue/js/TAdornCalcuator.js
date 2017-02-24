function TAdornCalcuator() {
     this.wallBrick=TAdornCalcuator_WallBrick;
     this.floorBrick=TAdornCalcuator_FloorBrick;
     this.wallPaint=TAdornCalcuator_WallPaint;
     this.wallPaper=TAdornCalcuator_WallPaper;
     this.windowTamping=TAdornCalcuator_WindowTamping;
     this.windowCloth=TAdornCalcuator_WindowCloth;
}

function TAdornCalcuator_WallBrick() {//墙砖计算器
    var iRate=1.05, iResult=0;
    var iRoomLong=_$("wallBrick_roomLong")*1000,iRoomWidth=_$("wallBrick_roomWidth")*1000,iRoomHeight=_$("wallBrick_roomHeight")*1000;
    var iDoorHeight=_$("wallBrick_doorHeight")*1000,iDoorWidth=_$("wallBrick_doorWidth")*1000,iDoorNum=_$("wallBrick_doorNum");
    var iWindowHeight=_$("wallBrick_windowHeight")*1000,iWindowWidth=_$("wallBrick_windowWidth")*1000,iWindowNum=_$("wallBrick_windowNum");
    var iBrickLong=_$("wallBrick_brickLong"),iBrickWidth=_$("wallBrick_brickWidth");
    var iUnitPrice=_$("wallBrick_unitPrice");
 
 //用砖数量（块数）=[（房间的长度÷砖长）×（房间高度÷砖宽）×2+ 
 //（房间的宽度÷砖长）×（房间高度÷砖宽）×2—（窗户的长度÷砖长）×
 //（窗户的宽度÷砖宽）×个数—（门的长度÷砖长）×（门的宽度÷砖宽）×个数]×1.05
    iResult=(iRoomLong/iBrickLong)*(iRoomHeight/iBrickWidth)*2;
    iResult=iResult+(iRoomWidth/iBrickLong)*(iRoomHeight/iBrickWidth) *2;
    iResult=iResult- (iWindowHeight/iBrickLong)*(iWindowWidth/iBrickWidth)*iWindowNum;
    iResult=iResult-(iDoorHeight/iBrickLong)*(iDoorWidth/iBrickWidth)*iDoorNum;
    iResult=Math.round(iResult*iRate);
    if(isNaN(iResult) || isNaN(iResult*iUnitPrice)){alert("请输入完整信息后进行计算");}else{
    	$$("wallBrick_result",iResult);
    	$$("wallBrick_totalPrice",iResult*iUnitPrice);
    }
}

function TAdornCalcuator_FloorBrick() {//地板计算器
    var oSelect=document.getElementById("floorBrick_rate");
	var iIndex=oSelect.selectedIndex;
    var iRate=parseFloat(oSelect.options[iIndex].value);
    var iRoomLong=_$("floorBrick_roomLong")*1000, iRoomWidth=_$("floorBrick_roomWidth")*1000;
    var iFloorLong=_$("floorBrick_floorLong"), iFloorWidth=_$("floorBrick_floorWidth");
    var iResult=Math.round((iRoomLong/iFloorLong)*(iRoomWidth/iFloorWidth)*iRate);
    if(isNaN(iResult)){alert("请输入完整信息后进行计算");}else{
    	$$("floorBrick_result",iResult);
    }
}

function TAdornCalcuator_WallPaint(form) {//涂料计算器
    var iRoomLong=_$("wallPaint_roomLong"),iRoomWidth=_$("wallPaint_roomWidth"),iRoomHeight=_$("wallPaint_roomHeight");
    var iDoorHeight=_$("wallPaint_doorHeight"),iDoorWidth=_$("wallPaint_doorWidth"),iDoorNum=_$("wallPaint_doorNum");
    var iWindowHeight=_$("wallPaint_windowHeight"),iWindowWidth=_$("wallPaint_windowWidth"),iWindowNum=_$("wallPaint_windowNum");
    var iRate=_$("wallPaint_rate"), iUnitPrice=_$("wallPaint_unitPrice");
    var iResult=(iRoomLong+iRoomWidth)*2*iRoomHeight+iRoomLong*iRoomWidth;

    iResult=iResult-iWindowHeight*iWindowWidth*iWindowNum;
    iResult=iResult-iDoorHeight*iDoorWidth*iDoorNum;
    iResult=(Math.round(iResult/iRate*100))/100;
    if(isNaN(iResult) || isNaN(iResult*iUnitPrice)){alert("请输入完整信息后进行计算");}else{
    	$$("wallPaint_result",iResult);
    	$$("wallPaint_totalPrice", iResult*iUnitPrice);
    }
}

function TAdornCalcuator_WallPaper() {//壁纸计算器 壁纸用量(卷)＝房间周长×房间高度×1.1÷每卷平米数
    var iRate=1.1;
    var iRoomLong=_$("wallPaper_roomLong"),iRoomWidth=_$("wallPaper_roomWidth"),iRoomHeight=_$("wallPaper_roomHeight");
    var iWallPaperPerMeter=_$("wallPaper_perMeter"),iUnitPrice=_$("wallPaper_unitPrice");
    var iResult=Math.round(((iRoomLong+iRoomWidth)*2*iRoomHeight*iRate)/iWallPaperPerMeter );
    if(isNaN(iResult) || isNaN(iResult*iUnitPrice)){alert("请输入完整信息后进行计算");}else{
    	$$("wallPaper_result",iResult);
    	$$("wallPaper_totalPrice",iResult*iUnitPrice);
    }
}

function TAdornCalcuator_WindowTamping() {//地砖计算器 [(砖长+砖宽) ÷ (砖长x砖宽)] x 砖厚度 x 缝的平均宽度 x 1.7
    var iBrickLong=_$("windowTamping_brickLong"),iBrickWidth=_$("windowTamping_brickWidth");
    var iTampingWidth=_$("windowTamping_tampingWidth"), iTampingHeight=_$("windowTamping_tampingHeight");
    var iUnitPrice=_$("windowTamping_unitPrice");
    var iResult=(iBrickWidth+iBrickLong)*iTampingWidth*iTampingHeight*1.7/(iBrickWidth*iBrickLong);
    if(isNaN(iResult) || isNaN(iResult*iUnitPrice)){alert("请输入完整信息后进行计算");}else{
    	$$("windowTamping_result",iResult);
    	$$("windowTamping_totalPrice",iResult*iUnitPrice);
    }
}

function TAdornCalcuator_WindowCloth() {//窗帘计算器  [（窗户宽+0.15米×2）×2] ÷ 布宽×（0.15米+窗户高+0.5米+0.2米）
    var iWindowHeight=_$("windowCloth_windowHeight"),iWindowWidth=_$("windowCloth_windowWidth");
    var iClothWidth=_$("windowCloth_clothWidth"), iUnitPrice=_$("windowCloth_unitPrice");
    var iResult=((iWindowWidth+parseFloat(0.15*2))*2)/iClothWidth*(parseFloat(0.15)+iWindowHeight+parseFloat(0.5)+parseFloat(0.2));
    if(isNaN(iResult) || isNaN(iResult*iUnitPrice)){alert("请输入完整信息后进行计算");}else{
    	$$("windowCloth_result",Math.round(iResult));
    	$$("windowCloth_totalPrice",Math.round(iResult)*iUnitPrice);
    }
}

//share function
function _$(a_sID) {
     var oObj=document.getElementById(a_sID);

     if(!oObj) return 0;
     
     return parseFloat(oObj.value);
}

function $$(a_sID,a_iValue) {
    var oObj=document.getElementById(a_sID);

    if(!oObj) return;

    oObj.value=a_iValue;
}

function checkInput(a_sStr,a_sType) {
    var oReg;

    switch(a_sType) {
    case "isNull":oReg=/^\s*$/g;break;
    case "isNumber":oReg=/^[0-9]*\.*[\d]*$/g;break;
    }

    return oReg.test(a_sStr);
}

function listenTip(a_sID) {
    var aID=a_sID.split(",");
    var oObj;

    for(var i=0;i<aID.length;i++) {
        oObj=document.getElementById(aID[i]);

        if(!oObj) continue;

        oObj.onfocus=function(event) {
            var evt=(event)?event:((window.event)?window.event:"");

            if (!evt) return;

            var oNode=_getTargetElement(evt);

            if (!oNode) return;
            
            var bResult=checkInput(oNode.value,"isNumber");

            if(bResult){hideTip(this.id);}
            else {showTip(this.id,"请输入数字");}  
        };
    }
}

////////////////////////////////////////////////////////////////////////////////////////////初始化
var oCalcuator;	
function openTool(a_sName) {
    var oImg=document.getElementById("img"+a_sName), oTable=document.getElementById("tbl"+a_sName);
	
	if((!oImg)||(!oTable)) return;

    if(oImg.src.indexOf("newimages/sq_tb.gif")>0) {
	    oTable.style.display="none";
		oImg.src=tplurl + "newimages/zk_tb.gif";
	} else {
	    oTable.style.display="";
		oImg.src=tplurl + "newimages/sq_tb.gif";
	}
}
function autoInputFloorBrick() {//地板自动填写
   var oSelect=document.getElementById("floorBrick_bricktype");
  
   if(!oSelect) return;

   var iIndex=oSelect.selectedIndex;
   var iType=oSelect.options[iIndex].value;

    switch(parseInt(iType)) {
    case 1:$$("floorBrick_floorLong",600);$$("floorBrick_floorWidth",90);break;
    case 2:$$("floorBrick_floorLong",750);$$("floorBrick_floorWidth",90);break;
    case 3:$$("floorBrick_floorLong",900);$$("floorBrick_floorWidth",90);break;
    case 4:$$("floorBrick_floorLong",1285);$$("floorBrick_floorWidth",192);break;
	}
}


function autoInputWallBrick() {//墙砖自动填写
   var oSelect=document.getElementById("wallBrick_bricktype");
  
   if(!oSelect) return;

   var iIndex=oSelect.selectedIndex;
   var iType=oSelect.options[iIndex].value;
   
	switch(parseInt(iType)) {
	case 0:$$("wallBrick_brickLong",200);$$("wallBrick_brickWidth",200);break;
	case 1:$$("wallBrick_brickLong",300);$$("wallBrick_brickWidth",300);break;
	case 2:$$("wallBrick_brickLong",400);$$("wallBrick_brickWidth",400);break;
	case 3:$$("wallBrick_brickLong",500);$$("wallBrick_brickWidth",500);break;
	case 4:$$("wallBrick_brickLong",600);$$("wallBrick_brickWidth",600);break;
	case 5:$$("wallBrick_brickLong",300);$$("wallBrick_brickWidth",200);break;
	case 6:$$("wallBrick_brickLong",250);$$("wallBrick_brickWidth",330);break;
	case 7:$$("wallBrick_brickLong",300);$$("wallBrick_brickWidth",450);break;	
	}
}

function calcuator(a_sName) {
    if(!oCalcuator) oCalcuator=new TAdornCalcuator();

    switch(a_sName){
    case "floorBrick":oCalcuator.floorBrick();break;
    case "wallBrick":oCalcuator.wallBrick();break;
    case "wallPaint":oCalcuator.wallPaint();break;
    case "wallPaper":oCalcuator.wallPaper();break;
    case "windowCloth":oCalcuator.windowCloth();break;
    case "windowTamping":oCalcuator.windowTamping();break;
    }
}