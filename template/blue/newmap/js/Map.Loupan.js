var projectMarkers=[],//楼盘Markers
    projectInfo={},//楼盘列表信息
    districtMarkers=[],//地区Markers
    districtAreaInfo=[],//单独地区信息数据
    historyProjectInfo={},//游览历史楼盘数据
    historyProjectMarkers=[],//游览历史楼盘Markers
    districtAreaMarkers=[];//单独地区

function changescreenWandH() {
	//动态判定右侧地图的高度
	var rightbarheight = $(window).height() - $('#boxhead').height()-$('#boxfooter').height(); //parseInt(document.body.clientHeight)-topbarheight;   
	$("#mapouterdiv").css({
		"height": rightbarheight
	});
	$("#divMap").css({
		"height": rightbarheight
	});
	//动态判定左侧列表的高度     
	$("#leftwrapper").css({
		"height": rightbarheight
	});
	var resultcontainerheight =  rightbarheight - $('#leftwrapperTips').height()-28;
	
   var lconnr1Height = $(window).height() - $('#searchResultShow').offset().top - 30;
   $('.lconnr1').css({height:lconnr1Height+'px'});
}
	
$(function(){
    initMap();//初始地图界面
	//动态监听浏览器宽高变化，初始化各部分宽高
	//动态判定地图各部分宽高
    $(window).resize(function () {
        changescreenWandH();
    });
    //模块初始化
    var loupanControl= new MapInitLoupanControl();
        loupanControl.Init();      
});

/***************************房源(出售,出租)地图函数库*******************************/

