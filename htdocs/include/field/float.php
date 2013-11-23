<?php
class field_float extends field_string
{
    public function set($content)
    {
        return $content !== '' ? str_replace(',', '.', $content) : null;
    }
    
    public function check($content, $errors_string = '')
    {
        return valid::factory('float')->check($content) &&
            parent::check($content, $errors_string);
    }
}
