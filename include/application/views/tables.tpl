<?php
/**
 * 表单界面模板
 */
    tabheader(
        $this->title,
        $this->formname,
        empty($this->formurl) ? (isset($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : "?entry={$this->entry}&action={$this->action}") : $this->formurl
    );
    if( !empty($this->tabletitle) && is_array($this->tabletitle) )
    {
        echo '<tr class="txt">';
        foreach($this->tabletitle as $title)
        {
            echo '<td class="title txtC">' . $title . '</td>';
        }
        echo '</tr>';
    }
    if( !empty($this->showdatas) && is_array($this->showdatas) )
    {
        foreach($this->showdatas as $key => $value)
        {
            if( !empty($value) )
            {
                echo '<tr class="txt"><td class="txtC">' . $key . '</td>';
                if(is_array($value))
                {
                    foreach($value as $v)
                    {
                        echo '<td class="txtC">' . (empty($v) ? '-' : $v) . '</td>';
                    }
                }
                else
                {
                    echo '<td class="txtC">' . (empty($value) ? '-' : $value) . '</td>';
                }
                echo '</tr>';
            }
        }
    }

    echo '</table>';
    if( !empty($this->submits) && is_array($this->submits) )
    {
        echo '<br />';
        $str = '';
        foreach($this->submits as $key => $value)
        {
            if( !empty($str) ) $str .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            $str .= '<input class="btn" type="submit" name="' . $key . '" value="' . $value . '">';
        }
        echo $str;
    }

    echo '</from>';