function MapInitLoupanControl(){	
// 搜索
	$("#keyword").val(mapInfo.defaultKeyword).blur(function(e){
			if($(this).val()==''){$(this).val(mapInfo.defaultKeyword)};
	}).focus(function(){
			if($(this).val()==mapInfo.defaultKeyword){$(this).val('')};
	}).keydown(function(e){
			if(e.keyCode==13){
			SearchByKeyword();
			}
	});
	$("#btnSearch").click(function(){
			SearchByKeyword();
	});
//keyword 搜索 by louis
function SearchByKeyword(){
		var value = $("#keyword").val();
		if(value==mapInfo.defaultKeyword || value==''){alert('请输入楼盘进行搜索');return;}
		searchHouseInfo.keyword = value;searchInfo.keyword = value;
		searchHouseInfo.district = '';searchInfo.district = '';			
		changeConditionTipsDiv();
		getProjectPoint();
		showProjectData(0,10);
}
    //楼盘初始筛选条件 by louis
    function InitLoupanConditions(){   		
    		if(Conditions.district) InitDistrictControl();			   			
        	if(Conditions.purpose) InitPurposeControl();
    		if(Conditions.price) InitPriceControl();
            if(Conditions.salestat) InitSalestatControl();			
			if(Conditions.loupantese) InitLoupanteseControl();
			if(Conditions.louceng) InitLoucengControl();
			if(Conditions.huanxian) InitHuanxianControl();
			if(Conditions.ditie) InitDitieControl();
			$("#search_result").show();
			$("#ltitle1").show();
			$("#conditionDiv").show();
			$("#total_count").show();
    }
    //地区控件 louis
    function InitDistrictControl(){
		var content = Conditions.district;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx" id="districtControl"><div id="divDistrict" class="select_box"><div id="spnDistrictTitle" District="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">地区</div></div>');
		var container = $("#divDistrict");
		var ul = $('<ul id="ulDistrict" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="district" district="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		} 
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var district = $(this).find("a").attr("district");
            if(searchHouseInfo.district!=district){
               searchInfo.district = $(this).find("a").html();
			   searchHouseInfo.district = district;
			   searchHouseInfo.projpageindex = 1;			   			   
				Conditions.shangquan = Conditions.district.coid2 && Conditions.district.coid2[searchHouseInfo.district] ? Conditions.district.coid2[searchHouseInfo.district] : '';
				InitShangquanControl(); 
			
			   $("#spnDistrictTitle").html(searchInfo.district).attr("district",searchHouseInfo.district);				   
			   changeConditionTipsDiv();              
			   getProjectPoint();
			   showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulDistrict").css("display") == 'none') {$("#ulDistrict").show();}else{$("#ulDistrict").hide();}
        }).bind("mouseenter", function () {
            $("#spnDistrictTitle").removeClass().addClass("tag_select_open");
            $("#ulDistrict").show();
        }).bind("mouseleave", function () {
            $("#spnDistrictTitle").removeClass().addClass("tag_select");
            $("#ulDistrict").hide();
        });        
    }
	//商圈控件 louis
    function InitShangquanControl(){
		var content = Conditions.shangquan;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		var flag = document.getElementById('shangquanControl');				
		if(flag == null){
			$("#districtControl").after('<div class="selectqx" id="shangquanControl"><div id="divShangquan" class="select_box"><div id="spnShangquanTitle" shangquan="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">商圈</div><ul id="ulShangquan" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul></div>');
		}
		var ul = $("#ulShangquan");		
		var contentLength = content.text.length ;
		var ulhtml = '';	
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ulhtml += '<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="shangquan" shangquan="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></li>';
		}
		ul.html(ulhtml);       
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var shangquan = $(this).find("a").attr("shangquan");
            if(searchHouseInfo.shangquan!=shangquan){
               searchInfo.shangquan = $(this).find("a").html();
			   searchHouseInfo.shangquan = shangquan;
			   searchHouseInfo.projpageindex = 1;
               $("#spnShangquanTitle").html(searchInfo.shangquan).attr("shangquan",searchHouseInfo.shangquan);	
               changeConditionTipsDiv();              
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        var container = $("#divShangquan");
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulShangquan").css("display") == 'none') {$("#ulShangquan").show();}else{$("#ulShangquan").hide();}
        }).bind("mouseenter", function () {
            $("#spnShangquanTitle").removeClass().addClass("tag_select_open");
            $("#ulShangquan").show();
        }).bind("mouseleave", function () {
            $("#spnShangquanTitle").removeClass().addClass("tag_select");
            $("#ulShangquan").hide();
        }); 
    }
	
	//楼盘特色控件 louis
    function InitLoupanteseControl(){
		var content = Conditions.loupantese;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divLoupantese" class="select_box"><div id="spnLoupanteseTitle" loupantese="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">楼盘特色</div></div>');
		var container = $("#divLoupantese");
		var ul = $('<ul id="ulLoupantese" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="loupantese" loupantese="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		}
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var loupantese = $(this).find("a").attr("loupantese");
            if(searchHouseInfo.loupantese!=loupantese){
               searchInfo.loupantese = $(this).find("a").html();
			   searchHouseInfo.loupantese = loupantese;
			   searchHouseInfo.projpageindex = 1;
               $("#spnLoupanteseTitle").html(searchInfo.loupantese).attr("loupantese",searchHouseInfo.loupantese);	
               changeConditionTipsDiv();              
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulLoupantese").css("display") == 'none') {$("#ulLoupantese").show();}else{$("#ulLoupantese").hide();}
        }).bind("mouseenter", function () {
            $("#spnLoupanteseTitle").removeClass().addClass("tag_select_open");
            $("#ulLoupantese").show();
        }).bind("mouseleave", function () {
            $("#spnLoupanteseTitle").removeClass().addClass("tag_select");
            $("#ulLoupantese").hide();
        }); 
    }
	
	//装修程度控件 louis
    function InitZhuangxiuchengduControl(){
		var content = Conditions.zhuangxiuchengdu;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divZhuangxiuchengdu" class="select_box"><div id="spnLounpanteseTitle" zhuangxiuchengdu="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">装修程度</div></div>');
		var container = $("#divZhuangxiuchengdu");
		var ul = $('<ul id="ulZhuangxiuchengdu" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="zhuangxiuchengdu" zhuangxiuchengdu="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		}
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var zhuangxiuchengdu = $(this).find("a").attr("zhuangxiuchengdu");
            if(searchHouseInfo.zhuangxiuchengdu!=zhuangxiuchengdu){
               searchInfo.zhuangxiuchengdu = $(this).find("a").html();
			   searchHouseInfo.zhuangxiuchengdu = zhuangxiuchengdu;
			   searchHouseInfo.projpageindex = 1;
               $("#spnZhuangxiuchengduTitle").html(searchInfo.zhuangxiuchengdu).attr("zhuangxiuchengdu",searchHouseInfo.zhuangxiuchengdu);	
               changeConditionTipsDiv();             
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulZhuangxiuchengdu").css("display") == 'none') {$("#ulZhuangxiuchengdu").show();}else{$("#ulZhuangxiuchengdu").hide();}
        }).bind("mouseenter", function () {
            $("#spnLoupanteseTitle").removeClass().addClass("tag_select_open");
            $("#ulZhuangxiuchengdu").show();
        }).bind("mouseleave", function () {
            $("#spnZhuangxiuchengduTitle").removeClass().addClass("tag_select");
            $("#ulZhuangxiuchengdu").hide();
        }); 
    }
	
	//楼层控件 louis
    function InitLoucengControl(){
		var content = Conditions.louceng;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divLouceng" class="select_box"><div id="spnLoucengTitle" louceng="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">楼层</div></div>');
		var container = $("#divLouceng");
		var ul = $('<ul id="ulLouceng" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="louceng" louceng="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		}
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var louceng = $(this).find("a").attr("louceng");
            if(searchHouseInfo.louceng!=louceng){
               searchInfo.louceng = $(this).find("a").html();
			   searchHouseInfo.louceng = louceng;
			   searchHouseInfo.projpageindex = 1;
               $("#spnLoucengTitle").html(searchInfo.louceng).attr("louceng",searchHouseInfo.louceng);	
               changeConditionTipsDiv();             
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulLouceng").css("display") == 'none') {$("#ulLouceng").show();}else{$("#ulLouceng").hide();}
        }).bind("mouseenter", function () {
            $("#spnLoucengTitle").removeClass().addClass("tag_select_open");
            $("#ulLouceng").show();
        }).bind("mouseleave", function () {
            $("#spnLoucengTitle").removeClass().addClass("tag_select");
            $("#ulLouceng").hide();
        }); 
    }
	
	//环线控件 louis
    function InitHuanxianControl(){
		var content = Conditions.huanxian;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divHuanxian" class="select_box"><div id="spnHuanxianTitle" huanxian="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">环线</div></div>');
		var container = $("#divHuanxian");
		var ul = $('<ul id="ulHuanxian" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="huanxian" huanxian="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		}
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var huanxian = $(this).find("a").attr("huanxian");
            if(searchHouseInfo.huanxian!=huanxian){
               searchInfo.huanxian = $(this).find("a").html();
			   searchHouseInfo.huanxian = huanxian;
			   searchHouseInfo.projpageindex = 1;
               $("#spnHuanxianTitle").html(searchInfo.huanxian).attr("huanxian",searchHouseInfo.huanxian);	
               changeConditionTipsDiv();             
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulHuanxian").css("display") == 'none') {$("#ulHuanxian").show();}else{$("#ulHuanxian").hide();}
        }).bind("mouseenter", function () {
            $("#spnHuanxianTitle").removeClass().addClass("tag_select_open");
            $("#ulHuanxian").show();
        }).bind("mouseleave", function () {
            $("#spnHuanxianTitle").removeClass().addClass("tag_select");
            $("#ulHuanxian").hide();
        }); 
    }
	
	//地铁控件 louis
    function InitDitieControl(){
		var content = Conditions.ditie;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divDitie" class="select_box"><div id="spnDitieTitle" ditie="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">地铁</div></div>');
		var container = $("#divDitie");
		var ul = $('<ul id="ulDitie" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="ditie" ditie="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		}
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var ditie = $(this).find("a").attr("ditie");
            if(searchHouseInfo.ditie!=ditie){
               searchInfo.ditie = $(this).find("a").html();
			   searchHouseInfo.ditie = ditie;
			   searchHouseInfo.projpageindex = 1;
				Conditions.ditiezhandian = Conditions.ditie.coid14[searchHouseInfo.ditie];
				InitDitiezhandianControl();
               $("#spnDitieTitle").html(searchInfo.ditie).attr("ditie",searchHouseInfo.ditie);	
               changeConditionTipsDiv();             
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulDitie").css("display") == 'none') {$("#ulDitie").show();}else{$("#ulDitie").hide();}
        }).bind("mouseenter", function () {
            $("#spnDitieTitle").removeClass().addClass("tag_select_open");
            $("#ulDitie").show();
        }).bind("mouseleave", function () {
            $("#spnDitieTitle").removeClass().addClass("tag_select");
            $("#ulDitie").hide();
        }); 
    }	
	//地铁站点控件 louis
    function InitDitiezhandianControl(){
		var content = Conditions.ditiezhandian;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		var flag = document.getElementById('ditiezhandianControl');
		if(flag == null){
			$("#search_cond_select_div").append('<div class="selectqx" id="ditiezhandianControl"><div id="divDitiezhandian" class="select_box"><div id="spnDitiezhandianTitle" ditiezhandian="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">地铁站点</div><ul id="ulDitiezhandian" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul></div>');		
		}		
		var ul = $("#ulDitiezhandian");
		var ulhtml = '';
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ulhtml += '<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="ditiezhandian" ditiezhandian="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>';
		}		
		ul.html(ulhtml);   
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var ditiezhandian = $(this).find("a").attr("ditiezhandian");
            if(searchHouseInfo.ditiezhandian!=ditiezhandian){
               searchInfo.ditiezhandian = $(this).find("a").html();
			   searchHouseInfo.ditiezhandian = ditiezhandian;
			   searchHouseInfo.projpageindex = 1;
               $("#spnDitiezhandianTitle").html(searchInfo.ditiezhandian).attr("ditiezhandian",searchHouseInfo.ditiezhandian);
               changeConditionTipsDiv();             
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        var container = $("#divDitiezhandian");
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulDitiezhandian").css("display") == 'none') {$("#ulDitiezhandian").show();}else{$("#ulDitiezhandian").hide();}
        }).bind("mouseenter", function () {
            $("#spnDitiezhandianTitle").removeClass().addClass("tag_select_open");
            $("#ulDitiezhandian").show();
        }).bind("mouseleave", function () {
            $("#spnDitiezhandianTitle").removeClass().addClass("tag_select");
            $("#ulDitiezhandian").hide();
        }); 
    }
	
    //物业控件
    function InitPurposeControl(){
  		var content = Conditions.purpose;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divPurpose" class="select_box"><div id="spnPurposeTitle" purpose="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">物业类型</div></div>');
		var container = $("#divPurpose");
		var ul = $('<ul id="ulPurpose" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="purpose" purpose="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		} 
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var purpose = $(this).find("a").attr("purpose");
            if(searchHouseInfo.purpose!=purpose){
               searchInfo.purpose = $(this).find("a").html();
			   searchHouseInfo.purpose = purpose;
			   searchHouseInfo.projpageindex = 1;
               $("#spnPurposeTitle").html(searchInfo.purpose).attr("purpose",searchHouseInfo.purpose);	
               changeConditionTipsDiv();
			   getProjectPoint();
               showProjectData(0,10);
            }	
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulPurpose").css("display") == 'none') {$("#ulPurpose").show();}else{$("#ulPurpose").hide();}
        }).bind("mouseenter", function () {
            $("#spnPurposeTitle").removeClass().addClass("tag_select_open");
            $("#ulPurpose").show();
        }).bind("mouseleave", function () {
            $("#spnPurposeTitle").removeClass().addClass("tag_select");
            $("#ulPurpose").hide();
        });
        
    }
    
    //价格控件
    function InitPriceControl(){
        var content = Conditions.price;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divPrice" class="select_box"><div id="spnPriceTitle" price="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">价格</div></div>');
		var container = $("#divPrice");
		var ul = $('<ul id="ulPrice" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="price" price="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		} 
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var price = $(this).find("a").attr("price");
            if(searchHouseInfo.price!=price){
               searchInfo.price = $(this).find("a").html();
			   searchHouseInfo.price = price;
			   searchHouseInfo.projpageindex = 1;
               $("#spnPriceTitle").html(searchInfo.price).attr("price",searchHouseInfo.price);	
               changeConditionTipsDiv();
			   getProjectPoint();
               showProjectData(0,10);
            }	
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulPrice").css("display") == 'none') {$("#ulPrice").show();}else{$("#ulPrice").hide();}
        }).bind("mouseenter", function () {
            $("#spnPriceTitle").removeClass().addClass("tag_select_open");
            $("#ulPrice").show();
        }).bind("mouseleave", function () {
            $("#spnPriceTitle").removeClass().addClass("tag_select");
            $("#ulPrice").hide();
        });
        
    }
    //销售状态控件
    function InitSalestatControl(){
        var content = Conditions.salestat;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divSalestat" class="select_box"><div id="spnSaleStatTitle" salestat="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">销售状态</div></div>');
		var container = $("#divSalestat");
		var ul = $('<ul id="ulSalestat" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="salestat" salestat="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		} 
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var salestat = $(this).find("a").attr("salestat");
            if(searchHouseInfo.salestat!=salestat){
               searchInfo.salestat = $(this).find("a").html();			   
			   searchHouseInfo.salestat = salestat;
			   searchHouseInfo.projpageindex = 1;
               $("#spnSaleStatTitle").html(searchInfo.salestat).attr("salestat",searchHouseInfo.salestat);	
               changeConditionTipsDiv();
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulSalestat").css("display") == 'none') {$("#ulSalestat").show();}else{$("#ulSalestat").hide();}
        }).bind("mouseenter", function () {
            $("#spnSalestatTitle").removeClass().addClass("tag_select_open");
            $("#ulSalestat").show();
        }).bind("mouseleave", function () {
            $("#spnSalestatTitle").removeClass().addClass("tag_select");
            $("#ulSalestat").hide();
        });
    }
 
