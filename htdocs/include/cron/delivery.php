<?php
	include_once dirname( dirname( dirname( __FILE__ ) ) ) . '/config/config.php';
	
	$delivery_body_list = db::select_all( 'select * from delivery_body' );
	
	if ( !count( $delivery_body_list ) ) exit;
	
	$delivery_body_list = array_reindex( $delivery_body_list, 'body_id' );
	
	$delivery_queue_list = db::select_all( '
		select queue_id, queue_body, person_email
		from delivery_queue, delivery_person
		where delivery_queue.queue_person = delivery_person.person_id
		limit 100' );
	
	if ( !count( $delivery_queue_list ) ) exit;
	
	set_include_path( get_include_path() . PATH_SEPARATOR . CLASS_DIR . 'PEAR' );
	
	include_once 'Mail.php';
	
	$mail = Mail::factory( 'mail' );
	
	foreach ( $delivery_queue_list as $delivery_queue_item )
	{
		if ( isset( $delivery_body_list[$delivery_queue_item['queue_body']] ) )
		{
			$delivery_body = $delivery_body_list[$delivery_queue_item['queue_body']];
			
			$mail -> send( $delivery_queue_item['person_email'], unserialize( $delivery_body['body_headers'] ), $delivery_body['body_text'] );
			
			db::delete( 'delivery_queue', array( 'queue_id' => $delivery_queue_item['queue_id'] ) );
		}
	}
