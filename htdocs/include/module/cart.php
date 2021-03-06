<?php
class module_cart extends module
{
    protected function action_index()
    {
        $this->view->assign('cart', cart::factory());
        $this->content = $this->view->fetch('module/cart/cart');
    }
    
    protected function action_info()
    {
        $this->view->assign('cart', cart::factory());
        $this->content = $this->view->fetch('module/cart/info');
    }
    
    protected function action_add()
    {
        try {
            $product = model::factory('product')->get(id());
        } catch (Exception $e) {
            not_found();
        }
        
        if (!$product->get_product_active()) {
            not_found();
        }
        
        cart::factory()->add(
            $product->get_id(), $product->get_product_price()
        );
        
        $this->action_info();
    }
    
    protected function action_delete()
    {
        cart::factory()->delete(id());
        redirect_back();
    }
    
    protected function action_clear()
    {
        cart::factory()->clear();
        redirect_back();
    }
    
    // Отключаем кеширование
    protected function get_cache_key()
    {
        return false;
    }
}