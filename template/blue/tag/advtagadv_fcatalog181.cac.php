<?php
defined('M_COM') || exit('No Permission');
$advtagadv_fcatalog181 = array (
  'ename' => 'adv_fcatalog181',
  'tclass' => 'advertising',
  'template' => '<div class="m-img">
            <div class="flash1">
              <ul id="bigimg" class="bigimg">
[row]
                  <li><img width="958" height="176" src="{c$ad_imageurl [cname=ad_imageurl/] [tclass=image/] [tname=image/] [val=u/] [maxwidth=958/] [maxheight=176/]}{url}{/c$ad_imageurl}"/></li>
[/row]
              </ul>
            </div>
         </div>
         <script type="text/javascript">
    $(\'#bigimg li\').imgChange()
</script>',
  'setting' => 
  array (
    'casource' => 'fcatalog181',
    'validperiod' => '1',
    'orderstr' => 'a.vieworder DESC ',
  ),
) ;