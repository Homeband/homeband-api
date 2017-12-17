<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 17/12/17
 * Time: 19:03
 */

class MY_Object implements ArrayAccess
{
    public function __construct($attr = array())
    {
        if(!empty($attr)){
            foreach($attr as $key => $value){
                if(property_exists($this, $key)){
                    $this->$key = $value;
                }
            }
        }
    }

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        if(property_exists($this, $offset))
            return $this->$offset;
        else
            return null;
    }

    public function offsetSet($offset, $value)
    {
        if(property_exists($this, $offset))
            $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}