<?php
class field_password extends field
{
    public function get($content)
    {
        return str_repeat('*', rand(5, 8));
    }
    
    public function form($content)
    {
        return '';
    }
    
    public function set($content)
    {
        return md5($content);
    }
    
    public function check($content, $errors_string = '')
    {
        return valid::factory('alpha')->check($content) &&
            parent::check($content, $errors_string);
    }
}
