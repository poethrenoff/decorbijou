<?php
class module_product extends module
{
    protected function action_index()
    {
        $this->content = 'product index';
    }
    
    protected function action_menu()
    {
        $this->content = 'product menu';
    }
    
    protected function action_tag()
    {
        $this->content = 'product tag';
    }
}