//开盘日历js开始
if(typeof(HTMLElement)!="undefined")   //给firefox定义contains()方法，ie下不起作用
  {   
      HTMLElement.prototype.contains=function(obj)   
      {   
          while(obj!=null&&typeof(obj.tagName)!="undefind"){ //通过循环对比来判断是不是obj的父元素
  　　　　if(obj==this) return true;   
  　　　　obj=obj.parentNode;
   　　}   
          return false;   
      };   
  }  
var ua = navigator.userAgent;
  Test = {
    version: (ua.match(/.+(?:rv|it|ra|ie|me)[\/: ]([\d.]+)/i)||[])[1],
    ie: /msie/i.test(ua) && !/opera/i.test(ua),
    op: /opera/i.test(ua),
    sa: /version.*safari/i.test(ua),
    ch: /chrome/.test(ua),
    ff: /gecko/i.test(ua) && !/webkit/i.test(ua),
    wk: /webkit/i.test(ua),
    mz: /mozilla/i.test(ua)&&!/(compatible|webkit)/i.test(ua)
  }
function addEvent(el, type, fn){
    (el.attachEvent) ? (el.attachEvent("on" + type, fn)) : (el.addEventListener(type, fn, false));
  };

  function fixMouseWheel(elem, fn,self) {
    var mousewheel = Test.ff ? "DOMMouseScroll" : "mousewheel";
    (elem == null || elem == window ) && (elem = document);
    return {
      type: mousewheel,
      elem: elem,
      fn: function(e){
        var delta = 0;
        e = e || window.event;
        if (e.wheelDelta) {
          delta = event.wheelDelta / 120;
          if ( Test.op && Test.version < 10 ) delta = -delta;
        } else if (e.detail) {
          delta = -e.detail / 3;
        }
        e.delta = Math.round(delta);
        fn.call(elem, e,self);
      }
    }
  }

