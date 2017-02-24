<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog159 = array (
  'ename' => 'adv_fcatalog159',
  'tclass' => 'advertising',
  'template' => '<div class="flash bor-gray">
                    <div id="big" class="big">
                        <ul>
                            {c$v3_hdp [cname=幻灯片/] [tclass=farchives/] [limits=4/] [casource=159/] [orderstr=a.vieworder ASC/] [validperiod=1/] [wherestr=FIND_IN_SET(\'$farea\',a.farea)/]}
                            <li><a href="{link}" >{c$ad_image296_218 [cname=ad_image296_218/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=296/] [maxheight=218/]}<img src="{url}" data-url="{$v[\'link\']}" width="296" height="218" alt="{$v[\'subject\']}"/>{/c$ad_image296_218}</a></li>
                            {/c$v3_hdp}
                        </ul>
                    </div>
                    <ul id="sml" class="sml clearfix">{c$v3_hdpsml [cname=幻灯片2/] [tclass=farchives/] [limits=4/] [casource=159/] [orderstr=a.vieworder ASC/] [validperiod=1/] [wherestr=FIND_IN_SET(\'$farea\',a.farea)/]}<li {if $v[\'sn_row\']==1}class="act"{/if}><a href="{link}" >{c$ad_image70_52 [cname=ad_image70_52/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=70/] [maxheight=52/]}<img src="{url}" width="70" height="52" alt="{$v[\'subject\']}"/>{/c$ad_image70_52}<b></b></a></li>
                        {/c$v3_hdpsml}
                    </ul>
                </div>

        <script type="text/javascript">
            $(\'#big\').find(\'li\').imgChange({thumbObj:\'#sml li\',effect:\'scroll\',showTxt:1})//flash
        </script>',
  'setting' => 
  array (
    'val' => 'a',
    'limits' => 1,
    'casource' => 'fcatalog159',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;