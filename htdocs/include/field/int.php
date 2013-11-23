<?php
class field_int extends field_string
{
    public function set($content)
    {
        return $content !== '' ? strval(intval($content)) : null;
    }
    
    public function check($content, $errors_string = '')
    {
        return valid::factory('int')->check($content) &&
            parent::check($content, $errors_string);
    }
}
