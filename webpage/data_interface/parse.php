<?php

class Params implements ArrayAccess {
    const T_INTEGER = 0;
    const T_FLOAT   = 1;
    const T_STRING  = 2;
    const T_DATE    = 3;
    const T_END     = 4;

    private $params = array();
    private $parsed = false;

    public function __construct() {
    }

    public function add_param(string $name, int $type, bool $required = false)
    {
        if ($this->parsed || isset($params[$name]) || $type >= self::T_END) {
            return;
        }

        $this->params[$name] = array(
            'type' => $type,
            'required' => $required,
            'value' => null
        );
    }

    /** allows the setting of variables */
    public function offsetSet($offset, $value)
    {
        if ($this->parsed || is_null($offset) || isset($this->params[$offset]) || !is_array($value) || !isset($value['type'])) {
            return;
        }

        $required = false;
        if (isset($value['required'])) {
            $required = $value['required'];
        }

        $this->add_param($offset, $value['type'], $required);
    }

    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset)
    {
        // not implemented
    }

    // get the parsed value
    public function offsetGet($offset)
    {
        if ($this->parsed && $this->offsetExists($offset)) {
            return $this->params[$offset]['value'];
        }

        return null;
    }

    public function parse()
    {
        if ($this->parsed) {
            return;
        }

        // say that we've already parsed
        $this->parsed = true;

        foreach ($this->params as $name => &$param) {
            $value = null;

            if (isset($_GET[$name])) {
                $value = $_GET[$name];
            } else if (isset($_POST[$name])) {
                $value = $_POST[$name];
            }

            if (is_null($value)) {
                if ($param['required']) {
                    throw new Exception("$name is required but not given.");
                }

                continue;
            }

            $type  = $param['type'];
            $value = htmlspecialchars($value);
            if ($type == self::T_INTEGER) {
                $value = intval($value);
            } else if ($type == self::T_FLOAT) {
                $value = floatval($value);
            } else if ($type == self::T_DATE) {
                // expects timestamp
                $value = strtotime($value);
            }

            // store the value
            $param['value'] = $value;
        } 
    }
}

?>