//筛选条件显示
function changeConditionTipsDiv() {
    var conditionDivShow = false;
    var html = "";
    $("#conditionDiv_tip").empty();
    //keyword
    if (searchHouseInfo.keyword != "") {
        $('#keyword').val(searchHouseInfo.keyword);
    } else {
        $('#keyword').val('');
    }
	//区域
	if ( searchHouseInfo.district != undefined &&  searchHouseInfo.district != "") {
        html = '<a class="xzjg" name="cleardistrict">' + searchInfo.district + '</a>';
        $("#conditionDiv_tip").append(html);
        conditionDivShow = true;		
        $('a[name="cleardistrict"]').bind("click", function () {
            map.centerAndZoom(new BMap.Point(mapInfo.px, mapInfo.py), mapInfo.initZoom);
            searchInfo.district = "";searchHouseInfo.district = "";
            changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
           
        });
    } else {
        $("#spnDistrictTitle").html("区域");
    }
	 
	//商圈	
	if (searchHouseInfo.shangquan != undefined && searchHouseInfo.shangquan != "") {
		html = '<a class="xzjg" name="clearshangquan">' + searchInfo.shangquan + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearshangquan"]').bind("click", function () {
			searchInfo.shangquan = "";
			searchHouseInfo.shangquan = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnShangquanTitle").html("商圈");
	}
	 
	//物业类型
	if (searchHouseInfo.purpose != undefined && searchHouseInfo.purpose != "") {
		html = '<a class="xzjg" name="clearpurpose">' + searchInfo.purpose + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearpurpose"]').bind("click", function () {
			searchInfo.purpose = "";
			searchHouseInfo.purpose = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnPurposeTitle").html("物业类型");
	}
	
	
	
	//楼盘特色
	if (searchHouseInfo.loupantese != undefined && searchHouseInfo.loupantese != "") {
		html = '<a class="xzjg" name="clearloupantese">' + searchInfo.loupantese + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearloupantese"]').bind("click", function () {
			searchInfo.loupantese = "";
			searchHouseInfo.loupantese = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnLoupanteseTitle").html("楼盘特色");
	}
	
	//楼层
	if (searchHouseInfo.louceng != undefined && searchHouseInfo.louceng != "") {
		html = '<a class="xzjg" name="clearlouceng">' + searchInfo.louceng + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearlouceng"]').bind("click", function () {
			searchInfo.louceng = "";
			searchHouseInfo.louceng = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnLoucengTitle").html("楼层");
	}
	
	//环线
	if (searchHouseInfo.huanxian != undefined && searchHouseInfo.huanxian != "") {
		html = '<a class="xzjg" name="clearhuanxian">' + searchInfo.huanxian + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearhuanxian"]').bind("click", function () {
			searchInfo.huanxian = "";
			searchHouseInfo.huanxian = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnHuanxianTitle").html("环线");
	}
	
	//地铁
	if (searchHouseInfo.ditie != undefined && searchHouseInfo.ditie != "") {
		html = '<a class="xzjg" name="clearditie">' + searchInfo.ditie + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearditie"]').bind("click", function () {
			searchInfo.ditie = "";
			searchHouseInfo.ditie = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnDitieTitle").html("地铁");
	}
	
	//地铁站点
	if (searchHouseInfo.ditiezhandian != undefined && searchHouseInfo.ditiezhandian != "") {
		html = '<a class="xzjg" name="clearditiezhandian">' + searchInfo.ditiezhandian + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearditiezhandian"]').bind("click", function () {
			searchInfo.ditiezhandian = "";
			searchHouseInfo.ditiezhandian = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnDitiezhandianTitle").html("地铁站点");
	}	
    
	//价格
	if (searchHouseInfo.price != undefined && searchHouseInfo.price != "") {
        html = '<a class="xzjg" name="clearprice">' + searchInfo.price + '</a>';
        $("#conditionDiv_tip").append(html);
        conditionDivShow = true;
        $('a[name="clearprice"]').bind("click", function () {
            searchInfo.price = "";
            searchHouseInfo.price = "";
            changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
        });
    } else {
        $("#spnPriceTitle").html("价格");
    }

    //销售状态
	if (searchHouseInfo.salestat != undefined && searchHouseInfo.salestat != "") {
		html = '<a class="xzjg" name="clearsalestat">' + searchInfo.salestat + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearsalestat"]').bind("click", function () {
			searchInfo.salestat = "";
			searchHouseInfo.salestat = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnSalestatTitle").html("销售状态");
	}
	
    if (!conditionDivShow) {
        $("#conditionDiv").hide();
        //动态判定左侧列表的高度
        leftbarheight = $(window).height() - 180;
        $("#resultcontainer").css({
            "height": leftbarheight
        });
    } else {
        $("#conditionDiv").show();
        //动态判定左侧列表的高度
        leftbarheight = $(window).height() - 180 - $("#conditionDiv").height() - 11;
        $("#resultcontainer").css({
            "height": leftbarheight
        });
    }
}

