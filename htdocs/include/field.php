<?php
abstract class field
{
    // Кеш объектов
    private static $object_cache = array();
    
    public static $errors = array(
        'require' => 1, 'int' => 2, 'float' => 4, 'date' => 8, 'datetime' => 16, 'email' => 32, 'alpha' => 64
   );
    
    public static function get_errors_code($errors_string = '')
    {
        $errors_code = 0;
        foreach (explode('|', $errors_string) as $error_name)
            if (isset(self::$errors[$error_name]))
                $errors_code |= self::$errors[$error_name];
        return $errors_code;
    }
    
    public static function get_errors_value($errors_code = 0)
    {
        $errors_value = array();
        foreach(self::$errors as $error_name => $error_code)
            if ($errors_code & $error_code)
                $errors_value[] = $error_name;
        return join('|', $errors_value);
    }
    
    public static function apply_default_errors($errors_code, $type)
    {
        if ($type == 'pk')
            $errors_code |= field::$errors['require'] | field::$errors['int'];
        if ($type == 'parent' || $type == 'order' || $type == 'table')
            $errors_code |= field::$errors['int'];
        if ($type == 'date')
            $errors_code |= field::$errors['date'];
        if ($type == 'datetime')
            $errors_code |= field::$errors['datetime'];
        if ($type == 'order')
            $errors_code |= field::$errors['require'];
        if ($type == 'int')
            $errors_code |= field::$errors['int'];
        if ($type == 'float')
            $errors_code |= field::$errors['float'];
        if ($type == 'password')
            $errors_code |= field::$errors['alpha'];
        
        return $errors_code;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    abstract public function get($content);
    
    abstract public function form($content);
    
    abstract public function set($content);
    
    public function check($content, $errors_string = '')
    {
        if (!empty($errors_string)) {
            foreach (explode('|', $errors_string) as $error_name) {
                if (!valid::factory($error_name)->check($content)) {
                    return false;
                }
            }
        }
        return true;
    }
    
    // Создание объекта поля
    public static final function factory($type)
    {
        if (!isset(self::$object_cache[$type])) {
            $class_name = 'field_' . $type;
            self::$object_cache[$type] = new $class_name();
        }
        return self::$object_cache[$type];
    }
    
    public static final function get_field($content, $type)
    {
        if (is_array($content))
            foreach($content as $item_id => $item)
                $content[$item_id] = self::get_field($item, $type);
        else
            $content = self::factory($type)->get($content);
        
        return $content;
    }
    
    public static final function form_field($content, $type)
    {
        if (is_array($content))
            foreach($content as $item_id => $item)
                $content[$item_id] = self::form_field($item, $type);
        else
            $content = self::factory($type)->form($content);
        
        return $content;
    }
    
    public static final function set_field($content, $field_desc)
    {
        if (is_array($content))
            foreach($content as $item_id => $item)
                $content[$item_id] = self::set_field($item, $field_desc);
        else
        {
            foreach(self::$errors as $error_name => $error_code)
                if ($field_desc['errors_code'] & $error_code)
                    if (!valid::factory($error_name)->check($content))
                        throw new AlarmException('Ошибочное значение поля "' . $field_desc['title'] . '".');
            
            $content = self::factory($field_desc['type'])->set($content);
        }
        
        return $content;
    }
}
