<?php
class model_product extends model
{
    // Возвращает каталог товара
    public function get_catalogue()
    {
        return model::factory('catalogue')->get($this->get_product_catalogue());
    }
    
    // Возвращает URL товара
    public function get_product_url()
    {
        return url_for(array('controller' => 'catalogue',
            'name' => $this->get_catalogue()->get_catalogue_name(), 'action' => 'item', 'id' => $this->get_id()));
    }
    
    // Возвращает список товаров по тегу
    public function get_by_tag($tag)
    {
        $records = db::select_all('
            select product.* from product
                inner join product_tag using(product_id)
            where tag_id = :tag_id and
                product_active = :product_active',
            array('tag_id' => $tag->get_id(), 'product_active' => 1));
        
        return $this->get_batch($records);
    }
    
    // Возвращает список тегов товара
    public function get_tag_list()
    {
        return model::factory('tag')->get_by_product($this);
    }
}