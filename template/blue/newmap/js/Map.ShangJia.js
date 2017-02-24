var Conditions,//筛选条件
    projectMarkers=[],//Markers
    projectInfo={},//列表信息
    districtMarkers=[],//地区Markers
    districtAreaInfo=[],//单独地区信息数据
    historyProjectInfo={},//游览历史数据
    historyProjectMarkers=[],//游览历史Markers
    districtAreaMarkers=[];//单独地区Marker
	

//动态判定地图各部分宽高
function changescreenWandH() {
	//动态判断搜索条件是否显示
	if (typeof conditionDivShow!= 'undefined' && !conditionDivShow) {
        $("#conditionDiv").hide()
    } else {		
        $("#conditionDiv").show();
    }
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
	
	var lconnr1Height = $(window).height() - $('#search_result').offset().top;
   $('.lconnr1').css({height:lconnr1Height});
}

$(function(){
    initMap();//初始地图界面
	//动态监听浏览器宽高变化，初始化各部分宽高
    $(window).resize(function () {
        changescreenWandH();
    });    
    //模块初始化
    var Control= new MapInitControl();
        Control.Init();   
});

/***************************模块地图函数库*******************************/

function MapInitControl(){
    //楼盘初始筛选条件 by louis
    function InitConditions(){
		 var url = CMS_ABS + uri2MVC("ajax/newmap/entry/ConditionData/mode/mchid_12");
		 $.ajax({
			type:'get',
			async:false,
			cache:false,
			url:url,
			dataType:'json',			
			success:function(data){
				Conditions = data;
				InitDistrictControl();
				InitProductControl();
			}
		});
    }	
//keyword 搜索 by louis
window.SearchByKeyword = function (){
            var value = $("#keyword").val();
            if(value==mapInfo.defaultKeyword || value==''){alert('请输入小区进行搜索');return;}
            searchHouseInfo.keyword = value;searchInfo.keyword = value;
            searchHouseInfo.district = '';searchInfo.district = '';
            changeConditionTipsDiv();              
			getProjectPoint();
            showProjectData(0,10);
}
    //地区控件 louis
    function InitDistrictControl(){
		var content = Conditions.district;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divDistrict" class="select_box"><div id="spnDistrictTitle" District="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">地区</div></div>');
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
    //产品分类
    function InitProductControl(){
  		var content = Conditions.product;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divProduct" class="select_box"><div id="spnProductTitle" product="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">主营产品</div></div>');
		var container = $("#divProduct");
		var ul = $('<ul id="ulProduct" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="product" product="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		} 
        
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var product = $(this).find("a").attr("product");
            if(searchHouseInfo.product!=product){
               searchInfo.product = $(this).find("a").html();
			   searchHouseInfo.product = product;
			   searchHouseInfo.projpageindex = 1;
               $("#spnProductTitle").html(searchInfo.product).attr("product",searchHouseInfo.product);	
               changeConditionTipsDiv();
			   getProjectPoint();
               showProjectData(0,10);
            }
        });
        
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulProduct").css("display") == 'none') {$("#ulProduct").show();}else{$("#ulProduct").hide();}
        }).bind("mouseenter", function () {
            $("#spnProductTitle").removeClass().addClass("tag_select_open");
            $("#ulProduct").show();
        }).bind("mouseleave", function () {
            $("#spnProductTitle").removeClass().addClass("tag_select");
            $("#ulProduct").hide();
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
	if (searchHouseInfo.district != "") {
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
	//产品分类
	if (searchHouseInfo.product != "") {
		html = '<a class="xzjg" name="clearproduct">' + searchInfo.product + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearproduct"]').bind("click", function () {
			searchInfo.product = "";
			searchHouseInfo.product = "";
			changeConditionTipsDiv();
            getProjectPoint();
            showProjectData(0,10);
		});
	} else {
		$("#spnProductTitle").html("主营产品");
	}
	
	/*
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
	*/
	
}

