<?php
class admin_table_product extends admin_table
{
    protected function action_add_save( $redirect = true )
    {
        if (!init_string('product_name')) {
            $_REQUEST['product_name'] = to_translit(init_string('product_title'));
        }
        unset( $this -> fields['product_name']['no_add'] );
        
        $primary_field = parent::action_add_save( false );
        
        if ( $redirect )
            $this -> redirect();
        
        return $primary_field;
    }
}