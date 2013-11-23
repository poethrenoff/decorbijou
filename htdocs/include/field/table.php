<?php
class field_table extends field_int
{
    public function set($content)
    {
        return strval(intval($content));
    }
}
