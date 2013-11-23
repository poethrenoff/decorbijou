<?php
class hierarchy extends model
{
    // Поле с идентификатором родительской записи
    protected $parent_field = '';
    
    // Родительский объект
    protected $parent = null;
    
    // Массив дочерних объектов
    protected $children = array();
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
    public function __construct($object)
    {
        parent::__construct($object);
        
        $object_desc = metadata::$objects[$object];
        foreach ($object_desc['fields'] as $field_name => $field_desc) {
            if ($field_desc['type'] == 'parent') {
                $this->parent_field = $field_name;
            }
        }
        if (!$this->parent_field) {
            throw new AlarmException('Ошибка в описании таблицы "' . $object . '". Отсутствует поле родительской записи.');
        }
    }

    // Получение идентификатора родительской записи
    public function get_parent_id()
    {
        return $this->fields[$this->parent_field];
    }
    
    // Получение поля с идентификатором родительской записи
    public function get_parent_field()
    {
        return $this->parent_field;
    }
    
    // Получение объекта-родителя
    public function get_parent()
    {
        return $this->parent;
    }
    
    // Получение списка дочерних объектов
    public function get_children()
    {
        return $this->children;
    }
    
    // Количество дочерних объектов
    public function children_count()
    {
        return count($this->children);
    }
    
    // Есть ли дочерние объекты
    public function has_children()
    {
        return $this->children_count() > 0;
    }
    
    // Построение дерева записей
    public function get_tree(&$records, $root_field = 0, $except = array())
    {
        $root_parent = null;
        
        $parent_method = 'get_' . $this->parent_field;
        $primary_method = 'get_' . $this->primary_field;
        
        if (!$root_field) {
            $records[] = model::factory($this->object); $except[] = 0;
        }
        
        foreach ($records as $parent_record) {
            foreach ($records as $child_record) {
                if ($child_record->$parent_method() == (int)$parent_record->$primary_method() &&
                        !in_array((int)$child_record->$primary_method(), $except)) {
                    $child_record->parent = $parent_record;
                    $parent_record->children[] = $child_record;
                }
            }
            if ((int)$parent_record->$primary_method() == $root_field) {
                $root_parent = $parent_record;
            }
        }
        return $root_parent;
    }
}