<?php if( !empty($this->member_model) && !empty($this->member_str) ) { ?>
<tr class="txt">
    <td class="txtC w120">
        <?=$this->member_model?><input type="checkbox" name="generate[members]" value="1" class="checkbox" />
    </td>
    <td class="txtL"><?=$this->member_str?></td>
</tr>
<?php } if( !empty($this->arc_model) && !empty($this->arc_str) ) { ?>
<tr class="txt">
    <td class="txtC w120">
        <?=$this->arc_model?><input type="checkbox" name="generate[archives]" value="1" class="checkbox" />
    </td>
    <td class="txtL">
        <?=$this->arc_str?>
    </td>
</tr>
<?php } if( !empty($this->commu_model) && !empty($this->commu_str) ) { ?>
<tr class="txt">
    <td class="txtC w120" align="left" class="txtL">
        <?=$this->commu_model?><input type="checkbox" name="generate[commus]" value="1" class="checkbox" />
    </td>
    <td class="txtL">
        <?=$this->commu_str?>
    </td>
</tr>
<?php } ?>