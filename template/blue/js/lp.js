$.getScript(CMS_ABS + uri2MVC('ajax=lp_commus&aid=' + lpid), function() {
    getYx();
    getPoint();
})

$('#items').length && $('#items').on('click', 'a', function() {
    var idcon = $(this).attr('data-idcon').split('_');
    if (cooktime > 0) {
        if (!$.cookie('loupan' + lpid + '_' + idcon[0])) {
            $.cookie('loupan' + lpid + '_' + idcon[0], 1, {
                expires: cooktime
            });
        } else {
            $.jqModal.tip('请不要频繁操作！', 'info');
            return false;
        }
    }
    $.getScript(CMS_ABS + uri2MVC("ajax=addyinxiang&aid=" + lpid + "&yinxiang=" + encodeURIComponent(idcon[1])), function() {
        var $span = $('#items').find('span');
        for (var i = 0; i < yxPerData.length; i++) {
            $span.eq(i).html(yxPerData[i]['baifenbi'] + '%');
        }
        $.jqModal.tip('提交成功！', 'succeed');
    });
})
/**
 * 得到背景颜色
 * @return color   颜色值
 */
function getBg(i) {
    return ['#F27C78','#EFBE23','#8DCA48','#8BD3E9','#6BB6D6','#BDA3E2','#5B89C7','#E192C2','#EF9B39'][i%9];
}

$.getScript(tplurl+'js/jq.cookie.js');

// 加载印象
function getYx() {
    var yxHtml = '';
    $.each(yxData, function(i, e) {
                yxHtml += '<a class="btn btn-sm" style="background-color:' + getBg(i) + '" data-idcon="' + yxData[i]['cid'] + '_' + yxData[i]['impression'] + '">' + yxData[i]['impression'] + '<span>' + yxData[i]['per'] + '%</span></a>'
            })
    $('#items').html(yxHtml);  
}

/**
 * 点击按钮提交印象
 */