//地区楼盘数量显示
function getDistrictsPoint(){
    var url = CMS_ABS + uri2MVC("ajax/newmap/entry/DistrictPoint/type/"+mapInfo.maptype+'/');
    $.getJSON(url,function(data){
        if(data.project && 0<data.project.length){
            var project=data.project;  
            for(var i=0;i<project.length;i++){
                 var html='<div class="qp00" district="'+project[i].index+'" districtname="'+project[i].name+'"><a class="noatag"><div class="s1"><em><i class="arrow"></i>'+ project[i].name +'<span>|'+project[i].count+'套</span></em></div></a></div>'; 
                 var point = new BMap.Point(project[i].px,project[i].py);   
                 var mySquare = new SquareOverlay(point, 100,html,1,"","",project[i].px,project[i].py,project[i].name,"","");
                 map.addOverlay(mySquare);
                 mySquare.addEventListener("mouseover", function (){
					 $(this).find("div").first().addClass("qp01");
					 this.style.zIndex =100;
                 });
				 mySquare.addEventListener("mouseout", function (){
					 $(this).find("div").first().removeClass("qp01");
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
	setMoreProjStatus(projectInfo.allcount);//翻下100个小区	
    projectMarkers = [];
    if(projectInfo.allcount>0){
        $('#no_search_result').hide();
        $('#have_search_result').show();
        var project = projectInfo.project;        
        var lcon = '';len = project.length;
        for(var i=0;i<len;i++){
            //if(i>=start&&i<end){
                //左边列表
                lcon +='<div class="seajgtd" markerid="'+i+'" projcode="'+project[i].projcode+'"><div class="fll" style="padding-right: 5px;"></div><ul><li><strong class="orange">'+project[i].projname+'</strong></li><li>联系电话：<strong class="orange">'+project[i].tel+'</strong></li><li>地址：'+project[i].address+'</li></ul></div>';
                //右边地图展示
                var html='<div class="qp00" projcode="'+project[i].projcode+'" markerid='+i+' projname="'+project[i].projname+'" ><a class="noatag"><div class="s1"><em><i class="arrow"></i>'+ project[i].projname +'</em></div></a></div>'; 
                var point = new BMap.Point(project[i].px,project[i].py);
                var mySquare = new SquareOverlay(point,100,html,1,project[i].product,project[i].projcode,project[i].px,project[i].py,project[i].projname,project[i].address,project[i].addresslong);
                
                map.addOverlay(mySquare);
                var overrideMouseOut=function (){
                     $(this).find("div").first().removeClass("qp01");
                     this.style.zIndex =-1;
                };
                var overrideMouseOver=function (){
                     $(this).find("div").first().addClass("qp01");
                     this.style.zIndex =100;
                };
                mySquare.addEventListener("mouseover", overrideMouseOver);
                mySquare.addEventListener("mouseout", overrideMouseOut);
            /*
			}else{
                //右边地图展示
                var html = '';
                var html = '<div class="smallmarker" markerid='+i+' projcode="'+project[i].projcode+'" projname="'+project[i].projname+'"><a class="noatag"><div class="sopenk" style="display: none;" >'+project[i].projname+'</div><div class="sqipo" onmouseover="this.className=\'sqipoa\'" onmouseout="this.className=\'sqipo\'"></div></a></div>';
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
                    $('#'+projcode+'_container').css('z-index',100).find('div').first().addClass('qp01');
                }).bind('mouseout',function(){
                    var projcode = $(this).removeClass('active bj').attr('projcode');
                    var obj = $('#'+projcode+'_container');
                    obj.css('z-index',1).find('div').first().removeClass('qp01'); 
                }).bind('click',function(){
                    var markerid = $(this).attr('markerid');
                    var projcode = $(this).attr('projcode');
                    var project = projectInfo['project'][markerid];
                    var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+$tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a> '+(1==project.vip ? '<i style="color:red;font-weight:bold">(VIP会员)</i>':'')+' </div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="140"></a></div><div class="sr"><ul><li class="marb5">主营产品：<strong class="orange">'+project.product+'</strong></li><li>联系人：'+project.conactor+'</li><li>联系电话：'+project.tel+'</li><li>地址：'+project.address+'</li><li><a href="'+project.url+'">查看商家详情</a></li></ul></div><div class="clear"></div></div></div><div class="jt"></div></div>';
                    $('#maptip').html(html).show();
                    map.panTo(new BMap.Point(project.px, project.py));
                    setHistoryCookie(projcode);
                });
                changeListShow();
                //右边地图展示
                var overrideClick = function(){
                                    var markerid = $(this).find('div').first().attr('markerid');
                                    var project = projectInfo['project'][markerid];
                                    var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+$tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a> '+(1==project.vip ? '<i style="color:red;font-weight:bold">(VIP会员)</i>':'')+' </div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="140"></a></div><div class="sr"><ul><li class="marb5">主营产品：<strong class="orange">'+project.product+'</strong></li><li>联系人：'+project.conactor+'</li><li>联系电话：'+project.tel+'</li><li>地址：'+project.address+'</li><li><a href="'+project.url+'">查看商家详情</a></li></ul></div><div class="clear"></div></div></div><div class="jt"></div></div>';
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
	var urlParam = 'type/'+escape(mapInfo.maptype)+'/product/'+escape(searchHouseInfo.product)+'/district/'+escape(searchHouseInfo.district)+'/x1/'+ escape(searchHouseInfo.x1) + '/x2/' + escape(searchHouseInfo.x2) + '/y1/' + escape(searchHouseInfo.y1) + '/y2/' + escape(searchHouseInfo.y2) + '/page/' + escape(searchHouseInfo.projpageindex)+'/keyword/'+escape(searchHouseInfo.keyword)+'/';
	var url = CMS_ABS + uri2MVC('ajax/newmap/entry/CommunityPointData/'+urlParam);
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
               $("#total_count").html('共找到<em>'+(projectInfo.allcount?projectInfo.allcount:0)+'</em>个商家');
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
                        $(this).find('img').first().attr('src',$tplurl+'newmap/images/icon05b.gif');
                    }else{
                        nowValue += '0';
                        $(this).find('img').first().attr('src',$tplurl+'newmap/images/icon05a.gif');
                    }
                    $(this).siblings().each(function(i){                        
                            var siblingValueString = $(this).attr('value');
                                if(0!==i){
                                    var siblingValueString = siblingValueString.substr(0,siblingValueString.length-1); 
                                    $(this).attr('value',siblingValueString+'0');
                                    $(this).find('img').first().attr('src',$tplurl+'newmap/images/icon05.gif');
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
                            $(this).find('img').first().attr('src',$tplurl+'newmap/images/icon05.gif');
                        });
                    $(this).parent().attr('value','0-0');
                     searchHouseInfo.order = searchInfo.order = '0-0';
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
            con += '<div class="lhistory" markerid="'+i+'" projcode="'+project[i].projcode+'" onmouseover="this.className=\'lhistory bj\';" onmouseout="this.className=\'lhistory\';"><span><img src="'+$tplurl+'newmap/images/close.gif" alt="删" width="10" height="10"></span><div><strong class="limitawid orange">'+project[i].projname+'</strong></div></div>';
            var html='<div class="qp00" projcode="'+project[i].projcode+'" markerid='+i+' projname="'+project[i].projname+'" ><a class="noatag"><div class="s1"><em><i class="arrow"></i>'+ project[i].projname +'</em></div></a></div>'; 
            var point = new BMap.Point(project[i].px,project[i].py);
            var mySquare = new SquareOverlay(point,100,html,1,project[i].purpose,project[i].projcode,project[i].px,project[i].py,project[i].projname,project[i].address,project[i].addresslong);
            map.addOverlay(mySquare);
            var overrideMouseOut=function (){
                     $(this).find("div").first().removeClass("qp01");                	 
                     this.style.zIndex =-1;
                };
            var overrideMouseOver=function (){
                     $(this).find("div").first().addClass("qp01");
                     this.style.zIndex =100;
                };
            var overrideClick = function(){
                                    var markerid = $(this).find('div').first().attr('markerid');
                                    var project = historyProjectInfo['project'][markerid];
                                    var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+$tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a> '+(1==project.vip ? '<i style="color:red;font-weight:bold">(VIP会员)</i>':'')+' </div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="140"></a></div><div class="sr"><ul><li class="marb5">主营产品：<strong class="orange">'+project.product+'</strong></li><li>联系人：'+project.conactor+'</li><li>联系电话：'+project.tel+'</li><li>地址：'+project.address+'</li><li><a href="'+project.url+'">查看商家详情</a></li></ul></div><div class="clear"></div></div></div><div class="jt"></div></div>';
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
            $('#'+projcode+'_container').css('z-index',100).find('div').first().addClass('qp01');    
        }).bind('mouseout',function(){
            var projcode = $(this).attr('projcode');
            var obj = $('#'+projcode+'_container');
            obj.css('z-index',1).find('div').first().removeClass('qp01'); 
        }).bind('click',function(){
            var markerid = $(this).attr('markerid');
            var project = historyProjectInfo['project'][markerid];
            var html = '<div class="openbox"><div class="openboxnr"><div class="title"><div class="close"><a onclick="closeMapInfoDiv();"><img src="'+$tplurl+'newmap/images/close.gif" width="10" height="10"></a></div><a href="'+project.url+'" target="_blank"><strong id="view_now_hs">'+project.projname+'</strong></a> '+(1==project.vip ? '<i style="color:red;font-weight:bold">(VIP会员)</i>':'')+' </div><div class="openboxnr01"><div class="sl"><a target="_blank" href="'+project.url+'"><img src="'+project.img+'" alt="'+project.projname+'" width="200" height="140"></a></div><div class="sr"><ul><li class="marb5">主营产品：<strong class="orange">'+project.product+'</strong></li><li>联系人：'+project.conactor+'</li><li>联系电话：'+project.tel+'</li><li>地址：'+project.address+'</li><li><a href="'+project.url+'">查看商家详情</a></li></ul></div><div class="clear"></div></div></div><div class="jt"></div></div>';
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
    InitConditions();
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


