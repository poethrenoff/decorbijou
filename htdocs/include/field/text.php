<?php
class field_text extends field_string
{
    public function get($content)
    {
        $content = strip_tags($content);
        $content = (mb_strlen($content, 'utf-8') > 80) ? mb_substr($content, 0, 80, 'utf-8') . '...' : $content;
        return parent::get($content);
    }
}
