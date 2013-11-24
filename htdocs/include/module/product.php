<?php
class module_product extends module
{
    protected function action_index()
    {
        $product_list = array();
        if ($catalogue_name = get_param('name')) {
            try {
                $catalogue = model::factory('catalogue')->get_by_name($catalogue_name);
            } catch (AlarmException $e) {
                not_found();
            }
            $product_list = model::factory('product')->get_list(
                array('product_active' => 1, 'product_catalogue' => $catalogue->get_id()), array('product_order' => 'asc')
            );
        } else if ($tag_name = urldecode(get_param('tag'))) {
            try {
                $tag = model::factory('tag')->get_by_title($tag_name);
            } catch (AlarmException $e) {
                not_found();
            }
            $product_list = model::factory('product')->get_by_tag($tag);
        } else {
            redirect_back();
        }
        
        $this->view->assign('product_list', $product_list);
        $this->content = $this->view->fetch('module/product/product');
    }
    
    protected function action_item()
    {
        try {
            $product = model::factory('product')->get(id());
        } catch (AlarmException $e) {
            not_found();
        }
        
        if (!$product->get_product_active()) {
            not_found();
        }
        
        $this->view->assign($product);
        $this->content = $this->view->fetch('module/product/item');
    }
    
    protected function action_menu()
    {
        $catalogue_list = model::factory('catalogue')->get_list(
            array(), array('catalogue_order' => 'asc')
        );
        
        $this->view->assign('catalogue_list', $catalogue_list);
        if ($catalogue_name = get_param('name')) {
            $this->view->assign('catalogue_name', $catalogue_name);
        }
        
        $this->content = $this->view->fetch('module/product/menu');
    }
    
    protected function action_cloud()
    {
        $tag_list = model::factory('tag')->get_cloud();
        
        $this -> view -> assign( 'tag_list', $tag_list );
        if ($tag_name = urldecode(get_param('tag'))) {
            $this->view->assign('tag_name', $tag_name);
        }
        
        $this->content = $this->view->fetch('module/product/tag');
    }
}