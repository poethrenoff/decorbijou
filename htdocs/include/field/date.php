<?php
class field_date extends field
{
    public function get($content)
    {
        return preg_replace('/\s+/', '&nbsp;', date::get($content, 'short'));
    }
    
    public function form($content)
    {
        return date::get($content, 'short');
    }
    
    public function set($content)
    {
        return date::set($content, 'short');
    }
    
    public function check($content, $errors_string = '')
    {
        return valid::factory('date')->internal_check($content) &&
            parent::check($content, $errors_string);
    }
}
