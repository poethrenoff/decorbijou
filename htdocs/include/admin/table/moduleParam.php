<?php
class admin_table_moduleParam extends admin_table_builder
{
	protected function action_add_save( $redirect = true )
	{
		$this -> check_param_default();
		
		$primary_field = parent::action_add_save( false );
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_copy_save( $redirect = true )
	{
		$primary_field = parent::action_copy_save( false );
		
		$this -> copy_param_values( id(), $primary_field );
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_edit_save( $redirect = true )
	{
		$this -> check_param_default();
		
		parent::action_edit_save( false );
		
		build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function check_param_default()
	{
		if ( init_string( 'param_require' ) &&
				!in_array( init_string( 'param_type' ), array( 'select', 'table', 'boolean' ) ) )
			$this -> fields['param_default']['errors_code'] |= field::$errors['require'];
		if ( init_string( 'param_type' ) == 'int' )
			$this -> fields['param_default']['errors_code'] |= field::$errors['int'];
		
		if ( init_string( 'param_type' ) == 'boolean' )
			$this -> fields['param_default']['type'] = 'boolean';
		if ( init_string( 'param_type' ) == 'int' )
			$this -> fields['param_default']['type'] = 'int';
		
		if ( init_string( 'param_type' ) == 'table' && !isset( metadata::$objects[init_string( 'param_table' )] ) )
			throw new AlarmException( 'Ошибочное значение поля "' . $this -> fields['param_table']['title'] . '".' );
	}
	
	protected function get_card_scripts( $action = 'edit', $record = null )
	{
		$scripts = parent::get_card_scripts( $action, $record );
		
		$scripts['module_param_card'] = '';
		
		return $scripts;
	}
}
