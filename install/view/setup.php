<?php defined('_08_INSTALL_EXEC') || exit('No Permission'); ?>
<input type="hidden" value="complete" name="task" />
<input type="hidden" value="<?php echo $this->install_token;?>" name="install_token" />
<div class="blank15"></div>
<div class="mb install"></div>
<div class="blank5"></div>
<div class="layA" style="overflow: auto;">
<ul class="ulA" id="ulA" style="height:300px"></ul>
<div class="blank5"></div>
</div>
<div class="btn1" id="btn1">正在安装...<img src="./view/images/loading_sml.gif" style="margin-top:10px" /></div>


<script type="text/javascript">
function _08_install(url)
{
    $.ajaxSetup({
        error:function(x,e){
            $('#ulA').html('<li>安装出错，错误信息为：<br /></li>' + x.responseText);
            $('#btn1').html('安装失败。')
            return false;
        }
    });
    
    $.getJSON(url + '&datatype=json', function(data){
        if ( data.insert_table_name.length )
        {
            for(var i = 0; i < data.insert_table_name.length; ++i)
            {
                data.message += ('<li>数据表：' + data.insert_table_name[i] + ' 数据导入完成。</li>');
            }
        }
        
        $('#ulA').html($('#ulA').html() + data.message);      
        
        if ( data.jumpurl )
        {
            _08_install(data.jumpurl);
        }
        else
        {
            $('#btn1').addClass('btn2').removeClass('btn1');
        	$('#btn1').html('安装完成').click(function(){
        	    $('#install_from').submit();
        	})
        }
    });
}
_08_install('<?php echo $this->jumpurl;?>');
</script>