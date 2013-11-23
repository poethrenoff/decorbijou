<?php
class valid_require extends valid
{
    public function check($content)
    {
        return (string) $content !== '';
    }
}