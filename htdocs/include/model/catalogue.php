<?php
class model_catalogue extends model
{
    // Возвращает объект каталога по системному имени
    public function get_by_name($catalogue_name)
    {
        $record = db::select_row('select * from catalogue where catalogue_name = :catalogue_name',
            array('catalogue_name' => $catalogue_name));
        if (!$record){
            throw new AlarmException("Ошибка. Запись {$this->object}({$catalogue_name}) не найдена.");
        }
        return $this->get($record['catalogue_id'], $record);
    }
    
    // Возвращает URL каталога
    public function get_catalogue_url()
    {
        return url_for(array('controller' => 'catalogue', 'name' => $this->get_catalogue_name()));
    }
}