<?php
class valid_alpha extends valid
{
    public function check($content)
    {
        return (string) $content === '' || preg_match('/^[a-z0-9_]+$/i', $content);
    }
}