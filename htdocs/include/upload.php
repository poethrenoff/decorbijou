<?php
/**
 * Класс для работы с загрузкой файлов
 * 
 * 		Пример использования
 * 		
 * 		$upload = upload::fetch( 'file_name', array( 'allowed_types' => 'gif|jpg|png' ) );
 * 		
 * 		if ( $upload -> is_error() )
 * 			throw new Exception( $upload -> get_error() );
 * 		
 * 		// Абсолютный путь к файлу
 * 		$file_path = $upload -> get_file_path();
 * 		
 * 		// Относительный путь к файлу
 * 		$file_link = $upload -> get_file_link();
 */
class upload
{
	public $max_size = 0;
	public $max_width = 0;
	public $max_height = 0;
	public $max_filename = 0;
	public $allowed_types;
	public $file_temp = '';
	public $file_name = '';
	public $orig_name = '';
	public $file_type = '';
	public $file_size = '';
	public $file_ext = '';
	public $file_link = '';
	public $overwrite = false;
	public $encrypt_name = false;
	public $translit_name = true;
	public $is_image = false;
	public $image_width = '';
	public $image_height = '';
	public $image_type = '';
	public $image_size_str = '';
	public $remove_spaces = true;
	
	protected $upload_path = '';
	
	protected $error_msg = '';
	protected $error_lang = 'ru';
	
	protected $error_names = array
	(
		'ru' => array
		(
			'upload_file_exceeds_limit' => 'Размер файла превышает максимально допустимый',
			'upload_file_exceeds_form_limit' => 'Размер файла превышает максимально допустимый',
			'upload_file_partial' => 'Файл был получен частично',
			'upload_no_temp_directory' => 'Отсутствует временный каталог',
			'upload_unable_to_write_file' => 'Ошибка записи файла на диск',
			'upload_stopped_by_extension' => 'Загрузка файла остановлена модулем',
			'upload_no_file_selected' => 'Файл не был загружен',
			'upload_invalid_filetype' => 'Недопустимый тип файла',
			'upload_invalid_filesize' => 'Размер файла превышает максимально допустимый',
			'upload_invalid_dimensions' => 'Размеры изображения превышают максимально допустимые по ширине или высоте',
			'upload_destination_error' => 'Ошибка записи файла на диск',
			'upload_no_filepath' => 'Каталог для загрузки файла не существует',
			'upload_not_writable' => 'Каталог для загрузки файла запрещен для записи',
		),
		'en' => array
		(
			'upload_userfile_not_set' => 'Unable to find a post variable called userfile',
			'upload_file_exceeds_limit' => 'The uploaded file exceeds the maximum allowed size in your PHP configuration file',
			'upload_file_exceeds_form_limit' => 'The uploaded file exceeds the maximum size allowed by the submission form',
			'upload_file_partial' => 'The file was only partially uploaded',
			'upload_no_temp_directory' => 'The temporary folder is missing',
			'upload_unable_to_write_file' => 'The file could not be written to disk',
			'upload_stopped_by_extension' => 'The file upload was stopped by extension',
			'upload_no_file_selected' => 'You did not select a file to upload',
			'upload_invalid_filetype' => 'The filetype you are attempting to upload is not allowed',
			'upload_invalid_filesize' => 'The file you are attempting to upload is larger than the permitted size',
			'upload_invalid_dimensions' => 'The image you are attempting to upload exceedes the maximum height or width',
			'upload_destination_error' => 'A problem was encountered while attempting to move the uploaded file to the final destination',
			'upload_no_filepath' => 'The upload path does not appear to be valid',
			'upload_not_writable' => 'The upload destination folder does not appear to be writable',
		),
	);
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct($props = array())
	{
		if (count($props) > 0)
		{
			$this->initialize($props);
		}
	}
	