//模拟滚动条  
function scroll(id){  
	 var self = this; 
	 self.id = id; 
	 self.obj = $('#'+id)[0];  
	 self.content = self.obj.getElementsByTagName('div')[0];  
	 self.barBgColor = document.createElement('div'); 
	 self.barBgColor.className = "scrollBarBg";
	 self.bar = document.createElement('div');  
	 self.bar.className = 'scrollBar';  
	 self.bar.style.marginTop = 0;  
	 self.bar.style.height = parseInt( (self.content.scrollHeight<=self.obj.offsetHeight?1:self.obj.offsetHeight / self.content.scrollHeight)  * self.obj.offsetHeight) + 'px'; 
	 self.obj.appendChild(self.barBgColor);  
	 self.barBgColor.appendChild(self.bar ); 
	 self.bar.y;  
	 self.srcElement;  
	 self.marginTop = 0;  
	 self.bar.onmousedown = function(e){ self.mousedown(e); }  
	 self.mosueScroll = fixMouseWheel(self.obj,self.onmousewheel,self);
	 addEvent(self.mosueScroll.elem, self.mosueScroll.type, self.mosueScroll.fn);
}  
scroll.prototype = {  
	mousedown:function(e){             
			var self = this;  
			var e = e || window.event;  
			self.bar.y = e.clientY;  
			self.bar.t = parseInt( self.bar.style.marginTop ); 
			document.onmousemove = function(e){ self.mousemove(e); }  
			stopDefault(e);  
	},  
	mousemove:function(e){
			if (this.content.scrollHeight<=this.obj.offsetHeight) return false;

			var e = e || window.event;  
			
			var m,eObj = e.srcElement ? e.srcElement : e.target;
			if (!this.obj.contains(eObj)) return;

			this.marginTop = this.bar.t + ( e.clientY - this.bar.y );  
			if( this.marginTop < 0 ) this.marginTop = 0;  
			if( this.marginTop > this.obj.offsetHeight - parseInt(this.bar.style.height) ){  
					this.marginTop = this.obj.offsetHeight - parseInt(this.bar.style.height); 
			}  
			//$("#output")[0].innerHTML = self.obj.offsetHeight - self.bar.offsetHeight;
			  this.bar.style.marginTop = this.marginTop + 'px';
			  m = this.marginTop/this.obj.offsetHeight*this.content.scrollHeight;
			  this.content.style.top =  -m + 'px';
			  this.content.style.height = "auto";
			setCurrTag(this.id,parseInt(m));
			this.content.scrollTop = ( this.content.scrollHeight - this.obj.offsetHeight ) * parseInt( this.marginTop ) / ( this.obj.clientHeight - this.bar.clientHeight );  
			document.onmouseup = function(e){ document.onmousemove = null; }  
			stopDefault(e);  

	} ,
	 onmousewheel:function(e,self){  
	 	if (self.content.scrollHeight<=self.obj.offsetHeight) return false;

		var e = e || window.event;  
		var m,n,eObj = e.srcElement ? e.srcElement : e.target;
		
		if(e.delta >0){
			self.marginTop = parseInt( self.bar.style.marginTop ) -10 ; 
		}else if(e.delta <0) {
			self.marginTop = parseInt( self.bar.style.marginTop ) +10 ; 
		}
		
		if( self.marginTop < 0 ) self.marginTop = 0; 

		if( self.marginTop > self.obj.offsetHeight - parseInt(self.bar.style.height) ){  
				self.marginTop = self.obj.offsetHeight - parseInt(self.bar.style.height); 
		}  
		self.bar.style.marginTop = self.marginTop + 'px';
		m = self.marginTop/self.obj.offsetHeight*self.content.scrollHeight;
		self.content.style.top =  -m + 'px';
		self.content.style.height = "auto";
		setCurrTag(self.id,parseInt(m));
		self.content.scrollTop = ( self.content.scrollHeight - self.obj.offsetHeight ) * parseInt( self.marginTop ) / ( self.obj.clientHeight - self.bar.clientHeight );  
		//document.onmouseup = function(e){ document.onmousemove = null; }  
		stopDefault(e);  

	}  
}  



function stopDefault( e ) {  
	if ( e && e.preventDefault )  
			e.preventDefault();  
	else  
			window.event.returnValue = false;  
	return false;  
}  

var p = new scroll ('scrollBox1' ),  
	p = new scroll ('scrollBoxC2' ),  
	p = new scroll ('scrollBox3' );  

//选择id
var $calendarTab = $("#calendarTab"),
	calendarUl = $calendarTab.find("ul"),
	$calendarUlLi = $calendarTab.find("li"),
    num = 1,
    $prev = $('#leftBtn'),
    $next = $('#rightBtn'),
    //tab = $('#calendarTab')[0],
    list = $calendarTab.find('ul'),
	wid = parseInt($calendarTab.css("width")),
	// $calendarCon = $("#calendarCon"),
	$inner = $('#inner'),
	$innerTitleLi = $inner.find("li"),
	$innerCon = $("#innerCon"),
	$innerConUl = $innerCon.find('ul'),

	// content2Ul = $("#content2")[0].getElementsByTagName("ul"),
	$calendarMonth = $("#calendarMonth"),
	cid = parseInt($("#calendarCon").css("width")),
	
	$scrollBoxC2 = $("#scrollBoxC2"),
	scrollBoxUl2 = $scrollBoxC2.find("ul"),
	scrollBoxLi2 = $scrollBoxC2.find("li"),
	
    $currentUl_two = $('#currentUl_two'),
    $currentUl_two_Li = $currentUl_two.find("li"),


	$scrollBox1 = $("#scrollBox1"),
	scrollBoxUl1 = $scrollBox1.find("ul"),
	scrollBoxLi1 = $scrollBox1.find("li"),

    $currentUl_one = $('#currentUl_one'),
    $currentUl_one_Li = $currentUl_one.find("li"),


	$scrollBox3 = $("#scrollBox3"),
	scrollBoxUl3 = $scrollBox3.find("ul"),
	scrollBoxLi3 = $scrollBox3.find("li"),

    $currentUl_three = $('#currentUl_three'),
    $currentUl_three_Li = $currentUl_three.find("li");
	