//地区楼盘数量显示
function getDistrictsPoint(){
    var url = CMS_ABS + uri2MVC("ajax/newmap/entry/DistrictPoint/type/"+mapInfo.maptype+'/');
    $.getJSON(url,function(data){
        if(data.project && 0<data.project.length){
            var project=data.project;  
            for(var i=0;i<project.length;i++){
                 var html='<div class="qp01" district="'+project[i].index+'" districtname="'+project[i].name+'"><a class="noatag"><div class="s1"><em><i class="arrow"></i>'+ project[i].name +'<span>|'+project[i].count+'套</span></em></div></a></div>'; 
                 var point = new BMap.Point(project[i].px,project[i].py);   
                 var mySquare = new SquareOverlay(point, 100,html,1,"","",project[i].px,project[i].py,project[i].name,"","");
                 map.addOverlay(mySquare);
                 mySquare.addEventListener("mouseover", function (){
					 $(this).find("div").first().removeClass().addClass("qp02");
					 this.style.zIndex =100;
                 });
				 mySquare.addEventListener("mouseout", function (){
					 $(this).find("div").first().removeClass().addClass("qp01");
					 this.style.zIndex =-1;
                 });
                 //点击事务	 
                 mySquare.addEventListener("click", function (){
					 var districtname=$(this).find("div").first().attr("districtname");
					 var district=$(this).find("div").first().attr("district");
					 $("#spnDistrictTitle").html(districtname).attr("district",district);
					 searchHouseInfo.district = district;searchInfo.district = districtname;
                     searchHouseInfo.keyword = '';searchInfo.keyword = '';
					 changeConditionTipsDiv();
                     getHousePoint();
                 });
                 districtMarkers.push(mySquare);		 
              }
              //showHouseData();//初始化展示左侧House信息列表
              getHousePoint();
        }   
    });
}

//左侧信息列表展示方式
function changeListShow() {
        $("#projListDiv").hide();
        $("#house_transitListDiv").show();
        $("#houseListDiv").show();
}

