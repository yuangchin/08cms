// uri2MVC
function uri2MVC(uri,addFileName)
{var _split='/';if(!_08_ROUTE_ENTRANCE)
{var _08_ROUTE_ENTRANCE='index.php?/';}
(addFileName==undefined)&&(addFileName=true);var _uri='';if(typeof uri=='string')
{_uri=uri.replace(/&/g,_split).replace(/=/g,_split);}
else
{for(var i in uri)
{_uri+=(i+_split+uri[i]+_split);}}
var _endstr=_uri.charAt(_uri.length-1);if(_endstr==_split)
{_uri=_uri.substr(0,_uri.length-1);}
var newURI=addFileName?_08_ROUTE_ENTRANCE+_uri:_uri;if(!/domain/i.test(newURI))
{newURI+=(_split+'domain'+_split+document.domain);}
return newURI;}

// 初始化
$(function() {
    Jingle.launch({
        // appType : 'muti' ,
        showPageLoading : true
    });
    //点评图标转换
    $('.comment-list').html(function(a,b) {
            return showFace(b);
        })

    $('body').on('click','#mapinfo_js',function(event) {
        event.preventDefault();
        $('#up_refresh_article').trigger('srl');
        /* Act on the event */
    });

    var htmlH=$('.js_minh200').eq(0);
    if(htmlH.height()>208){
        $(htmlH).css({
            height: 208,
            overflow: 'hidden'
        });
        $(".house_flex").css('display', 'block');
    } else {
        $(".house_flex").css('display','none');
    }

});

$('.house_flex').on('click', 'span', function(event) {
    event.preventDefault();
    /* Act on the event */
    if($(this).text()=="展开"){
        $('.js_minh200').css({
            height: 'auto',
            overflow: 'visible'
        });
        $(this).html('缩小'+'<i class="icon-e686"></i>');
    } else {
        $('.js_minh200').css({
            height: 208,
            overflow: 'hidden'
        });
        $(this).html('展开'+'<i class="icon-e684"></i>');
    }
});


$('section').one('pageshow',function() {
    var $header = $(this).find('header');

    $(this).find('article').andSelf().each(function() {
        var _this = this, str = _this.getAttribute('data-btn');
        str&&$.each(str.split(','), function(a,b) {
            $(_this).find('#'+b).show();
        })
    });

    if($header.find('#back').css('display')=='none') $('#logo').css('display','block');
    var $nav = $header.find('nav'),
        windW=$(window).width()>640?640:$(window).width(),
        titH=windW-$nav.eq(0).width()-$nav.eq(1).width()-10;
    this.title&&$header.find('.title').html(this.title).css({
        marginLeft:$nav.eq(0).width()
        ,width:titH
    });
    //解决卡片跳转时title出不来问题
    $header.css('position','fixed');


    if(this.getAttribute('data-footer')!='false') $(this).find('footer').removeClass('dn');
})


// 导航
$('body').on('click', '.menu',function(e){
    J.popup({
        elId : 'tpl_popup_menu'
        , pos : 'top-second'
    })
    return false;
})

if(typeof(opt)!='undefined'){
    if ($(opt.wrap).parent().hasClass('active')) {//加载完成后当前‘section’初始化
        pullRefresh(opt,this.id);
    }else{
        $(opt.wrap).parent().one('pageshow',function() {//section显示时才初始化
            pullRefresh(opt, this.id);
        })
    }
};

//向下滚动触发更多数据
$(window).on('scroll', function() {
    /*alert(111)*/
    var scrollh=$(document).height(),
        bua = navigator.userAgent.toLowerCase(),
        winH = $(window);
    if (bua.indexOf('iphone') != -1 || bua.indexOf('ios') != -1) {
        scrollh = scrollh - 140;
    } else {
        scrollh = scrollh - 80;
    }

    if ((winH.scrollTop() + winH.height()) >= scrollh) {
        $('section.active').trigger('srl');
    }
})

