<?php
class valid_float extends valid
{
    public function check($content)
    {
        return (string) $content === '' || preg_match('/^\-?\+?\d+[\.,]?\d*$/', $content);
    }
}