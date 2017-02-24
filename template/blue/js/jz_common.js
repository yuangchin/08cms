// search
var $sCate = $('#s-cate');
$sCate.mouseenter(function () {
    $(this).addClass('s-cate-hover');
})
.mouseleave(function() {
    $(this).removeClass('s-cate-hover');
})
.on('click', 'li', function () {
    var $this = $(this);
    var sOpt = $this.data('param');
    var fm = $sCate.closest('form')[0];

    $this.addClass('act').siblings('li').removeClass('act');
    $sCate.removeClass('s-cate-hover').find('.s-tit').html($this.html());
    $(fm).find('.s-txt label').html(sOpt.searchword);
    fm.caid.value = sOpt.caid;
    fm.addno.value = sOpt.addno;
    fm.searchword.title = fm.searchword.placeholder = sOpt.searchword;
})
.find('.act').trigger('click');
// 导航浮动
var $fixed = $('.fixed')
, $navDt = $fixed.find('.nav-dt')
, isKeep = $navDt.hasClass('keep');

$fixed.length &&
$fixed
.on('scrollUp', function () {
    $fixed.removeClass('fixed-fixed-down')
})
.on('scrollDown', function () {
    $fixed.addClass('fixed-fixed-down')
})
.on('fixed', function () {
    $fixed.addClass('fixed-fixed');
    isKeep && $navDt.removeClass('keep');
})
.on('unfixed', function () {
    $fixed.removeClass('fixed-fixed');
    isKeep && $navDt.addClass('keep');
})
.on('click', '.close', function () {
    $fixed.removeClass('fixed-fixed-down').off('scrollUp scrollDown');
})
.jqFixed($fixed.data());
// 导航下拉列表
var $navHover = $('.nav').find('.hover');
$('.nav')
.on('mouseenter', 'li:not(".keep")', function () {
    var $this = $(this);
    if ($this.is($navHover)) return;
    $this.addClass('hover')
    $navHover.removeClass('hover')
})
.on('mouseleave', 'li:not(".keep")', function () {
    var $this = $(this);
    if ($this.is($navHover)) return;
    $this.removeClass('hover')
    $navHover.addClass('hover')
})
// 检索
$('.condition')
.on('mouseenter', 'dd', function () {
    $(this).addClass('hover')
})
.on('mouseleave', 'dd', function () {
    $(this).removeClass('hover')
})

// 登录
$.getScript(CMS_ABS + uri2MVC('ajax=is_login&varname=test&datatype=js'),function() {
    test.user_info.mid != 0&&setLoginTpl(test.user_info);
})
/**
 * 会员退出
 * @return {[type]}
 */
function logout() {
    $.getScript(CMS_ABS + 'login.php?action=logout&datatype=js&varname=logoutInfo', function(){
        $.jqModal.tip(logoutInfo.message,logoutInfo.error?'error':'succeed');
        setLoginTpl();
        test.user_info.mid = 0;
    })
    return false;
}

/**
 * 会员登录
 * @return {[type]}
 */
function _08Login(obj) {
    var $obj = {
        'cmslogin' : obj.cmslogin.value
        , 'username' : obj.username.value
    }
    obj.cmslogin.value = '登录中...';
    // obj.username.value = encodeURIComponent(obj.username.value);
    $.getScript(CMS_ABS + uri2MVC('ajax=check_login&varname=test&datatype=js/'+$(obj).serialize()), function(){
        if( typeof(test.error) == 'undefined' || typeof(test.message) == 'undefined' ){
            $.jqModal.tip('服务器返回格式错误','error');
        }else if(test.error){
            $.jqModal.tip(test.error,'error');
        } else{
            $(obj).closest('.modal').jqModal('hide');
            test.user_info.mid != 0 && setLoginTpl(test.user_info);
            $(obj).jqValidate('resetForm');
            if (obj.regcode) obj['regcode-img'].src += 1;
        }
        obj.cmslogin.value = $obj['cmslogin'];

    })
    obj.username.value = $obj['username'];
    return false;
}


/**
 * 登录模板
 * @return {[text]} 模板内容
 */
