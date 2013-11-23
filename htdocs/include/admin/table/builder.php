<?php
class admin_table_builder extends admin_table
{
	protected function action_add_save( $redirect = true )
	{
		$primary_field = parent::action_add_save( false );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_copy_save( $redirect = true )
	{
		$primary_field = parent::action_copy_save( false );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_edit_save( $redirect = true )
	{
		parent::action_edit_save( false );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function action_move( $redirect = true )
	{
		parent::action_move( false );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function action_delete( $redirect = true )
	{
		parent::action_delete( false );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function action_show( $redirect = true )
	{
		parent::action_show( false );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function action_hide( $redirect = true )
	{
		parent::action_hide( false );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////
	
	protected function copy_blocks( $from_id, $to_id )
	{
		$blocks = db::select_all( '
				select * from block where block_page = :block_page',
			array( 'block_page' => $from_id ) );
		
		foreach ( $blocks as $block )
		{
			$block_id = $block['block_id'];
			
			unset( $block['block_id'] );
			$block['block_page'] = $to_id;
			
			db::insert( 'block', $block );
			
			$this -> copy_block_params( $block_id, db::last_insert_id() );
		}
	}
	
	protected function copy_block_params( $from_id, $to_id )
	{
		$block_params = db::select_all( '
				select * from block_param where block = :block',
			array( 'block' => $from_id ) );
		
		foreach( $block_params as $block_param )
		{
			$block_param['block'] = $to_id;
			
			db::insert( 'block_param', $block_param );
		}
	}
	
	protected function copy_module_params( $from_id, $to_id )
	{
		$module_params = db::select_all( '
				select * from module_param where param_module = :param_module',
			array( 'param_module' => $from_id ) );
		
		foreach ( $module_params as $module_param )
		{
			$module_param_id = $module_param['param_id'];
			
			unset( $module_param['param_id'] );
			$module_param['param_module'] = $to_id;
			
			db::insert( 'module_param', $module_param );
			
			$this -> copy_param_values( $module_param_id, db::last_insert_id() );
		}
	}
	
	protected function copy_param_values( $from_id, $to_id )
	{
		$param_values = db::select_all( '
				select * from param_value where value_param = :value_param',
			array( 'value_param' => $from_id ) );
		
		foreach( $param_values as $param_value )
		{
			unset( $param_value['value_id'] );
			$param_value['value_param'] = $to_id;
			
			db::insert( 'param_value', $param_value );
		}
	}
	
	protected function copy_layout_areas( $from_id, $to_id )
	{
		$layout_areas = db::select_all( '
				select * from layout_area where area_layout = :area_layout',
			array( 'area_layout' => $from_id ) );
		
		foreach( $layout_areas as $layout_area )
		{
			unset( $layout_area['area_id'] );
			$layout_area['area_layout'] = $to_id;
			
			db::insert( 'layout_area', $layout_area );
		}
	}
}