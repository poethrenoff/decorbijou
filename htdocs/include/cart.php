<?php
class cart
{
    const SESSION_VAR = '__cart__';
    
    private $items = array();
    
    private static $instance = null;
    
    public static final function factory()
    {
        if (self::$instance == null) {
            self::$instance = new cart();
       }
        return self::$instance;
    }
    
    private function __construct()
    {
        if (!isset($_SESSION[self::SESSION_VAR]) || !is_array($_SESSION[self::SESSION_VAR])) {
            $_SESSION[self::SESSION_VAR] = array();
        }
        $this->items = $_SESSION[self::SESSION_VAR];
    }
    
    public function __destruct()
    {
        $_SESSION[self::SESSION_VAR] = $this->items;
    }
    
    public function get()
    {
        return $this->items;
    }
    
    public function add($id, $price)
    {
        if (!isset($this->items[$id])) {
            $item = new StdClass();
            
            $item->id = $id;
            $item->price = $price;
            
            $this->items[$id] = $item;
        }
    }
    
    public function delete($id)
    {
        unset($this->items[$id]);
    }
    
    public function clear()
    {
        $this->items = array();
    }
    
    public function get_quantity()
    {
        return count($this->items);
    }
    
    public function get_sum()
    {
        $cart_sum = 0;
        foreach ($this->items as $item) {
            $cart_sum += $item->price;
        }
        return $cart_sum;
    }
}
