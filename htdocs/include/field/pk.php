<?php
class field_pk extends field_int
{
    public function set($content)
    {
        return strval(intval($content));
    }
    
    public function check($content, $errors_string = '')
    {
        return valid::factory('require')->check($content) &&
            parent::check($content, $errors_string);
    }
}
