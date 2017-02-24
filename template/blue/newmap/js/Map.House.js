var projectMarkers=[],//小区Markers
 districtMarkers=[],//地区Markers
 districtAreaInfo=[],//单独地区信息数据
 districtAreaMarkers=[];//单独地区Marker
/**************地图界面布局初始化**************/

//动态判定地图各部分宽高
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
    var resultcontainerheight =  $(window).height() - $('#resultcontainer').offset().top;
	
    $("#resultcontainer").css({
        "height": resultcontainerheight
    });
}

$(function () {
	initMap();//初始地图界面
	
    //changescreenWandH();//自适应高度
    	
    //动态监听浏览器宽高变化，初始化各部分宽高
    $(window).resize(function () {
        changescreenWandH();
    });    
   new MapInitControl().Init();//模块初始化 
});


/***************************房源(出售,出租)地图函数库*******************************/
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


//地图界面控件初始 by louis
function MapInitControl(){
	//发布人控件 by louis
	function InitPublisherControl(){
		var content = Conditions.publisher;
		if(content==undefined || !content.text) return;
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divPublisher" class="select_box"><div id="spnPublisherTitle" publisher="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">发布人</div></div>');
		var container = $("#divPublisher");
		var ul = $('<ul id="ulPublisher" class="tag_options" style="position: absolute; z-index: 999;display:none;">');
		var contentLength = content.text.length;		
		for(var i = 0; i < contentLength; i++){
			ddText = content.text[i];
			ddValue = content.value[i];
			ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="publisher" publisher="'+ddValue+'" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
		}
		//点击后的处理事务
        ul.find("li").bind("click", function () {
            var publisher = $(this).find("a").attr("publisher");
            if(searchHouseInfo.publisher!=publisher){
               searchInfo.publisher = $(this).find("a").html();
			   searchHouseInfo.publisher = publisher;
               $("#spnPublisherTitle").html(searchInfo.publisher).attr("publisher",searchHouseInfo.publisher);	
               changeConditionTipsDiv();
			   showHouseData();
            }	
        });
		//下拉效果
		container.append(ul).bind("click", function () {
            if ($("#ulPublisher").css("display") == 'none') {$("#ulPublisher").show();}else{$("#ulPublisher").hide();}
        }).bind("mouseenter", function () {
            $("#spnPublisherTitle").removeClass().addClass("tag_select_open");
            $("#ulPublisher").show();
        }).bind("mouseleave", function () {
            $("#spnPublisherTitle").removeClass().addClass("tag_select");
            $("#ulPublisher").hide();
        });
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
			   Conditions.shangquan =  (Conditions.district.coid2 && Conditions.district.coid2[searchHouseInfo.district]) ? Conditions.district.coid2[searchHouseInfo.district] : '';
			   InitShangquanControl(); 		   			 
               $("#spnDistrictTitle").html(searchInfo.district).attr("district",searchHouseInfo.district);	
               changeConditionTipsDiv();              
			   showHouseData();
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
		var contentLength = content.text.length;
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
               $("#spnShangquanTitle").html(searchInfo.shangquan).attr("shangquan",searchHouseInfo.shangquan);	
               changeConditionTipsDiv();              
			   showHouseData();
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
	
	//价格控件 by louis
    function InitPriceControl() {
        var content = Conditions.price;	
		if(content==undefined || !content.text) return;	
		var ddText = '',ddValue = '';
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divPrice" class="select_box"><div id="spnPriceTitle" price="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">价格</div></div>');
        var container = $("#divPrice");
        var ul = $('<ul id="ulPrice" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;
        for (var i = 0; i < contentLength; i++) {
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
                $("#spnPriceTitle").html(searchInfo.price).attr("price",searchHouseInfo.price);
                changeConditionTipsDiv();
    			showHouseData();
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
    //室控件 by louis
    function InitShiControl() {
            var content = Conditions.shi;
			if(content==undefined || !content.text) return;			
			$("#search_cond_select_div").append('<div class="selectqx"><div id="divShi" class="select_box"><div id="spnShiTitle" shi="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">室</div></div>');
            var container = $("#divShi");
			var ddText = '',ddValue = '';
            var ul = $('<ul id="ulShi" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
            var contentLength = content.text.length;
            for (var i = 0; i < contentLength; i++) {
                var ddText = content.text[i];
                var ddValue = content.value[i];        
                    ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="shi" shi="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
            }
			//点击后的处理事务		
            ul.find("li").bind("click", function () {
                var shi = $(this).find("a").attr("shi")
                if(searchHouseInfo.shi != shi){
    				searchInfo.shi = $(this).find("a").html();
    				searchHouseInfo.shi = shi;
                    $("#spnShiTitle").html(searchInfo.shi).attr("shi",searchHouseInfo.shi);
                    changeConditionTipsDiv();
    				showHouseData(); 
                }
            });
			//下拉效果
           container.append(ul).bind("click", function () {
                if ($("#ulShi").css("display") == 'none') {
                    $("#ulShi").show();
                } else {
                    $("#ulShi").hide();
                }
            }).bind("mouseenter", function () {
                $("#spnShiTitle").removeClass().addClass("tag_select_open");
                $("#ulShi").show();
            }).bind("mouseleave", function () {
                $("#spnShiTitle").removeClass().addClass("tag_select");
                $("#ulShi").hide();
            });
        }
	//厅控件 by louis
    function InitTingControl() {
            var content = Conditions.ting;
			if(content==undefined || !content.text) return;	
			$("#search_cond_select_div").append('<div class="selectqx"><div id="divTing" class="select_box"><div id="spnTingTitle" ting="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">厅</div></div>');
            var container = $("#divTing");
			var ddText = '',ddValue = '';
            var ul = $('<ul id="ulTing" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
            var contentLength = content.text.length;
            for (var i = 0; i < contentLength; i++) {
                var ddText = content.text[i];
                var ddValue = content.value[i];        
                    ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="ting" ting="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
            }
			//点击后的处理事务	
            ul.find("li").bind("click", function () {
                var ting = $(this).find("a").attr("ting")
                if(searchHouseInfo.ting != ting){
    				searchInfo.ting = $(this).find("a").html();
    				searchHouseInfo.ting = ting;
                    $("#spnTingTitle").html(searchInfo.ting).attr("ting",searchHouseInfo.ting);
                    changeConditionTipsDiv();
    				showHouseData(); 
                }
            });
			//下拉效果
           container.append(ul).bind("click", function () {
                if ($("#ulTing").css("display") == 'none') {
                    $("#ulTing").show();
                } else {
                    $("#ulTing").hide();
                }
            }).bind("mouseenter", function () {
                $("#spnTingTitle").removeClass().addClass("tag_select_open");
                $("#ulTing").show();
            }).bind("mouseleave", function () {
                $("#spnTingTitle").removeClass().addClass("tag_select");
                $("#ulTing").hide();
            });
        }
		
	//厨控件 by louis
    function InitChuControl() {
            var content = Conditions.chu;
			if(content==undefined || !content.text) return;			
			$("#search_cond_select_div").append('<div class="selectqx"><div id="divChu" class="select_box"><div id="spnChuTitle" chu="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">厨</div></div>');
            var container = $("#divChu");
			var ddText = '',ddValue = '';
            var ul = $('<ul id="ulChu" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
            var contentLength = content.text.length;
            for (var i = 0; i < contentLength; i++) {
                var ddText = content.text[i];
                var ddValue = content.value[i];        
                    ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="chu" chu="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
            }
			//点击后的处理事务		
            ul.find("li").bind("click", function () {
                var chu = $(this).find("a").attr("chu")
                if(searchHouseInfo.chu != chu){
    				searchInfo.chu = $(this).find("a").html();
    				searchHouseInfo.chu = chu;
                    $("#spnChuTitle").html(searchInfo.chu).attr("chu",searchHouseInfo.chu);
                    changeConditionTipsDiv();
    				showHouseData(); 
                }
            });
			//下拉效果
           container.append(ul).bind("click", function () {
                if ($("#ulChu").css("display") == 'none') {
                    $("#ulChu").show();
                } else {
                    $("#ulChu").hide();
                }
            }).bind("mouseenter", function () {
                $("#spnChuTitle").removeClass().addClass("tag_select_open");
                $("#ulChu").show();
            }).bind("mouseleave", function () {
                $("#spnChuTitle").removeClass().addClass("tag_select");
                $("#ulChu").hide();
            });
        }
	//卫控件 by louis
    function InitWeiControl() {
            var content = Conditions.wei;	
			if(content==undefined || !content.text) return;		
			$("#search_cond_select_div").append('<div class="selectqx"><div id="divWei" class="select_box"><div id="spnWeiTitle" wei="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">卫</div></div>');
            var container = $("#divWei");
			var ddText = '',ddValue = '';
            var ul = $('<ul id="ulWei" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
            var contentLength = content.text.length;
            for (var i = 0; i < contentLength; i++) {
                var ddText = content.text[i];
                var ddValue = content.value[i];        
                    ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="wei" wei="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
            }
			//点击后的处理事务		
            ul.find("li").bind("click", function () {
                var wei = $(this).find("a").attr("wei")
                if(searchHouseInfo.wei != wei){
    				searchInfo.wei = $(this).find("a").html();
    				searchHouseInfo.wei = wei;
                    $("#spnWeiTitle").html(searchInfo.wei).attr("wei",searchHouseInfo.wei);
                    changeConditionTipsDiv();
    				showHouseData(); 
                }
            });
			//下拉效果
           container.append(ul).bind("click", function () {
                if ($("#ulWei").css("display") == 'none') {
                    $("#ulWei").show();
                } else {
                    $("#ulWei").hide();
                }
            }).bind("mouseenter", function () {
                $("#spnWeiTitle").removeClass().addClass("tag_select_open");
                $("#ulWei").show();
            }).bind("mouseleave", function () {
                $("#spnWeiTitle").removeClass().addClass("tag_select");
                $("#ulWei").hide();
            });
        }
	//阳台控件 by louis
    function InitYangtaiControl() {
            var content = Conditions.yangtai;
			if(content==undefined || !content.text) return;			
			$("#search_cond_select_div").append('<div class="selectqx"><div id="divYangtai" class="select_box"><div id="spnYangtaiTitle" wei="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">阳台</div></div>');
            var container = $("#divYangtai");
			var ddText = '',ddValue = '';
            var ul = $('<ul id="ulYangtai" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
            var contentLength = content.text.length;
            for (var i = 0; i < contentLength; i++) {
                var ddText = content.text[i];
                var ddValue = content.value[i];        
                    ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="yangtai" yangtai="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
            }
			//点击后的处理事务		
            ul.find("li").bind("click", function () {
                var yangtai = $(this).find("a").attr("yangtai")
                if(searchHouseInfo.yangtai != yangtai){
    				searchInfo.yangtai = $(this).find("a").html();
    				searchHouseInfo.yangtai = yangtai;
                    $("#spnYangtaiTitle").html(searchInfo.yangtai).attr("yangtai",searchHouseInfo.yangtai);
                    changeConditionTipsDiv();
    				showHouseData(); 
                }
            });
			//下拉效果
           container.append(ul).bind("click", function () {
                if ($("#ulYangtai").css("display") == 'none') {
                    $("#ulWei").show();
                } else {
                    $("#ulWei").hide();
                }
            }).bind("mouseenter", function () {
                $("#spnYangtaiTitle").removeClass().addClass("tag_select_open");
                $("#ulYangtai").show();
            }).bind("mouseleave", function () {
                $("#spnYangtaiTitle").removeClass().addClass("tag_select");
                $("#ulYangtai").hide();
            });
        }
	//房屋配套控件 by louis
    function InitAreaControl() {
            var content = Conditions.area;
			if(content==undefined || !content.text) return;		
			$("#search_cond_select_div").append('<div class="selectqx"><div id="divArea" class="select_box"><div id="spnAreaTitle" area="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">面积</div></div>');
            var container = $("#divArea");
			var ddText = '',ddValue = '';
            var ul = $('<ul id="ulArea" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
            var contentLength = content.text.length;
            for (var i = 0; i < contentLength; i++) {
                var ddText = content.text[i];
                var ddValue = content.value[i];    
                    ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="area" area="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
            }
			//点击后的处理事务		
            ul.find("li").bind("click", function () {
                var area = $(this).find("a").attr("area")
                if(searchHouseInfo.area != area){
    				searchInfo.area = $(this).find("a").html();
    				searchHouseInfo.area = area;
                    $("#spnAreaTitle").html(searchInfo.area).attr("area",searchHouseInfo.area);
                    changeConditionTipsDiv();
    				showHouseData(); 
                }
            });
			//下拉效果
           container.append(ul).bind("click", function () {
                if ($("#ulArea").css("display") == 'none') {
                    $("#ulArea").show();
                } else {
                    $("#ulArea").hide();
                }
            }).bind("mouseenter", function () {
                $("#spnAreaTitle").removeClass().addClass("tag_select_open");
                $("#ulArea").show();
            }).bind("mouseleave", function () {
                $("#spnAreaTitle").removeClass().addClass("tag_select");
                $("#ulArea").hide();
            });
        }
	//房屋配套控件 by louis
    function InitFwptControl() {
            var content = Conditions.fwpt;
			if(content==undefined || !content.text) return;			
			$("#search_cond_select_div").append('<div class="selectqx"><div id="divFwpt" class="select_box"><div id="spnFwptTitle" fwpt="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">房屋配套</div></div>');
            var container = $("#divFwpt");
			var ddText = '',ddValue = '';
            var ul = $('<ul id="ulFwpt" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
            var contentLength = content.text.length;
            for (var i = 0; i < contentLength; i++) {
                var ddText = content.text[i];
                var ddValue = content.value[i];    
                    ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="fwpt" fwpt="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
            }
			//点击后的处理事务		
            ul.find("li").bind("click", function () {
                var fwpt = $(this).find("a").attr("fwpt")
                if(searchHouseInfo.fwpt != fwpt){
    				searchInfo.fwpt = $(this).find("a").html();
    				searchHouseInfo.fwpt = fwpt;
                    $("#spnFwptTitle").html(searchInfo.fwpt).attr("fwpt",searchHouseInfo.fwpt);
                    changeConditionTipsDiv();
    				showHouseData(); 
                }
            });
			//下拉效果
           container.append(ul).bind("click", function () {
                if ($("#ulFwpt").css("display") == 'none') {
                    $("#ulFwpt").show();
                } else {
                    $("#ulFwpt").hide();
                }
            }).bind("mouseenter", function () {
                $("#spnFwptTitle").removeClass().addClass("tag_select_open");
                $("#ulFwpt").show();
            }).bind("mouseleave", function () {
                $("#spnFwptTitle").removeClass().addClass("tag_select");
                $("#ulFwpt").hide();
            });
        }
   //房龄控件 by louis
    function InitFlControl() {
		var content = Conditions.fl;
		if(content==undefined || !content.text) return;			
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divFl" class="select_box"><div id="spnFlTitle" fl="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">房龄</div></div>');
        var container = $("#divFl");
		var ddText = '',ddValue = '';
        var ul = $('<ul id="ulFl" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;		
        for (var i = 0; i < contentLength ; i++) {
                ddText = content.text[i]; 
				ddValue = content.value[i];              
                ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="fl" fl="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
        }
		//点击后处理的事务
        ul.find("li").bind("click", function () {
            var fl = $(this).find("a").html();
            if(searchInfo.fl != fl){
              searchHouseInfo.fl = $(this).find("a").attr("fl");
			  searchInfo.fl = fl;
              $("#spnFlTitle").html(searchInfo.fl).attr("fl",searchHouseInfo.fl);
              changeConditionTipsDiv();
			  showHouseData();
            }
			
        });
		//下拉效果
        container.append(ul).bind("click", function () {
            if ($("#ulFl").css("display") == 'none') {
                $("#ulFl").show();
            } else {
                $("#ulFl").hide();
            }
        }).bind("mouseenter", function () {
            $("#spnFlTitle").removeClass().addClass("tag_select_open");
            $("#ulFl").show();
        }).bind("mouseleave", function () {
            $("#spnFlTitle").removeClass().addClass("tag_select");
            $("#ulFl").hide();
        });
    }
	//装修程度控件 by louis
    function InitZxcdControl() {
		var content = Conditions.zxcd;
		if(content==undefined || !content.text) return;		
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divZxcd" class="select_box"><div id="spnZxcdTitle" fl="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">装修程度</div></div>');
        var container = $("#divZxcd");
		var ddText = '',ddValue = '';
        var ul = $('<ul id="ulZxcd" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;		
        for (var i = 0; i < contentLength ; i++) {
                ddText = content.text[i]; 
				ddValue = content.value[i];              
                ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="zxcd" zxcd="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
        }
		//点击后处理的事务
        ul.find("li").bind("click", function () {
            var zxcd = $(this).find("a").html();
            if(searchInfo.zxcd != zxcd){
              searchHouseInfo.zxcd = $(this).find("a").attr("zxcd");
			  searchInfo.zxcd = zxcd;
              $("#spnZxcdTitle").html(searchInfo.zxcd).attr("zxcd",searchHouseInfo.zxcd);
              changeConditionTipsDiv();
			  showHouseData();
            }
			
        });
		//下拉效果
        container.append(ul).bind("click", function () {
            if ($("#ulZxcd").css("display") == 'none') {
                $("#ulZxcd").show();
            } else {
                $("#ulZxcd").hide();
            }
        }).bind("mouseenter", function () {
            $("#spnZxcdTitle").removeClass().addClass("tag_select_open");
            $("#ulZxcd").show();
        }).bind("mouseleave", function () {
            $("#spnZxcdTitle").removeClass().addClass("tag_select");
            $("#ulZxcd").hide();
        });
    }
	//房屋结构控件 by louis
    function InitFwjgControl() {
		var content = Conditions.fwjg;
		if(content==undefined || !content.text) return;			
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divFwjg" class="select_box"><div id="spnFwjgTitle" fwjg="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">房屋结构</div></div>');
        var container = $("#divFwjg");
		var ddText = '',ddValue = '';
        var ul = $('<ul id="ulFwjg" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;		
        for (var i = 0; i < contentLength ; i++) {
                ddText = content.text[i]; 
				ddValue = content.value[i];              
                ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="fwjg" fwjg="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
        }
		//点击后处理的事务
        ul.find("li").bind("click", function () {
            var fwjg = $(this).find("a").html();
            if(searchInfo.fwjg != fwjg){
              searchHouseInfo.fwjg = $(this).find("a").attr("fwjg");
			  searchInfo.fwjg = fwjg;
              $("#spnFwjgTitle").html(searchInfo.fwjg).attr("fwjg",searchHouseInfo.fwjg);
              changeConditionTipsDiv();
			  showHouseData();
            }
			
        });
		//下拉效果
        container.append(ul).bind("click", function () {
            if ($("#ulFwjg").css("display") == 'none') {
                $("#ulFwjg").show();
            } else {
                $("#ulFwjg").hide();
            }
        }).bind("mouseenter", function () {
            $("#spnFwjgTitle").removeClass().addClass("tag_select_open");
            $("#ulFwjg").show();
        }).bind("mouseleave", function () {
            $("#spnFwjgTitle").removeClass().addClass("tag_select");
            $("#ulFwjg").hide();
        });
    }
	//朝向控件 by louis
    function InitCxControl() {
		var content = Conditions.cx;
		if(content==undefined || !content.text) return;			
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divCx" class="select_box"><div id="spnCxTitle" cx="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">朝向</div></div>');
        var container = $("#divCx");
		var ddText = '',ddValue = '';
        var ul = $('<ul id="ulCx" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;		
        for (var i = 0; i < contentLength ; i++) {
                ddText = content.text[i]; 
				ddValue = content.value[i];              
                ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="cx" cx="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
        }
		//点击后处理的事务
        ul.find("li").bind("click", function () {
            var cx = $(this).find("a").html();
            if(searchInfo.cx != cx){
              searchHouseInfo.cx = $(this).find("a").attr("cx");
			  searchInfo.cx = cx;
              $("#spnCxTitle").html(searchInfo.cx).attr("cx",searchHouseInfo.cx);
              changeConditionTipsDiv();
			  showHouseData();
            }
			
        });
		//下拉效果
        container.append(ul).bind("click", function () {
            if ($("#ulCx").css("display") == 'none') {
                $("#ulCx").show();
            } else {
                $("#ulCx").hide();
            }
        }).bind("mouseenter", function () {
            $("#spnCxTitle").removeClass().addClass("tag_select_open");
            $("#ulCx").show();
        }).bind("mouseleave", function () {
            $("#spnCxTitle").removeClass().addClass("tag_select");
            $("#ulCx").hide();
        });
    }
	//朝向控件 by louis
    function InitFanglingControl() {
		var content = Conditions.fangling;
		if(content==undefined || !content.text) return;			
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divFangling" class="select_box"><div id="spnfanglingTitle" fangling="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">房龄区间</div></div>');
        var container = $("#divFangling");
		var ddText = '',ddValue = '';
        var ul = $('<ul id="ulFangling" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;		
        for (var i = 0; i < contentLength ; i++) {
                ddText = content.text[i]; 
				ddValue = content.value[i];              
                ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="fangling" fangling="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
        }
		//点击后处理的事务
        ul.find("li").bind("click", function () {
            var fangling = $(this).find("a").html();
            if(searchInfo.fangling != fangling){
              searchHouseInfo.fangling = $(this).find("a").attr("fangling");
			  searchInfo.fangling = fangling;
              $("#spnFanglingTitle").html(searchInfo.fangling).attr("fangling",searchHouseInfo.fangling);
              changeConditionTipsDiv();
			  showHouseData();
            }
			
        });
		//下拉效果
        container.append(ul).bind("click", function () {
            if ($("#ulFangling").css("display") == 'none') {
                $("#ulFangling").show();
            } else {
                $("#ulFangling").hide();
            }
        }).bind("mouseenter", function () {
            $("#spnFanglingTitle").removeClass().addClass("tag_select_open");
            $("#ulFangling").show();
        }).bind("mouseleave", function () {
            $("#spnFanglingTitle").removeClass().addClass("tag_select");
            $("#ulFangling").hide();
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
			   Conditions.ditiezhandian = Conditions.ditie.coid14[searchHouseInfo.ditie];
			   InitDitiezhandianControl(); 
               $("#spnDitieTitle").html(searchInfo.ditie).attr("ditie",searchHouseInfo.ditie);	
               changeConditionTipsDiv();             
			   showHouseData();
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
               $("#spnDitiezhandianTitle").html(searchInfo.ditiezhandian).attr("ditiezhandian",searchHouseInfo.ditiezhandian);
               changeConditionTipsDiv(); 
			   showHouseData();
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
	
	//租赁方式(出租) louis
    function InitZlfsControl() {
		var content = Conditions.zlfs;
		if(content==undefined || !content.text) return;		
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divZlfs" class="select_box"><div id="spnzlfsTitle" zlfs="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">租赁方式</div></div>');
        var container = $("#divZlfs");
		var ddText = '',ddValue = '';
        var ul = $('<ul id="ulZlfs" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;		
        for (var i = 0; i < contentLength ; i++) {
                ddText = content.text[i]; 
				ddValue = content.value[i];              
                ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="zlfs" zlfs="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
        }
		//点击后处理的事务
        ul.find("li").bind("click", function () {
            var zlfs = $(this).find("a").html();
            if(searchInfo.zlfs != zlfs){
              searchHouseInfo.zlfs = $(this).find("a").attr("zlfs");
			  searchInfo.zlfs = zlfs;
              $("#spnZlfsTitle").html(searchInfo.zlfs).attr("zlfs",searchHouseInfo.zlfs);
              changeConditionTipsDiv();
			  showHouseData();
            }
        });
		//下拉效果
        container.append(ul).bind("click", function () {
            if ($("#ulZlfs").css("display") == 'none') {
                $("#ulZlfs").show();
            } else {
                $("#ulZlfs").hide();
            }
        }).bind("mouseenter", function () {
            $("#spnZlfsTitle").removeClass().addClass("tag_select_open");
            $("#ulZlfs").show();
        }).bind("mouseleave", function () {
            $("#spnZlfsTitle").removeClass().addClass("tag_select");
            $("#ulZlfs").hide();
        });
    }
	
	//付款方式控件(出租) louis
    function InitFkfsControl() {
		var content = Conditions.fkfs;
		if(content==undefined || !content.text) return;			
		$("#search_cond_select_div").append('<div class="selectqx"><div id="divFkfs" class="select_box"><div id="spnFkfsTitle" fkfs="" class="tag_select" style="cursor: pointer;" onmouseover="this.className=\'tag_select_open\'" onmouseout="this.className=\'tag_select\'">付款方式</div></div>');
        var container = $("#divFkfs");
		var ddText = '',ddValue = '';
        var ul = $('<ul id="ulFkfs" class="tag_options" style="position: absolute; z-index: 999;display:none;"></ul>');
        var contentLength = content.text.length;		
        for (var i = 0; i < contentLength ; i++) {
                ddText = content.text[i]; 
				ddValue = content.value[i];              
                ul.append('<li style="cursor: pointer;" class="open" onmouseover="this.className=\'open_hover\'" onmouseout="this.className=\'open\'"><a selecttype="fkfs" fkfs="' + ddValue + '" style="color:#0055BB;text-decoration:none">' + ddText + '</a></dt>');
        }
		//点击后处理的事务
        ul.find("li").bind("click", function () {
            var fkfs = $(this).find("a").html();
            if(searchInfo.fkfs != fkfs){
              searchHouseInfo.fkfs = $(this).find("a").attr("fkfs");
			  searchInfo.fkfs = fkfs;
              $("#spnFkfsTitle").html(searchInfo.fkfs).attr("zlfs",searchHouseInfo.fkfs);
              changeConditionTipsDiv();
			  showHouseData();
            }
        });
		//下拉效果
        container.append(ul).bind("click", function () {
            if ($("#ulFkfs").css("display") == 'none') {
                $("#ulFkfs").show();
            } else {
                $("#ulFkfs").hide();
            }
        }).bind("mouseenter", function () {
            $("#spnFkfsTitle").removeClass().addClass("tag_select_open");
            $("#ulFkfs").show();
        }).bind("mouseleave", function () {
            $("#spnFkfsTitle").removeClass().addClass("tag_select");
            $("#ulFkfs").hide();
        });
    }
	//出售初始筛选条件 by louis
	function InitChushouConditions(){
			if(Conditions.district) InitDistrictControl();        			
        	if(Conditions.area) InitAreaControl();
			if(Conditions.price) InitPriceControl();
        	if(Conditions.shi) InitShiControl();
			if(Conditions.ting) InitTingControl();
			if(Conditions.chu) InitChuControl();
			if(Conditions.wei) InitWeiControl();						
			if(Conditions.yangtai) InitYangtaiControl();
			if(Conditions.fwpt) InitFwptControl();
			if(Conditions.fl) InitFlControl();
			if(Conditions.zxcd) InitZxcdControl();
			if(Conditions.fwjg) InitFwjgControl();
			if(Conditions.cx) InitCxControl();
			if(Conditions.fangling) InitFanglingControl();
			if(Conditions.publisher) InitPublisherControl();
			if(Conditions.ditie) InitDitieControl();
	}
    //出租初始筛选条件 by louis
	function InitChuzuConditions(){
			if(Conditions.district) InitDistrictControl();        			
        	if(Conditions.area) InitAreaControl();
			if(Conditions.price) InitPriceControl();			
			if(Conditions.zlfs) InitZlfsControl();	
			if(Conditions.fkfs) InitFkfsControl();		
        	if(Conditions.shi) InitShiControl();
			if(Conditions.ting) InitTingControl();
			if(Conditions.chu) InitChuControl();
			if(Conditions.wei) InitWeiControl();						
			if(Conditions.yangtai) InitYangtaiControl();
			if(Conditions.fwpt) InitFwptControl();
			if(Conditions.fl) InitFlControl();
			if(Conditions.zxcd) InitZxcdControl();
			if(Conditions.fwjg) InitFwjgControl();
			if(Conditions.cx) InitCxControl();
			if(Conditions.fangling) InitFanglingControl();
			if(Conditions.publisher) InitPublisherControl();
			if(Conditions.ditie) InitDitieControl();
	}
	//模块初始化 by louis
    function Init() {
		switch(mapInfo.maptype){
			case 'chushou':				
				InitChushouConditions();
				getDistrictsPoint();
            break;
            case 'chuzu':
				InitChuzuConditions();
				getDistrictsPoint();
			break;
		}
    }
    return {
        Init: Init
    };
}

//清空搜索条件和搜索信息 by louis
function clearSearchInfoConditions() {
    searchInfo.district = "";searchHouseInfo.district = "";
    searchInfo.price = "";searchHouseInfo.price = "";
    searchInfo.area = "";searchHouseInfo.area = "";
    searchInfo.shi = "";searchHouseInfo.shi = "";
    searchInfo.publisher = "";searchHouseInfo.publisher = "";
    searchInfo.keyword = "";searchHouseInfo.keyword = "";
    searchInfo.projcode = "";searchHouseInfo.projcode = "";    
}


//筛选条件显示 by louis
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
	if (searchHouseInfo.district != undefined && searchHouseInfo.district != "") {
        html = '<a class="xzjg" name="cleardistrict">' + searchInfo.district + '</a>';
        $("#conditionDiv_tip").append(html);
        conditionDivShow = true;
        $('a[name="cleardistrict"]').bind("click", function () {
            map.centerAndZoom(new BMap.Point(mapInfo.px, mapInfo.py), mapInfo.initZoom);
            searchInfo.district = "";searchHouseInfo.district = "";
            changeConditionTipsDiv();
            hideSingleDistrictMarker();//隐藏单个地区
            removeProjectMarkers();//去掉小区显示
            getDistrictsPoint();//地区显示
           
        });
    } else {
        $("#spnDistrictTitle").html("地区");
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
    //小区
	if (searchHouseInfo.projcode != undefined && searchHouseInfo.projcode != "") {
		html = '<a class="xzjg" name="clearprojcode">' + searchInfo.projcode + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearprojcode"]').bind("click", function () {
		    map.centerAndZoom(new BMap.Point(mapInfo.px, mapInfo.py), mapInfo.initZoom);
			searchInfo.projcode = "";searchHouseInfo.projcode = "";
            searchHouseInfo.keyword = "";
            removeProjectMarkers();//去掉小区显示
			changeConditionTipsDiv();
            getDistrictsPoint();//地区显示
		});
	} else {
	   if(!searchHouseInfo.district && !districtAreaMarkers.length){
	       hideSingleDistrictMarker();
	   }
	}    
	//面积
	if (searchHouseInfo.area != undefined && searchHouseInfo.area != "") {
		html = '<a class="xzjg" name="cleararea">' + searchInfo.area + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="cleararea"]').bind("click", function () {
			searchInfo.area = "";
			searchHouseInfo.area = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnAreaTitle").html("面积");
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
            showHouseData();
        });
    } else {
        $("#spnPriceTitle").html("价格");
    }
	
		//租赁方式
	if (searchHouseInfo.zlfs != undefined && searchHouseInfo.zlfs != "") {
        html = '<a class="xzjg" name="clearzlfs">' + searchInfo.zlfs + '</a>';
        $("#conditionDiv_tip").append(html);
        conditionDivShow = true;
        $('a[name="clearzlfs"]').bind("click", function () {
            searchInfo.zlfs = "";
            searchHouseInfo.zlfs = "";
            changeConditionTipsDiv();
            showHouseData();
        });
    } else {
        $("#spnZlfsTitle").html("租赁方式");
    }
	//付款方式	
	if (searchHouseInfo.fkfs != undefined && searchHouseInfo.fkfs != "") {
        html = '<a class="xzjg" name="clearfkfs">' + searchInfo.fkfs + '</a>';
        $("#conditionDiv_tip").append(html);
        conditionDivShow = true;
        $('a[name="clearfkfs"]').bind("click", function () {
            searchInfo.fkfs = "";
            searchHouseInfo.fkfs = "";
            changeConditionTipsDiv();
            showHouseData();
        });
    } else {
        $("#spnFksfTitle").html("付款方式");
    }
	//发布人
	if (searchHouseInfo.publisher != undefined && searchHouseInfo.publisher != "") {
        html = '<a class="xzjg" name="clearpublisher">' + searchInfo.publisher + '</a>';
        $("#conditionDiv_tip").append(html);
        conditionDivShow = true;
        $('a[name="clearpublisher"]').bind("click", function () {
            searchInfo.publisher = "";
            searchHouseInfo.publisher = "";
            changeConditionTipsDiv();
            showHouseData();
        });
    } else {
        $("#spnPublisherTitle").html("发布人");
    }
	//室
	if (searchHouseInfo.shi != undefined && searchHouseInfo.shi != "") {
		html = '<a class="xzjg"  name="clearshi">' + searchInfo.shi + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearshi"]').bind("click", function () {
			searchInfo.shi = "";
			searchHouseInfo.shi = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnShiTitle").html("室");
	}
	//厅
	if (searchHouseInfo.ting != undefined && searchHouseInfo.ting != "") {
		html = '<a class="xzjg"  name="clearting">' + searchInfo.ting + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearting"]').bind("click", function () {
			searchInfo.ting = "";
			searchHouseInfo.ting = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnTingTitle").html("厅");
	}
	//厨
	if (searchHouseInfo.chu != undefined && searchHouseInfo.chu != "") {
		html = '<a class="xzjg"  name="clearchu">' + searchInfo.chu + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearchu"]').bind("click", function () {
			searchInfo.chu = "";
			searchHouseInfo.chu = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnChuTitle").html("厨");
	}
	//卫
	if (searchHouseInfo.wei != undefined && searchHouseInfo.wei != "") {
		html = '<a class="xzjg"  name="clearwei">' + searchInfo.wei + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearwei"]').bind("click", function () {
			searchInfo.wei = "";
			searchHouseInfo.wei = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnWeiTitle").html("卫");
	}
	//阳台
	if (searchHouseInfo.yangtai != undefined && searchHouseInfo.yangtai != "") {
		html = '<a class="xzjg"  name="clearyangtai">' + searchInfo.yangtai + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearyangtai"]').bind("click", function () {
			searchInfo.yangtai = "";
			searchHouseInfo.yangtai = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnYangtaiTitle").html("阳台");
	}
	//房屋配套
	if (searchHouseInfo.fwpt != undefined && searchHouseInfo.fwpt != "") {
		html = '<a class="xzjg"  name="clearfwpt">' + searchInfo.fwpt + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearfwpt"]').bind("click", function () {
			searchInfo.fwpt = "";
			searchHouseInfo.fwpt = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnFwptTitle").html("房屋配套");
	}
	//房龄
	if (searchHouseInfo.fl != undefined && searchHouseInfo.fl != "") {
		html = '<a class="xzjg"  name="clearfl">' + searchInfo.fl + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearfl"]').bind("click", function () {
			searchInfo.fl = "";
			searchHouseInfo.fl = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnFlTitle").html("房龄");
	}
	//装修程度
	if (searchHouseInfo.zxcd != undefined && searchHouseInfo.zxcd != "") {
		html = '<a class="xzjg"  name="clearzxcd">' + searchInfo.zxcd + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearzxcd"]').bind("click", function () {
			searchInfo.zxcd = "";
			searchHouseInfo.zxcd = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnZxcdTitle").html("装修程度");
	}
	//房屋结构
	if (searchHouseInfo.fwjg != undefined && searchHouseInfo.fwjg != "") {
		html = '<a class="xzjg"  name="clearfwjg">' + searchInfo.fwjg + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearfwjg"]').bind("click", function () {
			searchInfo.fwjg = "";
			searchHouseInfo.fwjg = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnFwjgTitle").html("房屋结构");
	}
	//朝向
	if (searchHouseInfo.cx != undefined && searchHouseInfo.cx != "") {
		html = '<a class="xzjg"  name="clearcx">' + searchInfo.cx + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearcx"]').bind("click", function () {
			searchInfo.cx = "";
			searchHouseInfo.cx = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnCxTitle").html("朝向");
	}
	//房龄区间
	if (searchHouseInfo.fangling != undefined && searchHouseInfo.fangling != "") {
		html = '<a class="xzjg"  name="clearfangling">' + searchInfo.fangling + '</a>';
		$("#conditionDiv_tip").append(html);
		conditionDivShow = true;
		$('a[name="clearfangling"]').bind("click", function () {
			searchInfo.fangling = "";
			searchHouseInfo.fangling = "";
			changeConditionTipsDiv();
            showHouseData();
		});
	} else {
		$("#spnFanglingTitle").html("房龄区间");
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
    if (!conditionDivShow) {
        $("#conditionDiv").hide();
        //动态判定左侧列表的高度
        leftbarheight = $('#mapouterdiv').height() - $('#leftwrapperTips').height();
        $("#resultcontainer").css({
            "height": leftbarheight
        });
    } else {
        $("#conditionDiv").show();
        //动态判定左侧列表的高度
        leftbarheight = $('#mapouterdiv').height() - $('#leftwrapperTips').height();
        $("#resultcontainer").css({
            "height": leftbarheight
        });
    }    
}

//keyword 搜索 by louis
function SearchByKeyword(){
    switch(mapInfo.maptype){
        case 'chuzu'://出租
        case 'chushou'://出售
            var value = $("#keyword").val();
            if(value==mapInfo.defaultKeyword || value==''){alert('请输入小区进行搜索');return;}
            searchHouseInfo.keyword = value;searchInfo.keyword = value;
            searchHouseInfo.district = '';searchInfo.district = '';
            changeConditionTipsDiv();
            hideDistrictAreaMarker();//去掉地区显示
            hideSingleDistrictMarker();//去掉单个地区显示
            getHousePoint();
        break;
    }
}

//地区二手房数量图标  by louis
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
					 $(this).find("div").first().removeClass('qp01');
					 this.style.zIndex =-1;
                 });	 
                 mySquare.addEventListener("click", function (){
					 var districtname=$(this).find("div").first().attr("districtname");
					 var district=$(this).find("div").first().attr("district");
					 $("#spnDistrictTitle").html(districtname).attr("district",district);
					 searchHouseInfo.district = district;searchInfo.district = districtname;
                     searchHouseInfo.keyword = '';searchInfo.keyword = '';
					 searchHouseInfo.pageIndex = 1;
                     searchHouseInfo.projpageindex = 1;
					 changeConditionTipsDiv();
					 removeDistrictMarkers();//移除地区显示
					 getDistrictAreaInfo();//单独地区显示
                     getHousePoint();
                 });
                 districtMarkers.push(mySquare);		 
              }
              showHouseData();//初始化展示左侧House信息列表
        }   
    });
}

//小区二手房数量图标  by louis
function getHousePoint(){
	var bounds = map.getBounds(); 
	var sw = bounds.getSouthWest();
	var ne = bounds.getNorthEast();
    searchHouseInfo.x1=sw.lng;
    searchHouseInfo.y1=sw.lat;
    searchHouseInfo.x2=ne.lng;
    searchHouseInfo.y2=ne.lat;
    removeProjectMarkers();
	var urlParam = 'type/'+escape(mapInfo.maptype)+'/district/'+escape(searchHouseInfo.district)+'/x1/'+ escape(searchHouseInfo.x1) + '/x2/' + escape(searchHouseInfo.x2) + '/y1/' + escape(searchHouseInfo.y1) + '/y2/' + escape(searchHouseInfo.y2) + '/page/' + escape(searchHouseInfo.projpageindex)+'/keyword/'+encodeURIComponent(searchHouseInfo.keyword)+'/';
	var url = CMS_ABS + uri2MVC('ajax/newmap/entry/CommunityPointData/'+urlParam);
    $.getJSON(url,function(data){
		if(data.project){
            var project=data.project;
			var length = project.length;
            for(var i=0;i<length;i++){
                var leftClass="maskleft",rightClass="maskright",leftHover="left_hover",rightHover="right_hover";                
                    var projcode=project[i].projcode,projname=project[i].projname,housecount=project[i].housecount,purpose=project[i].purpose,address=project[i].address,addresslong=project[i].addresslong,px=project[i].px,py=project[i].py; 
                    var html='';			
               		 html='<div class="qp00" projcode="'+projcode+'" projname="'+projname+'"><a class="noatag"><div class="s1"><em><i class="arrow"></i>'+ projname +'<span style="display:none;">|'+housecount+'套</span></em></div></a></div>'; 
                      var point = new BMap.Point(px,py);
                      var mySquare = new SquareOverlay(point,100,html,1,purpose,projcode,px,py,projname,address,addresslong);
                      map.addOverlay(mySquare);
                       var overrideMouseOut=function (){
                             $(this).find("div").first().removeClass("qp01");
							 $(this).find("span").first().css('display','none');
                             this.style.zIndex =-1;
                       };
                       var overrideMouseOver=function (){
                             $(this).find("div").first().addClass("qp01");
							 $(this).find("span").first().css('display','inline');
                             this.style.zIndex =100;
                        };
                       var overrideClick = function(){
                            searchHouseInfo.projcode = $(this).find("div").first().attr('projcode');
                            searchInfo.projcode = $(this).find("div").first().attr('projname');
                            searchHouseInfo.district = '';searchInfo.district = '';
                            hideSingleDistrictMarker();
                            changeConditionTipsDiv();
							searchHouseInfo.pageIndex = 1;
                            showHouseData();
                       }
                       $('#div_ProjInfo').mouseover(function(){
                            $("#"+searchHouseInfo.projcode+"_container").css('z-index','100').find("div").first().addClass("qp01");
                        }).mouseout(function(){
                            $("#"+searchHouseInfo.projcode+"_container").css('z-index','-1').find("div").first().removeClass("qp01");
                        });
                       mySquare.addEventListener("mouseover", overrideMouseOver);
                       mySquare.addEventListener("mouseout", overrideMouseOut);
                       mySquare.addEventListener("click", overrideClick);
                     projectMarkers.push(mySquare);
            }		
		showHouseData();//初始化展示左侧House信息列表
		setMoreProjStatus(data.allcount);//翻下100个小区		
		}
    });
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
            $("#lakuangdiv").css({top:15});
            });
            $("#change100proj").unbind().bind("click",function(){
                searchHouseInfo.pageIndex = 1;
				searchHouseInfo.projpageindex= searchHouseInfo.projpageindex + 1;
                getHousePoint();
            });
        }else{
            $("#projturndiv").show();
            $("#closeprojturndiv").show();
            $("#lakuangdiv").css({top:45});
            $("#change100proj").html("返回");
            $("#closeprojturndiv").bind("click",function (){
            $("#projturndiv").hide();
            $("#closeprojturndiv").hide();
            $("#lakuangdiv").css({top:15});
            });
            $("#change100proj").unbind().bind("click",function(){
                searchHouseInfo.projpageindex=1;
                getHousePoint();
            });
        }
    }else{
        $("#projturndiv").hide();
        $("#closeprojturndiv").hide();
        $("#lakuangdiv").css({top:15});
    }
}
 
//移除小区显示 by louis
function removeProjectMarkers(){
	for(var i=0;i<projectMarkers.length;i++){map.removeOverlay(projectMarkers[i]);}
    projectMarkers=[];
}

//去掉地区显示 by louis
function removeDistrictMarkers(){
    for(var i=0;i<districtMarkers.length;i++){map.removeOverlay(districtMarkers[i]);}
    districtMarkers=[];
}

//单个地区被选中后,获取地区大标识Marker by louis
function getDistrictAreaInfo(){
	hideDistrictAreaMarker();
    if(searchHouseInfo.district){
        var url=CMS_ABS+uri2MVC('ajax/newmap/entry/CityPoint/district/'+escape(searchHouseInfo.district));
        $.ajax({
            type:'get',
            async:false,
            cache:false,
            url:url,
            dataType:'json',
            success: function(data){
                if(data.point){
                    districtAreaInfo=data.point;
                    showSingleDistrictMarker();
                }
            }
        });
    }
}
//显示单独地区Marker  by louis
function showSingleDistrictMarker(){
    hideSingleDistrictMarker();
    if(districtAreaInfo.length>0){
        var html='<div class="mapFinddingCanvasLabelStyle11"><table cellpadding=0 cellspacing=0 border=0><tr><td class="s1" >&nbsp;</td><td class="s2" ><img src="'+tplurl+'newmap/images/icon004.gif" alt="" />'+districtAreaInfo[0].name+'</td><td class="s3">&nbsp;&nbsp;</td><td class="s4"></td></tr><tr><td colspan="3" class="s5"></td></tr></table></div>';
        var center=new BMap.Point(districtAreaInfo[0].px,districtAreaInfo[0].py);
        singleDistrictMarker(center,html);
    }
}
//移除地区Marker  by louis
function hideDistrictAreaMarker(){
     for(var i=0;i<districtMarkers.length;i++){map.removeOverlay(districtMarkers[i]);}
     districtMarkers=[];
}

//移除单独地区  by louis
function hideSingleDistrictMarker(){
	 for(var i=0;i<districtAreaMarkers.length;i++){map.removeOverlay(districtAreaMarkers[i]);}
     districtAreaMarkers=[];
}

//单独地区Maker by louis
function singleDistrictMarker(point,html){
        map.setZoom(mapInfo.singleDistrictZoom);map.panTo(point);
        var marker = new SquareOverlay(point, 100,html,10000,"","",districtAreaInfo[0].px,districtAreaInfo[0].py,districtAreaInfo[0].name,"","");
        map.addOverlay(marker);
        districtAreaMarkers.push(marker);	
}

//左侧house信息列表 by louis
function showHouseData() {	
	var bounds = map.getBounds();
	var sw = bounds.getSouthWest();
	var ne = bounds.getNorthEast();
    searchHouseInfo.x1=sw.lng;
    searchHouseInfo.y1=sw.lat;
    searchHouseInfo.x2=ne.lng;
    searchHouseInfo.y2=ne.lat;	
	var urlParam = 'ajax/newmap/entry/HouseData/type/'+mapInfo.maptype+'/publisher/'+escape(searchHouseInfo.publisher)+'/page/'+escape(searchHouseInfo.pageIndex)+'/x1/'+escape(searchHouseInfo.x1)+'/x2/'+escape(searchHouseInfo.x2)+'/y1/'+escape(searchHouseInfo.y1)+'/y2/'+escape(searchHouseInfo.y2);
    if(Conditions.district!=undefined) {
		if(searchHouseInfo.district != undefined) urlParam += '/district/'+escape(searchHouseInfo.district);
		if(Conditions.district.coid2!=undefined && searchHouseInfo.shangquan != undefined)urlParam += '/shangquan/'+escape(searchHouseInfo.shangquan);
	}
	if(searchHouseInfo.projcode!=undefined) urlParam += '/projcode/'+escape(searchHouseInfo.projcode);
	if(Conditions.area!=undefined && searchHouseInfo.area != undefined) urlParam += '/area/'+escape(searchHouseInfo.area);
	if(Conditions.price!=undefined && searchHouseInfo.price != undefined) urlParam += '/price/'+escape(searchHouseInfo.price);
	if(Conditions.shi!=undefined && searchHouseInfo.shi!=undefined)  urlParam += '/shi/'+escape(searchHouseInfo.shi);
	if(Conditions.ting!=undefined && searchHouseInfo.ting!=undefined) urlParam += '/ting/'+escape(searchHouseInfo.ting);
	if(Conditions.chu!=undefined && searchHouseInfo.chu!=undefined) urlParam += '/chu/'+escape(searchHouseInfo.chu);
	if(Conditions.wei!=undefined && searchHouseInfo.wei!=undefined) urlParam += '/wei/'+escape(searchHouseInfo.wei);
	if(Conditions.yangtai!=undefined  && searchHouseInfo.yangtai != undefined) urlParam += '/yangtai/'+escape(searchHouseInfo.yangtai);
	if(Conditions.fwpt!=undefined  && searchHouseInfo.fwpt != undefined) urlParam += '/fwpt/'+escape(searchHouseInfo.fwpt);
	if(Conditions.fl!=undefined  && searchHouseInfo.fl != undefined) urlParam += '/fl/'+escape(searchHouseInfo.fl);
	if(Conditions.fangling!=undefined  && searchHouseInfo.fangling != undefined) urlParam += '/fangling/'+escape(searchHouseInfo.fanging);
	if(Conditions.zxcd!=undefined  && searchHouseInfo.zxcd != undefined) urlParam += '/zxcd/'+escape(searchHouseInfo.zxcd);
	if(Conditions.fwjg!=undefined  && searchHouseInfo.fwjg != undefined) urlParam += '/fwjg/'+escape(searchHouseInfo.fwjg);
	if(Conditions.cx!=undefined  && searchHouseInfo.cx != undefined) urlParam += '/cx/'+escape(searchHouseInfo.cx);
	if(Conditions.ditie!=undefined){
		if(searchHouseInfo.ditie !=undefined) urlParam += '/ditie/'+escape(searchHouseInfo.ditie);
		if(Conditions.ditie.coid14!=undefined && searchHouseInfo.ditiezhandian!=undefined)  urlParam += '/ditiezhandian/'+escape(searchHouseInfo.ditiezhandian);
	}
	if(Conditions.zlfs!=undefined  && searchHouseInfo.zlfs != undefined) urlParam += '/zlfs/'+escape(searchHouseInfo.zlfs);
	if(Conditions.fkfs!=undefined  && searchHouseInfo.fkfs != undefined) urlParam += '/fkfs/'+escape(searchHouseInfo.fkfs);
	var url = CMS_ABS + uri2MVC(urlParam);
    $.getJSON(url, function (data) {
        if (data.project  && data.project.projcode) {
            var html = "";
            var project = data.project;
            html += '<div class="map-cszinfo"><ul><li><a class="blue f14b" target="_blank" href="' +project.projurl+ '" id="aProjectName" title="' + project.projname + '">' + (project.projname.length>12 ? project.projname.substring(0, 12) + "..." : project.projname) + '</a><span id="spnPriceOut"><span class="pl40">均价：</span><strong class="org02">' + project.price + '</strong>元/' + (mapInfo.maptype == 'chushou' ? "平米" : "月") + '</span></li><li class="addr" title="' + project.address + '">' + (project.address.length > 25 ? (project.address.substring(0, 25) + "...") : project.address) + '</li><li id="p_housecount"><span class="fys">已有<span class="red">' + data.allcount + '</span>套房源</span></li></ul></div><div class="clear"></div>';
            $("#div_ProjInfo").show().html(html);
        }else{
            $("#div_ProjInfo").hide();
        }
        if (data.house && 0!=data.house.length) {
            var list = data.house;
            var html = '';
            if (mapInfo.maptype == "chushou") {//二手房
                for (var i = 0; i < list.length; i++) {
                    html += '<div class="list02"><a href="' + list[i].houseurl + '" target="_blank"><img src="' + list[i].houseimg + '" width="90" height="68" alt="'+list[i].title+'" class="floatl"/></a><ul><li class="fontwy"><a href="' + list[i].houseurl + '" target="_blank" title="' + list[i].title + '">' + list[i].shorttitle + '</a></li><li class="gray6" title="' + list[i].projname + '">' + list[i].projname + '<span class="xg">/</span>' + list[i].room  + list[i].hall + '</li><li class="numli fontwy"><span class="numspan">' + (list[i].buildarea==0 ? '' : '<strong class="fb16 gray3 fontwy">'+list[i].buildarea+'</strong>平米' ) + '</span><span class="org02 pl15 fontwy">'+(list[i].price == 0 ? '' : '<strong class="fb16">' + list[i].price + '</strong>万' )+'</span></li></ul></div>';
                }
            }else{//出租房
                for (var i = 0; i < list.length; i++) {
                    html += '<div class="list02"><a href="' + list[i].houseurl + '" target="_blank"><img src="' + list[i].houseimg + '" width="90" height="68" alt="'+list[i].title+'" class="floatl"/></a><ul><li class="fontwy"><a href="' + list[i].houseurl + '" target="_blank" title="' + list[i].title + '">' + list[i].shorttitle + '</a></li><li class="gray6" title="' + list[i].projname + '">' + list[i].projname + '<span class="xg">/</span>' + list[i].room + list[i].hall + '</li><li class="numli fontwy"><span class="numspan">'+ (list[i].buildarea==0 ? '' : '<strong class="fb16 gray3 fontwy">'+list[i].buildarea+'</strong>平米' ) + '</span><span class="org02 pl15 fontwy">'+(list[i].price == 0 ? '' : '<strong class="fb16">' + list[i].price + '</strong>元/月' )+'</span></li></ul></div>';
                }
            }
            $("#divProjectHouse").html(html);
        } else {
            changeConditionTipsDiv();
            var noresultdiv = '<div class="mt10 "><ul class="nohouselist"><li><strong>抱歉，未找到相关房源</strong></li><li>建议您：</li><li>·输入准确的小区名或商圈</li><li>·尝试去除某些条件进行搜索</li></ul></div>'
            $("#divProjectHouse").html(noresultdiv);
            $("#fanye_P").hide();
        }
	 //页码
        if (data.allcount) {
            $("#spnHouseCount").html(data.allcount);
            $("#bottomcountDiv").show();
            $("#p_housecount").show();
        } else {
            $("#spnHouseCount").html("0");
            $("#bottomcountDiv").hide();
            $("#p_housecount").hide();
        }
        loadPageBar();
    });
    changeConditionTipsDiv();
    changeListShow();
}

//左侧信息列表展示方式 by louis
function changeListShow() {
        $("#projListDiv").hide();
        $("#house_transitListDiv").show();
        $("#houseListDiv").show();
}

//房源列表分页 by louis
function loadPageBar() {
    $("#fanye_P").empty();
    var projcode = searchHouseInfo.projcode;
    var pIndex = searchHouseInfo.pageIndex;
    var pageSize = 7;
    var html = '';
    var pIntCount = parseInt($("#spnHouseCount").text());
    var pageCount = (pIntCount > 0 && pIntCount >= pageSize) ? parseInt(Math.ceil(pIntCount / pageSize)) : 1;
    if (pageCount > 1) {
        if (pIndex == 1) {
            html = '&lt;&nbsp;上一页&nbsp;1&nbsp;<a href="#" aPage="2">2</a> <a href="#" aPage="3">3</a> <a href="#" aPage="4">4</a> <a href="#" aPage="5">5</a> <a href="#" id="aHousePageNext">下一页 &gt;</a>';
        }
        else if (pIndex == 2 && pIndex == pageCount) {
            html = '<a href="#" id="aHousePagePre">&lt; 上一页</a> <a href="#" aPage="1">1</a> <a href="#" aPage="2">2</a>&nbsp;下一页&nbsp;&gt;';
        }
        else if (pIndex == 2) {
            html = '<a href="#" id="aHousePagePre">&lt; 上一页</a> <a href="#" aPage="1">1</a>&nbsp;2&nbsp;<a href="#" aPage="3">3</a> <a href="#" aPage="4">4</a> <a href="#" aPage="5">5</a> <a href="#" id="aHousePageNext">下一页 &gt;</a>';
        }
        else if (pIndex == 3 && pIndex == pageCount) {
            html = '<a href="#" id="aHousePagePre">&lt; 上一页</a> <a href="#" aPage="1">1</a> <a href="#" aPage="2">2</a>&nbsp;3&nbsp;下一页&nbsp;&gt;';
        }
        else if (pIndex == 4 && pIndex == pageCount) {
            html = '<a href="#" id="aHousePagePre">&lt; 上一页</a> <a href="#" aPage="1">1</a> <a href="#" aPage="2">2</a> <a href="#" aPage="3">3</a>&nbsp;4&nbsp;下一页&nbsp;&gt;';
        }
        else if (pIndex == pageCount) {
            html = '<a href="#" id="aHousePagePre">&lt; 上一页</a> <a href="#" aPage="' + (pageCount - 4) + '">' + (pageCount - 4) + '</a> <a href="#" aPage="' + (pageCount - 3) + '">' + (pageCount - 3) + '</a> <a href="#" aPage="' + (pageCount - 2) + '">' + (pageCount - 2) + '</a> <a href="#" aPage="' + (pageCount - 1) + '">' + (pageCount - 1) + '</a>&nbsp;' + pageCount + '&nbsp;下一页&nbsp;&gt;';
        } else {
            html = '<a href="#" id="aHousePagePre">&lt; 上一页</a> <a href="#" aPage="' + (pIndex - 2) + '">' + (pIndex - 2) + '</a> <a href="#" aPage="' + (pIndex - 1) + '">' + (pIndex - 1) + '</a> ' + pIndex + ' <a href="#" aPage="' + (parseInt(pIndex) + 1) + '">' + (parseInt(pIndex) + 1) + '</a> <a href="#" aPage="' + (pIndex + 2) + '">' + (parseInt(pIndex) + 2) + '</a> <a href="#" id="aHousePageNext">下一页 &gt;</a>';
        }
        $("#fanye_P").append(html).find("a").css({ "cursor": "pointer" });
        $("#aHousePageNext").bind("click", function () {
            searchHouseInfo.pageIndex = parseInt(searchHouseInfo.pageIndex) + 1;
            showHouseData();
            $("#resultcontainer").scrollTop(0);
        });
        $("#aHousePagePre").bind("click", function () {
            searchHouseInfo.pageIndex = parseInt(searchHouseInfo.pageIndex) - 1;
            showHouseData();
            $("#resultcontainer").scrollTop(0);
        });
        $("#fanye_P").find("a[aPage]").bind("click", function () {
            searchHouseInfo.pageIndex = $(this).text();
            showHouseData();
            $("#resultcontainer").scrollTop(0);
        }).each(function () {
            if ($(this).text() > pageCount) {
                $(this).hide();
            }
        });
        $("#fanye_P").show();
    } else {
        html = '&nbsp;';
        $("#fanye_P").hide();
    }
	changescreenWandH();
}


