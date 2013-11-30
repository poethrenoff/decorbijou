<?php
class module_purchase extends module
{
    /**
     * Текущий пользователь
     */
    protected $client = null;
    
    /**
     * Корзина
     */
    protected $cart = null;
    
    /**
     * Список способов доставки
     */
    protected $delivery_list = null;
    
    /**
     * Оформление заказа
     */
    protected function action_index()
    {
        $this->delivery_list = model::factory('delivery')
            ->get_list(array('delivery_active' => 1), array('delivery_price' => 'asc'));
        $this->view->assign('delivery_list', $this->delivery_list);
        
        $this->cart = cart::factory();
        
        if (session::flash('purchase_complete')) {
            $this->content = $this->view->fetch('module/purchase/complete');
        } else {
            $this->client = module_client::get_info();
            $error = !empty($_POST) && $this->cart->get_quantity() ? $this->add_purchase() : array();
            
            $this->view->assign('error', $error);
            $this->view->assign('cart', $this->cart);
            $this->view->assign('client', $this->client);
            $this->content = $this->view->fetch('module/purchase/form');
        }
    }
    
    /**
     * Создание заказа
     */
    protected function add_purchase()
    {
        $error = array();
        
        $field_list = array(
            'client_title', 'client_email', 'purchase_phone', 'purchase_address',
            'purchase_request', 'purchase_comment', 'purchase_delivery', 'purchase_luxury');
        foreach ($field_list as $field_name) {
            $$field_name = trim(init_string($field_name));
        }
        
        if ($this->client) {
            $field_list = array(
                'purchase_phone', 'purchase_address');
            foreach ($field_list as $field_name)
                if (is_empty($$field_name))
                    $error[$field_name] = 'Поле обязательно для заполнения';
        } else {
            $field_list = array(
                'client_title', 'client_email', 'purchase_phone', 'purchase_address');
            foreach ($field_list as $field_name)
                if (is_empty($$field_name))
                    $error[$field_name] = 'Поле обязательно для заполнения';
            
            if (!isset($error['client_email']) && !valid::factory('email')->check($client_email)) {
                $error['client_email'] = 'Поле заполнено некорректно';
            }
            if (!isset($error['client_email'])) {
                $this->client = model::factory('client')->get_by_email($client_email);
            }
        }
        
        $delivery_selected = false;
        foreach ($this->delivery_list as $delivery) {
            if ($delivery->get_id() == $purchase_delivery) {
                $delivery_selected = true; break;
            }
        }
        if (!isset($error['purchase_delivery']) && !$delivery_selected) {
            $error['purchase_delivery'] = 'Не выбран способ доставки';
        }
        
        if (count($error)) {
            return $error;
        }
        
        if (!$this->client) {
            $client_password = generate_key(8);
            
            // Добавление пользователя
            $this->client = model::factory('client')
                ->set_client_title($client_title)
                ->set_client_email($client_email)
                ->set_client_password(md5($client_password))
                ->save();
            
            $from_email = get_preference('from_email');
            $from_name = get_preference('from_name');
            $subject = get_preference('registration_subject');
            
            $message = module_text::get_by_tag('registration_letter');
            $message = str_replace('{client_email}', $client_email, $message);
            $message = str_replace('{client_password}', $client_password, $message);
            
            sendmail::send($client_email, $from_email, $from_name, $subject, $message);
        }
        
        $delivery = model::factory('delivery')->get($purchase_delivery);
        $purchase_sum = $this->cart->get_sum() + $delivery->get_delivery_price() +
            ($purchase_luxury ? get_preference('luxury_price') : 0);
        
        // Сохранение заказа
        $purchase = model::factory('purchase')
            ->set_purchase_client($this->client->get_id())
            ->set_purchase_phone($purchase_phone)
            ->set_purchase_address($purchase_address)
            ->set_purchase_request($purchase_request)
            ->set_purchase_comment($purchase_comment)
            ->set_purchase_delivery($delivery->get_id())
            ->set_purchase_luxury($purchase_luxury ? 1 : 0)
            ->set_purchase_date(date::now())
            ->set_purchase_sum($purchase_sum)
            ->set_purchase_status(1)
            ->save();
        
        // Сохранение позиций заказа
        foreach($this->cart->get() as $item) {
            model::factory('purchase_item')
                ->set_item_purchase($purchase->get_id())
                ->set_item_product($item->id)
                ->save();
        }
        
        // Отправка сообщения
        $from_email = get_preference('from_email');
        $from_name = get_preference('from_name');
        
        $client_email = $this->client->get_client_email();
        $client_subject = get_preference('client_subject');
        
        $manager_email = get_preference('manager_email');
        $manager_subject = get_preference('manager_subject');
        
        $purchase_view = new view();
        $purchase_view->assign('purchase', $purchase);
        $purchase_view->assign('client', $this->client);
        
        $client_message = $purchase_view->fetch('module/purchase/client_message');
        $manager_message = $purchase_view->fetch('module/purchase/manager_message');
        
        sendmail::send($client_email, $from_email, $from_name, $client_subject, $client_message);
        sendmail::send($manager_email, $from_email, $from_name, $manager_subject, $manager_message);
        
        session::flash('purchase_complete', true);
        
        $this->cart->clear();
        
        redirect_back();
    }
    
    // Отключаем кеширование
    protected function get_cache_key()
    {
        return false;
    }
}