// 上拉刷新 文档
function pullRefresh(o, id) {
    var refreshOpt = {
        _param : {
            'aj_model'    : 'a,4,4', //模型信息(a-文档/m-会员/cu-交互/co-类目,3,1-模型表; 如:a,3,1)
            'aj_check'    : 1 ,     //是否审核(0/1或不设置)
            'aj_pagenum'  : 2 , //当前分页(数字,默认2)
            'aj_pagesize'  : 10 ,
            'datatype'    : 'json',
            'ordermode'    : 0
        },
        filterUrl : '' ,
        ajax : 'pageload' ,
        type : 'pullUp'
    }
    var sid = id || 'index_section';
    var _opt = $.extend(true, {}, refreshOpt, o);
    var isFinished = 1;

    if (_opt._param.aj_pagenum == 1) {
        getDefData();
    };

    // 滚动到页面底部时，自动加载更多
    $('#' + sid).off('srl').on('srl', function() {
        if (isFinished) {
                if(!isFinished) return;
                isFinished = 0;
                getDefData();
            }
    })

    J.Refresh( _opt.wrap, _opt.type, function(){
        if(!isFinished) return;
        isFinished = 0;
        getDefData(this);
    })

    function getDefData(scroll) {
        $('#upinfo_js span').parent().show().end().eq(0).removeClass('icon-e61c').addClass('icon-e982');
        $.getJSON(CMS_ABS + uri2MVC('ajax='+_opt.ajax+'/' + $.param(_opt._param).replace(/\+/g,"%20") + _opt.filterUrl) +'&callback=?', function(data){
                var _html = '';
                if (data.length) {
                    $.each(data,function(a,b) {
                        _html += _opt.template.call(b);
                    })
                    $(_opt.dataWrap).append(showFace(_html));
                    _opt._param.aj_pagenum > 1&&J.showToast('加载成功','toast top');
                    setTimeout(function () {
                        $('#upinfo_js span').eq(0).removeClass('icon-e982').addClass('icon-e61c');
                        _opt._param.aj_pagenum++;
                    }, 100);
                    isFinished = 1;
                    _opt._param.aj_pagesize > data.length&&$(_opt.wrap).find('.refresh-container').hide();
                }else{
                        _opt._param.aj_pagenum > 1&&J.showToast('没有数据','error top');
                        // 如果是第一页且没有数据,则提示没有数据
                        if(_opt._param.aj_pagenum==1){
                            _html ='<li class="noinfo">~ 暂无相关数据 ~</li>';
                            $(_opt.dataWrap).append(showFace(_html));
                    }
                        setTimeout(function () {
                                $(_opt.wrap).find('.refresh-container').hide();
                            // _opt._param.aj_pagenum > 1?scroll.refresh():J.Scroll(_opt.wrap);
                        }, 100);
                };

            })
    }


}

/**
 * [noLogInfo 会员中心没有登陆时的列表信息提示]
 * @return {[type]} [description]
 */
function noLogInfo(ement){
    var _html ='<li class="noinfo">~ 请登录后查看相关信息 ~</li>';
    $(ement).find('ul.list').append(_html);
}

//popup
function popupExt(args) {

    var tgt = (typeof args == 'object' ? $(args).attr('href') : args).replace('#','');
    J.popup({
        elId           : tgt
        , pos          : 'center'
        , url          : tgt.indexOf('//') > 0 ? tgt : null
        , showCloseBtn : 1
    })

    return false;
}

// loupan
//走势图
    // body...
typeof(_data)!='undefined'&&renderLine();
function renderLine(){
    //重新设置canvas大小
    var wh = {
            height : $(window).height()/2<200?200:$(window).height()/2,
            width : $('#section_container').width() - 20
        };
     $('#line_canvas').attr({width:wh.width, height:wh.height});
    var dataMin = Math.min.apply(null,_data.datasets[0].data)
    var _start = dataMin < 1000 ? 0 : dataMin - 1000;
    var data = _data;
    var line = new JChart.Line(data,{
        id : 'line_canvas' ,
        smooth : false ,
        fill : false ,
        scale : {
                step : 5,//(刻度的个数)
            stepValue : 500,//(每两个刻度线之间的差值)
            start : _start //(起始刻度值)
        },
        datasetShowNumber : 6
    });
    line.on('click.point',function(d,i,j){
        // J.alert(data.labels[i],d);
        setTimeout(function() {
            J.popup({
                html: '<div style="padding:10px;font-size: 20px;font-weight: 600;color:#E74C3C "><span class="f-peter-river">'+data.labels[i].replace('-','月')+'日</span><br>'+d+'元/m&sup2;</div>',
                pos : 'center'
            })
        },300);
        return false;
    });
    line.draw();
}
//重置表单项
function resetFm(f) {
    $(f).find('input[type="text"],textarea').val('').eq(0).focus();
    resetReg(f);
}

