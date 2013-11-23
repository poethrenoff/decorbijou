<?php
abstract class cache
{
	private static $cache_driver = null;
	
	private static function get_driver()
	{
		if ( self::$cache_driver == null )
			self::$cache_driver = self::factory();
		
		return self::$cache_driver;
	}
	
	private static function factory( $cache_type = CACHE_TYPE )
	{
		$driver_name = 'cache_' . $cache_type;
		
		return new $driver_name( $cache_type );
	}
	
	private static function get_key_name( $key )
	{
		return CACHE_DIR . md5( $key );
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////
	
	public static function get( $key, $expire = CACHE_TIME )
	{
		return self::get_driver() -> get( self::get_key_name( $key ), $expire );
	}
	
	public static function set( $key, $var, $expire = CACHE_TIME )
	{
		return self::get_driver() -> set( self::get_key_name( $key ), $var, $expire );
	}
	
	public static function clear()
	{
		return self::get_driver() -> clear();
	}
}
