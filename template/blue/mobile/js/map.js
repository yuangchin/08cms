var _08map, aZB = [],
    isFinished1 = 1,
    zb = {
        'icon-e608': '公交',
        'icon-e6f9': '学校',
        'icon-f0fe': '医院',
        'icon-e630': '银行',
        'icon-f07a': '购物',
        'icon-e611': '楼盘'
    };

$(dtopt.mapWrap).css({
    width : $(window).width()
    , height : $(window).height()
})
// 加载地图
createMap(dtopt);
// 下一页
$('#tip').on('click', 'a', function() {
    dtopt._param.aj_pagenum++;
    createMap(dtopt);
})
// 周边
$('#zb').on('click', 'a', function() {
    var t = this.className;
    var local = new BMap.LocalSearch(_08map, {
        /*renderOptions:{map: _08map}
        ,*/onSearchComplete:function(d) {
            $.each(aZB, function(a, b) {
                _08map.removeOverlay(b);
            })
            if (typeof(d)=='undefined') return false;
            $.each(d._pois, function(a, b) {
                // 添加自定义覆盖物
                addZB(b,t);
            })
        }
    });
    local.searchInBounds(zb[t], _08map.getBounds());
    $(this).toggleClass('active').siblings('a').removeClass('active');
    return false;
}).on('click', 'span', function() {
    $(this).parent().andSelf().toggleClass('active');
});

function addZB(info,type) {
    var myIcon = new BMap.Icon(tplurl+'mobile/images/dian.png', new BMap.Size(17,24), {imageSize:new BMap.Size(21, 30)});
    var marker = new BMap.Marker(new BMap.Point(info.point.lng,info.point.lat), {title:type});
    var _b ={
        dt_1:info.point.lng
        ,dt_0:info.point.lat
        ,subject:info.title
        ,address:info.address
        ,type:type
        ,classN:'zb-item'
    }
    var v = new SquareOverlay(_b);
    aZB.push(v);
    _08map.addOverlay(v);
    marker.addEventListener("click", function(){this.openInfoWindow(new BMap.InfoWindow(info.title));});
    return marker;
}


function createMap(opt1) {
    // 百度地图API功能
    _08map = new BMap.Map(opt1.mapWrap.replace('#',''));
    _08map.centerAndZoom(new BMap.Point(opt1.defDt[1],opt1.defDt[0]), opt1.zoom);
    _08map.addControl(new BMap.ZoomControl());

    if(!isFinished1) return;
    isFinished1 = 0;
    $(_08map.getPanes().markerPane).parent().andSelf().parent().andSelf().css({
        width:'100%'
        , height:'100%'
    });

    $.getJSON(CMS_ABS + uri2MVC('ajax='+opt1.ajax+'/' + $.param(opt1._param).replace(/\+/g,"%20") + opt1.filterUrl +'&callback=?'), function(data){
        if (data.length) {
            $.each(data, function(a, b) {
                b.classN = "item";
                // 添加自定义覆盖物
                 _08map.addOverlay(new SquareOverlay(b,opt1));
            })
            $('#tip').html('每页'+opt1._param.aj_pagesize+'个，当前第'+opt1._param.aj_pagenum+'页<a>[下一页]</a>');
            isFinished1 = 1;
        }else{
            J.showToast('没有数据','info');
        };
    })
};

// 定义自定义覆盖物的构造函数
function SquareOverlay(o,o1){
    this.o = o;
    this.o1 = o1;
}
// 继承API的BMap.Overlay
SquareOverlay.prototype = new BMap.Overlay();

// 实现初始化方法
SquareOverlay.prototype.initialize = function(map){
    var _d = this.o;
    var _opt = this.o1;
    // 保存map对象实例
    this._map = map;
    // 创建div元素，作为自定义覆盖物的容器
    var div = document.createElement("div");
    if (!_d.type) {
        // 可以根据参数设置元素外观
        $(div).addClass(_d.classN+' '+_d.classN+'-'+_d.ccid18)
        .on('tap', function() {
            map.centerAndZoom(new BMap.Point(_d.dt_1,_d.dt_0),_opt.zoom);
            itemClick.call(_d);
        })
        div.innerHTML = this.o1.dttemplate.call(_d);
    }else{
        $(div).addClass(_d.classN+' '+_d.type)
        .on('tap', function() {
            J.popup({
                pos : 'bottom'
                , html : '<div style="padding:10px">'+_d.subject+'<br/>'+_d.address+'</div>'
                , showCloseBtn : 1
            })
        });
    };
    // 将div添加到覆盖物容器中
    map.getPanes().markerPane.appendChild(div);
    // 保存div实例
    this._div = div;
    this._width = $(div).width()-5;
    this._height = $(div).height()+6;
    // 需要将div元素作为方法的返回值，当调用该覆盖物的show、
    // hide方法，或者对覆盖物进行移除时，API都将操作此元素。
    return div;
}

// 实现绘制方法
SquareOverlay.prototype.draw = function(){
var _d = this.o;
// 根据地理坐标转换为像素坐标，并设置给容器
   var position = this._map.pointToOverlayPixel({lng: _d.dt_1, lat: _d.dt_0});

   this._div.style.left = position.x - this._width / 2 + "px";
   this._div.style.top = position.y - this._height / 2 + "px";
}