function resetReg(f){
    if(f.regcode) {
        f.regcode.value = '';
        f.regcodeimg.src+=1;
    }
}
// 点评
var plFinished = 1;
function add_pl(fm){
    // cmtOpt._param.aid = fm.aid;
    if (plFinished == 0) return false;
    plFinished = 0;
    var btnHTML = fm.bsubmit.innerHTML;
    fm.bsubmit.innerHTML = '正在提交...';
    $.getJSON(CMS_ABS + uri2MVC("ajax=cuajaxpost/"+$(fm).serialize()) + '&callback=?',function(d){

        if(!d.error){
            if (!fm.tocid||!fm.tocid.value) {
                //评论
                d.cu_data.louceng = '就在刚刚。。。';
                opt.autoCheck&&$(opt.dataWrap).prepend(opt.template.call(d.cu_data));
                // 第一次成功再隐藏~暂无数据~提示
                $(opt.dataWrap).children('.noinfo').hide();

            } else{
                // 回复
                $(opt.template.call(d.cu_data,1)).insertAfter(fm.parentNode);
                fm.style.display = 'none';
            };
            J.showToast(d.message,'success top');
            // J.Scroll(opt.wrap);

            // 重置
            resetFm(fm);
        }else{
            J.showToast(d.error,'info top');
            // 重置
            resetReg(fm);
        }
        plFinished = 1
        fm.bsubmit.innerHTML = btnHTML;
    });
    return false;
}
// 回复
var $hfFm;

$('#comment-list').on('tap','.hf-btn',function(e) {
    if($hfFm){
        if (!$(this.parentNode).next('form').length) {
            $hfFm.insertAfter(this.parentNode).hide();
        };
        $hfFm.toggle().find('input[name="tocid"]').val($(this).attr('data-cid'));

    }else{
        $hfFm = $('#commu1').clone().insertAfter(this.parentNode).css({padding: 10, backgroundColor: '#EEE', borderRadius: 5, marginTop: 10});
        $hfFm.find('#regcodeimg').attr('src', CMS_ABS +'tools/regcode.php?verify=commu1_img1&t='+ parseInt(new Date().getTime() / 1e3)).tap(function() {
            this.src += 1;
        })
        .next()[0].value = 'commu1_img1';
    }
    $hfFm.find('input[name="tocid"]').val($(this).attr('data-cid'));
    e.stopPropagation();
})
/**
 * 文字表情转换成图片
 * @param  {[type]} content 要转换的内容
 * @return {[type]}
 */
function showFace(content){
    return content.replace(/\{\:face(\d+?)\:\}/g,"<img src='"+tplurl+"images/face/face$1.gif'/>");;
}


/**
 * 格式化时间
 * @return {[type]} 2014-10-10 10:10:10
 */
function getLocalTime(nS,T) {
    var myDate = new Date(parseInt(nS) * 1000);
    if (T==1) {
        var _nS = parseInt(new Date().getTime()/1000) - nS;
        switch(true){
            case _nS < 60:
                return '才刚刚';
            break;
            case _nS < 1800:
                return Math.floor(_nS / 60) + '分钟前'
            break;
            case _nS < 3600:
                return '半小时前';
            break;
            case _nS < 86400:
                return Math.floor(_nS / 3660) + '小时前';
            break;
            case _nS < 86400 * 30:
                return Math.floor(_nS / 86400) + '天前';
            break;
            default :
                return Math.floor(_nS / 86400 / 30) + '个月前';
            break;
        }
    }else if(T==2){
        return myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+(myDate.getDate()<10&&'0'||'')+myDate.getDate()+' '+myDate.getHours()+':'+myDate.getMinutes()+':'+myDate.getSeconds();
    }else{
        return myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+(myDate.getDate()<10&&'0'||'')+myDate.getDate();
    }
}

//文档收藏与会员收藏ajax
/**
 * @param id--分为两种会员mid、文档aid(必填)
 * @param typeVal--分为两种类型：文档收藏(默认不填)、店铺收藏(typeVal为m)
 * @param cuid--交互cuid(默认为6)
 * @param other--new新房\old新增二手房\rent新增出租（看情况，当收藏楼盘）
 */
