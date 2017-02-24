<?php
/**
 * 标识还原为管理窗口
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */

(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('tpl')) cls_message::show($re);

tabheader('标识还原','restore_form',"?entry=tags_restore&action=init");
templatebox('标识内模板', 'restore', '', 15, 110);
echo <<<EOT
        <tr>
            <td></td>
            <td align="left">
                <p style="width:700px; text-align:center;">
                    <input type="button" class="btn" value="开始还原" onclick="openCreateSelectText('restore', 'restore')" />
                </p>
            </td>
        </tr>
    </table>
    <script type="text/javascript">
          document.getElementsByTagName("textarea")[0].parentNode.style.cssText="text-align:left; padding-left:120px; +padding-left:111px;";
          document.getElementsByTagName("textarea")[0].style.width = "700px";
    </script>
EOT;
a_guide('tags_restore');