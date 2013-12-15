<?php
class module_client extends module
{
    /**
     * Текущий пользователь
     */
    protected $client = null;
    
    public function action_index()
    {
        if (self::is_auth()) {
            redirect_to(array('controller' => 'client/purchase'));
        } else {
            $error = !empty($_POST) ? $this->auth_from_request() : array();
            
            $this->view->assign('error', $error);
            $this->content = $this->view->fetch('module/client/form');
        }
    }
    
    /**
     * Регистрация
     */
    protected function action_registration()
    {
        if (session::flash('registration_complete')) {
            $this->content = $this->view->fetch('module/client/registration/complete');
        } else {
            $error = !empty($_POST) ? $this->add_client() : array();
            
            $this->view->assign('error', $error);
            $this->content = $this->view->fetch('module/client/registration/form');
        }
    }
    
    /**
     * Восстановление пароля
     */
    protected function action_recovery()
    {
        if (session::flash('recovery_complete')) {
            $this->content = $this->view->fetch('module/client/recovery/complete');
        } else {
            $error = !empty($_POST) ? $this->recovery_password() : array();
            
            $this->view->assign('error', $error);
            $this->content = $this->view->fetch('module/client/recovery/form');
        }
    }
    
    /**
     * Личные данные
     */
    protected function action_profile()
    {
        if (!self::is_auth()) {
            redirect_to(array('controller' => 'client'));
        } elseif (session::flash('profile_complete')) {
            $this->content = $this->view->fetch('module/client/profile/complete');
        } else {
            $this->client = self::get_info();
            $error = !empty($_POST) ? $this->save_client() : array();
            
            $this->view->assign('error', $error);
            $this->view->assign('client', $this->client);
            $this -> content = $this -> view -> fetch( 'module/client/profile/form' );
        }
    }
    
    /**
     * Мои заказы
     */
    protected function action_purchase()
    {
        if (!self::is_auth()) {
            redirect_to(array('controller' => 'client'));
        } else {
            $this->client = self::get_info();
            $this->view->assign('client', $this->client);
            $this -> content = $this -> view -> fetch( 'module/client/purchase/index' );
        }
    }
    
    /**
     * Выход с сайта
     */
    public function action_logout()
    {
        if (self::is_auth()) {
            unset($_SESSION['client']);
            self::clear_client_cookie();
        }
        
        redirect_back();
    }
    
