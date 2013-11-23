<?php
// Инициализация строковой переменной
function init_string( $varname, $vardef = '' )
{
	if ( isset( $_REQUEST[$varname] ) )
		return (string) $_REQUEST[$varname];
	else
		return (string) $vardef;
}

// Инициализация массива
function init_array( $varname, $vardef = array() )
{
	if ( isset( $_REQUEST[$varname] ) && is_array( $_REQUEST[$varname] ) )
		return (array) $_REQUEST[$varname];
	else
		return (array) $vardef;
}

// Инициализация переменной из сессии
function init_session( $varname, $vardef = '' )
{
	if ( isset( $_SESSION[$varname] ) )
		return $_SESSION[$varname];
	else
		return $vardef;
}

// Инициализация переменной из куков
function init_cookie( $varname, $vardef = '' )
{
	if ( isset( $_COOKIE[$varname] ) )
		return $_COOKIE[$varname];
	else
		return $vardef;
}

function array_reindex( $array, $key1 = '', $key2 = '', $key3 = '', $key4 = '' )
{
	$reverted_array = array();
	
	if ( is_array( $array ) )
	{
		foreach( $array as $item )
		{
			if ( !$key1 )
				$reverted_array[$item] = $item;
			else if ( !$key2 )
				$reverted_array[$item[$key1]] = $item;
			else if ( !$key3 )
				$reverted_array[$item[$key1]][$item[$key2]] = $item;
			else if ( !$key4 )
				$reverted_array[$item[$key1]][$item[$key2]][$item[$key3]] = $item;
			else
				$reverted_array[$item[$key1]][$item[$key2]][$item[$key3]][$item[$key4]] = $item;
		}
	}
	
	return $reverted_array;
}

function array_group( $array, $key1 = '', $key2 = '', $key3 = '', $key4 = '' )
{
	$grouped_array = array();
	
	if ( is_array( $array ) )
	{
		foreach ( $array as $item )
		{
			if ( !$key1 )
				$grouped_array[$item][] = $item;
			else if ( !$key2 )
				$grouped_array[$item[$key1]][] = $item;
			else if ( !$key3 )
				$grouped_array[$item[$key1]][$item[$key2]][] = $item;
			else if ( !$key4 )
				$grouped_array[$item[$key1]][$item[$key2]][$item[$key3]][] = $item;
			else
				$grouped_array[$item[$key1]][$item[$key2]][$item[$key3]][$item[$key4]][] = $item;
		}
	}
	
	return $grouped_array;
}

function array_list( $array, $key )
{
	$values_array = array();
	
	if ( is_array( $array ) )
		foreach ( $array as $item )
			$values_array[] = $item[$key];
	
	return $values_array;
}

function array_make_in( $array, $key = '', $quote = false )
{
	$in = '0'; $ids = array();
	
	if ( is_array( $array ) )
	{
		foreach ( $array as $record )
			$ids[] = $quote ? ( $key ? addslashes( $record[$key] ) : addslashes( $record ) ) :
				( $key ? intval( $record[$key] ) : intval( $record ) );
		
		if ( count( $ids ) )
			$in = $quote ? ( "'" . join( "', '", $ids ) . "'" ) : join( ", ", $ids );
	}
	
	return $in;
}

function get_translate_clause( $table_name, $field_name, $table_record, $record_lang, $field_title = null )
{
	if ( is_null( $field_title ) )
		$field_title = $field_name;
	
	return "(
		select
			record_value
		from
			translate, lang
		where
			translate.table_record = {$table_record} and 
			translate.table_name = '{$table_name}' and
			translate.field_name = '{$field_name}' and
			lang.lang_id = translate.record_lang and
			lang.lang_name = '{$record_lang}'
	) as {$field_title}";
}

function get_translate_values( $table_name, $field_name, $table_record, $record_lang = null )
{
	$translate_values = db::select_all( '
		select lang.lang_name, translate.record_value
		from translate left join lang on lang.lang_id = translate.record_lang
		where table_name = :table_name and field_name = :field_name and table_record = :table_record
		order by lang.lang_default desc',
	array( 'table_name' => $table_name, 'field_name' => $field_name, 'table_record' => $table_record ) );
	
	$record_values = array_reindex( $translate_values, 'lang_name' );
	
	if ( !is_null( $record_lang ) )
		return $record_values[$record_lang];
	
	return $record_values;
}

function get_preference( $preference_name, $default_value = '' )
{
	if ( defined( $preference_name ) )
		return constant( $preference_name );
	else
		return $default_value;
}

function redirect_to( $url_array = array() )
{
	if ( !is_array( $url_array ) )
		$location = $url_array;
	else
		$location = url_for( $url_array );
	
	header( 'Location: ' . $location );
	
	exit;
}