//获取scrollTop函数
function getHeight1(len){
	if (len == 5 && scrollBoxUl1[5].offsetHeight<=$scrollBox1[0].offsetHeight) {
		//alert(scrollBoxUl1[4].offsetHeight+scrollBoxUl1[5].offsetHeight);
		if (scrollBoxUl1[4].offsetHeight+scrollBoxUl1[5].offsetHeight<=$scrollBox1[0].offsetHeight)
		{
			return scrollBoxUl1[4].offsetTop;
		}
			return scrollBoxUl1[4].offsetTop+scrollBoxUl1[4].offsetHeight+scrollBoxUl1[5].offsetHeight-$scrollBox1[0].offsetHeight;
	}
	return scrollBoxUl1[len].offsetTop;
	
}
function getHeight2(len){
	
	if (len == 5 && scrollBoxUl2[5].offsetHeight<=$scrollBoxC2[0].offsetHeight) {
		if (scrollBoxUl2[4].offsetHeight+scrollBoxUl2[5].offsetHeight<=$scrollBoxC2[0].offsetHeight)
		{
			return scrollBoxUl2[4].offsetTop;
		}
			return scrollBoxUl2[4].offsetTop+scrollBoxUl2[4].offsetHeight+scrollBoxUl2[5].offsetHeight-$scrollBoxC2[0].offsetHeight;
	}
	return scrollBoxUl2[len].offsetTop;
}
function getHeight3(len){
	if (len == 5 && scrollBoxUl3[5].offsetHeight<=$scrollBox3[0].offsetHeight) {
		if (scrollBoxUl3[4].offsetHeight+scrollBoxUl3[5].offsetHeight<=$scrollBox3[0].offsetHeight)
		{
			return scrollBoxUl3[4].offsetTop;
		}
			return scrollBoxUl3[4].offsetTop+scrollBoxUl3[4].offsetHeight+scrollBoxUl3[5].offsetHeight-$scrollBox3[0].offsetHeight;
	}
	return scrollBoxUl3[len].offsetTop;
}

