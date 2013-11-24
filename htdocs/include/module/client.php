<?php
class module_client extends module
{
    protected function action_index()
    {
        $this->content = 'client index';
    }
    
    protected function action_info()
    {
        $this->content = 'client info';
    }
}