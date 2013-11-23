<?php
class field_parent extends field_int
{
    public function set($content)
    {
        return strval(intval($content));
    }
}
