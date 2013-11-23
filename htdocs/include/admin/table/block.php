<?php
class admin_table_block extends admin_table_builder
{
	protected function action_add_save( $redirect = true )
	{
		$primary_field = parent::action_add_save( false );
		
		if ( $redirect )
			$this -> apply_default_params( $primary_field );
		
		if ( $redirect )
			build();
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_copy_save( $redirect = true )
	{
		$primary_field = parent::action_copy_save( false );
		
		$this -> copy_block_params( id(), $primary_field );
		
		build();
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_edit_save( $redirect = true )
	{
		$record = $this -> get_record();
		$primary_field = $record[$this -> primary_field];
		
		parent::action_edit_save( false );
		
		if ( $record['block_module'] != init_string( 'block_module' ) )
			$this -> apply_default_params( $primary_field );
		
		build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function action_param()
	{
		$record = $this -> get_record();
		$params = $this -> get_params( $record[$this -> primary_field] );
		
		$form_fields = array();
		foreach( $params as $param_index => $param_value )
		{
			$param_errors = array();
			if ( $param_value['param_require'] ) $param_errors[] = 'require';
			if ( $param_value['param_type'] == 'int' ) $param_errors[] = 'int';
			$param_errors = join( '|', $param_errors );
			
			$form_fields['param[' . $param_value['param_id'] . ']'] = array(
				'title' => $param_value['param_title'],
				'type' => $param_value['param_type'],
				'errors' => $param_errors,
				'require' => $param_value['param_require'] ? 'require' : '',
				'value' => field::form_field( $param_value['value'], $param_value['param_type'] ) );
			if ( $param_value['param_type'] == 'select' )
			{
				$values = db::select_all( '
						select * from param_value
						where value_param = :value_param
						order by value_title',
					array( 'value_param' => $param_value['param_id'] ) );
					
				$value_records = array();
				foreach ( $values as $value )
					$value_records[] = array( 'value' => (string) $value['value_id'], 'title' => field::form_field($value['value_title'], 'string') );
				
				$form_fields['param[' . $param_value['param_id'] . ']']['values'] = $value_records;
			}
			if ( $param_value['param_type'] == 'table' )
			{
				$form_fields['param[' . $param_value['param_id'] . ']']['values'] =
					$this -> get_table_records( $param_value['param_table'] );
			}
		}
		
		$record_title = $record[$this -> main_field];
		$action_title = 'Параметры блока';
		
		$this -> view -> assign( 'record_title', $this -> object_desc['title'] . ' :: ' . $record_title );
		$this -> view -> assign( 'action_title', $action_title );
		$this -> view -> assign( 'fields', $form_fields );
		
		$form_url = url_for( array( 'object' => $this -> object, 'action' => 'param_save', 'id' => $record[$this -> primary_field] ) );
		$this -> view -> assign( 'form_url', $form_url );
		
		$this -> content = $this -> view -> fetch( '/admin/form' );
		$this -> output['meta_title'] .= ' :: ' . $record_title . ' :: ' . $action_title;
	}
	
	protected function action_param_save( $redirect = true )
	{
		$record = $this -> get_record();
		$params = $this -> get_params( $record[$this -> primary_field] );
		
		$param_values = init_array( 'param' );
		
		$insert_fields = array();
		foreach( $params as $param_index => $param_value )
		{
			$param_errors_code = 0;
			if ( $param_value['param_require'] ) $param_errors_code |= field::$errors['require'];
			if ( $param_value['param_type'] == 'int' ) $param_errors_code |= field::$errors['int'];
			
			$value_content = isset( $param_values[$param_value['param_id']] ) ?
				$param_values[$param_value['param_id']] : '';
			
			$insert_fields[$param_value['param_id']] =
				field::set_field( $value_content, array( 'title' => $param_value['param_title'],
					'type' => $param_value['param_type'], 'errors_code' => $param_errors_code ) );
		}
		
		db::delete( 'block_param', array( 'block' => $record['block_id'] ) );
		foreach( $insert_fields as $param_id => $param_value )
			db::insert( 'block_param', array(
				'block' => $record['block_id'], 'param' => $param_id, 'value' => $param_value ) );
		
		build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function action_multiply( $redirect = true )
	{
		$record = $this -> get_record();
		
		$page_list = db::select_all( '
			select * from page, layout_area
			where page.page_layout = layout_area.area_layout and
				layout_area.area_id = :block_area and
				page.page_id <> :block_page and page.page_folder <> 1',
			array( 'block_area' => $record['block_area'], 'block_page' => $record['block_page'] ) );
		
		$insert_block = $record; unset( $insert_block['block_id'] );
		$insert_block_params = db::select_all( 'select param, value from block_param where block = :block_id',
			array( 'block_id' => $record['block_id'] ) );
		
		foreach ( $page_list as $page )
		{
			$block = db::select_row( 'select * from block where block_page = :block_page and block_area = :block_area',
				array( 'block_area' => $insert_block['block_area'], 'block_page' => $page['page_id'] ) );
			
			if ( $block )
			{
				db::update( 'block', array_merge( $insert_block, array( 'block_page' => $page['page_id'] ) ),
					array( 'block_id' => $block['block_id'] ) );
				$block_id = $block['block_id'];
			}
			else
			{
				db::insert( 'block', array_merge( $insert_block, array( 'block_page' => $page['page_id'] ) ) );
				$block_id = db::last_insert_id();
			}
			
			db::delete( 'block_param', array( 'block' => $block['block_id'] ) );
			foreach( $insert_block_params as $insert_block_param )
				db::insert( 'block_param', array_merge( $insert_block_param, array( 'block' => $block_id ) ) );
		}
		
		build();
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function get_params( $block_id )
	{
		return db::select_all( '
				select module_param.*, block_param.value
				from module_param
					inner join block on block.block_module = module_param.param_module
					left join block_param on block_param.param = module_param.param_id and
						block_param.block = block.block_id
				where block.block_id = :block_id
				order by module_param.param_order',
			array( 'block_id' => $block_id ) );
	}
	
	protected function apply_default_params( $block_id )
	{
		$params = $this -> get_params( $block_id );
		
		$params_in = array_make_in( $params, 'param_id' );
		$param_values = db::select_all( 'select * from param_value where value_param in (' . $params_in . ' ) order by value_default' );
		$param_values = array_reindex( $param_values, 'value_param' );
		
		$insert_fields = array();
		foreach( $params as $param_index => $param_value )
		{
			if ( $param_value['param_type'] == 'select' )
				$value = db::select_cell( 'select value_id from param_value
						where value_param = :value_param and value_default = 1',
					array( 'value_param' => $param_value['param_id'] ) );
			else
				$value = $param_value['param_default'];
			
			$insert_fields[$param_value['param_id']] = 
				field::set_field( $value, array( 'title' => $param_value['param_title'],
					'type' => $param_value['param_type'], 'errors_code' => 0 ) );
		}
		
		db::delete( 'block_param', array( 'block' => $block_id ) );
		foreach( $insert_fields as $param_id => $param_value )
			db::insert( 'block_param', array(
				'block' => $block_id, 'param' => $param_id, 'value' => $param_value ) );
	}
	
	protected function get_record_actions( $record )
	{
		$actions = parent::get_record_actions( $record );
		
		$actions['property'] = array( 'title' => 'Параметры', 'url' =>
			url_for( array( 'object' => $this -> object, 'action' => 'param', 'id' => $record[$this -> primary_field] ) ) );
		$actions['multiply'] = array( 'title' => 'Размножить блок', 'url' =>
			url_for( array( 'object' => $this -> object, 'action' => 'multiply', 'id' => $record[$this -> primary_field] ) ),
				'event' => array( 'method' => 'onclick', 'value' => 'return confirm( \'Вы действительно хотите размножить этот блок?\' )' ) );
		
		return $actions;
	}
	
	protected function get_card_scripts( $action = 'edit', $record = null )
	{
		$scripts = parent::get_card_scripts( $action, $record );
		
		$page_list = db::select_all( 'select page_id, page_layout from page, layout where page_layout = layout_id' );
		$area_list = db::select_all( 'select area_id, area_title, area_layout from layout_area, layout where area_layout = layout_id order by area_title' );
		
		$scripts['block_card'] = json_encode( array( 'page_list' => $page_list, 'area_list' => $area_list ) );
		
		return $scripts;
	}
}
