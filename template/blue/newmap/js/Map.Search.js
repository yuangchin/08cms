function ComplexCustomOverlay(point, text, mouseoverText){
      this._point = point;
      this._text = text;
      this._overText = mouseoverText;
    }
    ComplexCustomOverlay.prototype = new BMap.Overlay();
    ComplexCustomOverlay.prototype.initialize = function(map){
      this._map = map;
      var div = this._div = document.createElement("div");
      div.style.position = "absolute";
      div.style.zIndex = BMap.Overlay.getZIndex(this._point.lat);
      div.style.whiteSpace = "nowrap";
      div.style.MozUserSelect = "none";
      div.style.fontSize = "12px";
      div.innerHTML = this._text;
      var arrow = this._arrow = document.createElement("div");
      arrow.style.position = "absolute";
      arrow.style.width = "11px";
      arrow.style.height = "10px";
      arrow.style.top = "22px";
      arrow.style.left = "10px";
      arrow.style.overflow = "hidden";
      div.appendChild(arrow);
      this._map.getPanes().labelPane.appendChild(div);
      return div;
    }
    ComplexCustomOverlay.prototype.draw = function(){
      var map = this._map;
      var pixel = map.pointToOverlayPixel(this._point);
      this._div.style.left = pixel.x - parseInt(this._arrow.style.left) + "px";
      this._div.style.top  = pixel.y - 30 + "px";
    }

