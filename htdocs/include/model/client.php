<?php
class model_client extends model
{
    // ���������� ������ ������������ �� email
    public function get_by_email($client_email, $throw_exception = false)
    {
        $record = db::select_row('select * from client where client_email = :client_email',
            array('client_email' => $client_email));
        if (empty($record)) {
            if ($throw_exception) {
                throw new AlarmException("������. ������ {$this->object}({$client_email}) �� �������.");
            } else {
                return false;
            }
        }
        return $this->get($record['client_id'], $record);
    }
    
    // ���������� ������ �������
    public function get_purchase_list()
    {
        return model::factory('purchase')->get_list(
            array('purchase_client' => $this->get_id()), array('purchase_date' => 'desc')
        );
    }
}