function setCurrTag(tagIndex,m){
	var n,j;
	switch (tagIndex) {
	   case "scrollBox1" :
		   n = 1;
		   break;
	   case "scrollBoxC2" :
		   n = 2;
		   break;
	   case "scrollBox3" :
		   n = 3;
		   break;
	} 
	switch (n) {
	   case 1 :
		 if (m>=0 && m<scrollBoxUl1[1].offsetTop){
			j = 0;
		}else if(m>=scrollBoxUl1[1].offsetTop && m<scrollBoxUl1[2].offsetTop){
			j = 1;
		}else if(m>=scrollBoxUl1[2].offsetTop && m<scrollBoxUl1[3].offsetTop){
			j = 2;
		}else if(m>=scrollBoxUl1[3].offsetTop && m<scrollBoxUl1[4].offsetTop){
			j = 3;
		}else if(m>=scrollBoxUl1[4].offsetTop && m<(scrollBoxUl1[5].offsetHeight<=$scrollBox1[0].offsetHeight?scrollBoxUl1[5].offsetTop-(scrollBoxUl1[4].offsetHeight-scrollBoxUl1[5].offsetHeight):scrollBoxUl1[5].offsetTop)){
			j = 4;
		}else
		{
			j = 5;
		}
		
	   	$currentUl_one_Li.removeClass('calendarOn')[j].className='calendarOn';
	   break;
	   case 2:
		   if (m>=0 && m<scrollBoxUl2[1].offsetTop){
			j = 0;
		}else if(m>=scrollBoxUl2[1].offsetTop && m<scrollBoxUl2[2].offsetTop){
			j = 1;
		}else if(m>=scrollBoxUl2[2].offsetTop && m<scrollBoxUl2[3].offsetTop){
			j = 2;
		}else if(m>=scrollBoxUl2[3].offsetTop && m<scrollBoxUl2[4].offsetTop){
			j = 3;
		}else if(m>=scrollBoxUl2[4].offsetTop && m<(scrollBoxUl2[5].offsetHeight<=$scrollBox1[0].offsetHeight?scrollBoxUl2[5].offsetTop-(scrollBoxUl2[4].offsetHeight-scrollBoxUl2[5].offsetHeight):scrollBoxUl2[5].offsetTop)){
			j = 4;
		}else
		{
			j = 5;
		}
	   	$currentUl_two_Li.removeClass('calendarOn')[j].className='calendarOn';
	   break;
	   case 3 :
	   if (m>=0 && m<scrollBoxUl3[1].offsetTop){
			j = 0;
		}else if(m>=scrollBoxUl3[1].offsetTop && m<scrollBoxUl3[2].offsetTop){
			j = 1;
		}else if(m>=scrollBoxUl3[2].offsetTop && m<scrollBoxUl3[3].offsetTop){
			j = 2;
		}else if(m>=scrollBoxUl3[3].offsetTop && m<scrollBoxUl3[4].offsetTop){
			j = 3;
		}else if(m>=scrollBoxUl3[4].offsetTop && m<(scrollBoxUl3[5].offsetHeight<=$scrollBox1[0].offsetHeight?scrollBoxUl3[5].offsetTop-(scrollBoxUl3[4].offsetHeight-scrollBoxUl3[5].offsetHeight):scrollBoxUl3[5].offsetTop)){
			j = 4;
		}else
		{
			j = 5;
		}
	   	$currentUl_three_Li.removeClass('calendarOn')[j].className='calendarOn';
	   	break;
	} 

}