function setLoginTpl(o) {
    var __html = o ?
    '<span class="login-info"><i class="l">您好,' + (o.qq_nickname||o.mname) + '</i>\
        <a href="'+ CMS_ABS +'adminm.php" ><i class="ico08 mr5">&#xe658;</i>管理</a> \
        <a onclick="return logout();" href="'+ CMS_ABS +'login.php?action=logout" ><i class="ico08 mr5">&#xe762;</i>退出</a>\
    </span>' :
    '<a class="log-btn" onclick="$(\'#login-wrap\').jqModal(\'show\'); return false;" href="'+ CMS_ABS +'login.php" target="_self">登陆</a>\
    <span>|</span>\
    <a class="log-btn" href="'+ CMS_ABS +'register.php" >注册</a>';

    $('#userLogin').html(__html);
}

// 微信登录
$('#ico-login').click(function() {
    $('.wrap-pc,.wrap-wx').toggle();
    $(this).toggleClass('ico-pc');
    this.title = $($(this).hasClass('ico-pc') ? '.wrap-pc' : '.wrap-wx').data('title');
    if ($(this).hasClass('ico-pc')) {
        $('.wrap-wx').trigger('load-wx');
    }
});
// 微信二维码
$('.wrap-wx').on('load-wx', function (e) {
    var oWxImg = $(e.target).find('img')[0];
    oWxImg.src = tplurl + 'images/blank.gif';
    $.getScript(CMS_ABS + uri2MVC('ajax=is_login&getsid=1&datatype=js&varname=data'), function() {
        if (data.getsid) {
            oWxImg.src = CMS_ABS + uri2MVC('weixin=show_qrcode&scene_id=' + data.getsid + '&expire_seconds=600');
        }
    });
})

$('#wx-tag-tip').hover(function() {
    $('#wx-login-tip').fadeToggle(300);
});

$('#wx-refresh').click(function () {
    $('.wrap-wx').trigger('load-wx');
})

!function ($) {
    !('placeholder' in document.createElement('input')) &&
    $('input[placeholder], textarea[placeholder]').each(function(){
        var $el = $(this);
        var _pla = $('<label class="placeholder">' + $el.attr('placeholder') + '</label>')
            .insertBefore(this).css({
                display : !this.value ? 'block' : 'none'
            })
            .click(function () {
                $el.trigger('focus');
            })

        $el.on('input propertychange change', function () {
            _pla[0].style.display = !this.value ? 'block' : 'none';
        })
    })

    var aVcodes = vcodes.split(',');
    $('.reg-wrap').each(function() {
        var $regWrap = $(this), regcode = $regWrap.data('regcode');
        if ($.inArray(regcode, aVcodes) >= 0) {
            var $regInput = $regWrap.show().find('input[name="regcode"]')
                            .attr({
                                'data-init': '请输入验证码',
                                'data-type': '*',
                                'data-offset': 1
                            });

            $regInput.wrap('<div class="txt-wrap"></div>')
            var codeName = regcode + '_img' + ($regWrap.data('alias') || '');

            if ($regInput.hasClass('ajaxurl')) {
                $regInput
                .attr({
                    'data-url': CMS_ABS + 'index.php?/ajax/regcode/verify/' + codeName + '/datatype/json/domain/' + document.domain
                })
                .on('ajaxDone', function (e,res,fun) {
                    if (res == '验证码错误') fun('error', '验证码错误!');
                    else fun('pass');
                })
            };
            $('<span class="lbl">\
                <img class="regcode-img" name="regcode-img" src="'+ CMS_ABS +'tools/regcode.php?verify='+ codeName +'&t=" />\
                <input type="hidden" name="verify" value="'+ codeName +'"/>\
                <span class="msg">换一张</span>\
            </span>')
            .insertBefore($regInput.parent())
            .on('click', function() {
                $(this).find('img')[0].src += 1;
            })
        }
        else $regWrap.remove();
    })
}(jQuery)

// hover
$('.hover-list').length && $('.hover-list').each(function(){$(this).on('mouseover','li',function(){$(this).addClass('hover').siblings().removeClass('hover');})})
$('.hover-list1').length && $('.hover-list1').each(function(){$(this).on('mouseover','li',function(){$(this).addClass('hover');}).on('mouseout','li',function(){$(this).removeClass('hover');})})

