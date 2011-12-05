<?php
/**
 *
 *
 */
class Model
{
    public $id;
    public $validation;
    public $validation_errors;

    /** 
     * モデルを生成する
     *
     * 生成時にメンバを設定できる。
     *
     */
    public function __construct(array $data = array())
    {   
        $this->set($data);
    }   

    /** 
     * メンバの値をセットする
     *
     * 複数のメンバの値を一度にセットできる。
     *
     */
    public function set(array $data)
    {   
        foreach ($data as $k => $v) {
            $this->$k = $v; 
        }   
    }   
}
