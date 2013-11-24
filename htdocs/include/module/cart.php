<?php
class module_cart extends module
{
    protected function action_index()
    {
        $this->content = 'cart index';
    }
    
    protected function action_info()
    {
        $this->content = 'cart info';
    }
}