function redirect_back()
{
	$back_url = '/';
	
	if ( isset( $_SERVER['HTTP_REFERER'] ) && strstr( $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'] ) )
		$back_url = $_SERVER['HTTP_REFERER'];
	
	redirect_to( $back_url );
}

function prepare_query( $include = array(), $exclude = array() )
{
	foreach ( $include as $var_name => $var_value )
		if ( in_array( $var_name, $exclude ) || is_empty( $var_value ) )
			unset( $include[$var_name] );
	
	return $include;
}

function self_url( $include = array(), $exclude = array() )
{
	$self_url = preg_replace( '/\?.*$/', '', $_SERVER['REQUEST_URI'] );
	
	$query_string = http_build_query( prepare_query( $include, $exclude ) );
	
	return $self_url . ( $query_string ? '?' . $query_string : '' );
}

function request_url( $include = array(), $exclude = array() )
{
	return self_url( array_merge( $_GET, $include ), $exclude );
}

function not_found()
{
	header( 'HTTP/1.0 404 Not Found' );
	
	print block( '404' );
	
	exit;
}

function is_empty( $var )
{
	if ( is_array( $var ) || is_object( $var ) )
		return empty( $var );
	else
		return trim( $var ) === '';
}

function declOfNum( $number, $titles, $view_number = true )
{
	$cases = array( 2, 0, 1, 1, 1, 2 ); $value = abs($number);
	return ( $view_number ? $number . ' ' : '' ) . $titles[( $value % 100 > 4 && $value % 100 < 20 ) ? 2 : $cases[min( $value % 10, 5 )]];
}

function generate_key( $max = 128 )
{
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$len = strlen( $chars ) - 1; $password = '';
	while ( $max-- )
		$password .= $chars[rand( 0, $len )];
	return $password;
}

function get_probability( $percent )
{
	return mt_rand( 0, mt_getrandmax() ) < $percent * mt_getrandmax() / 100;
}

function delete_directory( $dir )
{
	if ( !file_exists( $dir ) )
		return true;
	if ( !is_dir( $dir ) )
		return unlink( $dir );
	
	foreach ( scandir( $dir ) as $item )
	{
		if ( $item == '.' || $item == '..' )
			continue;
		if ( !delete_directory( $dir . DIRECTORY_SEPARATOR . $item ) )
			return false;
	}
	
	return rmdir( $dir );
}

function block( $template, $params = array() )
{
	$view = new view();
	
	foreach ( $params as $key => $value )
		$view -> assign( $key, $value );
	
	return $view -> fetch( $template );
}

function normalize_path( $path )
{
	return preg_replace( "/\/+/", "/", str_replace( "\\", "/", trim( $path ) ) );
}

function strip_tags_attributes( $string, $allowtags = null, $allowattributes = null )
{ 
	$string = strip_tags( $string, $allowtags ); 
	
	if ( !is_null( $allowattributes ) )
	{ 
		if ( !is_array( $allowattributes ) ) 
			$allowattributes = explode( ',', $allowattributes ); 
		if ( is_array( $allowattributes ) ) 
			$allowattributes = implode( ')(?<!', $allowattributes );
		if ( strlen( $allowattributes ) > 0 ) 
			$allowattributes = '(?<!' . $allowattributes . ')'; 
		$string = preg_replace_callback( '/<[^>]*>/i', create_function( 
			'$matches', 'return preg_replace("/ [^ =]*' . $allowattributes .
				'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);' ), $string ); 
	}
	
	return $string; 
}

function global_autoload( $class_name )
{
	$class_path = join( DIRECTORY_SEPARATOR, explode( '_', $class_name ) );
	
	if ( file_exists( $class_file = CLASS_DIR . $class_path . '.php' ) )
		include_once( $class_file );
}

spl_autoload_register( 'global_autoload' );

function exception_handler( $e, $return = false, $admin = false )
{
	$error_view = new view();
	$error_plug = $error_view -> fetch( 'block/error' );
	
	$error_view -> assign( 'message', $e -> getMessage() );
	$error_short = $error_view -> fetch( 'block/error' );
	
	$error_view -> assign( 'exception', $e );
	$error_content = $error_view -> fetch( 'block/error' );
	
	$error_log = date( 'd.m.Y H:i:s' ) . ' - ' . $e -> getMessage() . "\n" .
		$e -> getFile() . ' (' . $e -> getLine(). ')' . "\n" . $e -> getTraceAsString() . "\n\n";
	$error_file = LOG_DIR . $_SERVER['HTTP_HOST'] . '.log';
	
	if ( PRODUCTION )
	{
		if (!$admin || !$return)
		{
			@file_put_contents( $error_file, $error_log, FILE_APPEND );
			@sendmail::send( ERROR_EMAIL, ERROR_EMAIL, $_SERVER['HTTP_HOST'], ERROR_SUBLECT, $error_content );
		}
		
		if ( $admin )
		{
			$error_content = $return ? $error_short : $error_plug;
		}
		else
		{
			$error_content = $return ? '' : $error_plug;
		}
	}
	
	if ( ob_get_length() !== false )
		ob_clean();
	
	if ( $return )
		return $error_content;
	
	die( $error_content );
}

if ( isset( $_SERVER['HTTP_HOST'] ) )
	set_exception_handler( 'exception_handler' );

class AlarmException extends Exception {
	// Уведомительное исключение, не прерывающее выполнение программы
}

system::init();
