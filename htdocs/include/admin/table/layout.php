<?php
class admin_table_layout extends admin_table_builder
{
	protected function action_copy_save( $redirect = true )
	{
		$primary_field = parent::action_copy_save( false );
		
		$this -> copy_layout_areas( id(), $primary_field );
		
		build();
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
}
