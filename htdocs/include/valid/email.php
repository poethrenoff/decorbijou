<?php
class valid_email extends valid
{
    public function check($content)
    {
        return (string) $content === '' || preg_match('/^[a-z0-9_\.-]+@[a-z0-9_\.-]+\.[a-z]{2,}$/i', $content);
    }
}