<?php
/**
 * 点击复制按钮，该文件必须用于框架内部使用
 *
 * @author Wilson
 * @copyright 2013
 */

include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
if(empty($data)) exit;
if(isset($select_id)) {
    $select_string = "\$(window.parent.document).contents().find('#{$select_id}').select();";
} else {
    $select_string = '';
}
$type_string = '';
if(empty($type)) {
    $type_string .= <<<EOT
        .flashcopier { float:right; }
        .flashcopier_div { width: 26px; height:15px; overflow: hidden; float:left; text-align: center; }
        .flashcopier_flash { margin: -65px 0 0 -51px; }
EOT;
}
echo _08_Advertising::showCopyCode($data);
cls_phpToJavascript::loadJQuery();
echo <<<EOT
    <script type="text/javascript">
        $(document).ready(function(){
            $.closeClipBoard = function() {
                {$select_string}
                alert('复制成功！');
            }
        });
    </script>
    <style type="text/css">
        * { margin:0px; padding:0px; }
        {$type_string}
    </style>
EOT;
