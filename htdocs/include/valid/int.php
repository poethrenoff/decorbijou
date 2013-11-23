<?php
class valid_int extends valid
{
    public function check($content)
    {
        return (string) $content === '' || preg_match('/^\-?\+?\d+$/', $content);
    }
}