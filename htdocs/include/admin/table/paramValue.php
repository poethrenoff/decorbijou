<?php
class admin_table_paramValue extends admin_table_builder
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
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_delete( $redirect = true )
	{
		$record = $this -> get_record();
		$primary_field = $record[$this -> primary_field];
		
		parent::action_delete( false );
		
		$default_value = db::select_cell( 'select value_id from param_value
				where value_param = :value_param and value_default = 1',
			array( 'value_param' => $record['value_param'] ) );
		
		db::update( 'block_param', array( 'value' => $default_value ),
			array( 'param' => $record['value_param'], 'value' => $primary_field ) );
		
		build();
		
		if ( $redirect )
			$this -> redirect();
	}
}
