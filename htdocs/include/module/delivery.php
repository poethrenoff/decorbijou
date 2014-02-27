<?php
class module_delivery extends module
{
    /**
     * Доставка
     */
    protected function action_index()
    {
        $this->package_list = model::factory('package')
            ->get_list(array('package_active' => 1), array('package_price' => 'asc'));
        $this->view->assign('package_list', $this->package_list);
        $this->content = $this->view->fetch('module/delivery/index');
    }
}