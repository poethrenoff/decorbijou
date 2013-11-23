<?php
class sendmail
{
	public static function send( $to, $from, $name, $subject, $message, $files = array() )
	{
		list( $headers, $body ) = self::prepare( $from, $name, $subject, $message, $files );
		
		$result = Mail::factory( 'mail' ) -> send( $to, $headers, $body );
		
		return !is_a( $result, 'PEAR_Error' );
	}
	
	public static function prepare( $from, $name, $subject, $message, $files = array() )
	{
		set_include_path( get_include_path() . PATH_SEPARATOR . CLASS_DIR . 'PEAR' );
		
		include_once 'Mail.php';
		include_once 'Mail/mime.php';
		
		$build_params = array( 'eol' => "\n",
			'head_encoding' => 'base64', 'text_encoding' => 'base64', 'html_encoding' => 'base64',
			'html_charset'  => 'UTF-8', 'text_charset'  => 'UTF-8', 'head_charset'  => 'UTF-8' );
		
		$mime = new Mail_Mime( $build_params );
		
		$mime -> setSubject( self::header_encode( $subject ) );
		$mime -> setFrom( !empty( $name ) ? '"' . self::header_encode( $name ) . '" <' . $from . '>' : '<' . $from . '>' );
		
		$mime -> setHTMLBody( $message );
		$mime -> setTXTBody( strip_tags( $message ) );
		
		preg_match_all( '/src=\"(.+)\"/isU', $message, $match, PREG_SET_ORDER );
		
		foreach( $match as $src )
		{
			$path_parts = pathinfo( $src[1] );
			$mime_type = 'image/' . strtolower( $path_parts['extension'] );
			
			if ( $file_contents = @file_get_contents( $src[1] ) )
				$mime -> addHTMLImage( $file_contents, $mime_type, $src[1], false );
		}
		
		foreach ( $files as $file_name => $file_path )
			$mime -> addAttachment( $file_path, 'application/octet-stream',
				$file_name, true, 'base64', 'attachment', 'UTF-8' );
		
		$body = $mime -> get();
		$headers = $mime -> headers();
		
		return array( $headers, $body );
	}
	
	public static function header_encode( $text )
	{
		return '=?UTF-8?B?' . base64_encode( $text ) . '?=';
	}
}