// 发送楼盘信息到手机
function sendLpInfo() {
    console.log('这个功能还没做');
    alert('这个功能还没做');
}

/**
 * @param oForm 表单对象
 * @param fmTit 表单提交成功后，提示title
 * @param iswin 代表是否以弹窗口形式出现，是填写1，反之默认不填
 * @returns {boolean}
 */

function fyCummus(fm,fmTit,iswin) {
    var cuid=fm.cuid.value;
    var ajaxscpit=(cuid==8||cuid==35||cuid==45)?'cutgbaoming': ( cuid==3 ? 'loupanduanx':'cuajaxpost');
    if(cuid==46) ajaxscpit= 'cusms';
    var fmbtn = $(fm).find('[type="submit"]')[0];
    var btnTxt = fmbtn.value;
    fmbtn.value = '提交中...'
    $.getJSON(CMS_ABS + uri2MVC('ajax='+ajaxscpit+'/' + $(fm).serialize() +'/datatype=json') + '&callback=?',function(d) {
        if (!d.error) {
            $.jqModal.tip(fmTit?fmTit+'成功！':d.message,'succeed');
            // 如果有发送到手机,需要发送手机
            if (cuid == 3 && fm['fmdata[dyfl]5'] && fm['fmdata[dyfl]5'].checked) sendLpInfo();
            // 重置
            fmregcode(fm,iswin);
        }else{
            if(d.error=="您没有此交互的操作权限!"){
                $('#login-wrap').jqModal('show')
            }else{
                $.jqModal.tip(d.error,'warn');
                if(fm['regcode-img']) fm['regcode-img'].src += 1;
            }
        };
        fmbtn.value = btnTxt;
    });
    return false;
}

function fmregcode(fm,iswin){
    if (iswin) $(fm).closest('.modal').jqModal('hide');
    $(fm).jqValidate('resetForm');
    if(fm['regcode-img']) fm['regcode-img'].src += 1;
}
//文档收藏与会员收藏ajax
/**
 * @param id--分为两种会员mid、文档aid(必填)
 * @param typeVal--分为两种类型：文档收藏(默认不填)、店铺收藏(typeVal为m)
 * @param cuid--交互cuid(默认为6)
 * @param other--new新房动态\old新增二手房\rent新增出租（看情况，当收藏楼盘）
 */
function publicCollect(id,typeVal,cuid,other){
    if(test.user_info.mid == 0) {
        var $popLog = $('#login-wrap');
        $popLog.jqModal($popLog.data());
        return false;
    }
    if(typeVal){
        var urlbase='ajax=cuajaxpost&cuid=11&cutype=m&tomid='+id+'&aj_func=Favor&pfield=tomid';
    }else{
        var dyohObj=other?'&'+other+'=1':'',dycuid=cuid?cuid:'6';
        var csfile = cuid=='7' ? 'cuscloupan' : 'cuajaxpost';
        var urlbase='ajax='+csfile+'&cuid='+dycuid+'&cutype=a&aid='+id+''+dyohObj+'&aj_func=Favor&pfield=aid';
    }
    $.getJSON(CMS_ABS + uri2MVC(urlbase+"&datatype=json"), function(info){
        if(info.result=='OK'){
            $.jqModal.tip('收藏添加成功！','succeed');
        }else if(info.result=='Repeat'){
            $.jqModal.tip('不能重复收藏','error');
        }
    });
}

/**
 * 格式化时间
 * @return {[type]} 2014-10-10 10:10:10
 */
function getLocalTime(nS,T) {
    var myDate = new Date(parseInt(nS) * 1000);
    var myDateStr = myDate.getFullYear() + '-' + (myDate.getMonth() + 1) + '-' + (myDate.getDate() < 10 ? '0' : '') + myDate.getDate();
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
        return myDateStr + ' ' + myDate.getHours() + ':' + myDate.getMinutes() + ':' + myDate.getSeconds();
    }else{
        return myDateStr ;
    }
}


