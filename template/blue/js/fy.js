$.getScript(tplurl+"js/jq.cookie.js",function () {
    if (typeof(caid) == 'undefined') return false;
    var cookieName = "list_"+caid,nid,N = 10;//设置cookie保存的浏览记录的条数
    HistoryRecord();
    //记录最近浏览过的房源
    function HistoryRecord() {
        var historyp=null;
        nid = aid;
        if (nid == null || nid == "") return;
        //判断是否存在cookie
    	var opt = { expires: 60*60*24*30, path: '/' };
        if ($.cookie(cookieName) == null) $.cookie(cookieName, nid, opt);
        else{
            historyp = $.cookie(cookieName);
            var pArray = historyp.split(',');
            historyp = nid;
            var count = 0;
            for (var i = 0; i < pArray.length; i++) {
                if (pArray[i] != nid) {
                    historyp = historyp + "," + pArray[i];
                    count++;
                    if (count == N - 1) {
                        break;
                    }
                }
            }
            //修改cookie的值
            $.cookie(cookieName, historyp, opt);
        }
    }
    //
    getBrowseFy();
})

function getBrowseFy() {
    $.getScript(CMS_ABS + uri2MVC("ajax=fangyuan&caid="+caid+"&aids="+$.cookie('list_'+caid+'')),function (){
        var rs   = fangyuan
        ,len     = rs.length
        ,newhtml = ''
        ,dw      = caid ==3?'万': '元/月';

        for(var i=0;i<len;i++) {
            newhtml += "<li>"
                    +       "<span class='td1'><a href='" + rs[i]['arcurl'] + "' target='_blank'>" + rs[i]['subject'] + "</a></span><span class='td2'>"+rs[i]['mj']+"m&sup2;</span><span class='td3 fco'>" + (rs[i]['zj']!=0 ? rs[i]['zj']+dw:'面议')+"</span>"
                    +   "</li>";
        }

        if (newhtml) $('#list_'+caid).after('<div class="coltit1"><h3 class="tit1">最近浏览过的房子</h3></div><ul class="tlist2 bd-gray p10">'+newhtml+'</ul>');
    })
}

// 走势图
if(typeof(jsonData)!='undefined'){
    var options = {
        chart: {
            renderTo: 'container',
            type: "line"
        },
        //3条线的颜色
        colors:["#5689D6", "#BF5A2F", "#62AB00"],
        title: {
            text: jsonData.title,
            style:{
                color: '#666666',
                fontSize: '12px',
                fontFamily: 'arial'
            }
        },
        subtitle: {
            text: ""
        },

        xAxis: {
            categories: jsonData.month_s,
            tickmarkPlacement: "on",
            reversed:true,
            labels: {
                style: {
                    fontSize: "14px",
                    fontFamily: "Microsoft YaHei"
                },
                y: 25
            }
        },
        yAxis: {
            title: "",
            gridLineColor: "#D9D9D9",
            opposite: true,
            labels: {
                formatter: function() {
                    if (this.value == 0) {
                        return "待定"
                    } else {
                        return this.value + "元"
                    }
                },
                style: {
                    fontSize: "14px",
                    fontFamily: "Microsoft YaHei"
                },
                y: 3
            }//,
            // min: f.setMin
        },
        
        tooltip: {
            crosshairs: true,
            shared: true,
            borderWidth:0,
            formatter: function() {
                var s = '<small>'+this.points[0].key+'</small>';
                $.each(this.points, function(i, point) {
                    s += '<br/>'+ point.series.name +': '+ point.y;
                });
                return s;
            }
        },
        //曲线设置
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: "pointer"
            }
        },
        //节点浮动框
        legend: {
            enabled: false
        },
        series: seriesData
    };
    $('#zst').length&&$('#zst').highcharts(options);
}

$('#tab-tit').on('fixed', function () {
    $(this).width(1200).find('.tab-info').css('display', 'block');
}).on('unfixed', function () {
    $(this).find('.tab-info').css('display', 'none');
})