//判断当前月
for (var r = 0;r < calendarUl[1].getElementsByTagName("li").length;r++){
	if($calendarMonth.html().substring(0,2) === calendarUl[1].getElementsByTagName("li")[r].innerHTML.substring(0,2))
	{
		$calendarUlLi.removeClass();
		calendarUl[1].getElementsByTagName("li")[r].className = "calendarOn";
		
		$scrollBoxC2.find(".scrollContent").css({top:-getHeight2(r),height:200 + getHeight2(r)});
		$scrollBoxC2.find(".scrollBar").css("margin-top",parseInt((getHeight2(r) / (scrollBoxLi2.length*scrollBoxLi2[0].offsetHeight))*$scrollBoxC2[0].offsetHeight));
	}
	
}
//center
for(var i=0;i<$currentUl_two_Li.length;i++){
	//设置索引值
	$currentUl_two_Li[i].index = i;
	
	$currentUl_two_Li[i].onmouseover = function(){
		if (scrollBoxUl2[this.index].innerHTML == "") return false;
		$currentUl_two_Li.removeClass('calendarOn');
		this.className = "calendarOn";
		$scrollBoxC2.find(".scrollContent").css({top:-getHeight2(this.index),height:200 + getHeight2(this.index)});
		$scrollBoxC2.find(".scrollBar").css("margin-top",parseInt((getHeight2(this.index) / (scrollBoxLi2.length*scrollBoxLi2[0].offsetHeight))*$scrollBoxC2[0].offsetHeight));
		
	}

}
//pre
for(var x=0;x<$currentUl_one_Li.length;x++){
	//设置索引值
	$currentUl_one_Li[x].index = x;
	$currentUl_one_Li[x].onmouseover = function(){
		
		if (scrollBoxUl1[this.index].innerHTML == "") return false;
		$currentUl_one_Li.removeClass('calendarOn');
		this.className = "calendarOn";
		$scrollBox1.find(".scrollContent").css({top:-getHeight1(this.index),height:200 + getHeight1(this.index)});
		$scrollBox1.find(".scrollBar").css("margin-top",parseInt((getHeight1(this.index) / (scrollBoxLi1.length*scrollBoxLi1[0].offsetHeight))*$scrollBox1[0].offsetHeight));
	}

}
//$next
for(var d=0;d<$currentUl_three_Li.length;d++){
	//设置索引值
	$currentUl_three_Li[d].index = d;
	$currentUl_three_Li[d].onmouseover = function(){
		if (scrollBoxUl3[this.index].innerHTML == "") return false;
		$currentUl_three_Li.removeClass('calendarOn');
		this.className = "calendarOn";
		$scrollBox3.find(".scrollContent").css({top:-getHeight3(this.index),height:200 + getHeight3(this.index)});
		$scrollBox3.find(".scrollBar").css("margin-top",parseInt((getHeight3(this.index) / (scrollBoxLi3.length*scrollBoxLi3[0].offsetHeight))*$scrollBox3[0].offsetHeight));
		
	}

}
$("#yearTitle").html($currentUl_two.attr("year"));
//点击向左箭头
$prev.click(function(){
    if( num > 0 ){
        $inner.css({marginLeft: function(index, value) {
	        return parseInt(value) +wid;
      	}});
        $innerCon.css({marginLeft: function(index, value) {
	        return parseInt(value) +cid;
      	}});
        num--;
		$calendarUlLi.removeClass();	
		list[num].getElementsByTagName('li')[0].className = "calendarOn";
		if($inner[0].style.marginLeft === "0px"){
			$("#yearTitle").html($currentUl_one.attr("year"));
			$prev[0].style.visibility = "hidden";
			$next[0].style.display = "block";
		}
		if($inner[0].style.marginLeft === "-180px"){
			$("#yearTitle").html($currentUl_two.attr("year"));
			$prev[0].style.visibility = "visible";
			$next[0].style.display = "block";
		}
    }
	$scrollBox1.find(".scrollContent").css({top:-getHeight1(0),height:200 + getHeight1(0)});
	$scrollBox1.find(".scrollBar").css("margin-top",parseInt((getHeight1(0) / (scrollBoxLi2.length*scrollBoxLi2[0].offsetHeight))*$scrollBoxC2[0].offsetHeight));
});
//点击向右箭头
$next.click(function(){
	//alert(wid);
    if( num < list.length-1 ){
        $inner.css({marginLeft: function(index, value) {
	        return parseInt(value) -wid;
      	}});
        $innerCon.css({marginLeft: function(index, value) {
	        return parseInt(value) -cid;
      	}});
        num++;
		$calendarUlLi.removeClass();	
		list[num].getElementsByTagName('li')[0].className = "calendarOn";
		if($inner[0].style.marginLeft === "-180px"){
			$("#yearTitle").html($currentUl_two.attr("year"));
			$next[0].style.display = "block";
			$prev[0].style.visibility = "visible";
		}
		if($inner[0].style.marginLeft === "-360px"){
			$("#yearTitle").html($currentUl_three.attr("year"));
			$next[0].style.display = "none";
			$prev[0].style.visibility = "visible";
		}
    }
	$scrollBoxC2.find(".scrollContent").css({top:-getHeight2(0),height:200 + getHeight2(0)});
	$scrollBoxC2.find(".scrollBar").css("margin-top",parseInt((getHeight2(0) / (scrollBoxLi2.length*scrollBoxLi2[0].offsetHeight))*$scrollBoxC2[0].offsetHeight));
});
$("#calendarBox").hover(function(){
		$(this).find(".scrollBarBg").show();
	},function(){
		$(this).find(".scrollBarBg").hide();	
	});

for(var w=0;w<$innerConUl.length;w++){
	if($innerConUl[w].innerHTML ==""){
		$innerTitleLi[w].style.color ="#cfd7e7";
		
	}	
}
//开盘日历js结束/* 
		