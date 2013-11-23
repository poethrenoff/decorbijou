<?php
abstract class db
{
	protected static $db_driver = null;
	
	protected static function get_driver()
	{
		if ( self::$db_driver == null )
			self::$db_driver = db_driver::factory( DB_TYPE, DB_HOST, '', DB_NAME, DB_USER, DB_PASSWORD );
		
		return self::$db_driver;
	}
	
	protected static function get_result( $method, $query, $fields = array(), $expiration = 0 )
	{
		$cache_key = $expiration > 0 ? static::get_cache_key( $query, $fields ) : false;
		
		if ( !$cache_key || ( $result = cache::get( $cache_key, $expiration ) ) === false )
		{
			$result = static::get_driver() -> $method( $query, $fields );
			
			if ( $cache_key )
			{
				cache::set( $cache_key, $result, $expiration );
			}
		}
		
		return $result;
	}
	
	protected static function get_cache_key($query, $fields = array())
	{
		return md5(serialize(array($query, $fields)));
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////
	
	public static function query( $query, $fields = array() )
	{
		return static::get_driver() -> query( $query, $fields );
	}
	
	public static function select_cell( $query, $fields = array(), $expiration = 0 )
	{
		return static::get_result( 'select_cell', $query, $fields, $expiration );
	}
	
	public static function select_row( $query, $fields = array(), $expiration = 0 )
	{
		return static::get_result( 'select_row', $query, $fields, $expiration );
	}
	
	public static function select_all( $query, $fields = array(), $expiration = 0 )
	{
		return static::get_result( 'select_all', $query, $fields, $expiration );
	}
	
	public static function insert( $table, $fields = array() )
	{
		return static::get_driver() -> insert( $table, $fields );
	}
	
	public static function update( $table, $fields = array(), $where = array() )
	{
		return static::get_driver() -> update( $table, $fields, $where );
	}
	
	public static function delete( $table, $where = array() )
	{
		return static::get_driver() -> delete( $table, $where );
	}
	
	public static function last_insert_id( $sequence = null )
	{
		return static::get_driver() -> last_insert_id( $sequence );
	}
	
	public static function beginTransaction()
	{
		return static::get_driver() -> beginTransaction();
	}
	
	public static function commit()
	{
		return static::get_driver() -> commit();
	}
	
	public static function rollBack()
	{
		return static::get_driver() -> rollBack();
	}
	
	public static function create()
	{
		return static::get_driver() -> create();
	}
}