//100下翻小区
function setMoreProjStatus(allcount){
    if(allcount>mapInfo.ViewVolume){
        if(searchHouseInfo.projpageindex<Math.ceil(allcount/mapInfo.ViewVolume)){
            $("#projturndiv").show();
            $("#closeprojturndiv").show();
            $("#lakuangdiv").css({top:45});
            $("#ViewVolume").html(mapInfo.ViewVolume);
            $("#change100proj").html("换一批");
            $("#closeprojturndiv").bind("click",function (){
            $("#projturndiv").hide();
            $("#closeprojturndiv").hide();
           // $("#lakuangdiv").css({top:15});
            });
            $("#change100proj").unbind().bind("click",function(){
                searchHouseInfo.projpageindex=searchHouseInfo.projpageindex+1;
                getProjectPoint();
				showProjectData(0,10);
            });
        }else{
            $("#projturndiv").show();
            $("#closeprojturndiv").show();
            $("#lakuangdiv").css({top:45});
            $("#change100proj").html("返回");
            $("#closeprojturndiv").bind("click",function (){
            $("#projturndiv").hide();
            $("#closeprojturndiv").hide();
            //$("#lakuangdiv").css({top:15});
            });
            $("#change100proj").unbind().bind("click",function(){
                searchHouseInfo.projpageindex=1;
                getProjectPoint();
				showProjectData(0,10);
            });
        }
    }else{
        $("#projturndiv").hide();
        $("#closeprojturndiv").hide();
        //$("#lakuangdiv").css({top:15});
    }
}

//左侧信息列表
function showProjectData(start,end) {
    projectMarkers = [];
	setMoreProjStatus(projectInfo.allcount);//翻下100个小区	
    if(projectInfo.allcount>0){
        $('#no_search_result').hide();
        $('#have_search_result').show();
        var project = projectInfo.project;      
        var lcon = '';len = project.length;		
        for(var i=0;i<len;i++){
            //if(i>=start&&i<end){
                //左边列表
                lcon +='<div class="seajgtd" markerid='+i+' projcode="'+project[i].projcode+'"><ul><li><strong ><a href="'+project[i].url+'" target="_blank">'+project[i].projname+'</a></strong><span class="salestate00 salestate0'+project[i].salepic+'" >'+project[i].salestat+'</span><em>['+project[i].purpose+']</em></li><li><span>销售均价：<strong class="orange">'+(0==project[i].price ? '待定' : project[i].price)+'</strong>'+(0==project[i].price ? '' : '元/平方米')+'</span></li><li>销售电话：'+project[i].tel+'</li><li>楼盘地址：'+project[i].address+'</li></ul><div class="acqs"><div ></div></div></div>';
                //右边地图展示
                var html='<div class="qp00 qp0'+project[i].salepic+'" salestat="'+project[i].salepic+'" projcode="'+project[i].projcode+'" markerid='+i+' projname="'+project[i].projname+'" ><a class="noatag"><div class="s1"><em><i class="arrow"></i>'+ project[i].projname +'<span style="display:none;">|'+(0==project[i].price ? '均价待定':project[i].price+'元/平方米' )+'</span></em></div></a></div>'; 
                var point = new BMap.Point(project[i].px,project[i].py);
                var mySquare = new SquareOverlay(point,100,html,1,project[i].purpose,project[i].projcode,project[i].px,project[i].py,project[i].projname,project[i].address,project[i].addresslong);
                map.addOverlay(mySquare);
                var overrideMouseOut=function (){
						 	$(this).find("div").first().addClass("qp0"+$(this).find("div").first().attr('salestat'));
                	 		$(this).find("span").first().css('display','none');
                     		this.style.zIndex =-1;			 
                };
                var overrideMouseOver=function (){				
                     $(this).find("div").first().removeClass("qp0"+$(this).find("div").first().attr('salestat'));
                	 $(this).find("span").first().css('display','inline');
                     this.style.zIndex =100;
                };
                mySquare.addEventListener("mouseover",overrideMouseOver);
                mySquare.addEventListener("mouseout", overrideMouseOut);
           // }
			/*
			else{
                //右边地图展示
                var html = '';
                var html = '<div class="smallmarker" markerid='+i+' projcode="'+project[i].projcode+'" projname="'+project[i].projname+'"><a class="noatag"><div class="sopenk" style="display: none;" >'+project[i].projname+'|'+project[i].price+'元/平方米</div><div class="sqipo" onmouseover="this.className=\'sqipoa\'" onmouseout="this.className=\'sqipo\'"></div></a></div>';
                var point = new BMap.Point(project[i].px,project[i].py);
                var mySquare = new SquareOverlay(point,100,html,1,project[i].purpose,project[i].projcode,project[i].px,project[i].py,project[i].projname,project[i].address,project[i].addresslong);
                var overrideMouseOver = function(){
                    $(this).find('.sopenk').show();
                }
                var overrideMouseOut = function(){
                    $(this).find('.sopenk').hide();
                }
                map.addOverlay(mySquare);
                mySquare.addEventListener("mouseover", overrideMouseOver);
                mySquare.addEventListener("mouseout", overrideMouseOut);
            }
			*/
                //左边列表                
                $("#house_result_wrap").html(lcon);
                $("#search_result .seajgtd").bind('mouseover',function(){
                    var projcode = $(this).addClass('active bj').attr('projcode');	
					var obj = $('#'+projcode+'_container');				
                    $('#'+projcode+'_container').css('z-index',100).find('div').first().removeClass('qp0'+obj.find('div').first().attr('salestat'));
                }).bind('mouseout',function(){
                    var projcode = $(this).removeClass('active bj').attr('projcode');
                    var obj = $('#'+projcode+'_container');
                    obj.css('z-index',1).find('div').first().addClass('qp0'+obj.find('div').first().attr('salestat')); 
                }).bind('click',function(){
                    var markerid = $(this).attr('markerid');
                    var projcode = $(this).attr('projcode');
                    var project = projectInfo['project'][markerid];
                    var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a><span class="salestate00 salestate0'+project.salepic+'" >'+project.salestat+'</span></div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="170"></a></div><div class="sr"><ul><li class="marb5">均价：<em>'+(0==project.price?'待定':project.price)+'</em>'+(0==project.price?'':'元/平方米')+'</li><li>销售电话：<lable>'+project.tel+'</lable></li><li>开盘日期：'+project.time+'</li><li>物业类型：'+project.purpose+'</li><li> 开 发 商：'+project.kfs+'</li><li>楼盘地址：'+project.address+'</li><li><a href="'+project.url+'">查看楼盘详情</a></li></ul></div><div class="clear"></div></div><div class="newqipaonr02">[<a href="'+project.url1+'" target="_blank">楼盘动态</a>][<a href="'+project.url2+'" target="_blank">历史价格</a>][<a href="'+project.url3+'" target="_blank">楼盘图库</a>][<a href="'+project.url4+'" target="_blank">户型图</a>][<a href="'+project.url5+'" target="_blank">楼盘点评</a>][<a href="'+ CMS_ABS +'index.php?caid=516&lpid='+ project.projcode +'" target="_blank">楼盘问答</a>]</div></div><div class="jt"></div></div>';
                    $('#maptip').html(html).show();
                    map.panTo(new BMap.Point(project.px, project.py));
                    setHistoryCookie(projcode);
                });
                changeListShow();
                //右边地图展示
                var overrideClick = function(){
                                    var markerid = $(this).find('div').first().attr('markerid');
                                    var project = projectInfo['project'][markerid];
                                    var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a><span class="salestate00 salestate0'+project.salepic+'" >'+project.salestat+'</span></div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="170"></a></div><div class="sr"><ul><li class="marb5">均价：<em>'+(0==project.price?'待定':project.price)+'</em>'+(0==project.price?'':'元/平方米')+'</li><li>销售电话：<lable>'+project.tel+'</lable></li><li>开盘日期：'+project.time+'</li><li>物业类型：'+project.purpose+'</li><li> 开 发 商：'+project.kfs+'</li><li>楼盘地址：'+project.address+'</li><li><a href="'+project.url+'">查看楼盘详情</a></li></ul></div><div class="clear"></div></div><div class="newqipaonr02">[<a href="'+project.url1+'" target="_blank">楼盘动态</a>][<a href="'+project.url2+'" target="_blank">历史价格</a>][<a href="'+project.url3+'" target="_blank">楼盘图库</a>][<a href="'+project.url4+'" target="_blank">户型图</a>][<a href="'+project.url5+'" target="_blank">楼盘点评</a>][<a href="'+ CMS_ABS +'index.php?caid=516&lpid='+ project.projcode +'" target="_blank">楼盘问答</a>]</div></div><div class="jt"></div></div>';
                                    $('#maptip').html(html).show();
                                    var projcode = $(this).find('div').first().attr('projcode');
                                    setHistoryCookie(projcode);                                   
                                }
                mySquare.addEventListener("click", overrideClick);       
                projectMarkers.push(mySquare); 
        }
   }else{
        $('#have_search_result').hide();
        $('#no_search_result').show();
   }
	changescreenWandH();
}

