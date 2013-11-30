<?php
class model_purchase extends model
{
    // Возвращает список позиций
    public function get_item_list()
    {
        return model::factory('purchase_item')->get_list(
            array('item_purchase' => $this->get_id())
        );
    }
    
    // Возвращает название статуса
    public function get_purchase_status_title()
    {
        $status_list = array_reindex(
            metadata::$objects['purchase']['fields']['purchase_status']['values'], 'value'
        );
        return $status_list[$this->get_purchase_status()]['title'];
    }
}