<?php
namespace fay\core;

class Input{
    private static $_instance;
    private $_get = array();
    private $_post = array();
    
    private function __construct(){}
    
    private function __clone(){}
    
    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self();
            self::$_instance->init();
        }
        return self::$_instance;
    }
    
    private function init(){
        $this->_get = $_GET;
        $this->_post = $_POST;
    }
    
    /**
     * 获取get传递的参数
     * @param string $key 键值
     * @param string $filter 适配器（其实就函数名，比如trim|intval），多个适配器以竖线分割
     * @param string $default 默认值
     * @return mixed
     */
    public function get($key = null, $filter = '', $default = null){
        if($filter){
            $filters = explode('|', $filter);
        }else{
            $filters = array();
        }
        if($key === null){
            return $this->filterR($filters, $this->_get);
        }
        if(is_array($key)){
            $return = array();
            foreach($key as $k){
                $return[$k] = isset($this->_get[$k]) ? $this->filterR($filters, $this->_get[$k]) : null;
            }
            return $return;
        }else if(isset($this->_get[$key])){
            return $this->filterR($filters, $this->_get[$key]);
        }else{
            return $default;
        }
    }
    
    /**
     * 获取post传递的参数
     * @param string $key 键值
     * @param string $filter 适配器（其实就函数名，比如intval），多个适配器以竖线分割
     * @param string $default 默认值
     * @return mixed
     */
    public function post($key = null, $filter = '', $default = null){
        if($filter){
            $filters = explode('|', $filter);
        }else{
            $filters = array();
        }
        if($key === null){
            return $this->filterR($filters, $this->_post);
        }
        if(is_array($key)){
            $return = array();
            foreach($key as $k){
                $return[$k] = isset($this->_post[$k]) ? $this->filterR($filters, $this->_post[$k]) : null;
            }
            return $return;
        }else if(isset($this->_post[$key])){
            return $this->filterR($filters, $this->_post[$key]);
        }else{
            return $default;
        }
    }
    
    /**
     * 就是合并了下post和get
     * @param string $key
     * @param string $filter
     * @param string $default
     * @return mixed
     */
    public function request($key = null, $filter = '', $default = null){
        if($key){
            if(($temp = $this->post($key, $filter)) !== null){
                return $temp;
            }else if(($temp = $this->get($key, $filter)) !== null){
                return $temp;
            }else{
                return $default;
            }
        }else{
            $temp_get = $this->get(null, $filter);
            $temp_post = $this->post(null, $filter);
            return array_merge($temp_get, $temp_post);
        }
    }
    
    /**
     * 设置get参数
     * @param string $key
     * @param mixed $value
     * @param bool $rewrite 若为false，则会判断该key是否已存在，若存在，则不覆盖
     */
    public function setGet($key, $value, $rewrite = true){
        if($rewrite){
            $this->_get[$key] = $value;
        }else if(!isset($this->_get[$key])){
            $this->_get[$key] = $value;
        }
    }
    
    public function setPost($key, $value){
        $this->_post[$key] = $value;
    }
    
    public function isAjaxRequest(){
        return Request::isAjax();
    }
    
    /**
     * 用指定的filter，递归的过滤data
     * @param array|string $filters 可以是数组，也可以是竖线分隔的字符串
     * @param array|string $data
     * @param string $fields 可以是数组，也可以是逗号分隔的字符串，但不可以有多余的空格
     * @param bool $skip_on_empty 若为true，则当值为空时不调用过滤器进行过滤，默认为true
     * @return mixed
     */
    public function filterR($filters, $data, $fields = null, $skip_on_empty = true){
        if($skip_on_empty && ($data === '' || $data === null || $data === array())){
            return $data;
        }
        if(is_string($filters)){
            $filters = explode('|', $filters);
        }
        if($filters){
            if($fields != null){
                if(is_string($fields)){
                    $fields = explode(',', $fields);
                }
            }
            if(is_array($data) || is_object($data)){
                $return = array();
                foreach($data as $key => $d){
                    if(is_array($d) || is_object($d)){
                        $return[$key] = $this->filterR($filters, $d, $fields);
                    }else{
                        $temp = $d;
                        if($fields == null || in_array($key, $fields)){
                            foreach($filters as $f){
                                $temp = eval('return '.$f.'($temp);');
                            }
                        }
                        $return[$key] = $temp;
                    }
                }
                return $return;
            }else{
                $temp = $data;
                foreach($filters as $f){
                    $temp = eval('return '.$f.'($temp);');
                }
                return $temp;
            }
        }else{
            return $data;
        }
    }
}