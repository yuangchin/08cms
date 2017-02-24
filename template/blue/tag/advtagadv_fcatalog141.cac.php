<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog141 = array (
  'ename' => 'adv_fcatalog141',
  'tclass' => 'advertising',
  'template' => '<div class="flash">
    <div id="bigimg" class="bigimg">
    <ul >
        {c$farchives [tclass=farchives/] [validperiod=1/] [casource=141/] [orderstr=a.vieworder ASC/]}
        <li><a href="{link}" target="_blank">{c$ad_image290_231 [cname=ad_image290_231/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=290/] [maxheight=231/]}<img src="{url}" alt="{$v[subject]}" data-url="{$v[link]}" width="290" height="231" />{/c$ad_image290_231}</a></li>
        {/c$farchives}
        </ul>
    </div>
    <div id="num" class="num">{c$farchives [tclass=farchives/] [validperiod=1/] [casource=141/] [orderstr=a.vieworder ASC/]}<i class="{if $v[\'sn_row\']==1}act{/if}act">{sn_row}</i>{/c$farchives}</div>
</div>
<script type="text/javascript">
			$(\'#bigimg li\').imgChange({thumbObj:\'#num i\',showTxt:1,vertical:0});//幻灯片
</script>',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog141',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;