function setHistoryCookie(projcode){
    if(null == $.cookie('08MapHistory')) $.cookie('08MapHistory',',');
    if($.cookie('08MapHistory').indexOf(','+projcode+',') > -1)  return;
    if($.cookie('08MapHistory').match(/,/g).length >= 11){
        $.cookie('08MapHistory',$.cookie('08MapHistory').replace(/,\d+,$/,','));  
    }
    $.cookie('08MapHistory',','+projcode+$.cookie('08MapHistory'));
}

function deletehHistoryCookie(projcode){
    if(projcode){
        if($.cookie('08MapHistory').indexOf(','+projcode+',') > -1){
            var reg = new RegExp(','+projcode+',','g');
            $.cookie('08MapHistory',$.cookie('08MapHistory').replace(reg,','));
            $('#total_history_count').html($('#total_history_count').html() - 1);
        }
    }else{        
        $.cookie('08MapHistory',null);
        $('#total_history_count').html(0);  
    }
}

//清除楼盘数据
function removeProjectData(){
    for(var i=0;i<projectMarkers.length;i++){map.removeOverlay(projectMarkers[i]);}
    projectMarkers=[];
    projectInfo = {};
}

//清除游览历史楼盘数据
function removehistoryProjectData(markerid){
    if(markerid){
        map.removeOverlay(historyProjectMarkers[markerid]);
        historyProjectMarkers[markerid] = null;
        historyProjectInfo.project[markerid] = null;
    }else{
        for(var i=0;i<historyProjectMarkers.length;i++){map.removeOverlay(historyProjectMarkers[i]);}
        historyProjectMarkers=[];
        historyProjectInfo = {};
    }

    
}