function add_yinxiang() {
    var yx = $('#yinxiang')[0];
    if (yx.value.length == 0 || yx.value == '至多5个字印象') {
        $.jqModal.tip('印象不能为空', 'info')
        return false;
    }!$.cookie('loupan' + lpid) && $.cookie('loupan' + lpid, 1, {
        expires: cooktime
    });

    var loupan_aid = $.cookie('loupan' + lpid),
        num = totalnum;

    if (loupan_aid > num) {
        $.jqModal.tip('只能提交' + num + '个印象!', 'info')
        return false;
    }
    $.getScript(CMS_ABS + uri2MVC("ajax=addyinxiang&subm=1&aid=" + lpid + "&yinxiang=" + encodeURIComponent(yx.value)), function() {
        if (info.indexOf("成功") != -1) {
            $.getScript(CMS_ABS + uri2MVC('ajax=lp_commus&aid=' + lpid), function() {
                $.jqModal.tip('添加成功！', 'succeed');
                yx.value = '';
                getYx();
            })
            if (info.indexOf("存在") == -1) {
                //点击按钮提交印象时，通过cookie记录个数来限制印象的提交个数
                $.cookie('loupan' + lpid, Number(loupan_aid) + 1, {
                    expires: cooktime
                });
            }
        } else {
            $.jqModal.tip(info, 'info');
        }
    });
}
// 加载评分
function getPoint() {
    
// 评分
    var pointHtml = '',
        starHtml = '';
    for(var i in pointData){
        if (i == 'total') {
            $('#total').html(pointData[i]);
        }else{
            pointHtml += '<li id="'+pointData[i]['ename']+'">'
                            +'<span class="pnm">'+pointData[i]['cname']+'：</span>'
                            +'<span class="per"><em style="width:'+pointData[i]['per']+'%;background-color:'+getBg(i)+'"></em>'
                            +'</span>'
                            +'<span class="point">'+pointData[i]['point']+'分</span>'
                            +'<span class="pren">'+pointData[i]['pren']+'人</span>'
                        +'</li>'
            starHtml +=
                    '<li><i class="l lbl">'+pointData[i]['cname']+'：</i>'
                        +'<i id="s-'+pointData[i]['ename']+'" class="star l"><b></b>'
                            +'<div class="blank0"></div>'
                            +'<a>1</a><a>2</a><a>3</a><a>4</a><a>5</a><a>6</a><a>7</a><a>8</a><a>9</a><a>10</a>'
                        +'</i>'
                        +'<i class="tip"><b>0</b>分</i> '
                    +'</li>'
        };
    }
    $('#point-list').html(pointHtml);
    $('#star-list').html(starHtml)
        .find('i.star').each(function() {
            var iS = 0,
                iStar = 0,
                $o = $(this),
                $b = $o.find('b'),
                $msg = $o.next().find('b');

            $o.on('mouseover', 'a', function(e) {
                    fnP(e.target.innerHTML);
                }).on('mouseout', 'a', function() {
                    fnP();
                }).on('click', 'a', function(e) {
                    iStar = e.target.innerHTML;
                    add_point($o[0].id.replace('s-', ''), iStar);
                })
                //评分处理
            function fnP(n) {
                $msg[0].innerHTML = iS = (n || iStar) * 10;
                $b[0].style.width = iS + 'px';
            }
        });
}
function add_point(name,point){
    $.getScript(CMS_ABS + uri2MVC("ajax=add_point&aid=" + lpid + "&field=" + name + "&point=" + point),function(message){
        if(dpPerData['error']){
            $.jqModal.tip('请不要频繁操作！','error');
            return;
        }
        $('#total').html(dpPerData['total']);
        var $zbitem=$('#'+dpPerData['field']);

        $zbitem.find('.pren').html(dpPerData['renshu'] + '人');
        $zbitem.find('.point').html(dpPerData['point'] + '分');
        $zbitem.find('.per em').width(dpPerData['point'] + '%');
        $.jqModal.tip('提交成功！','succeed');
    });
}

$(function(){
    // 走势图
    if(typeof(jsonData) != 'undefined' && jsonData.series && jsonData.series.length){
        var options = {
            colors:[ "#ee4433", "#F8CE5D", "#339966" ],
            chart:{
                renderTo:'zst',
                type:"line"
            },
            title:{
                text:""
            },
            subtitle:{
                text:""
            },
            xAxis:{
                categories:jsonData.month_s,
                tickmarkPlacement:"on",
                labels:{
                    style:{
                        fontSize:"14px",
                        fontFamily:"Microsoft YaHei"
                    },
                    y:25
                }
            },
            yAxis:{
                title:"",
                gridLineColor:"#ddd",
                opposite:true,
                labels:{
                    formatter:function() {
                        if (this.value == 0) {
                            return "待定";
                        } else {
                            return this.value + "元";
                        }
                    },
                    style:{
                        fontSize:"14px",
                        fontFamily:"Microsoft YaHei"
                    },
                    y:3
                },
                min:jsonData.min
            },
            tooltip:{
                crosshairs:true,
                useHTML:true,
                borderWidth:1,
                borderColor:"#999999",
                borderRadius:3,
                backgroundColor:"#FFFFFF",
                style:{
                    padding:"8px"
                },
                shared:true,
                formatter:function() {
                    if (this.y == 0) {
                        return "待定"
                    } else {
                        return jsonData.series[0].name + '<br/>' + this.y + "元/m&sup2;"
                    }
                }
            },
            legend:{
                enabled:false
            },
            plotOptions:{
                line:{
                    fillOpacity:.4,
                    marker:{
                        symbol:"circle",
                        radius:5,
                        lineWidth:1
                    }
                }
            },
            series:jsonData.series
        };
        if($('#zst').length) $('#zst').highcharts(options)
    }
})

$(function () {
    $('#lpdt').height($('#lpyx').height());
})