function publicCollect(id,typeVal,cuid,other){
    isLogin();
    if(typeVal){
        var urlbase='ajax=cuajaxpost&cuid=11&cutype=m&tomid='+id+'&aj_func=Favor&pfield=tomid';
    }else{
        var dyohObj=other?'&'+other+'=1':'',dycuid=cuid?cuid:'6';
        var csfile = cuid=='7' ? 'cuscloupan' : 'cuajaxpost';
        var urlbase='ajax='+csfile+'&cuid='+dycuid+'&cutype=a&aid='+id+''+dyohObj+'&aj_func=Favor&pfield=aid';
    }
    $.getJSON(CMS_ABS + uri2MVC(urlbase + "&datatype=json") + '&callback=?', function(info) {
        if (info.result == 'OK') {
            J.showToast('收藏成功！', 'success top');
        } else if (info.result == 'Repeat') {
            J.showToast('不能重复收藏', 'info top');
        }
    });
    return false;
}
// 表单提交
function fyCummus(fm,t) {
    var btnHTML = fm.bsubmit.innerHTML;
    fm.bsubmit.innerHTML = '提交中...';

    var getCuid = fm.cuid.value;
    var ajaxscpit = (getCuid==8||getCuid==45)?'cutgbaoming':'cuajaxpost';
    $.getJSON(CMS_ABS + uri2MVC('ajax='+ajaxscpit+'/' + $(fm).serialize() +'/datatype=json/') + '&callback=?',function(d) {
        if (!d.error) {
            J.showToast(t?t+'成功！':d.message,'success top');
            J.closePopup();
            // 重置
            resetFm(fm);
        }else{
            J.showToast(d.error,'info top');
            resetReg(fm);
            if(d.error=="您没有此交互的操作权限。"){
                J.closePopup();
                isLogin();
            }
        };
        fm.bsubmit.innerHTML = btnHTML;
    });
    return false;
}
// 验证码
function loadRegcode(args) {
    var $reg = $('form').find('input[name="regcode"]');
    args = args.split(',');
    $reg.each(function() {
        var fmId = $(this).closest('form')[0].id;
        if($.inArray(fmId,args)>=0){
                $('<img class="regcode-img" id="regcodeimg" src="'+ CMS_ABS +'tools/regcode.php?verify='+ fmId +'_img&t='+parseInt(new Date().getTime() / 1e3)+'" /><input type="hidden" name="verify" value="'+ fmId +'_img"/>').insertAfter(this)
                .click(function() {
                    this.src += 1;
                });
        }else{
            $(fmId == 'archive_fy'?this.parentNode:this).remove();
        }

    });
}

loadRegcode(vcodes);
// gotop
// var vendor = (function() {
//  var ds  = document.createElement('div').style,
//  vendors = 't,webkitT,MozT,msT,OT'.split(','),
//  i       = 0,
//  l       = vendors.length;

//  for ( ; i < l; i++ ) {
//      if ( vendors[i] + 'ransform' in ds ) {
//          return vendors[i].substr(0, vendors[i].length - 1);
//      }
//  }
//  return false;
// })()

// $('#gotop').tap(function() {
//  var _S = $('section.active').find('article.active').children();
//  _S.last().children().add(_S[0]).css((vendor && '-' + vendor + '-')+'transform', 'translate(0, 0) scale(1) translateZ(0)');
//  return false;
// });


/***
内页-加载更多的内容
***/
function morePage(o){
    o.aj_page++;
    if(o.aj_page>o.aj_pmax) {
        J.showToast('没有数据','error top');
        $(loadopt.moreObj).hide();
        return false
    };
    $.get(o.url+'&page='+o.aj_page+'&inajax=1&domain='+document.domain,function(html){
        $(o.loadObj).append(html);
        //图片延时加载
        $(".detail-img img").length&&$(".detail-img img").each(function(){
            imgLoad(this, function() {
                J.Scroll("#up_refresh_article");
            });
        });
        setTimeout(function () {
            J.Scroll(o.scrollObj)
        },500);
    });
}


