<?php defined('_08_INSTALL_EXEC') || exit('No Permission'); ?>
<input type="hidden" value="database" name="task" />
<input type="hidden" value="<?php echo $this->install_token;?>" name="install_token" />
<div class="blank10"></div>
<div class="mb"><a class="next" href="javascript:void(0);">下一步</a></div>
<div class="blank10"></div>
<div class="layA">
	 <div class="step1">
		<h2>环境检测</h2>
		<ul class="ulB ulC mb30 clearfix">
		    <li class="h fwb">项目名称</li>
		    <li class="h fwb">要求环境</li>
		    <li class="h fwb">当前环境</li>
            
            <?php
                foreach ( $this->extension_loaded as $ext ) 
                {
                    if ( ($ext == 'mcrypt' && version_compare(PHP_VERSION, '5.3', '>=')) || 
                         ($ext == 'mysql' && version_compare(PHP_VERSION, '5.5', '>=')) )
                    {
                        continue;
                    }
                    
                    echo <<<HTML
<li class="h">{$ext} 扩展库</li>
<li><i>开启</i></li>
<li>
HTML;
                    if( extension_loaded($ext) )
                    {
                         echo '<i>开启</i>';
                    }
                    else
                    {
                   	     echo '<i class="er">未开启</i>';
                    }
                    echo '</li>';
                }
            ?>
		</ul>
        
		<h2>目录文件夹权限检查 <span style="color: red;">（注：未检测的目录请设置为不可写）</span></h2>
		<ul class="ulB ulC mb30 clearfix">
		    <li class="h fwb">项目名称</li>
		    <li class="h fwb">要求环境</li>
		    <li class="h fwb">当前环境</li>
            
		    <?php
                foreach ( $this->paths as $key => $path ) 
                {                    
                    if ( is_numeric($key) )
                    {
                        $string = $path;
                    }
                    else
                    {
                    	$string = $key;
                    }
                    
                    // 检测子目录权限
//                    if ( in_array($path, array('./userfiles', './dynamic')) )
//                    {
//                        $pathInstance = new _08_FileSystemPath(realpath(M_ROOT . $path));
//                        $errPath = $pathInstance->setPermissions();
//                        if ( !empty($errPath) )
//                        {
//                            $path .= '________________';
//                        }
//                    }
                    
                    echo <<<HTML
<li class="h">{$string}</li>
<li><i>可写</i></li>
<li>
HTML;
                    if( is_writable(realpath(M_ROOT . $path)) )
                    {
                         echo '<i>可写</i>';
                    }
                    else
                    {
                   	     echo '<i class="er">不可写</i>';
                    }
                    echo '</li>';
                }
            ?>
		</ul>
        <div class="mb"><a class="next" href="javascript:void(0);">下一步</a></div>
	</div>
</div>
<div id="dialog-message" style="display: none;">环境检测未通过，如果继续安装会导致系统功能不正常，<br />请问是否继续？</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.next').click(function(){
        if ( $('i[class="er"]').length == 0 )
        {
            $('#install_from').submit();
        }
        else
        {
        	 $('#dialog-message').dialog({ 
                 title: '08CMS 温馨提示您：',
                 width: 400,
                 buttons: [{ 
                    text: "继续", 
                    click: function() { 
                        $('#install_from').submit();
                    }
                 }, { 
                    text: "取消", 
                    click: function() { 
                        $( this ).dialog( "close" ); 
                    }
                 }]
             });
             return false;
        }
    })
})
</script>