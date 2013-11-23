<?php
class array2xml extends DOMDocument
{
	private static $dom;
	
	public static function convert( $data, $rootName = 'response' )
	{
		self::$dom = new DOMDocument();
		
		self::$dom -> encoding = 'UTF-8';
		self::$dom -> formatOutput = true;
		
		self::buildXML( array( $data ), $rootName, self::$dom );
		
		return self::$dom -> saveXML();
	}
	
	private static function buildXML( $data, $parentName, $parentNode )
	{
		foreach( $data as $key => $value ) 
		{
			$elementName = is_numeric( $key ) ? ( is_numeric( $parentName ) ? 'key' . $key : $parentName ) : $key;
			
			if ( !is_array( $value ) )
			{
				$node = self::$dom -> createElement( $elementName );
				
				if ( self::is_cdata( $value ) )
					$text = self::$dom -> createCDATASection( $value );
				else
					$text = self::$dom -> createTextNode( $value );
				
				$text = $node -> appendChild( $text );
				
				$node = $parentNode -> appendChild( $node );
			}
			else if ( !self::is_vector( $value ) )
			{
				$node = self::$dom -> createElement( $elementName );
				$node = $parentNode -> appendChild( $node );
				
				self::buildXML( $value, $elementName, $node );
			}
			else
			{
				self::buildXML( $value, $elementName, $parentNode );
			}
		}
	}
	
	private static function is_vector( $data )
	{
		return count( array_diff_key( $data, range( 0, count( $data ) - 1 ) ) ) == 0;
	}
	
	private static function is_cdata( $data )
	{
		return $data != htmlspecialchars( $data, ENT_QUOTES, 'UTF-8' );
	}
}