//楼盘数据
function getProjectPoint(){
    removeProjectData();
    var bounds = map.getBounds(); 
	var sw = bounds.getSouthWest();
	var ne = bounds.getNorthEast();
    searchHouseInfo.x1=sw.lng;
    searchHouseInfo.y1=sw.lat;
    searchHouseInfo.x2=ne.lng;
    searchHouseInfo.y2=ne.lat;
	//var urlParam = 'order/'+escape(searchHouseInfo.order)+'/type/'+escape(mapInfo.maptype)+'/purpose/'+escape(searchHouseInfo.purpose)+'/price/'+escape(searchHouseInfo.price)+'/shangquan/'+escape(searchHouseInfo.shangquan)+'/salestat/'+escape(searchHouseInfo.salestat)+'/district/'+escape(searchHouseInfo.district)+'/x1/'+ escape(searchHouseInfo.x1) + '/x2/' + escape(searchHouseInfo.x2) + '/y1/' + escape(searchHouseInfo.y1) + '/y2/' + escape(searchHouseInfo.y2) + '/page/' + escape(searchHouseInfo.projpageindex)+'/keyword/'+escape(searchHouseInfo.keyword)+'/';
	var urlParam = 'order/'+escape(searchHouseInfo.order)+'/type/'+escape(mapInfo.maptype)+'/x1/'+ escape(searchHouseInfo.x1) + '/x2/' + escape(searchHouseInfo.x2) + '/y1/' + escape(searchHouseInfo.y1) + '/y2/' + escape(searchHouseInfo.y2) + '/page/' + escape(searchHouseInfo.projpageindex)+'/keyword/'+encodeURIComponent(searchHouseInfo.keyword); //escape
	//+'/purpose/'+escape(searchHouseInfo.purpose)+'/price/'+escape(searchHouseInfo.price)+'/shangquan/'+escape(searchHouseInfo.shangquan)+'/salestat/'+escape(searchHouseInfo.salestat)
	if(Conditions.district!=undefined) {
		if(searchHouseInfo.district != undefined) urlParam += '/district/'+escape(searchHouseInfo.district);
		if(Conditions.district.coid2!=undefined && searchHouseInfo.shangquan != undefined)urlParam += '/shangquan/'+escape(searchHouseInfo.shangquan);
	}
	if(Conditions.purpose!=undefined && searchHouseInfo.purpose!=undefined) urlParam += '/purpose/'+escape(searchHouseInfo.purpose);
	if(Conditions.price!=undefined && searchHouseInfo.price != undefined) urlParam += '/price/'+escape(searchHouseInfo.price);
	if(Conditions.salestat!=undefined && searchHouseInfo.salestat!=undefined)  urlParam += '/salestat/'+escape(searchHouseInfo.salestat);
	if(Conditions.loupantese!=undefined && searchHouseInfo.loupantese!=undefined) urlParam += '/loupantese/'+escape(searchHouseInfo.loupantese);
	if(Conditions.louceng!=undefined && searchHouseInfo.louceng!=undefined) urlParam += '/louceng/'+escape(searchHouseInfo.louceng);
	if(Conditions.zhuangxiuchengdu!=undefined && searchHouseInfo.zhuangxiuchengdu!=undefined) urlParam += '/zhuangxiuchengdu/'+escape(searchHouseInfo.zhuangxiuchengdu);
	if(Conditions.huanxian!=undefined  && searchHouseInfo.huanxian != undefined) urlParam += '/huanxian/'+escape(searchHouseInfo.huanxian);
	if(Conditions.ditie!=undefined){
		if(searchHouseInfo.ditie !=undefined) urlParam += '/ditie/'+escape(searchHouseInfo.ditie);
		if(Conditions.ditie.coid14!=undefined && searchHouseInfo.ditiezhandian!=undefined)  urlParam += '/ditiezhandian/'+escape(searchHouseInfo.ditiezhandian);
	}
	var url = CMS_ABS + uri2MVC('ajax/newmap/entry/CommunityPointData/'+urlParam+'/');
    $.ajax({
        type:'get',
        async:false,
        cache:false,
        url:url,
        dataType:'json',
        beforeSend: function(){
          $('#total_count').html('努力查找中...');
        },
        success:function(data){
		       projectInfo = data;
               $("#total_count").html('共找到<em>'+(projectInfo.allcount?projectInfo.allcount:0)+'</em>个楼盘');
        }
    });
}

//游览历史楼盘数据
function getHistoryProjectPoint(HistoryProject){
    removeProjectData();
    var bounds = map.getBounds(); 
	var sw = bounds.getSouthWest();
	var ne = bounds.getNorthEast();
    searchHouseInfo.x1=sw.lng;
    searchHouseInfo.y1=sw.lat;
    searchHouseInfo.x2=ne.lng;
    searchHouseInfo.y2=ne.lat;
	var urlParam = 'type/'+escape(mapInfo.maptype)+'/HistoryProject/'+escape(HistoryProject)+'/';
	var url = CMS_ABS + uri2MVC('ajax/newmap/entry/history/'+urlParam);
    $.ajax({
        type:'get',
        async:false,
        cache:false,
        url:url,
        dataType:'json',
        success:function(data){
               if(data){
                historyProjectInfo = data;
                $("#total_history_count").html(data.allcount);
               }    
        }
    });
}

//排序事件绑定
function sort(){
        $('.lstitle').children().bind('click',function(){
                $('.lstitle').children().removeClass('s1').addClass('s2');
                $(this).removeClass('s2').addClass('s1');
                if(this.id!='sort_default'){
                    var valueString = $(this).attr('value');
                    var lastWord = valueString.substr(-1,1);
                    var nowValue = valueString.substr(0,valueString.length-1);
                    if(lastWord=='0'){
                        nowValue += '1';
                        $(this).find('img').first().attr('src',tplurl+'newmap/images/icon05b.gif');
                    }else{
                        nowValue += '0';
                        $(this).find('img').first().attr('src',tplurl+'newmap/images/icon05a.gif');
                    }
                    $(this).siblings().each(function(i){                        
                            var siblingValueString = $(this).attr('value');
                                if(0!==i){
                                    var siblingValueString = siblingValueString.substr(0,siblingValueString.length-1); 
                                    $(this).attr('value',siblingValueString+'0');
                                    $(this).find('img').first().attr('src',tplurl+'newmap/images/icon05.gif');
                                }
                        });
                    $(this).attr('value',nowValue);                    
                    $(this).parent().attr('value',nowValue);
                    searchHouseInfo.order = searchInfo.order = nowValue;
                }else{
                    var siblings= $(this).siblings();
                        siblings.each(function(i){
                            var valueString = $(this).attr('value');                            
                            var nowValue = valueString.substr(0,valueString.length-1); 
                            $(this).attr('value',nowValue+'0');
                            $(this).find('img').first().attr('src',tplurl+'newmap/images/icon05.gif');
                        });
                    $(this).parent().attr('value','0%0');
                     searchHouseInfo.order = searchInfo.order = '0%0';
                }
                  getProjectPoint();
                  showProjectData(0,10);        
            });
}

