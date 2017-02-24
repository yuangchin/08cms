<?php defined('_08_INSTALL_EXEC') || exit('No Permission'); ?>
<!DOCTYPE html>
    <head>
        <title>08CMS <?php echo $this->iversion; ?> 安装程序</title>
        <meta charset="<?php echo $this->mcharset; ?>" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- 让安装包支持手机安装 -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="./view/css/install.css" />
        <link rel="stylesheet" href="./view/css/jquery-ui-1.10.0.custom.css" />
        <link rel="stylesheet" href="./view/css/blue.css" />  
        <!--[if lt IE 9]>
        <link rel="stylesheet" href="./view/css/jquery.ui.1.10.0.ie.css" />
        <![endif]-->
        <link rel="stylesheet" href="./view/css/bootstrap-switch.min.css" />
        <script src="./view/js/jquery-1.9.0.min.js" type="text/javascript"></script>
        <script src="./view/js/jquery-ui-1.10.0.custom.min.js" type="text/javascript"></script>
        <script src="./view/js/bootstrap-switch.min.js" type="text/javascript"></script>
        <script src="./view/js/jquery.icheck.min.js" type="text/javascript"></script>
    </head>
<body>
<?php
    if ( isset($_SERVER['HTTP_USER_AGENT']) && (false !== stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) )
    {
        die('<div style="color:#fff; background-color:red; width:100%; text-align:center; font-size:14px;">本安装包不支持IE7以下的浏览器，请先升级您的浏览器再安装。</div>');
    }
?>
  <div class="head">
  	<div class="wrap pos">
  		<p>08cms安装</p>
  		<div class="copy">08CMS <?php echo $this->iversion; ?></div>
  	</div>
  </div>
  <div class="wrap">
        <form action="" name="install_from" id="install_from" method="POST">
            <?php echo $this->contents; ?>
        </form>
	    <div class="info">Powered by 08CMS <?php echo $this->iversion; ?> &copy 2008-<?php echo $this->date;?> www.08cms.com</div>
  </div>  
</body>
</html>