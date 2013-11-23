<?php
class field_string extends field
{
    public function get($content)
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }
    
    public function form($content)
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }
    
    public function set($content)
    {
        return $content;
    }
}