    /**
     * Панель пользователя
     */
    public function action_info()
    {
        $this->view->assign('client', self::get_info());
        $this->content = $this->view->fetch('module/client/info');
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * Добавление нового пользователя
     */
    protected function add_client()
    {
        $error = array();
        
        $field_list = array(
            'client_title', 'client_email', 'client_password', 'client_password_confirm');
        foreach ($field_list as $field_name)
            if (is_empty($$field_name = trim(init_string($field_name))))
                $error[$field_name] = 'Поле обязательно для заполнения';
         
        if (!isset($error['client_email']) && !valid::factory('email')->check($client_email)) {
            $error['client_email'] = 'Поле заполнено некорректно';
        }
        if (!isset($error['client_email']) && model::factory('client')->get_by_email($client_email)) {
            $error['client_email'] = 'Пользователь с таким электронным адресом уже зарегистрирован';
        }
        if (!isset($error['client_password']) && !isset($error['client_password_confirm']) &&
                strcmp($client_password, $client_password_confirm)) {
            $error['client_password_confirm'] = 'Пароли не совпадают';
        }
        
        if (count($error)) {
            return $error;
        }
        
        // Добавление пользователя
        $client = model::factory('client')
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
        
        session::flash('registration_complete', true);
        
        redirect_back();
    }
    
    /**
     * Изменение личных данных
     */
    protected function save_client()
    {
        $error = array();
        
        $field_list = array(
            'client_title', 'client_email');
        foreach ($field_list as $field_name)
            if (is_empty($$field_name = trim(init_string($field_name))))
                $error[$field_name] = 'Поле обязательно для заполнения';
         
        if (!isset($error['client_email']) && !valid::factory('email')->check($client_email)) {
            $error['client_email'] = 'Поле заполнено некорректно';
        }
        if (!isset($error['client_email']) && ($client = model::factory('client')->get_by_email($client_email)) && ($client->get_id() != $this->client->get_id())) {
            $error['client_email'] = 'Пользователь с таким электронным адресом уже зарегистрирован';
        }
        
        if (count($error)) {
            return $error;
        }
        
        $field_list = array(
            'client_password_old', 'client_password', 'client_password_confirm' );
        $change_password = false;
        foreach ( $field_list as $field_name )
            $change_password |= !is_empty($$field_name = trim(init_string($field_name)));
        
        if ($change_password) {
            foreach ($field_list as $field_name)
                if (is_empty($$field_name))
                    $error[$field_name] = 'Поле обязательно для заполнения';
            
            if (!isset($error['client_password_old']) && strcmp(md5($client_password_old), $this->client->get_client_password())) {
                $error['client_password_old'] = 'Неверное значение старого пароля';
            }
            if ( !isset($error['client_password']) && !isset($error['client_password_confirm']) &&
                    strcmp( $client_password, $client_password_confirm)) {
                $error['client_password_confirm'] = 'Пароли не совпадают';
            }
        }
        
        if (count($error)) {
            return $error;
        }
        
        // Сохранение профиля
        $client
            ->set_client_title($client_title)
            ->set_client_email($client_email);
        if (!is_empty($client_password)) {
            $client->set_client_password(md5($client_password));
        }
        $client->save();
        
        $_SESSION['client'] = $client;
        
        if (init_cookie('client')) {
            self::set_client_cookie($client);
        }
        
        session::flash('profile_complete', true);
        
        redirect_back();
    }
    
    /**
     * Отправка нового пароля
     */
    protected function recovery_password()
    {
        $error = array();
        
        $field_list = array('client_email');
        foreach ($field_list as $field_name)
            if (is_empty($$field_name = trim(init_string($field_name))))
                $error[$field_name] = 'Поле обязательно для заполнения';
        
        if (!isset($error['client_email']) && !valid::factory('email')->check($client_email)) {
            $error['client_email'] = 'Поле заполнено некорректно';
        }
        
        if (!isset($error['client_email']) && !($client = model::factory('client')->get_by_email($client_email))) {
            $error['client_email'] = 'Пользователь с таким электронным адресом не зарегистрирован';
        }
        
        if (count($error)) {
            return $error;
        }
        
        $client_password = generate_key(8);
        $client->set_client_password(md5($client_password))->save();
        
        $from_email = get_preference('from_email');
        $from_name = get_preference('from_name');
        $subject = get_preference('recovery_subject');
        
        $message = module_text::get_by_tag('recovery_letter');
        $message = str_replace('{client_password}', $client_password, $message);
        
        sendmail::send($client_email, $from_email, $from_name, $subject, $message);
        
        session::flash('recovery_complete', true);
        
        redirect_back();
    }
    
    /**
     * Аутентификация из формы
     */
    public static function auth_from_request()
    {
        $error = array();
        
        $field_list = array(
            'client_email', 'client_password');
        foreach ($field_list as $field_name)
            if (is_empty($$field_name = trim(init_string($field_name))))
                $error[$field_name] = 'Поле обязательно для заполнения';
        
        if (!isset($error['client_email']) && !valid::factory('email')->check($client_email)) {
            $error['client_email'] = 'Поле заполнено некорректно';
        }
        
        if (count($error)) {
            return $error;
        }
        
        try {
            $client = self::auth($client_email, md5($client_password));
        } catch (Exception $e) {
            return array(
                'client_email' => $e->getMessage(),
            );
        }
        
        if (init_string('client_remember')) {
            self::set_client_cookie($client);
        }
        
        $_SESSION['client'] = $client;
        
        redirect_back();
    }
    
    /**
     * Аутентификация по кукам
     */
    public static function auth_from_cookie()
    {
        @list($client_email, $client_password) = cookie::get_data('client');
        
        try {
            $client = self::auth($client_email, $client_password);
        } catch (Exception $e) {
            return false;
        }
        
        $_SESSION['client'] = $client;
        
        return true;
    }
    
    /**
     * Авторизован ли пользователь
     */
    public static function is_auth()
    {
        if (isset($_SESSION['client'])) {
            return true;
        }
        return self::auth_from_cookie();
    }
    
    /**
     * Возвращает информацию о текущем пользователе
     */
    public static function get_info()
    {
        if (self::is_auth()) {
            return $_SESSION['client'];
        } else {
            return false;
        }
    }
    
    /**
     * Аутентификация пользователя по логину и паролю
     */
    public static function auth($client_email, $client_password)
    {
        $client = model::factory('client')->get_by_email($client_email);
        
        if (!$client) {
            throw new Exception('Пользователь с таким email не зарегистрирован на сайте');
        }
        
        if (strcmp($client->get_client_password(), $client_password)) {
            throw new Exception('Неверный пароль');
        }
        
        return $client;
    }
    
    /**
     * Установка пользовательских кук
     */
    public static function set_client_cookie($client)
    {
        cookie::set_data(
            'client', array(
                $client->get_client_email(),
                $client->get_client_password(),
            ), time() + 60 * 60 * 24 * 7, '/'
        );
    }
    
    /**
     * Очистка пользовательских кук
     */
    public static function clear_client_cookie()
    {
        cookie::set_data(
            'client', null, time() - 60 * 60 * 24, '/'
        );
    }
    
    /**
     * Отключаем кеширование
     */
    protected function get_cache_key()
    {
        return false;
    }
}