//图片延时加载
// $(".detail-img img").length&&$(".detail-img img").each(function(){
//  imgLoad(this, function() {
//         J.Scroll("#up_refresh_article");
//     });
// });
function imgLoad(img,callback) {
    var timer = setInterval(function() {
        if (img.complete) {
            callback(img)
            clearInterval(timer)
        }
    }, 50)
}
/**
 * @class _08cms.multiStore
 * @author Peace@08cms.com
 * @参考: http://www.cnblogs.com/zjcn/archive/2012/07/03/2575026.html#comboWrap
 * Demo: _08cms.locStore.setGroup('Xmkd_chid2','542476',10);
 */

function multiStore(flag){ // local,session
    this.parFlag = flag=='session' ? 'sessionStorage' : 'localStorage';
    this.parStore = flag=='session' ? window.sessionStorage : window.localStorage;
    // 是否支持localStorage/sessionStorage
    this.ready = function(){
        return (this.parFlag in window) && (window[this.parFlag] !== null);
    };
    // 扩展 : 最多设置保存mnum个key(如最近浏览历史记录)
    this.setGroup = function(keyid,nowkey,mnum){
        if(nowkey.length==0) return;
        if(!mnum) mnum = 10;
        var oldkeys = this.get(keyid);
        if(!oldkeys){
            var keystr = nowkey;
        }else{
            var oldarr = oldkeys.split(',');
            var keystr = nowkey; unum = 1;
            for(var i=0;i<oldarr.length;i++){
                if(oldarr[i]==nowkey || oldarr[i].length==0) continue;
                if(unum<mnum){
                    keystr += ','+oldarr[i];
                    unum++;
                }else{
                    break;
                }
            }
        }
        keystr = keystr.replace(/[^0-9A-Za-z_\.\-\:\,\|\;]/g,''); // setGroup内容字符限制 \=\)\(\]\[  善用ascii码
        this.set(keyid,keystr);
    };
    // 设置值
    this.set = function(key, value){
        //在iPhone/iPad上有时设置setItem()时会出现诡异的QUOTA_EXCEEDED_ERR错误；这时一般在setItem之前，先removeItem()就ok了
        if( this.get(key) !== null )
            this.remove(key);
        this.parStore.setItem(key, value);
    };
    // 获取值 查询不存在的key时，有的浏览器返回undefined，这里统一返回null
    this.get = function(key){
        var v = this.parStore.getItem(key);
        return v === undefined ? null : v;
    };
    this.each = function(fn){
        var n = this.parStore.length, i = 0, fn = fn || function(){}, key;
        for(; i<n; i++){
            key = this.parStore.key(i);
            if( fn.call(this, key, this.get(key)) === false )
                break;
            //如果内容被删除，则总长度和索引都同步减少
            if( this.parStore.length < n ){
                n --;
                i --;
            }
        }
    };
    this.remove = function(key){
        this.parStore.removeItem(key);
    }
    this.clear = function(){
        this.parStore.clear();
    };

}
var _08cms = {};
_08cms.locStore = new multiStore('local');
_08cms.sesStore = new multiStore('session');

/**
 * 会员登录
 */
var loginfo = {};
loginfo.user_info = {}

// 是否登录
function isLogin() {
    $.getJSON(CMS_ABS + uri2MVC('ajax=is_login&datatype=json') + '&callback=?', function(d) {
        loginfo = d;
        loginfo.user_info.mid != 0 && setLoginTpl(loginfo.user_info);

        if (loginfo && loginfo.user_info.mid == 0) {
            popupExt('#tpl_popup_login');
            return false;
        };
    })
}
/**
 * 会员退出
 * @return {[type]}
 */
function logout() {
    J.showMask('正在退出...');
    $.getScript(CMS_ABS + 'login.php?action=logout&datatype=js&varname=test', function(){
        J.showToast(test.message,'success top');
        setLoginTpl();
        loginfo&&(loginfo.user_info.mid = 0);
        J.hideMask();
    })
    return false;
}

/**
 * 登录模板
 * @return {[text]} 模板内容
 */
