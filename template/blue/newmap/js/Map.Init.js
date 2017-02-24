/**************地图界面布局初始化**************/

//--------------------地图初始函数库--------------------------------------------------------------------------
//创建和初始化地图 by louis
function initMap() {
		changescreenWandH();//地图布局界面调整
		createMap(); //创建地图
		setMapEvent(); //设置地图事件
		addMapControl();//向地图添加控件
}
//创建地图函数 by louis
function createMap() {
		window.map = new BMap.Map("divMap");
		var point = new BMap.Point(mapInfo.px, mapInfo.py); //定义一个中心点坐标
		map.centerAndZoom(point, mapInfo.initZoom);
		window.map = map; //将map变量存储在全局
		/*
		map.addEventListener("movestart", function () {            		
				
		});	
		map.addEventListener("moveend", function () { 
		
		});	
		map.addEventListener("zoomstart", function () {

		});
		map.addEventListener("zoomend", function () {

		});
		*/
	}
	
//地图事件设置函数 by louis
function setMapEvent() {
	map.enableDragging(); //启用地图拖拽事件，默认启用(可不写)
	map.enableScrollWheelZoom(); //启用地图滚轮放大缩小
	map.enableDoubleClickZoom(); //启用鼠标双击放大，默认启用(可不写)
	map.enableKeyboard(); //启用键盘上下左右键移动地图
	map.setDraggingCursor('hand');//设置拖拽地图时的鼠标指针样式为扒手
}

//地图控件添加函数 by louis
function addMapControl() {
	//向地图中添加缩放控件
	var ctrl_nav = new BMap.NavigationControl({
		anchor: BMAP_ANCHOR_TOP_LEFT,
		type: BMAP_NAVIGATION_CONTROL_LARGE
	});
	map.addControl(ctrl_nav);
	//向地图中添加缩略图控件
	var ctrl_ove = new BMap.OverviewMapControl({
		anchor: BMAP_ANCHOR_BOTTOM_RIGHT,
		isOpen: 1
	});
	map.addControl(ctrl_ove);
	//向地图中添加比例尺控件
	var ctrl_sca = new BMap.ScaleControl({
		anchor: BMAP_ANCHOR_BOTTOM_LEFT
	});
	map.addControl(ctrl_sca);
}

/*****************************定义自定义覆盖物的构造函数**********************************/
function SquareOverlay(center,length,html,zIndex,purpose,projcode,px,py,projname,address,addresslong){
	this._center = center; 
	this._length = length;
	this._html = html;
	this._zIndex = zIndex;
	this._purpose = purpose;
	this._projcode = projcode;
	this._px = px;
	this._py = py;
	this._projname = projname;
	this._address = address;
	this._addresslong = addresslong;
 }
// 继承API的BMap.Overlay
 SquareOverlay.prototype = new BMap.Overlay(); 
 // 实现初始化方法
 SquareOverlay.prototype.initialize = function(map){
	// 保存map对象实例
	 this._map = map;
	 var that = this;   
	 // 创建div元素，作为自定义覆盖物的容器 
	 var div = document.createElement("div");   
	 div.style.position = "absolute";
	 div.style.zIndex =this._zIndex; 
	 div.setAttribute("id",that._projcode+"_container"); 
	 // 可以根据参数设置元素外观   
	  div.innerHTML = this._html;
	 // 将div添加到覆盖物容器中 
	 map.getPanes().markerPane.appendChild(div); 
	 // 保存div实例  
	 this._div = div;
	 return div;
  } 
  // 实现绘制方法
  SquareOverlay.prototype.draw = function(){
	// 根据地理坐标转换为像素坐标，并设置给容器  
	var position = this._map.pointToOverlayPixel(this._center);
   this._div.style.left = position.x - 12   + "px"; 
   this._div.style.top = position.y - 30  + "px"; 
  }
  SquareOverlay.prototype.show = function()
	{   if (this._div){   
		this._div.style.display = "";   } 
		  
	} 
  SquareOverlay.prototype.hide = function()
	{   if (this._div){    
	  this._div.style.display = "none";   }  
	}
  SquareOverlay.prototype.changehtml = function(html){  
   if (this._div){   
	   this._div.innerHTML= html; 
		}
   }
  SquareOverlay.prototype.addEventListener = function(event,fun){ 
	 this._div['on'+event] = fun;
   }

//动态判定地图各部分宽高
function changescreenWandH() {
	//动态判定右侧地图的高度
	$('#boxfooter').show();
	var rightbarheight = $(window).height() - $('#boxhead').height()-$('#boxfooter').height(); //parseInt(document.body.clientHeight)-topbarheight;

	$("#divMap").css({
		"height": rightbarheight
	});
	//动态判定左侧列表的高度
	$("#resultcontainer").css({
		"height": rightbarheight - $('#leftwrapperTips').height()
	});
}