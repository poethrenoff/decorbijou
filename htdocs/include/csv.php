<?php
class CSV
{
	// Функция пытается определить разделитель полей в CSV файле
	// Если возникла критическая ошибка то устаналвивает ошибку в глобальной переменной $php_errormsg
	// Если определить не удалось из-за того что файл пустой например возвращает ";"
	// при любой ошибке возвращает false
	// На вход - указатель файла
	static function get_delim($fh)
	{
		$old_pos = @ftell($fh);
		if ($old_pos === false) { $GLOBALS["php_errormsg"] = $php_errormsg; return false; }
	
		$CSVDelimeter = null;
		
		if (@fseek($fh, 0) === false) { $GLOBALS["php_errormsg"] = $php_errormsg; return false; }
		$header1 = @fgetcsv($fh, 16384, ",");
		if ($header1 === false) { $GLOBALS["php_errormsg"] = $php_errormsg; return false; }
	
		if (@fseek($fh, 0) === false) { $GLOBALS["php_errormsg"] = $php_errormsg; return false; }
		$header2 = @fgetcsv($fh, 16384, ";");
		if ($header2 === false) { $GLOBALS["php_errormsg"] = $php_errormsg; return false; }
	
		if (count($header1) > count($header2)) { $CSVDelimeter = ","; } else { $CSVDelimeter = ";"; }
		if (@fseek($fh, $old_pos) === false) { $GLOBALS["php_errormsg"] = $php_errormsg; return false; }
		
		return $CSVDelimeter;
	}
	
	// Функция аналогичная fputcsv - только она возвращает экранированную строку
	static function escape($s)
	{
		if (preg_match("/[\";\\\n\r\t ]/", $s))
		{ return '"' . str_replace('"', '""', $s) . '"'; }
		else { return $s; }
	}
	
	// Получает на вход массив массивов, берёт ключи первого массива как заголовки столбцов
	// И отдаёт данные в формате .csv
	static function dump($data, $filename = null, $delim = ";")
	{
		if ($filename === null) { $filename = "data_" . date("d.m.Y_H_i_s") . ".csv"; }
		while (@ob_end_clean());
	
		header("Pragma: private");
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=\"$filename\"");
	
		$head = reset($data);
		if ($head !== false)
		{
			$keys = array_keys($head);
			foreach ($keys as $key) { echo self::escape($key) . $delim; }
			echo "\r\n";
	
			foreach ($data as $row)
			{
				foreach ($row as $cell) { echo self::escape($cell) . $delim; }
				echo "\r\n";
			}
		}
		exit;
	}
}