<?php
class field_boolean extends field_string
{
    public function get($content)
    {
        return $content ? 'да' : 'нет';
    }
    
    public function set($content)
    {
        return strval($content ? 1 : 0);
    }
}
