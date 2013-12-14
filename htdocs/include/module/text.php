<?php
class module_text extends module
{
    protected function action_index()
    {
        $text_id = $this->get_param('id');
        $text_template = $this->get_param('template');
        
        try {
            $text_item = model::factory('text')->get($this->get_param('id'));
        } catch (AlarmException $e) {
            not_found();
        }
        
        $this->view->assign($text_item);
        $this->content = $this->view->fetch('module/text/' . $text_template);
    }
    
    // Получение текста по тегу
    public static function get_by_tag($text_tag)
    {
        return model::factory('text')->get_by_tag($text_tag)->get_text_content();
    }
}