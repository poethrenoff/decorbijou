<?php
class admin_table_module extends admin_table_builder
{
	protected function action_add_save( $redirect = true )
	{
		$primary_field = parent::action_add_save( false );
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_copy_save( $redirect = true )
	{
		$primary_field = parent::action_copy_save( false );
		
		$this -> copy_module_params( id(), $primary_field );
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_delete( $redirect = true )
	{
		$module_params = db::select_all( '
				select * from module_param where param_module = :param_module',
			array( 'param_module' => id() ) );
		
		parent::action_delete( false );
		
		foreach ( $module_params as $module_param )
			db::delete( 'param_value', array( 'value_param' => $module_param['param_id'] ) );
		
		build();
		
		if ( $redirect )
			$this -> redirect();
	}
}
