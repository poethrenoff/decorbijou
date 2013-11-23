<?php
abstract class module extends object
{
	// Параметры модуля
	protected $params = array();
	
	// Вызываемый метод
	protected $action = null;
	
	// Модуль в главной области
	protected $is_main = false;
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	
	// Создание объекта модуля
	public static final function factory( $object )
	{
		$class_name = 'module_' . $object;
		
		if ( !class_exists( $class_name ) )
			throw new Exception( 'Ошибка. Класс "' . $class_name . '" не найден.' );
		
		return new $class_name( $object );
	}
	
	// Инициализация модуля
	public function init( $action = 'index', $params = array(), $is_main = false )
	{
		$this -> view = new view();
		
		foreach ( $params as $param_name => $param_value )
			$this -> params[$param_name] = $param_value;
		
		$this -> action = $action;
		$this -> is_main = $is_main;
		
		$action_name = 'action_' . $action;
		
		if ( !method_exists( $this, $action_name ) )
		{
			if ( !$is_main )
				throw new AlarmException( 'Ошибка. Метод "' . $action_name . '" не найден.' );
			else
				not_found();
			
			return;
		}
		
		$cache_key = $this -> get_cache_key();
		
		if ( !$cache_key || ( $cache_values = cache::get( $cache_key ) ) === false )
		{
			$this -> $action_name();
			
			if ($cache_key)
			{
				cache::set( $cache_key, array( $this -> content, $this -> output ) );
			}
		}
		else
		{
			list( $this -> content, $this -> output ) = $cache_values;
		}
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	
	// У модуля обязательно должно быть действие по умолчанию
	protected abstract function action_index();
	
	// Возврашает значение параметра по его имени 
	protected function get_param( $varname, $vardef = '' )
	{
		if ( isset( $this -> params[$varname] ) )
			return $this -> params[$varname];
		else
			return $vardef;
	}
	
	// Вычисляет хэш параметров модуля
	protected function get_cache_key()
	{
		if ( !is_cache() ) return false;
		
		if ( !empty( $_POST ) ) return false;
		
		$cache_key['_host'] = $_SERVER['HTTP_HOST'];
		$cache_key['_class'] = get_class( $this );
		$cache_key['_action'] = $this -> action;
		
		$cache_key += $this -> params;
		
		parse_str( $_SERVER['QUERY_STRING'], $query_string );
		$cache_key += $query_string;
		
		$cache_key += $this -> ext_cache_key();
		
		$cache_key = serialize( $cache_key );
		
		return md5( $cache_key );
	}
	
	// Дополнительные параметры хэша модуля
	protected function ext_cache_key()
	{
		return array();
	}
}