//游览历史数据显示
function historyProjectShow(){
    if(historyProjectInfo.allcount>0){
        var con = '';
        var html = '';
        var project = historyProjectInfo.project;
        var len = project.length;
        historyProjectMarkers = [];
        for(var i=0;i<len;i++){
            con += '<div class="lhistory" markerid="'+i+'" projcode="'+project[i].projcode+'" onmouseover="this.className=\'lhistory bj\';" onmouseout="this.className=\'lhistory\';"><span><img src="'+tplurl+'newmap/images/close.gif" alt="删" width="10" height="10"></span><div><strong class="limitawid"><a title="'+project[i].projname+'">'+project[i].projname+'</a></strong><span class="salestate00 salestate0'+project[i].salepic+'" >'+project[i].salestat+'</span><em>['+project[i].purpose+']</em></div></div>';
            html = '<div class="qp00 qp0'+project[i].salepic+'" salestat="'+project[i].salepic+'" projcode="'+project[i].projcode+'" markerid='+i+' projname="'+project[i].projname+'" ><a class="noatag"><div class="s1"><em><i class="arrow"></i>'+ project[i].projname +'<span style="display:none;">|'+project[i].price+'元/平方米</span></em></div></a></div>'; 
            var point = new BMap.Point(project[i].px,project[i].py);
            var mySquare = new SquareOverlay(point,100,html,1,project[i].purpose,project[i].projcode,project[i].px,project[i].py,project[i].projname,project[i].address,project[i].addresslong);
            map.addOverlay(mySquare);
              var overrideMouseOut=function (){
						 	$(this).find("div").first().addClass("qp0"+$(this).find("div").first().attr('salestat'));
                	 		$(this).find("span").first().css('display','none');
                     		this.style.zIndex =-1;			 
                };
                var overrideMouseOver=function (){				
                     $(this).find("div").first().removeClass("qp0"+$(this).find("div").first().attr('salestat'));
                	 $(this).find("span").first().css('display','inline');
                     this.style.zIndex =100;
                };
            var overrideClick = function(){
                                    var markerid = $(this).find('div').first().attr('markerid');
                                    var project = historyProjectInfo['project'][markerid];
                                    var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a><span class="salestate00 salestate0'+project.salepic+'" >'+project.salestat+'</span></div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="170"></a></div><div class="sr"><ul><li class="marb5">均价：<em>'+project.price+'</em>元/平方米</li><li>销售电话：<lable>'+project.tel+'</lable></li><li>开盘日期：'+project.time+'</li><li>物业类型：'+project.purpose+'</li><li> 开 发 商：'+project.kfs+'</li><li>楼盘地址：'+project.address+'</li><li><a href="'+project.url+'">查看楼盘详情</a></li></ul></div><div class="clear"></div></div><div class="newqipaonr02">[<a href="'+project.url1+'" target="_blank">楼盘动态</a>][<a href="'+project.url2+'" target="_blank">历史价格</a>][<a href="'+project.url3+'" target="_blank">楼盘图库</a>][<a href="'+project.url4+'" target="_blank">户型图</a>][<a href="'+project.url5+'" target="_blank">楼盘点评</a>][<a href="'+ CMS_ABS +'index.php?caid=516&lpid='+ project.projcode +'" target="_blank">楼盘问答</a>]</div></div><div class="jt"></div></div>';
                                    $('#maptip').html(html).show();
                                    var projcode = $(this).find('div').first().attr('projcode');                                  
                                }                         
                mySquare.addEventListener("mouseover", overrideMouseOver);
                mySquare.addEventListener("mouseout", overrideMouseOut); 
                mySquare.addEventListener("click", overrideClick);
         historyProjectMarkers.push(mySquare);          
        }
        $('#browsing_history').html(con);        
        $('#browsing_history .lhistory').bind('mouseover',function(){
            var projcode = $(this).attr('projcode');
			var obj = $('#'+projcode+'_container');
            $('#'+projcode+'_container').css('z-index',100).find('div').first().removeClass('qp0'+obj.find('div').first().attr('salestat'));    
        }).bind('mouseout',function(){
            var projcode = $(this).attr('projcode');
            var obj = $('#'+projcode+'_container');
            obj.css('z-index',1).find('div').first().addClass('qp0'+obj.find('div').first().attr('salestat')); 
        }).bind('click',function(){
            var markerid = $(this).attr('markerid');
            var project = historyProjectInfo['project'][markerid];
            var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a><span class="salestate00 salestate0'+project.salepic+'" >'+project.salestat+'</span></div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="170"></a></div><div class="sr"><ul><li class="marb5">均价：<em>'+project.price+'</em>元/平方米</li><li>销售电话：<lable>'+project.tel+'</lable></li><li>开盘日期：'+project.time+'</li><li>物业类型：'+project.purpose+'</li><li> 开 发 商：'+project.kfs+'</li><li>楼盘地址：'+project.address+'</li><li><a href="'+project.url+'">查看楼盘详情</a></li></ul></div><div class="clear"></div></div><div class="newqipaonr02">[<a href="'+project.url1+'" target="_blank">楼盘动态</a>][<a href="'+project.url2+'" target="_blank">历史价格</a>][<a href="'+project.url3+'" target="_blank">楼盘图库</a>][<a href="'+project.url4+'" target="_blank">户型图</a>][<a href="'+project.url5+'" target="_blank">楼盘点评</a>][<a href="'+ CMS_ABS +'index.php?caid=516&lpid='+ project.projcode +'" target="_blank">楼盘问答</a>]</div></div><div class="jt"></div></div>';
            $('#maptip').html(html).show();
            map.panTo(new BMap.Point(project.px, project.py));
                     
        });
        $('#browsing_history .lhistory span').bind('click',function(event){
            event.stopPropagation();
            var obj = $(this).parent().hide();
            var projcode = obj.attr('projcode');
            var markerid = obj.attr('markerid');         
            deletehHistoryCookie(projcode);
            removehistoryProjectData(markerid);
            $('#maptip').hide();
        });
        $('#clearAllHistoryProject').bind('click',function(){            
            deletehHistoryCookie();
            $('#browsing_history').children().hide();
            removehistoryProjectData();
            $('#maptip').hide();
        });  
    }
}

//搜索结果，游览历史切换事务
function menuChange(){
    var divs = $('#ltitle1').find('div');
        divs.each(function(i){
            $(this).bind('click',function(){
                $(this).removeClass('s2').addClass('s1');
                var siblings = $(this).parent().siblings();
                    siblings.each(function(){
                        $(this).find('div').removeClass('s1').addClass('s2');
                    });
                if(0==i){
                    $('#historyshow').hide();
                    $('#searchResultShow').show();
                    getProjectPoint();
                    showProjectData(0,10);
                    $('#maptip').hide();
                }else if(1==i){
                    removeProjectData();//清除楼盘数据
                    $('#searchResultShow').hide();
                    $('#historyshow').show();                    
                    if(null!==$.cookie('08MapHistory')){                        
                        getHistoryProjectPoint($.cookie('08MapHistory'));//获取历史楼盘数据
                        historyProjectShow();//游览历史数据显示
                        $('#maptip').hide();
                    }
                }
            });
        });
    
}

//模块初始化 by louis
function Init(){
    InitLoupanConditions();
     getProjectPoint();
     showProjectData(0,10);
     sort();//绑定排序
     menuChange();//搜索结果,游览历史切换
}
    return {Init:Init};
}

function closeMapInfoDiv() {
    $("#maptip").fadeOut(500);         
}


