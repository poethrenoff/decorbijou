<?php
class module_order extends module
{
    protected function action_index()
    {
        $delivery_list = model::factory('delivery')->get_list(array('delivery_active' => 1), array('delivery_price' => 'asc'));
        $this->view->assign('delivery_list', $delivery_list);
        
        $cart = cart::factory();
        
        if (!empty($_POST) && $cart->get_quantity()) {
            $error = array();
            
            if (!isset($_POST['name']) || is_empty($_POST['name'])) {
                $error['name'] = 'Не заполнено обязательное поле';
            }
            if (!isset($_POST['phone']) || is_empty($_POST['phone'])) {
                $error['phone'] = 'Не заполнено обязательное поле';
            }
            if (!isset($_POST['email']) || is_empty($_POST['email'])) {
                $error['email'] = 'Не заполнено обязательное поле';
            }
            if (!isset($error['email']) && !valid::factory('email')->check($_POST['email'])) {
                $error['email'] = 'Поле заполнено некорректно';
            }
            
            if (!$error) {
                // Сохранение заказа
                $order = model::factory('orders')
                    ->set_order_client_name($_POST['name'])
                    ->set_order_client_phone($_POST['phone'])
                    ->set_order_client_email($_POST['email'])
                    ->set_order_client_address($_POST['address'])
                    ->set_order_client_comment($_POST['comment'])
                    ->set_order_date(date::now())
                    ->set_order_sum($cart->get_sum())
                    ->save();
                
                foreach($cart->get() as $item) {
                    $product_item = model::factory('product')->get($item->id);
                    model::factory('order_item')
                        ->set_item_order($order->get_id())
                        ->set_item_title($product_item->get_product_title())
                        ->set_item_price($item->price)
                        ->set_item_quantity($item->quantity)
                        ->save();
                }
                
                // Отправка сообщения
                $from_email = get_preference('from_email');
                $from_name = get_preference('from_name');
                
                $client_email = $_POST['email'];
                $client_subject = get_preference('client_subject');
                
                $manager_email = get_preference('manager_email');
                $manager_subject = get_preference('manager_subject');
                
                $order_view = new view();
                $order_view->assign($cart);
                
                $client_message = $order_view->fetch('module/order/client_message');
                $manager_message = $order_view->fetch('module/order/manager_message');
                
                sendmail::send($client_email, $from_email, $from_name, $client_subject, $client_message);
                sendmail::send($manager_email, $from_email, $from_name, $manager_subject, $manager_message);
                
                session::flash('success', true);
                
                $cart->clear();
                
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $this->view->assign('error', $error);
        }
        
        $this->view->assign('cart', $cart);
        $this->content = $this->view->fetch('module/order/form');
    }
    
    // Отключаем кеширование
    protected function get_cache_key()
    {
        return false;
    }
}