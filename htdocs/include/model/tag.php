<?php
class model_tag extends model
{
    const MIN_FONT_SIZE = 30;
    
    const FONT_LEVEL_COUNT = 30;
    
    // Значение размера шрифта
    protected $font_size = null;
    
    // Устанавливает значение размера шрифта
    public function set_font_size($font_size)
    {
        $this->font_size = $font_size;
        return $this;
    }
    
    // Возвращает значение размера шрифта
    public function get_font_size()
    {
        return $this->font_size;
    }
    
    // Возвращает объект тега по имени
    public function get_by_title($tag_title)
    {
        $record = db::select_row('select * from tag where tag_title = :tag_title',
            array('tag_title' => $tag_title));
        if (!$record){
            throw new AlarmException("Ошибка. Запись {$this->object}({$tag_title}) не найдена.");
        }
        return $this->get($record['tag_id'], $record);
    }
    
    // Возвращает URL тега
    public function get_tag_url()
    {
        return url_for(array('controller' => 'catalogue', 'tag' => urlencode($this->get_tag_title())));
    }
    
    // Получение список тегов товара
    public function get_by_product($product) {
        $records = db::select_all('
            select tag.* from tag
                inner join product_tag on product_tag.tag_id = tag.tag_id
            where product_tag.product_id = :product_id
            order by tag_title',
                array('product_id' => $product->get_id()));
        return $this->get_batch($records);
    }
    
    // Возвращает облако тегов
    public function get_cloud()
    {
        $records = db::select_all( '
            select tag.*, count( product_tag.tag_id ) as tag_count
            from tag, product_tag, product
            where tag.tag_id = product_tag.tag_id and
                product_tag.product_id = product.product_id and product.product_active = 1
            group by product_tag.tag_id
            order by tag_count desc, tag.tag_title' );
        
        $cloud = array();
        
        if ( count( $records ) )
        {
            $num_links_max = $records[0]['tag_count'];
            $num_links_min = $records[count( $records ) - 1]['tag_count'];
            $level_step = ( $num_links_max - $num_links_min ) / self::FONT_LEVEL_COUNT;
            
            foreach ( $records as $record )
            {
                $tag = model::factory('tag')->get($record['tag_id'], $record);
                
                if ( $level_step > 0 ) {
                    $tag->set_font_size(round( self::MIN_FONT_SIZE + ( $record['tag_count'] - $num_links_min ) / $level_step ) );
                } else {
                    $tag->set_font_size(round( self::MIN_FONT_SIZE + self::FONT_LEVEL_COUNT / 2 ) );
                }
                
                $cloud[] = $tag;
            }
            
            usort($cloud, create_function( '$a, $b',
                'return strcmp( mb_strtolower( $a->get_tag_title() ), mb_strtolower( $b->get_tag_title() ) );'
            ));
        }
        
        return $cloud;
    }
}