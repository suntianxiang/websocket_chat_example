<?php

namespace App;

class RandomNamePool
{
    protected $names = [
        '胡适', '李大钊', '鲁迅', '陈独秀', '列宁',
        '毛泽东', '蒋介石', '林彪', '周恩来', '朱德',
        '谭嗣同', '康广仁', '林旭', '杨深秀', '杨锐', '刘光第',
        '董存瑞', '黄继光', '周瑾', '小萝卜头', '刘胡兰', '杨靖宇',
        '迪丽热巴', '郭碧婷', '张馨予', '杨幂', 'angelababy',
        '李易峰', '陈伟霆', '鹿晗', '彭于晏', '吴亦凡', '张云龙',
    ];

    protected $length;

    protected static $instance;

    public function __construct()
    {
        $this->length = count($this->names);
    }

    /**
     * get a name
     *
     * @return string
     */
    public function get()
    {
        $offset = mt_rand(0, $this->length-1);
        $name = array_splice($this->names, $offset, 1)[0];
        $this->names = array_values($this->names);
        $this->length = count($this->names);

        return $name;
    }

    /**
     * recycle a name
     *
     * @param string $name
     * @return void
     */
    public function recycle($name)
    {
        array_push($this->names, $name);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
