<?php
class model_purchaseItem extends model
{
    // Возвращает товар
    public function get_product()
    {
        return model::factory('product')->get(
            $this->get_item_product()
        );
    }
}