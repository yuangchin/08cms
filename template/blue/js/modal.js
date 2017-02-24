/**
*author:ahuing
*date:2014-08-06
*modal v2.0
*modify:2014-09-04
 */
(function($){

$.extend({
    // 获取页面大小，窗口大小
    getSize:function(o) {
        var $w = $(window)
        , s    = {
            ww  : $w.width()
            ,wh : $w.height()
            ,w  : o.outerWidth()
            ,h  : o.outerHeight()
        }
        s.maxL=s.ww-s.w;
        s.maxT=s.wh-s.h;
        return s;
    }
    ,isIE6:function() {
        return !-[1,] && !window.XMLHttpRequest;
    }
    ,getIndex:function() {
        return parseInt(new Date().getTime()/1000);
    }
    // 拖拽函数
    ,drag:function(o) {
        var o = $.extend({},dragOpt, o)
            , drag = o.dragHandle||o.obj
            , $w = $(window)
            , isIE6 = $.isIE6();

        drag.css('cursor', 'move').on('mousedown',function(e) {

            // 禁止鼠标选中文字
            this.onselectstart = function() {
                return false
            };

            var _l = parseFloat(o.obj.css('left'))-e.pageX
            ,_t    = parseFloat(o.obj.css('top'))-e.pageY
            ,st    = $w.scrollTop()
            ,s     = $.getSize(o.obj);

            o.obj.css({
                opacity:0.8
                ,zIndex:$.getIndex()
            })
            .find('.ifrlay').css('z-index',11)

            $(document).on('mousemove',function(e) {

                var nL = _l+e.pageX
                ,nT    = _t+e.pageY
                ,l     = nL<0?0:(nL>s.maxL?s.maxL:nL)
                ,t     = nT<0?0:(nT>s.maxT?s.maxT:nT);

                // if (!o.fixed) t = nT-st<0?st:(nT-st>s.maxT?s.maxT+st:nT)
                if (!o.fixed) t = nT

                if (isIE6&&o.fixed) {
                    t=nT<st?st:(nT-st>s.maxT?s.maxT+st:nT);
                    $.modal.top = t-st;
                };

                o.obj.css({
                    left: l
                    ,top: t
                })
            }).on('mouseup',function() {
                $(this).off('mousemove mouseup mousedown');

                o.obj.css('opacity',1)
                .find('.ifrlay').css('z-index',1);
            });

            return false;
        })
    }
    //弹窗
    ,modal:function(o){

        var o = $.extend({},$.modal.opt, o);
        // 加载css
        if($('#modal-'+o.id).length) return;
        var isIE6=$.isIE6()
            ,$w=$(window)
            ,obj={
            init:function() {
                var Z = $.getIndex()
                ,html = '<div id="modal-'+(o.id||Z)+'" class="m-box" style="z-index:'+Z+';position:'+(o.fixed&&!isIE6&&'fixed'||'absolute')+';">'
                            +'<div class ="m-body '+o.modalType+'">'
                                +(o.title?'<div class ="m-title">'+o.title+'</div>':'')
                                +'<div class ="m-content"></div>'
                                +'<a class="m-close" title="关闭" href="#"></a>'
                            +'</div>'
                        +'</div>';

                this.box     = $(html).appendTo('body').css(o.css).addClass(o.animateClass);
                this.title   = this.box.find('.m-title').css(o.titleCss);
                this.content = this.box.find('.m-content');
                this.mBody   = this.box.find('.m-body').css(o.borderCss);
                this.box.find('.m-close').on('click',function() {
                    obj.closeBox();
                    return false;
                });
                //加载遮罩层
                if(o.overlay) {
                    this.overlay = $('<div class ="m-overlay" style="z-index:'+Z+'"></div>').insertBefore(this.box)
                    .css({
                        display:'block'
                        ,opacity:o.overlay
                    })
                    .on('click',function(){
                        if (!o.lock) obj.closeBox();
                    });
                }

                //ie6隐藏select
                if(isIE6) $('select').css('visibility','hidden');

                o.loadBefoe&&o.loadBefoe.call();
                var str = o.target.content;

                switch(o.target.type) {
                    case 'html':
                        obj.content.html(str);
                    break;
                    case 'img':
                        obj.loading(1)

                        var $img = $('<img>')
                        ,_img = new Image();

                        _img.onerror=function () {
                            obj.loading(0)
                        }

                        _img.onload=function () {
                            var imgSize=obj.getImgSize(_img,parseInt($w.width()*.8),parseInt($w.height()*.8));
                            obj.content.html($img.css({
                                width:imgSize[0]
                                ,height:imgSize[1]
                            }));
                            obj.setPos();
                        }
                        _img.src = str;
                        $img.attr('src',str);
                    break;
                    case 'url':
                        obj.loading(1)
                        $.ajax({
                            url:str
                            ,success:function(html){
                                obj.content.html(html);
                                obj.setPos();
                            }
                            ,error:function(xml,textStatus,error){
                                obj.loading()
                                obj.setPos();
                            }
                        });
                    break;
                    case 'element':
                        if(!$('.m-box').find(str).length){
                            $(str).show().appendTo(obj.content)
                            isIE6&&$(str).find('select').css('visibility','visible');
                        }
                    break;
                    case 'iframe':

                        $('<iframe id="iframe" scrolling="no" allowtransparency="true" frameborder="0"></iframe><div class="ifrlay"></div>').appendTo(obj.content).eq(0).attr('src',str).load(function() {
                            $(this).css('background','none');
                            $('.m-content,#iframe').height($(this).contents().find('body').height());
                            obj.setPos();
                        });

                }

                obj.setPos();

                $(document).off('keydown.modal').on('keydown.modal', function(e){
                    e.which == 27&&obj.closeBox();
                    return true;
                });

                o.callBack&&o.callBack.call(this);

                if(o.timeout) setTimeout(obj.closeBox,o.timeout);

            }
            ,loading:function(state) {
                obj.content.html('<div class="tip"><i class="ico-'+(state?'loading':'error')+'"></i>'+(state?'加载中':'加载出错')+'...</div>');
            }
            ,getImgSize:function (img, w, h) {
                var nH, nW
                    ,nW = _w = img.width
                    ,nH = _H = img.height;
                if (_w > 0 && _H > 0) {
                    if (_w / _H >= w / h && _w > w) {
                        nW = w;
                        nH = parseInt(_H * w / _w);
                    }else if (_H > h) {
                        nH = h;
                        nW = parseInt(_w * h / _H);
                    }
                }
                return [nW,nH];
            }

            // 设置位置
            ,setPos:function (){
                var s=$.getSize(obj.box);
                obj.box.css({
                    left: o.css.left||s.maxL / 2
                    ,top: o.css.top||s.maxT / 2 + ((isIE6||!o.fixed)&&$w.scrollTop())
                    ,display:'block'
                }).addClass(o.animateClassShow);
                o.target.type=='iframe'&&obj.content.height(s.h-obj.title.outerHeight());

                if (isIE6&&o.fixed) {
                    $.modal.top=s.maxT / 2;
                    $w.on('scroll',function(){
                        obj.box.css({'top':$.modal.top+$w.scrollTop()})
                    });
                };

                o.drag&&$.drag({
                    obj : obj.box
                    , dragHandle : o.dragHandle?obj.box.find(o.dragHandle):obj.title
                });
            }
            // 关闭
            ,closeBox:function(speed) {
                //还原标签
                setTimeout(function() {
                    o.target.type=='element'&&$(o.target.content).hide().appendTo('body');
                    obj.box.removeClass(o.animateClassShow).remove();
                    obj.overlay&&obj.overlay.remove();
                },speed||0)

                if(isIE6){
                    $('select').css('visibility','visible');
                    // $w.off('scroll');
                }
                $(document).off('keydown.modal');
                o.closeFun&&o.closeFun.call(this);
            }

            ,getBox:function() {
                return obj;
            }
        }

        obj.init();

        !o.drag&&$(window).on('resize',function(){
            obj.setPos();
        });

        return obj;
    }

})
    // 配置$.modal({title:''})
    $.modal.opt = {
        modalType          : 'modal'//[ modal | tip | lay ]
        , id : ''
        , title            : '会员登录'//标题
        , fixed            : 1//fixed效果
        , overlay          : 0.2//显示遮罩层, 0为不显示
        , drag             : 0//拖拽
        , dragHandle       : ''
        , lock             : 1//锁定遮罩层
        , timeout          : 0
        , target           : {
            type      : 'element'//[ html | img | url | element | iframe ]
            , content : '#pop-login'
        }
        , loadBefoe        : null
        , closeFun         : null
        , css              : {}
        , borderCss        : {}
        , titleCss         : {}
        , animateClass     : 'm-scale'
        , animateClassShow : 'm-scale-show'
    };

    dragOpt = {
        obj : ''
        , dragHandle : ''
        , fixed : 1
    }

   $('<link rel="stylesheet">').appendTo('head').attr('href', (typeof(tplurl)!='undefined'?tplurl:'')+'css/modal.css');
    // tip
    $.modal.tip=function(o,t,s) {
        // console.log(t);
        var opt={
            modalType :'tip'
            ,fixed    :0
            ,timeout  :t=='loading'?0:(s||1500)
            ,target   :{
                type      :'html'
                ,content  :'<i class="ico ico-'+t+'"></i>'+o
            }
            ,borderCss :{
                border:'none'
            }
        }
        return $.modal($.extend(true, {}, $.modal.opt, opt));
    }
    // lay
    $.modal.lay=function(type,content,width,height) {
        var opt={
            modalType :'lay'
            ,fixed    :0
            ,lock     :0
            ,target   :{
                type      :type
                ,content  :content
            }
            ,css      :{}
            // ,border:0
        }
        if (type == 'iframe') {
            opt.css.width  = width;
            opt.css.height = height;
        };
        return $.modal($.extend({}, $.modal.opt, opt));
    }
})(jQuery);

    var modaler;
    function modalExt() {
        var arg = arguments
            , tgt = !arg[0].length ? $(arg[0]).attr('href') : arg[0]
            , p = tgt.indexOf('//') > 0 ? originDomain && (tgt.indexOf('?') > -1 ? '&' : '?') + 'domain=' + originDomain : '';

        modaler = $.modal({
            title : arg[1]
            , target : {
                type : tgt.indexOf('//') > 0 ? 'iframe' : 'element'
                ,content : tgt + p
            }
            , css : {
                width : arg[2]
                , height : arg[3]
            }
            , lock : 0
        })
        return false;
    }