<?php
class admin_table_catalogue extends admin_table
{
    protected function action_add_save( $redirect = true )
    {
        if (!init_string('catalogue_name')) {
            $_REQUEST['catalogue_name'] = to_translit(init_string('catalogue_title'));
        }
        unset( $this -> fields['catalogue_name']['no_add'] );
        
        $primary_field = parent::action_add_save( false );
        
        if ( $redirect )
            $this -> redirect();
        
        return $primary_field;
    }
}