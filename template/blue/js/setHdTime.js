$.getScript(CMS_ABS + uri2MVC("ajax=ftend&datatype=js&varname=serverTime"), function() {
    updateEndTime();
});

function updateEndTime() {
    $("[data-endTime]").each(function(i) {
        var $this = $(this),
            _tpl;

        if (!$this.data('template')) {
            _tpl = this.innerHTML;
            $this.data('template', _tpl);
        } else {
            _tpl = $this.data('template');
        }

        var endtime = $this.data('endtime'); //结束时间字符串
        var lag = endtime - serverTime; //当前时间和结束时间之间的秒数

        if (endtime != 0) {

            if (lag > 0) {
                var second = Math.floor(lag % 60),
                minutes    = Math.floor((lag / 60) % 60),
                hour       = Math.floor((lag / 3600) % 24),
                day        = Math.floor((lag / 3600) / 24);

                $this.html(_tpl.toLowerCase().replace('<d>0</d>', day).replace('<h>0</h>', hour).replace('<m>0</m>', minutes).replace('<s>0</s>', second))
            } else {
                $($this.data('endele')).addClass('is-end');
                $this.html($this.data('end')).removeAttr('data-endTime');
            }
        } else $this.html($this.data('noend')).removeAttr('data-endTime');;
    });
    serverTime++;
    setTimeout("updateEndTime()", 1000);
}