var Search = {
    iType: null,
    map: null,
    config:null,
    markerBounds: new BMap.Bounds(),
    //panel: null,
    caidConfig: null,
    markersInfo:[],
    metaMarkers: [],
    transit:null,//百度公交线路实例
    driving:null,//自驾线路实例
    traffic_type:0,
    zhoubian:null,
    panelShow: function(s){
        var me = this;
        var html = '';
        if(me.iType=='lp'){
            html = '';
            var project = me.zhoubian.lp.project,len=project.length;
            for(var i=0; i<len; i++){
                //dis = Math.floor(me.map.getDistance(new BMap.Point(me.config.lng,me.config.lat),new BMap.Point(project[i].lng,project[i].lat)));
                html += '<li id="mapSearchList'+i+'"><em><span>'+project[i].dis+'米</span>'+project[i].subject+'</em></li>';
            }
        $('#mapitem-list .hd h4 span').html('楼盘');
        $('#searchPanel').html(html);

        }else{
            if(s){html = s.join("")};
            $('#searchPanel').html(html);
        }
        for(var i=0;i<me.metaMarkers.length;i++){
            if(i==0) continue;
            me.metaMarkers[i].addEventListener('mouseover',function(){
                    me.focusZhouBianMarker(this);
                });
            me.metaMarkers[i].addEventListener('mouseout',function(){
                    me.blurZhouBianMarker(this);
                });
            me.metaMarkers[i].addEventListener('click',function(){
                    me.clickZhouBianMarker(this);
                });
            $('#mapSearchList'+(i-1)).bind('mouseover',function(e){
                var id = parseInt(e.currentTarget.id.substr(13))+1;
                me.focusZhouBianMarker(me.metaMarkers[id]);
            });
            $('#mapSearchList'+(i-1)).bind('mouseout',function(e){
                var id = parseInt(e.currentTarget.id.substr(13))+1;
                me.blurZhouBianMarker(me.metaMarkers[id]);
            });
            $('#mapSearchList'+(i-1)).bind('click',function(e){
                var id = parseInt(e.currentTarget.id.substr(13))+1;
                me.clickZhouBianMarker(me.metaMarkers[id]);
            });
            /*
            $('#mapSearchList'+(i-1)).bind('click',function(e){
                var id = parseInt(e.currentTarget.id.substr(13))+1;
                me.clickZhouBianMarker(me.metaMarkers[id],'lp');
            });
            */
        }
            $('#mapitem-list').show();
        },
    panelClose: function(){
        me = this;
        me.removeOtherOverlays();
        me.map.panTo(new BMap.Point(me.config.lng,me.config.lat));
        $('#mapitem-list').hide();
        $('#map-lp').hide();
        $('#mapitems').show();
        },
    createMap: function(divMap,config){
        var me = this;
        me.config = config;
        var mapOptions = {minZoom:config.minZoom||12, maxZoom:config.maxZoom||19};
        var map = new BMap.Map(divMap,mapOptions); //创建Map实例
        map.enableDragging(); //启用地图拖拽事件，默认启用(可不写)
        map.centerAndZoom(new BMap.Point(config.lng,config.lat),config.zoom||15);
        map.enableScrollWheelZoom();//启用滚轮放大缩小
        map.addControl(new BMap.NavigationControl()); // 添加平移缩放控件
        map.addControl(new BMap.ScaleControl({type:BMAP_NAVIGATION_CONTROL_SMALL})); // 添加比例尺控件
        me.map = map;
        },
    getZhoubianData: function(){
        var me = this;
        var url=CMS_ABS+uri2MVC('ajax/lp_newzhoubian/entry/allzhoubian/aid/'+me.config.aid+'/chid/'+me.config.chid+'/r/'+me.config.distance+'/lng/'+me.config.lng+'/lat/'+me.config.lat);
        $.ajax({
            type:'get',
            async:false,
            cache:false,
            url:url,
            dataType:'json',
            success: function(data){
                me.zhoubian = data;
                me.caidConfig = data['caidconfig'];
            }
        });
        },
    showSearchResult: function(){
        var me = this;
            me.iType = 'lp';
            $('#mapitems li').first().addClass('current');
            $('#mapitems').children().each(function(){
                $(this).bind('click',function(){
                        $(this).addClass('current');
                        $(this).siblings().removeClass('current');
                });
            });

            me.showSelfPoint();
            //me.showZhoubianPoint();//开启后默认显示楼盘周边
        },
    getTypeImg_b:function(n) {
            var i = - parseInt(n) * 24;
            return {url: tplurl + 'newmap/images/qipao.png', size:new BMap.Size(19,29), imageOffset:new BMap.Size(i, -198), anchor:new BMap.Size(-19, -29)};
    },
    showZhoubianPoint: function(i){
        var me = this;
        //me.metaMarkers = me.metaMarkers.slice(0,1);
        //me.markersInfo = [];
        me.map.panTo(new BMap.Point(me.config.lng,me.config.lat));
        var html = '';
        if(undefined==i || 0==i){
            me.iType='lp';
        }else{
            if(me.caidConfig[i] != undefined) {
                me.iType = me.caidConfig[i];
            }
        }
        me.removeOtherOverlays();
        me.removeZhoubianPoint(); 
		
        if('lp'==me.iType){
            try{
            var c = me.zhoubian[me.iType].project;
            var html = '';
            var len = c.length;
            for(var i=0; i<len; i++){
                var dis = Math.floor(me.map.getDistance(new BMap.Point(me.config.lng,me.config.lat),new BMap.Point(c[i].lng,c[i].lat)));
                c[i].dis = dis;
            }
            me.shellSort(c);
            for(var i=0; i<len;i++){
                if(i<5){
                    markerInfo = me.getTypeImg_b(i);
                    var myIcon = new BMap.Icon(markerInfo.url,markerInfo.size,{imageOffset:markerInfo.imageOffset});
                    //var marker = new BMap.Marker( new BMap.Point(c[i].lng,c[i].lat), {icon: myIcon});
                }else{
                    markerInfo = {url:tplurl+'newmap/images/sqp01.png', size:new BMap.Size(15,15)};
                    var myIcon = new BMap.Icon(markerInfo.url,markerInfo.size);
                }
                var marker = new BMap.Marker( new BMap.Point(c[i].lng,c[i].lat), {icon: myIcon});
                marker.id = i;
                marker.disableMassClear();
                marker.setTitle(c[i].subject);
                marker.info = c[i];
                me.map.addOverlay(marker);
                me.metaMarkers.push(marker);
                //me.focusZhouBianMarker(marker);
                //me.blurZhouBianMarker(marker);
                //me.clickZhouBianMarker(marker,me.iType);
                }
            }catch(e){}
            me.panelShow();
        }else{
            //百度周边
            var dis = 1000;//百度搜索范围
            var keywords;
            switch(me.iType){
                //case 'lp':
                //keywords = '楼盘';
                //break;
                case 'school':
                keywords = '学校';
                break;
                case 'bus':
                keywords = '公交';
                break;
                case 'cy':
                keywords = '餐馆';
                break;
                case 'hospital':
                keywords = '医院';
                break;
                case 'bank':
                keywords = '银行';
                break;
                case 'supermark':
                keywords = '超市';
                break;
                case 'park':
                keywords = '公园';
                break;
                case 'fun':
                keywords = '娱乐';
                dis = 500;
                break;
            } 
            $('#h4metaTitle').html(keywords);
            var mPoint = new BMap.Point(me.config.lng, me.config.lat);
            //var circle = new BMap.Circle(mPoint,dis,{fillColor:"blue", strokeWeight: 1 ,fillOpacity: 0.3, strokeOpacity: 0.3});
            var local =  new BMap.LocalSearch(me.map);
            local.setPageCapacity(3);//设置每个的数量
            //var bounds = me.getSquareBounds(circle.getCenter(),circle.getRadius());
            //local.searchInBounds(keywords,bounds);
            local.searchNearby(keywords,mPoint,dis);
            local.setSearchCompleteCallback(function(results){
					var s = [];
					me.markersInfo = [];//制空
					//百度数据
					if (local.getStatus() == BMAP_STATUS_SUCCESS){ // 判断状态是否正确
						//results.getCurrentNumPois() 第一页的条数
						//results.getNumPois() 所有的条数
						var num = results.getCurrentNumPois();
						for (var i = 0; i < num; i++){
							 var result = results.getPoi(i);
							 var dis = Math.floor(me.map.getDistance(new BMap.Point(parseFloat(me.config.lng),parseFloat(me.config.lat)),result.point));
							 me.markersInfo.push({'lng':result.point.lng,'lat':result.point.lat,'subject':result.title,'address':result.address,'dis':dis});
						}
					}
                    //加入本地数据周边
                    if(me.zhoubian[me.iType]){
                        var zhoubianData = me.zhoubian[me.iType].project;
                        var num = zhoubianData.length;
                        for(var i=0;i<num;i++){
                            var dis = Math.floor(me.map.getDistance(new BMap.Point(parseFloat(me.config.lng),parseFloat(me.config.lat)),new BMap.Point(parseFloat(zhoubianData[i].lng),parseFloat(zhoubianData[i].lat))));
                            me.markersInfo.push({'lng':parseFloat(zhoubianData[i].lng),'lat':parseFloat(zhoubianData[i].lat),'subject':zhoubianData[i].subject,'address':zhoubianData[i].abstract,'dis':dis});
                        }
                    } //console.log(me.zhoubian);
                    //距离排序
                    me.markersInfo = me.shellSort(me.markersInfo);
                    //marker信息展示
                    for(var j=0;j < me.markersInfo.length;j++){
                        var result = me.markersInfo[j];
                        if(j<10){
                            var markerInfo = me.getTypeImg_b(j);
                            var myIcon = new BMap.Icon(markerInfo.url,markerInfo.size,{imageOffset:markerInfo.imageOffset});
                        }else{
                            var markerInfo = {url: tplurl + 'newmap/images/sqp01.png', size:new BMap.Size(19,29)};
                            var myIcon = new BMap.Icon(markerInfo.url,markerInfo.size,{imageOffset:markerInfo.imageOffset});
                        }
                        var marker = new BMap.Marker( new BMap.Point(result.lng,result.lat), {icon: myIcon});
                        var startInfowin = new BMap.InfoWindow("<p class='t-c'><input value='选为起点' type='button' onclick='startDeter();' /></p>");
                        s.push("<li id=\"mapSearchList"+j+"\">");
                        s.push("<em><span>"+result.dis+"米</span>"+result.subject+"</em>");
                        s.push("</li>");
                        marker.info = result;
                        marker.setTitle(result.subject);
                        marker.id = j;
                        me.metaMarkers.push(marker);
                        me.map.addOverlay(marker);
                    }
                    //document.getElementById("log").innerHTML = s.join("<br>");//调试点
                   me.panelShow(s);
                
                });
        }
        //me.panelShow();

        },
    shellSort : function(arr){//希尔算法
        for (var step = arr.length >> 1; step > 0; step >>= 1){
        for (var i = 0; i < step; ++i){
            for (var j = i + step; j < arr.length; j += step){
            var k = j, value = arr[j].dis,tmp = arr[j];
            while (k >= step && arr[k - step].dis > value){
                arr[k] = arr[k - step];
                k -= step;
            }
            arr[k] = tmp;
            }
        }
        }
        return arr;
    },
    openTip: function(){
        $('#maptip').show();
        },
    closeTip: function(){
        $('#maptip').hide();
        },
    clickZhouBianMarker: function(marker,type){
        var me = this;
        if('lp'==type){
                var content = '<div><div><ul><li>楼盘地址:'+marker.info.address+'</li><li><a href="'+marker.info.url1+'">楼盘动态</a><a href="'+marker.info.url+'">楼盘详情</a></li></div><div><img src="'+marker.info.thumb+'" width="120" height="80"></div></div>';
                    var searchInfoWindow = new BMapLib.SearchInfoWindow(me.map, content, {
                    title  : marker.info.subject+'<span class="'+marker.info.salecolor+' rbox2 ml10 mt15">'+marker.info.salestat+'</span>',//标题
                    width  : 290,             //宽度
                    height : 105,             //高度
                    panel  : "panel",         //检索结果面板
                    enableAutoPan : true,     //自动平移
                    searchTypes   :[
                    BMAPLIB_TAB_SEARCH,   //周边检索
                    BMAPLIB_TAB_TO_HERE,  //到这里去
                    BMAPLIB_TAB_FROM_HERE //从这里出发
                    ]
                    });
                    marker.enableDragging(); //marker可拖拽
                    marker.addEventListener("click", function(e){
                        searchInfoWindow.open(marker);
                    });
            }else{
                var content = '<div>'+marker.info.address+'</div>';
                var searchInfoWindow = new BMapLib.SearchInfoWindow(me.map, content, {
                        title: marker.info.subject, //标题
                        width  : 290,             //宽度
                        height : 55,             //高度
                        panel : "panel", //检索结果面板
                        enableAutoPan : true, //自动平移
                        searchTypes :[
                            BMAPLIB_TAB_FROM_HERE, //从这里出发
                            BMAPLIB_TAB_SEARCH   //周边检索
                        ]
                    });
                    //marker.addEventListener("click", function(e){
                        searchInfoWindow.open(marker);
                    //});
            }
        },
    focusZhouBianMarker: function(marker){
        //var focusFun = function(){
                var icon = marker.getIcon();
                if(icon.imageOffset.height == -198){
                    icon.imageOffset.height = -230;
                }else if(icon.imageUrl == tplurl+"newmap/images/sqp01.png"){
                    icon.imageUrl = tplurl+"newmap/images/sqp01a.png";
                }
                $('#mapSearchList'+marker.id).css('background','rgb(232,244,255)');
                marker.setIcon(icon);
        //};
        //marker.addEventListener('mouseover',focusFun);
        },
    blurZhouBianMarker: function(marker){
        //var blurFun = function(){
                var icon = marker.getIcon();
                if(icon.imageOffset.height == -230){
                    icon.imageOffset.height = -198;
                }else if(icon.imageUrl == tplurl+"newmap/images/sqp01a.png"){
                    icon.imageUrl = tplurl+"newmap/images/sqp01.png";
                }
                $('#mapSearchList'+marker.id).css('background','');
                marker.setIcon(icon);
                marker.setTop(false);
        //  };
        //marker.addEventListener('mouseout',blurFun);
    },
    removeZhoubianPoint: function(){
        var me = this,len = me.metaMarkers.length;
        for(var i=1;i<len;i++){
            me.map.removeOverlay(me.metaMarkers[i]);
        }
        me.metaMarkers = me.metaMarkers.slice(0,1);
        me.markersInfo = [];
        },
    showSelfPoint: function(){
        var me = this;
        var mp = me.map;
    // 复杂的自定义覆盖物
    var txt = '<div class="searchRichMarker" id="tip'+me.config.aid+'"><div><em>'+me.config.subject+'<span style="display:none;">|'
    + (me.config.dj?me.config.dj+'元/平方米</span>':'待定')
    + '</em></div></div>', mouseoverTxt = '<div class="searchRichMarker" id="tip'+me.config.aid+'"><div><em>'+me.config.subject+'<span>|'
    + (me.config.dj?me.config.dj+'元/平方米</span>':'待定')
    + '</em></div></div>' ;
    var marker = new ComplexCustomOverlay( new BMap.Point(me.config.lng,me.config.lat), txt,mouseoverTxt);
    me.map.addOverlay(marker);
    me.metaMarkers.push(marker);
        $("#tip"+me.config.aid).mouseenter(function(){
            me.markerHover(true);
        }).mouseleave(function(){
            me.markerHover(false);
        });
        },
    markerHover: function(flag){
        var me = this;
        if(flag){
            $('#tip'+me.config.aid+' span').show();
            }else{
            $('#tip'+me.config.aid+' span').hide();
                }
        },
    getSquareBounds: function(centerPoi,r){
        var me = this;
        var a = Math.sqrt(2) * r; //正方形边长
        mPoi = me.getMecator(centerPoi);
        var x0 = mPoi.x, y0 = mPoi.y;
        var x1 = x0 + a / 2 , y1 = y0 + a / 2;//东北点
        var x2 = x0 - a / 2 , y2 = y0 - a / 2;//西南点
        var ne = me.getPoi(new BMap.Pixel(x1, y1)), sw = me.getPoi(new BMap.Pixel(x2, y2));
        return new BMap.Bounds(sw, ne);
        },
    getMecator: function(poi){
        var me = this;
         return me.map.getMapType().getProjection().lngLatToPoint(poi);
        },
    getPoi: function(mecator){
        var me = this;
         return me.map.getMapType().getProjection().pointToLngLat(mecator);
        },
    openurl: function(url){
        open(url);
        },
    openMapLp: function(index){
            var me = this;
            me.removeOtherOverlays();
            //$('#B_PointName0').val(options.subject);
            $('#B_PointName0').val('请输入起点');
            $('#B_PointName1').val('请输入终点');
            $('#mapitems').hide();
            $('#mapitem-list').hide();
            $('#bus_wrap').hide();
            if(1==index){
                $('#traffic_title').html('公交');
                me.traffic_type = 1;
            }else if(2==index){
                $('#traffic_title').html('驾车');
                me.traffic_type = 2;
            }
            $('#bus_ipt').show();
            $('#map-lp').show();
        },
    selinputb: function (obj,tipvalue){
        var defaultValue = '请输入关键字';
                if(tipvalue == obj.value|| obj.value == defaultValue){
                            obj.value = '';
                }else if('' == obj.value){
                            obj.value = tipvalue;
                }
            },
    milkSearchFun: function(){
            var drivewrapDiv = $("#bus_wrap");
            if(null != Search.transit && null != drivewrapDiv){
                var result = Search.transit.getResults();
                var count = result.getNumPlans();
                var tansitHtml = '';
                if(count > 0){
                    tansitHtml += '<div id="dv_scroll">';
                    for(var i = 0; i <count; i++){
                        var plan = result.getPlan(i);
                        var linesnum = plan.getNumLines();
                        var num = i+1;
                        tansitHtml += '<div class="map_line" >';
                        tansitHtml += '<div class="map_line_tit" onclick="Search.show_menu(\'buswarp\',\''+i+'\',\''+count+'\');Search.drawLine('+i+')">';
                        tansitHtml += '<strong><span class="hcard">'+num+'.</span>';
                        for(var j = 0; j < linesnum; j++){
                            var zhandian = plan.getLine(j).title;
                            var zhandianarr = zhandian.split("(");
                            tansitHtml += zhandianarr[0];
                            if(j < linesnum -1){
                                tansitHtml += '<span class="rarr">→</span>';
                            }
                        }
                        var time = parseInt(plan.getDuration(false));
                        var distance = parseFloat(plan.getDistance(false)/1000);
                        distance = Math.round(distance * 10) / 10;
                        var min = parseInt(time/60);
                        tansitHtml += '</strong><em>约'+ min +'分钟/'+distance+'公里</em>';
                        tansitHtml += '</div>';
                        if(0 == i){
                            tansitHtml += '<div id="buswarp'+i+'">';
                        }else{
                            tansitHtml += '<div id="buswarp'+i+'" style="display:none">';
                        }
                        tansitHtml += '<dl class="map_line_way">';
                        tansitHtml += '<dt class="start"><strong>'+Search.B_PointName0+'</strong></dt>';
                        var stationcount = plan.getNumLines();
                        for(var m = 0; m < stationcount; m++){
                            var routs = plan.getRoute(m);
                            var lines = plan.getLine(m);
                            if('0' != routs.getDistance(false)){
                                tansitHtml += '<dd>';
                                tansitHtml += '<i class="walk"></i>';
                                tansitHtml += '<div class="info">步行至&nbsp;<a href="javascript:void(0)" >'+lines.getGetOnStop().title+'</a></div>';
                                tansitHtml += '</dd>';
                            }
                            tansitHtml += '<dd>';
                            if(lines.title.indexOf("地铁") > 0 ){
                                tansitHtml += '<i class="bus">&nbsp;</i>';
                            }else{
                                tansitHtml += '<i class="bus">&nbsp;</i>';
                            }
                            var ztitle = lines.title.split("(");
                            tansitHtml += '<div class="info">乘坐&nbsp;<strong>'+ztitle[0]+'</strong>,&nbsp;在&nbsp;<a class="ks" href="javascript:void(0)" >'+lines.getGetOffStop().title+'</a>&nbsp;下车&nbsp;&nbsp;</div>';
                            tansitHtml += '<dd>';
                        }
                        var routs = plan.getRoute(stationcount+1);
                        if(null != routs && '0' != routs.getDistance(false)){
                            tansitHtml += '<dd>';
                            tansitHtml += '<i class="walk"></i>';
                            tansitHtml += '<div class="info">步行至&nbsp;<a class="ks" href="javascript:void(0)" >'+lines.getGetOnStop().title+'</a></div>';
                            tansitHtml += '</dd>';
                        }
                        tansitHtml += '<dt class="end" ><strong>'+Search.B_PointName1+'</strong></dt>';
                        tansitHtml += '</dl>';
                        tansitHtml += '</div>';
                        tansitHtml += '</div>';
                    }
                    tansitHtml += '</div>';
                }else{
                    tansitHtml += '<div class="lzbcxb" id="lzbcxb">';
                    tansitHtml += '<div class="title">请选择准确的起点、途经点或终点</div>';
                    tansitHtml += '<div class="content">';
                    if('s' == Search.tag){
                        tansitHtml += '<div class="seltop  no2">';
                        tansitHtml += '<div class="s4"></div>';
                        tansitHtml += '<div class="name1">起点：<strong>'+Search.B_PointName0+'</strong></div>';
                        tansitHtml += '</div>';
                    }else{
                        tansitHtml += '<div class="seltop  mart5">';
                        tansitHtml += '<div class="s3"></div>';
                        tansitHtml += '<div class="name">起点：<strong>'+Search.B_PointName0+'</strong></div>';
                        tansitHtml += '</div>';
                    }
                    if('e' == Search.tag){
                        tansitHtml += '<div class="seltop no2">';
                        tansitHtml += '<div class="s4"></div>';
                        tansitHtml += '<div class="name1">起点：<strong>'+Search.B_PointName1+'</strong></div>';
                        tansitHtml += '</div>';
                    }else{
                        tansitHtml += '<div class="seltop mart5">';
                        tansitHtml += '<div class="s3"></div>';
                        tansitHtml += '<div class="name">终点：<strong>'+Search.B_PointName1+'</strong></div>';
                        tansitHtml += '</div>';
                    }
                    tansitHtml += '<div class="info">未找到相关地点。<br />您可以修改搜索内容。</div>';
                    tansitHtml += '</div>';
                    tansitHtml += '</div>';
                }
                drivewrapDiv.html(tansitHtml).show();
                $('#bus_ipt').hide();
            }
    },
    driverSearchFun: function(){

    },
    searchway:function() {
            var me = this;
            var defaultValue = '请输入关键字';
            var startname = $("#B_PointName0").val();
            var endname = $("#B_PointName1").val();
            startname = startname.replace(/(^\s*)|(\s*$)/g, "").replace(/\s/g,"");

            if(startname=="" || startname == defaultValue || startname == '请输入起点') {
                alert("请填写起点信息");
                return;
            }
            endname = endname.replace(/(^\s*)|(\s*$)/g, "");
            endname = endname.replace(/\s/g,"");
            me.B_PointName0 = startname;
            me.B_PointName1 = endname;
            if(endname=="" || endname == defaultValue || endname == '请输入终点') {
                alert("请填写终点信息");
                return;
            }
            me.removeZhoubianPoint();
            if(1==me.traffic_type){//公交
                if(!me.transit){
                    me.transit = new BMap.TransitRoute(me.map, {renderOptions: {map: me.map, autoViewport: true}, onSearchComplete: me.milkSearchFun,onResultsHtmlSet: function(){$("#bus_wrap").show();}});
                    //me.transit = new BMap.TransitRoute(me.map, {renderOptions: {map: me.map, autoViewport: true, panel: 'bus_wrap'}});//简约公交线路
                    }
                me.clearTransitRoute();
                me.transit.search(startname,endname);
            }else if(2==me.traffic_type){
                if(!me.driving){
                    //me.driving = new BMap.DrivingRoute(me.map, {renderOptions: {map: me.map, autoViewport: true}, onSearchComplete: me.driverSearchFun});
                    me.driving = new BMap.DrivingRoute(me.map, {renderOptions: {map: me.map, autoViewport: true, panel: 'bus_wrap'}});//简约公交线路
                }
                me.clearDriveRoute();
                me.driving.search(startname,endname);
                $('#bus_ipt').hide();
                $('#bus_wrap').html('');
            }
        $('#bus_wrap').show();
    },
    clearDriveRoute: function() {
            this.driving.clearResults();
    },
    clearTransitRoute: function() {
            this.transit.clearResults();
    },
    show_menu: function(id, index, count){
        for (var i = 0; i < count; i++) {
            document.getElementById(id.toString() + i).style.display = "none";
        }
        document.getElementById(id.toString() + index).style.display = "block";
    },
    changeStartEnd :  function(){
            var defaultValue = '请输入关键字';
            var startname = $("#B_PointName0").val();
            var endname = $("#B_PointName1").val();
            var _endname,_startname;
            if(startname=='请输入起点'){
                _endname=defaultValue;
            }else{
                _endname=startname;
            }
            if(endname=='请输入终点'){
                _startname=defaultValue;
            }else{
                _startname=endname;
            }
            $("#B_PointName0").val(_startname);
            $("#B_PointName1").val(_endname);
    },
    drawLine:function(index){
            var me=this;
            var results = me.transit.getResults();
            var opacity = 0.45;
            var planObj = results.getPlan(index);
            var bounds = new Array();
            var addMarkerFun = function(point,imgType,index,title){
                var url,width,height,myIcon;
                // imgType:1的场合，为起点和终点的图；2的场合为过程的图形
                if(imgType == 1){
                    url = "http://map.baidu.com/image/dest_markers.png";
                    width = 42;
                    height = 34;
                    myIcon = new BMap.Icon(url,new BMap.Size(width, height),{offset: new BMap.Size(14, 32),imageOffset: new BMap.Size(0, 0 - index * height)});
                }else{
                    url = "http://map.baidu.com/image/trans_icons.png";
                    width = 22;
                    height = 25;
                    var d = 25,cha = 0,jia = 0
                    if(index == 2){
                        d = 21;
                        cha = 5;
                        jia = 1;
                    }
                    myIcon = new BMap.Icon(url,new BMap.Size(width, d),{offset: new BMap.Size(10, (11 + jia)),imageOffset: new BMap.Size(0, 0 - index * height - cha)});
                }

                var marker = new BMap.Marker(point, {icon: myIcon});
                if(title != null && title != "") marker.setTitle(title);
                // 起点和终点放在最上面
                if(imgType == 1) marker.setTop(true);
                me.map.addOverlay(marker);
            };
            var addPoints = function(points){
                for(var i = 0; i < points.length; i++) bounds.push(points[i]);
            };
            me.removeOtherOverlays();
            //me.map.clearOverlays();
            /*
            // 绘制驾车步行线路
            for (var i = 0; i < planObj.getNumRoutes(); i ++){
                var route = planObj.getRoute(i);
                if (route.getDistance(false) > 0){
                    // 步行线路有可能为0
                    map.addOverlay(new BMap.Polyline(route.getPath(), {strokeStyle:"dashed",strokeColor: "#30a208",strokeOpacity:0.75,strokeWeight:4,enableMassClear:true}));
                }
            }
           */
            // 绘制公交线路
            for (i = 0; i < planObj.getNumLines(); i ++){
                var line = planObj.getLine(i);
                addPoints(line.getPath());
                // 公交
                if(line.type == BMAP_LINE_TYPE_BUS){
                    // 上车
                    addMarkerFun(line.getGetOnStop().point,2,2,line.getGetOnStop().title);
                    // 下车
                    addMarkerFun(line.getGetOffStop().point,2,2,line.getGetOffStop().title);
                    // 地铁
                }else if(line.type == BMAP_LINE_TYPE_SUBWAY){
                    // 上车
                    addMarkerFun(line.getGetOnStop().point,2,3,line.getGetOnStop().title);
                    // 下车
                    addMarkerFun(line.getGetOffStop().point,2,3,line.getGetOffStop().title);
                }
                me.map.addOverlay(new BMap.Polyline(line.getPath(), {strokeColor: "#0030ff",strokeOpacity:opacity,strokeWeight:6,enableMassClear:true}));
            }
            me.map.setViewport(bounds);
            // 终点
            addMarkerFun(results.getEnd().point,1,1);
            // 开始点
            addMarkerFun(results.getStart().point,1,0);
        },
        removeOtherOverlays : function(){
            var me = this;
            var allOverlay = me.map.getOverlays();
            var len = allOverlay.length;
            for (var i = 1; i < len; i++){
                    if(allOverlay[i] instanceof ComplexCustomOverlay) continue;
                    me.map.removeOverlay(allOverlay[i]);

            }
        },
        openDis : function(){
            var me = this;
            if('undefined' == typeof me._disTool) {
                    me._disTool = new BMapLib.DistanceTool(me.map);
                    me._disTool.addEventListener('drawend', function(e){me._disTool.close();});
            }
            me._disTool.open();
        }
    }