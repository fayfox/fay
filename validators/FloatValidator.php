<?php
namespace fay\validators;

use fay\core\Validator;

/**
 * 验证是否为小数
 * 支持MySQL的字段类型的思路验证
 * - 例如：length为5，decimal为2，则-999.99~999.99之间的数值都是合法的。
 * 同时支持最大值最小值验证方式
 * - 例如：max为5.12；min为3.14之间的小数
 * 两种方式可结合使用
 */
class FloatValidator extends Validator{
    /**
     * 若为true，允许传入数组，每个数组项都必须是小数
     */
    public $allow_array = true;
    
    /**
     * 长度
     */
    public $length;
    
    /**
     * 小数位
     */
    public $decimal = 2;
    
    /**
     * 最小值（length+decimal验证通过后才会验证该值）
     * @var float
     */
    public $min;
    
    /**
     * 最大值（length+decimal验证通过后才会验证该值）
     * @var float
     */
    public $max;
    
    public $too_long = '{$attribute}必须是{$min}到{$max}的数字';
    
    public $decimal_too_long = '{$attribute}小数位不能多于{$decimal}位';
    
    public $too_big = '{$attribute}必须是不大于{$max}的数字';
    
    public $too_small = '{$attribute}必须是不小于{$min}的数字';
    
    public $message = '{$attribute}必须是数字';
    
    public function validate($value){
        if($this->allow_array && is_array($value)){
            //如果允许传入数组且传入的是数组
            foreach($value as $v){
                if($this->skip_on_empty && ($v === null || $v === '' || $v === array())){
                    //跳过为空的值
                    continue;
                }
                $check = $this->checkItem($v);
                if($check !== true){
                    return $this->addError($check[0], $check[1], $check[2]);
                }
            }
                
            return true;
        }
        
        //只允许纯字符串
        $check = $this->checkItem($value);
        if($check !== true){
            return $this->addError($check[0], $check[1], $check[2]);
        }
            
        return true;
    }
    
    public function checkItem($value){
        if(!preg_match('/^-?\d+(\.\d+)?$/', $value)){
            return array($this->message, $this->code, array());
        }
        
        $point_pos = strpos($value, '.');
        if($point_pos && strlen($value) - $point_pos - 1 > $this->decimal){
            return array($this->decimal_too_long, $this->code, array(
                'decimal'=>$this->decimal,
            ));
        }
        
        
        if($this->length){
            $max = '1'.str_repeat('0', $this->length - $this->decimal);
            if($value > $max || $value < -$max){
                return array($this->too_long, $this->code, array(
                    'max'=>($this->max !== null && $this->max < $max) ? $this->max : $max,
                    'min'=>($this->min !== null && $this->min > -$max) ? $this->min : -$max,
                    'decimal'=>$this->decimal,
                ));
            }
        }
        
        if($this->max !== null && $value > $this->max){
            return array($this->too_big, $this->code, array(
                'max'=>$this->max,
            ));
        }
        
        if($this->min !== null && $value < $this->min){
            return array($this->too_small, $this->code, array(
                'min'=>$this->min,
            ));
        }
        
        return true;
    }
}