function setLoginTpl(o) {
    var __html = o?'<div class="grid">'
                + '    <div class="col-0">'
                + '        <div class="mem-img">'
                + '            <img src="'+o.image+'"  height="71" width="71"/>'
                + '        </div>'
                + '    </div>'
                + '    <div class="col-1">'
                + '        <div class="grid fz14 p10">'
                + '            <div class="col-1">欢迎,'+(o.qq_nickname||o.mname)+'</div>'
                + '            <div class="col-0"><a onclick="logout()" class="block"><i class="icon icon-e762 left f-pomegranate"></i>退出</a></div>'
                + '        </div>'
                + '        <div class="fz14 p5">'
                + '            <a href="'+CMS_ABS+'mobile/index.php?caid=11&addno=1" class="button alizarin small block"><i class="icon-e641"></i>修改资料</a>'
                + '        </div>'
                + '    </div>'
                + '</div>'
                : '<div class="grid">'
                + '    <div class="col-0">'
                + '        <div class="mem-img">'
                + '            <i class="icon-e63e"></i>'
                + '        </div>'
                + '    </div>'
                + '    <div class="col-1">'
                + '        <div class="col-1 p5">'
                + '            <button class="small block" onclick="return isLogin();" ><i class="icon-e603"></i>会员登录</button>'
                + '        </div>'
                + '        <div class="grid fz14">'
                + '            <div class="col-1 p5"><a class="button carrot small block"><i class="icon-f059"></i>忘记密码</a></div>'
                + '            <div class="col-1 p5"><a href="'+mobileurl+'register.php" class="button alizarin small block"><i class="icon-f059"></i>会员注册</a></div>'
                + '        </div>'
                + '    </div>'
                + '</div>';

    $('#userLogin').html(__html);
}
/**
 * 会员登录
 * @return {[type]}
 */
function _08Login(fm) {
    var btnHTML = fm.bsubmit.innerHTML;
    fm.bsubmit.innerHTML = '登录中...'
    $.getScript(CMS_ABS + uri2MVC('ajax=check_login/' + $(fm).serialize() + '/datatype/js/varname=d/') + '&callback=?', function(){
        loginfo = d ;
        if( typeof(d.error) == 'undefined' || typeof(d.message) == 'undefined' ){
            J.showToast('服务器返回格式错误','error top');
        }else if( d.error ){
            J.showToast(d.error,'error top');
            resetReg(fm);
        }else{
            J.closePopup();
            J.showToast('登录成功','success top');
            d.user_info.mid != 0&&setLoginTpl(d.user_info);
            // 重置
            resetFm(fm);
        }
        fm.bsubmit.innerHTML = btnHTML;
    })
    return false;
}

//tel电话处理
$('a[href^="tel:"]').click(function() {
    var mobile=$(this).attr('href'),
        aTel = mobile.replace('-','').replace('转',':').split(':');
    var doTel=function(){
        if (typeof(uexCall)!='undefined') {
            uexCall.dial(aTel[1]);
        }else{
            location.href = 'tel:'+aTel[1];
        }
    }

    if(aTel.length == 2) doTel();
    else if(aTel.length == 3) {
            location.href = 'tel:' + aTel[1] + ',' + aTel[2];
    }
    return false;
});

//返回到顶部
function goTop(acceleration, time) {
    acceleration = acceleration || 0.1;
    time = time || 16;
   var y=$(window).scrollTop(),
       speed = 1 + acceleration;
    $(window).scrollTop(Math.floor(y/speed));
    if ( y>0) {
        var invokeFunction = "goTop(" + acceleration + ", " + time + ")";
        window.setTimeout(invokeFunction, time);
    }
}
$("#gotop").on('click',  function(event) {
    event.preventDefault();
    goTop();
});
$(window).on('scroll',  function(event) {
    event.preventDefault();
    var stph=$(window).scrollTop();
    if(stph<120){
         $("#gotop").hide();
    }else{
       $("#gotop").show();
    }

});


//last
window.jQuery = window.Zepto;


// 计算器
//本息还款的月还款额(参数: 年利率/贷款总额/贷款总月份)
function getMonthMoney1(lilv,total,month){
    var lilv_month = lilv / 12;//月利率
    return total * lilv_month * Math.pow(1 + lilv_month, month) / ( Math.pow(1 + lilv_month, month) -1 );
}

var zj = $('#zj').text()*1;
$('#sf').html(Math.round(zj*.3*100)/100);
$('#yg').html(Math.round(getMonthMoney1(0.0490, zj*.7, 240)*1000000)/100);