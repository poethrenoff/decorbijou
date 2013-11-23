<?php
class module_text extends module
{
    protected function action_index()
    {
        try {
            $item = model::factory('text')->get($this->get_param('id'));
        } catch (AlarmException $e) {
            not_found();
        }
        
        $this->view->assign($item);
        $this->content = $this->view->fetch('module/text/item');
    }
    
    // Получение текста по тегу
    public static function get_by_tag($text_tag)
    {
        return model::factory('text')->get_by_tag($text_tag)->get_text_content();
    }
}