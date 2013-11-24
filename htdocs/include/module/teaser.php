<?php
class module_teaser extends module
{
    // Вывод случайного тизера
    protected function action_index()
    {
        $teaser_list = model::factory('teaser')->get_list(array('teaser_active' => 1), array('teaser_order' => 'asc'));
        
        $this->view->assign('teaser_list', $teaser_list);
        $this->content = $this->view->fetch('module/teaser/index');
    }
}