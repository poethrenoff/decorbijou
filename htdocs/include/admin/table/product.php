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
		
		if ((isset( $_FILES['product_image_file']['name']) && $_FILES['product_image_file']['name'])) {
			$this->apply_watermark($primary_field);
		}
		
		if ( $redirect )
			$this -> redirect();
		
		return $primary_field;
	}
	
	protected function action_edit_save( $redirect = true )
	{
		parent::action_edit_save( false );
		
		if (isset($_FILES['product_image_file']['name']) && $_FILES['product_image_file']['name']) {
			$this->apply_watermark(id());
		}
		
		if ( $redirect )
			$this -> redirect();
	}
	
	protected function apply_watermark($primary_field)
	{
		$product = model::factory('product')->get($primary_field);
		$source_image = str_replace(UPLOAD_ALIAS, normalize_path(UPLOAD_DIR), $product->get_product_image());
		$watermark_image = $_SERVER['DOCUMENT_ROOT'] . '/image/watermark.png';
		
		image::process('watermark', array(
			'source_image' => $source_image, 'watermark_image' => $watermark_image,
		));
	}
}