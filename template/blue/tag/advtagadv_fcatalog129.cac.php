<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog129 = array (
  'ename' => 'adv_fcatalog129',
  'tclass' => 'advertising',
  'template' => '<div class="flash mb10 bd">
                    <div id="big" class="big">
                        <ul>
                            {c$v3_hdp [cname=幻灯片/] [tclass=farchives/] [limits=4/] [casource=129/] [orderstr=a.vieworder ASC/] [validperiod=1/]}
                            <li><a href="{link}" >{c$ad_image296_218 [cname=ad_image296_218/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=296/] [maxheight=218/]}<img src="{url}" width="296" height="218" alt="{$v[\'subject\']}" data-url="{$v[\'link\']}"/>{/c$ad_image296_218}</a></li>
                            {/c$v3_hdp}
                        </ul>
                    </div>
                    <ul id="sml" class="sml clearfix">{c$v3_hdpsml [cname=幻灯片2/] [tclass=farchives/] [limits=4/] [casource=129/] [orderstr=a.vieworder ASC/] [validperiod=1/]}<li {if $v[\'sn_row\']==1}class="act"{/if}><a href="{link}" >{c$ad_image70_52 [cname=ad_image70_52/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=70/] [maxheight=52/]}<img src="{url}" width="70" height="52" alt="{$v[\'subject\']}"/>{/c$ad_image70_52}<b></b></a></li>{/c$v3_hdpsml}
                    </ul>
                </div>
<script type="text/javascript">
                $("#big").find("li").imgChange({thumbObj:"#sml li",effect:"scroll",showTxt:1,vertical:0})//flash    
        </script>',
  'setting' => 
  array (
    'limits' => 1,
    'casource' => 'fcatalog129',
    'validperiod' => '1',
    'orderstr' => ' a.vieworder DESC ',
  ),
) ;