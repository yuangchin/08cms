!function ($) {
    $.fn.serializeObject = function() {
       var o = {};    
       var a = this.serializeArray();    
       $.each(a, function() {    
           if (o[this.name]) {    
               if (!o[this.name].push) {    
                   o[this.name] = [o[this.name]];    
               }    
               o[this.name].push(this.value || '');    
           } else {    
               o[this.name] = this.value || '';    
           }
       });    
       return o;    
    };
    //autocomplete
    var Autocomplete = function (input, o) {
        this.o        = $.extend(true, {}, Autocomplete.def, o);
        this.$sWord   = $(input);
        this.param    = $.extend(this.o.param, $(input.form).serializeObject());
        this.init();
    }

    Autocomplete.def = {
         param : {//要传入脚本的参数
            ajax :'search_choice'//脚本名字
            , _isxq : 0
        }
    }

    Autocomplete.prototype = {
        init : function () {
            var o = this.o
            , $sWord = this.$sWord;

            var $dataBox = this.$dataBox = $('<div id="drop-data" class="drop-data"></div>').appendTo('body')
            .on('mouseover', 'li', function() {
                $(this).addClass('act').siblings().removeClass('act');
            })
            .on('mousedown', 'li', function () {
                $sWord.trigger('blur').trigger('pressDown', [$(this)])
                return false;
            })

            $sWord.attr('autocomplete', 'off')
            .on({
                keyup : $.proxy(function (e) {
                    switch (e.which) {
                        case 9: case 27: case 38: case 40: 
                            return false;
                        default:
                            var val = $.trim($sWord.val());
                            if (this.ajaxAbort) this.ajaxAbort.abort();
                            this.param.searchword = encodeURIComponent(val);
                            this.ajaxAbort = $.getScript(CMS_ABS + uri2MVC($.param(this.param) + "&datatype=js&varname=data"), function() {
                                $sWord.trigger('ajaxDone', [$dataBox, data])
                            });
                            return false;
                    }
                }, this)
                , focus : function () {
                    var $el = $sWord
                    var oft = $el.offset();
                    $dataBox.css({
                        width      : $el.outerWidth()
                        , left     : oft.left
                        , top      : $el.outerHeight() + oft.top
                        , position : 'absolute'
                    })
                }
                , blur : function () {
                    $dataBox.css('display', 'none');
                }
                , keydown : function(e) {
                    switch (e.which) {
                        case 13:
                            var $actLi = $dataBox.find('.act');
                            if ($actLi.length) {
                                $sWord.trigger('blur')
                                $sWord.trigger('pressDown', [$actLi]);
                            };
                            return false;
                        case 27: //esc
                            $dataBox.css('display', 'none');
                            return false;
                        case 38: case 40://up down
                            var step   = e.which == 40 ? 1 : -1;
                            var $li    = $dataBox.find('li');
                            var $actLi = $li.filter('.act').removeClass('act');
                            var i      = $actLi.length ? $actLi.index() : (step == -1 ? 0 : -1);

                            $sWord.val($li.eq((i + step) % $li.length).addClass('act').find('.subject').text());
                            return false;
                    }
                }
            })
        }
    }

    function Plugin(option) {
        return this.each(function () {
            var $this   = $(this)
            var data    = $this.data('jqAutocomplete')
            var options = typeof option == 'object' && option

            if (!data) $this.data('jqAutocomplete', (data = new Autocomplete(this, options)))

            if (typeof option == 'string') data[option]()
        })
    }

    var old = $.fn.jqAutocomplete;

    $.fn.jqAutocomplete             = Plugin
    $.fn.jqAutocomplete.Constructor = Autocomplete;

    $.fn.jqAutocomplete.noConflict = function () {
        $.fn.jqAutocomplete = old
        return this
    }

    $(window).load(function() {
        $('.jqAutocomplete').each(function(i, el) {
            $(this).jqAutocomplete()
        });
    });

} (jQuery);

// 下面是自定义的
var template1 = function() {//下拉框内列表项目模板function() this:返回的数据
    return '<li data-url="' + this.url + '" data-aid="' + this.aid + '">'
          + '   <span class="subject">' + this.subject + '</span>'
          + '   <span class="fcg">'+ this.address +'</span>'
          + '</li>'
}
var template2 = function(caid) {//下拉框内列表项目模板function() this:返回的数据
    var url = CMS_ABS +'index.php?caid='+ caid +'&addno=1&searchword='+ this.subject;

    return '<li data-url="' + url + '" data-aid="' + this.aid + '">'
          +'    <span class="fcg r">约'+ (caid == 3 ? this.lpesfsl : this.lpczsl) +'个房源</span>'
          +'    <span class="subject">' + this.subject + '</span><span class="fcg">'+ this.address +'</span>'
          +'</li>'
}

$('.jqAutocomplete').each(function(i, el) {
    var caid = this.form.caid;
    var template = (caid == 3 || caid == 4) ? template2 : template1;
    $(this).on('ajaxDone', function (el, $dataBox, data) {
        var l = data.length;
        if (l > 0) {
            var html = '<ul>';
            for (var i = 0; i < l; i++) html += template.call(data[i], caid);
            html += '</ul>';
            $dataBox.html(html).css('display','block');
        } //else $dataBox.css('display', 'none');
    })
    .on('pressDown', function (el, $li) {
        window.open($li.data('url'))
    });
});

