<?php defined('_08_INSTALL_EXEC') || exit('No Permission'); ?>
<input type="hidden" value="setup" name="task" />
<input type="hidden" value="<?php echo $this->install_token;?>" name="install_token" />
<div class="blank10"></div>
<div class="mb <?php echo $this->task;?>"><a class="next" href="javascript:void(0);">下一步</a> <a href="./">上一步</a></div>
<div class="blank10"></div>
<div class="layA">
	<div class="step2">
		<div class="boxA">
		<h2>填写数据库信息</h2>
			<ul class="ulD">
				<li><label>数据库服务器 *：</label><div><input type="text" value="<?php echo $this->configs['dbhost'];?>" name="install_database[dbhost]" id="install_database_dbhost" /><i></i></div></li>
				<li class="m">数据库服务器地址，一般为 "localhost"</li>
                
				<li><label>数据库用户名 *：</label><div><input type="text" value="<?php echo $this->configs['dbuser'];?>" name="install_database[dbuser]" id="install_database_dbuser" /><i></i></div></li>
				<li class="m">数据库账号用户名，一般是 "root"</li>
                
				<li><label>数据库密码 *：</label><div><input type="password" value="<?php echo $this->configs['dbpw'];?>" name="install_database[dbpw]" id="install_database_dbpw" /><i></i></div></li>
				<li class="m">数据库账号密码</li>
                
				<li><label>数据库名称 *：</label><div><input type="text" value="<?php echo $this->configs['dbname'];?>" id="install_database_dbname" name="install_database[dbname]" /><i></i></div></li>
				<li class="m">数据库名称</li>
                
				<li><label>系统Email：</label><div><input type="text" id="install_database_adminemail" value="<?php echo $this->configs['adminemail'];?>" name="install_database[adminemail]" /><i></i></div></li>
				<li class="m">用于发送程序错误报告</li>
                
				<li><label>数据表前缀 *：</label><div><input type="text" value="<?php echo $this->configs['tblprefix'];?>" name="install_database[tblprefix]" id="install_database_tblprefix" /><i></i></div></li>
				<li class="m">同一数据库安装多个程序可设置为不同的值</li>
                <!--
				<li>
                    <label>旧数据的处理：</label>
                    <div class="make-switch">
                        <input type="radio" name="install_database[backup]" id="install_database_backup" value="backup" checked="checked" />
                    </div>
                </li>
				<li class="m">以前安装的 08CMS 在安装过程中将会被<span id="backup_tip">修复</span></li>
                -->
			</ul>
		</div>
		<div class="boxB">
		<h2>填写管理员与基本信息</h2>
			<ul class="ulD">
				<li class="gt"><label>网站名称：</label><div><input type="text" value="<?php echo $this->admininfo['site_name'];?>" name="install_admin[site_name]" id="install_site_name" /><i></i></div></li>
				<li class="gt"><label>创始人帐号 *：</label><div><input type="text" value="<?php echo $this->admininfo['username'];?>" name="install_admin[username]" id="install_admin_username" /><i></i></div></li>
				<li class="gt"><label>创始人密码 *：</label><div><input type="password" value="<?php echo $this->admininfo['password1'];?>" name="install_admin[password1]" id="install_admin_password1" /><i></i></div></li>
                <li class="m">长度范围：5-15位字符组成</li>
				<li class="gt"><label>重输创始人密码 *：</label><div><input type="password" value="" name="install_admin[password2]" id="install_admin_password2" /><i></i></div></li>
				<li class="gt"><label>创始人Email：</label><div><input type="text" value="<?php echo $this->admininfo['email'];?>" name="install_admin[email]" id="install_admin_email"/><i></i></div></li>

                <?php if( is_file($this->sql_file) && (@filesize($this->sql_file) > 10) ) { ?>
                <li class="gt" style="margin-top: 70px;">
                        <label for="icheckbox1" style="margin-top:-5px; width:170px; text-align: left;">安装基本数据包：</label>
                        <input type="checkbox" class="icheckbox" checked="checked" disabled="true" />
                </li>
                <li class="gt">
                        <label for="icheckbox2" style="margin-top:-5px; width:170px; text-align: left;">安装<?php echo $this->pakageName;?>数据包：</label>          
                        <input type="checkbox" class="icheckbox" name="install_database[extdata]" id="icheckbox2" checked="checked" />
                </li>
                <?php } ?>
			</ul>
		</div>
	</div>
</div>
<div id="dialog-message" style="display: none;"></div>

<script type="text/javascript">
var _backup = '修复';
var _remove = '删除';
$("[name='install_database[backup]']").bootstrapSwitch('onText', _backup);
$("[name='install_database[backup]']").bootstrapSwitch('offText', _remove);
$("[name='install_database[backup]']").on('switchChange.bootstrapSwitch', function(event, state) {
    if ( state )
    {
        this.value = 'backup';
        $('#backup_tip').html(_backup);
    }
    else
    {
    	this.value = 'remove';
        $('#backup_tip').html(_remove);
    }
});
$('.icheckbox').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '20%' // optional
});
$('.next').click(function(){
    var msg = '';
    if ( !$('#install_database_dbuser').val() ) {
        msg = '数据库用户名不能为空';
    } else if ( /[^\w]/.test($('#install_database_dbuser').val()) ) {
        msg = '数据库用户名含有非法字符';
    }
    
    if ( !$('#install_database_dbname').val() ) {
        msg = '数据库名称不能为空';
    }
    
    var checkEmail = !/[\w-]+@([\w-]+\.)+[\w-]+/.test($('#install_database_adminemail').val());
    if ( $('#install_database_adminemail').val() && checkEmail ) {
        msg = '系统Email格式不正确';
    }
    
    if ( !$('#install_database_tblprefix').val() ) {
        msg = '数据表前缀不能为空';
    }
    
    if ( !$('#install_admin_username').val() ) {
        msg = '创始人帐号不能为空';
    }
    
    var passLength = $('#install_admin_password1').val().length;
    if ( !$('#install_admin_password1').val() ) {
        msg = '创始人密码不能为空';
    }  else if ( $('#install_admin_password1').val() != $('#install_admin_password2').val() ) {
        msg = '创始人密码与重输创始人密码不一致';
    }  else if ( (passLength < 5) || (passLength > 15) ) {
        msg = '创始人密码长度不对';
    }
    
    if ( $('#install_admin_email').val() && !/[\w-]+@([\w-]+\.)+[\w-]+/.test($('#install_admin_email').val()) ) {
        msg = '创始人Email格式不正确';
    } 
    
    if ( msg )
    {
        $('#dialog-message').html(msg);
        $('#dialog-message').dialog({ 
             title: '08CMS 温馨提示您：',
             width: 400,
             buttons: [{ 
                text: "确定", 
                click: function() { 
                    $( this ).dialog( "close" ); 
                }
             }]
         });
         return false;
    }
    else
    {
    	$('#install_from').submit();
    }
})
</script>