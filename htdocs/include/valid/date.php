<?php
class valid_date extends valid
{
    public function check($content)
    {
        return (string) $content === '' || preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $content, $match) && 
            checkdate ($match[2], $match[1], $match[3]);
    }
    
    public function internal_check($content)
    {
        return (string) $content === '' || preg_match('/^(\d{4})(\d{2})(\d{2})000000$/', $content, $match) && 
            checkdate ($match[2], $match[3], $match[1]);
    }
}