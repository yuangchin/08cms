/***----------免费发送到手机
-------------------------------------------***/
//是否开启手机免费短信
$.getJSON(CMS_ABS + uri2MVC("ajax=sms_msend&mod="+isOpenMob+"&act=init&datatype=json"), function(info){
    $('#sendtophone')[info.error=='close' ? 'hide' : 'show']();
});

function sendSMSphone(fm){
    if (typeof msgpara == 'undefined') return false;
    // var msstr=encodeURIComponent($('#sendm_msg').text().replace(/\s/g,"").substr(0,400));
    $.getJSON(CMS_ABS + uri2MVC("ajax=sms_msend&mod="+isOpenMob+"&act=send&"+$(fm).serialize()+"&msgpara="+msgpara+"&datatype=json")+'&callback=?', function(info){
        if (info.error) {
            $.jqModal.tip(info.message,'error');
        }
        else {
            $(fm).jqValidate('resetForm').closest('.modal').jqModal('hide');
            if(fm['regcode-img']) fm['regcode-img'].src += 1;
            $.jqModal.tip(info.message,'succeed');
        }
    });
    return false;
}