	/**
	 * Factory
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	object
	 */
	static function fetch( $field = 'userfile', $props = array() )
	{
		$obj = new Upload( $props );
		$obj -> do_upload( $field );
		
		return $obj;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize preferences
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */	
	function initialize($props = array())
	{
		if (count($props) > 0)
		{
			foreach ($props as $key => $val)
			{
				$method = 'set_'.$key;
				if (method_exists($this, $method))
				{
					$this->$method($val);
				}
				else
				{
					$this->$key = $val;
				}
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Perform the file upload
	 *
	 * @access	public
	 * @return	bool
	 */	
	function do_upload($field = 'userfile')
	{
		// Is $_FILES[$field] set? If not, no reason to continue.
		if ( ! isset($_FILES[$field]))
		{
			$this->set_error('upload_no_file_selected');
			return FALSE;
		}
		
		// Is the upload path valid?
		if ( ! $this->validate_upload_path())
		{
			// errors will already be set by validate_upload_path() so just return FALSE
			return FALSE;
		}

		// Was the file able to be uploaded? If not, determine the reason why.
		if ( ! is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			$error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

			switch($error)
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
					$this->set_error('upload_file_exceeds_limit');
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->set_error('upload_file_exceeds_form_limit');
					break;
				case 3: // UPLOAD_ERR_PARTIAL
				   $this->set_error('upload_file_partial');
					break;
				case 4: // UPLOAD_ERR_NO_FILE
				   $this->set_error('upload_no_file_selected');
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->set_error('upload_no_temp_directory');
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$this->set_error('upload_unable_to_write_file');
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->set_error('upload_stopped_by_extension');
					break;
				default :   $this->set_error('upload_no_file_selected');
					break;
			}

			return FALSE;
		}

		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];
		$this->file_name = $this->_prep_filename($_FILES[$field]['name']);
		$this->file_size = $_FILES[$field]['size'];
		$this->file_ext  = $this->get_extension($_FILES[$field]['name']);
		
		$this->file_name = mb_strtolower( $this->file_name, 'UTF-8' );
		$this->file_ext = mb_strtolower( $this->file_ext, 'UTF-8' );
		
		$this->file_type = $this->_get_file_type($this->file_ext);

		// Is the file type allowed to be uploaded?
		if ( ! $this->is_allowed_filetype())
		{
			$this->set_error('upload_invalid_filetype');
			return FALSE;
		}

		// Is the file size within the allowed maximum?
		if ( ! $this->is_allowed_filesize())
		{
			$this->set_error('upload_invalid_filesize');
			return FALSE;
		}

		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if ( ! $this->is_allowed_dimensions())
		{
			$this->set_error('upload_invalid_dimensions');
			return FALSE;
		}

		// Sanitize the file name for security
		$this->file_name = $this->clean_file_name($this->file_name);
		
		// Truncate the file name if it's too long
		if ($this->max_filename > 0)
		{
			$this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
		}
		
		// Remove white spaces in the name
		if ($this->remove_spaces == TRUE)
		{
			$this->file_name = preg_replace("/\s+/", "_", $this->file_name);
		}

		/*
		 * Validate the file name
		 * This function appends an number onto the end of
		 * the file if one with the same name already exists.
		 * If it returns false there was a problem.
		 */
		$this->orig_name = $this->file_name;

		if ($this->overwrite == FALSE)
		{
			$this->file_name = $this->set_filename($this->upload_path, $this->file_name);
			
			if ($this->file_name === FALSE)
			{
				return FALSE;
			}
		}
		
		/*
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 */
		if ( ! @copy($this->file_temp, $this->upload_path.$this->file_name))
		{
			if ( ! @move_uploaded_file($this->file_temp, $this->upload_path.$this->file_name))
			{
				$this->set_error('upload_destination_error');
				return FALSE;
			}
		}
		
		@chmod($this->upload_path.$this->file_name, 0777);
		
		/*
		 * Set the finalized image dimensions
		 * This sets the image width/height (assuming the
		 * file was an image).  We use this information
		 * in the "data" function.
		 */
		$this->set_image_properties($this->upload_path.$this->file_name);

		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Upload Path
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function set_upload_path($path)
	{
		$this -> upload_path = normalize_path( UPLOAD_DIR ) . rtrim( $path, '/' ) . '/';
	}
	
	/**
	 * Set the file name
	 *
	 * This function takes a filename/path as input and looks for the
	 * existence of a file with the same name. If found, it will append a
	 * number to the end of the filename to avoid overwriting a pre-existing file.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */	
	function set_filename($path, $filename)
	{
		if ($this->encrypt_name == TRUE)
		{
			mt_srand();
			$filename = md5(uniqid(mt_rand())).$this->file_ext;
		}
		
		if ($this->translit_name == TRUE)
		{
			$filename = to_translit( $filename );
		}
		
		if ( ! file_exists($path.$filename))
		{
			return $filename;
		}
		
		$filename = str_replace($this->file_ext, '', $filename);
		
		$new_filename = $filename . $this->file_ext; $n = 0;
		while ( file_exists( $path . $new_filename ) )
			$new_filename = $filename . '_' . ( ++$n ) . $this->file_ext;
		
		return $new_filename;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Maximum File Size
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */	
	function set_max_filesize($n)
	{
		$this->max_size = ((int) $n < 0) ? 0: (int) $n;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Maximum File Name Length
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */	
	function set_max_filename($n)
	{
		$this->max_filename = ((int) $n < 0) ? 0: (int) $n;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Set Maximum Image Width
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */	
	function set_max_width($n)
	{
		$this->max_width = ((int) $n < 0) ? 0: (int) $n;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Maximum Image Height
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */	
	function set_max_height($n)
	{
		$this->max_height = ((int) $n < 0) ? 0: (int) $n;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Allowed File Types
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function set_allowed_types($types)
	{
		if ( $types )
			$this->allowed_types = explode('|', $types);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Image Properties
	 *
	 * Uses GD to determine the width/height/type of image
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function set_image_properties($path = '')
	{
		if ( ! $this->is_image())
		{
			return;
		}

		if (function_exists('getimagesize'))
		{
			if (FALSE !== ($D = @getimagesize($path)))
			{	
				$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

				$this->image_width		= $D['0'];
				$this->image_height		= $D['1'];
				$this->image_type		= ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
				$this->image_size_str	= $D['3'];  // string containing height and width
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Validate the image
	 *
	 * @access	public
	 * @return	bool
	 */	
	function is_image()
	{
		// IE will sometimes return odd mime-types during upload, so here we just standardize all
		// jpegs or pngs to the same file type.

		$png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');
		
		if (in_array($this->file_type, $png_mimes))
		{
			$this->file_type = 'image/png';
		}
		
		if (in_array($this->file_type, $jpeg_mimes))
		{
			$this->file_type = 'image/jpeg';
		}

		$img_mimes = array(
							'image/gif',
							'image/jpeg',
							'image/png',
						   );

		return (in_array($this->file_type, $img_mimes, TRUE)) ? TRUE : FALSE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Verify that the filetype is allowed
	 *
	 * @access	public
	 * @return	bool
	 */	
	function is_allowed_filetype()
	{
		if (count($this->allowed_types) == 0 OR ! is_array($this->allowed_types))
		{
			return TRUE;
		}

		$image_types = array('gif', 'jpg', 'jpeg', 'png', 'jpe');

		foreach ($this->allowed_types as $val)
		{
			$mime = $this->mimes_types(strtolower($val));
			
			if ($mime === FALSE)
			{
				continue;
			}

			// Images get some additional checks
			if (in_array($val, $image_types))
			{
				if (@getimagesize($this->file_temp) === FALSE)
				{
					return FALSE;
				}
			}

			if (is_array($mime))
			{
				if (in_array($this->file_type, $mime, TRUE))
				{
					return TRUE;
				}
			}
			else
			{
				if ($mime == $this->file_type)
				{
					return TRUE;
				}	
			}		
		}
		
		return FALSE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Verify that the file is within the allowed size
	 *
	 * @access	public
	 * @return	bool
	 */	
	function is_allowed_filesize()
	{
		if ($this->max_size != 0  AND  $this->file_size > $this->max_size)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Verify that the image is within the allowed width/height
	 *
	 * @access	public
	 * @return	bool
	 */	
	function is_allowed_dimensions()
	{
		if ( ! $this->is_image())
		{
			return TRUE;
		}

		if (function_exists('getimagesize'))
		{
			$D = @getimagesize($this->file_temp);

			if ($this->max_width > 0 AND $D['0'] > $this->max_width)
			{
				return FALSE;
			}

			if ($this->max_height > 0 AND $D['1'] > $this->max_height)
			{
				return FALSE;
			}

			return TRUE;
		}

		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Validate Upload Path
	 *
	 * Verifies that it is a valid upload path with proper permissions.
	 *
	 *
	 * @access	public
	 * @return	bool
	 */	
	function validate_upload_path()
	{
		if ($this->upload_path == '')
		{
			$this->set_error('upload_no_filepath');
			return FALSE;
		}

		if (function_exists('realpath') AND @realpath($this->upload_path) !== FALSE)
		{
			$this->upload_path = str_replace("\\", "/", realpath($this->upload_path));
		}

		if ( ! @is_dir($this->upload_path) && ! @mkdir($this->upload_path, 0777, true) )
		{
			$this->set_error('upload_no_filepath');
			return FALSE;
		}

		if ( ! is_writable($this->upload_path))
		{
			$this->set_error('upload_not_writable');
			return FALSE;
		}

		$this->upload_path = preg_replace("/(.+?)\/*$/", "\\1/",  $this->upload_path);
		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Extract the file extension
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function get_extension($filename)
	{
		$x = explode('.', $filename);
		return '.'.end($x);
	}	
	
	// --------------------------------------------------------------------
	
	/**
	 * Clean the file name for security
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */		
	function clean_file_name($filename)
	{
		$bad = array(
			"<!--",
			"-->",
			"'",
			"<",
			">",
			'"',
			'&',
			'$',
			'=',
			';',
			'?',
			'/',
			"%20",
			"%22",
			"%3c",		// <
			"%253c", 	// <
			"%3e", 		// >
			"%0e", 		// >
			"%28", 		// (
			"%29", 		// )
			"%2528", 	// (
			"%26", 		// &
			"%24", 		// $
			"%3f", 		// ?
			"%3b", 		// ;
			"%3d"		// =
		);
					
		$filename = str_replace($bad, '', $filename);

		return stripslashes($filename);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Limit the File Name Length
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */		
	function limit_filename_length($filename, $length)
	{
		if (strlen($filename) < $length)
		{
			return $filename;
		}
	
		$ext = '';
		if (strpos($filename, '.') !== FALSE)
		{
			$parts		= explode('.', $filename);
			$ext		= '.'.array_pop($parts);
			$filename	= implode('.', $parts);
		}
	
		return substr($filename, 0, ($length - strlen($ext))).$ext;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * 	Метод устанавливает язык сообщений об ошибках
	 *
	 * @access	public
	 * @param	string
	 */
	function set_error_lang($lang)
	{
		if (array_key_exists($lang, $this->error_names))
		{
			$this->error_lang = $lang;
		}
	}
	
	/**
	 * 	Возвращает true если при закачке файла были ошибки
	 *
	 * @access	public
	 * @return	boolean
	 */
	function is_error()
	{
		return $this->error_msg ? true : false;
	}
	
	/**
	 * Set an error message
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */	
	function set_error($msg)
	{
		$this->error_msg = isset( $this -> error_names[$this->error_lang][$msg] ) ?
			$this -> error_names[$this->error_lang][$msg] : $msg;
	}
	
	/**
	 * 	Возвращает описание ошибки
	 *
	 * @access	public
	 * @return	string
	 */
	function get_error()
	{
		return $this->error_msg;
	}
	
	/**
	 * 	Абсолютный путь к закаченному файлу
	 *
	 * @access	public
	 * @return	string
	 */
	function get_file_path()
	{
		return $this -> upload_path . $this -> file_name;
	}
	
	/**
	 * 	Относительный путь к закаченному файлу
	 *
	 * @access	public
	 * @return	string
	 */
	function get_file_link()
	{
		return str_replace( normalize_path( UPLOAD_DIR ), UPLOAD_ALIAS, $this -> get_file_path() );
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * List of Mime Types
	 *
	 * This is a list of mime types.  We use it to validate
	 * the "allowed types" set by the developer
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function mimes_types($mime)
	{
		return ( !isset(mime::$types[$mime]) ) ? FALSE : mime::$types[$mime];
	}

	// --------------------------------------------------------------------
	
	/**
	 * Prep Filename
	 *
	 * Prevents possible script execution from Apache's handling of files multiple extensions
	 * http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _prep_filename($filename)
	{
		if (strpos($filename, '.') === FALSE)
		{
			return $filename;
		}
		
		$parts		= explode('.', $filename);
		$ext		= array_pop($parts);
		$filename	= array_shift($parts);
				
		foreach ($parts as $part)
		{
			if ($this->mimes_types(strtolower($part)) === FALSE)
			{
				$filename .= '.'.$part.'_';
			}
			else
			{
				$filename .= '.'.$part;
			}
		}
		
		$filename .= '.'.$ext;
		
		return $filename;
	}
	
	/**
	 * Определение типа файла
	 *
	 * Если сервер не может определить тип файла, опеределяем его сами по расширению
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _get_file_type($file_ext)
	{
		$file_ext = str_replace( '.', '', $file_ext);
		
		if ( $this -> mimes_types( strtolower( $file_ext ) ) == false )
			return '';
		
		$mime = $this -> mimes_types( strtolower( $file_ext ) );
		
		if ( is_array( $mime ) )
			$mime = current( $mime );
		
		return $mime;
	}
}
