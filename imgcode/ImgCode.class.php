<?php

/**
 * 图片验证码
 * 
 * @author vtejuf@126.com
 * 
 * 保留以上信息免费使用
 */
abstract class ImgCode {

    protected $img;
    protected $seeds;
    protected $imgset;
    protected $fontColor;
    static public $code;

    /**
     * 对象接口
     */
    static function showImg($who, $config) {
        $C = $config['C'];
        $imgSet = $config['S'];
        self::$code=$C['codestr'];
        
        switch ($who) {
            case 'one':
                return new ImgOne($C, $imgSet);
                break;
            case 'two':
                return new ImgTwo($C, $imgSet);
                break;
        }
    }

    /**
     * 画背景
     */
    protected function makebg() {
        header("content-type:image/png");
        $img = ImageCreate($this->S['width'], $this->S['height']);
        $bgcolor = ImageColorAllocate($img, 0, 0, 0);
        $bgcolortrans = ImageColorTransparent($img, $bgcolor);
        $this->fontColor = imagecolorallocate($img, $this->S['fontColor'][0], $this->S['fontColor'][1], $this->S['fontColor'][2]);

        $this->img = $img;
    }

    /**
     * 写字
     */
    protected function fillNum($raws, $point, $fontSize) {
        foreach ($raws as $k => $v) {
            $font_padding = $fontSize * $k;
            foreach ($v as $fill) {
                imagerectangle(
                        $this->img, ($point[$fill][0] + $font_padding), $point[$fill][1], ($point[$fill][2] + $font_padding), $point[$fill][3], $this->fontColor
                );
            }
        }
    }

    /**
     * 输出图片，释放资源
     */
    protected function showAndDestroy() {
        ImagePng($this->img);
        ImageDestroy($this->img);
    }

}

class ImgOne extends ImgCode {

    private $C;

    function __construct($config, $imgSet) {
        $this->C = $config;
        $this->S = $imgSet;
        return $this->show();
    }

    private function show() {
        $fillRaws = $this->process($this->C['raws']);
        $this->makebg($this->S);
        $this->fillNum($fillRaws, $this->C['point'], $this->S['fontSize']);
        $this->showAndDestroy();
    }

    private function process($raws) {
        foreach ($raws as $v) {
            $arr[] = $v[1];
        }
        return $arr;
    }

}

class ImgTwo extends ImgCode {

    private $C;

    function __construct($config, $imgSet) {
        $this->C = $config; //对象配置项
        $this->S = $imgSet; //图片配置项
        return $this->show();
    }

    /**
     * 保存图片
     */
    private function show() {
        $fillRaws = $this->process($this->C['raws']);
        $this->makebg($this->S);
        $this->fillNum($fillRaws, $this->C['point'], $this->S['fontSize']);
        $this->showAndDestroy();
    }

    /**
     * 从匹配好的种子树中找出自己的部分
     */
    private function process($raws) {
        foreach ($raws as $v) {
            $arr[] = $v[2];
        }
        return $arr;
    }

}

final class Config {

    private $config;
    private $imgset;

    function __construct($set) {
        $this->imgset = $set;
        $this->config['seeds'] = $this->makeSeed($set['fontLen']);
        $this->config['codestr'] = $this->codeStr($this->config['seeds']);
        $this->config['seedTree'] = $this->seedTree();
        $this->config['point'] = $this->point($set['fontSize']);
        $this->config['raws'] = $this->allot();
    }

    function getConfig() {
        return $this->config;
    }

    function getImgSet() {
        return $this->imgset;
    }

    /**
     * 生成种子 若干数字
     */
    private function makeSeed($len) {
        for ($i = 0; $i < $len; $i++) {
            $seeds[] = rand(0, 9);
        }
        return $seeds;
    }

    /**
     * 生成验证字符串，seeds连接成字符串
     */
    private function codeStr($seeds) {
        $seeds = implode(',',$seeds);
        return str_replace(',', '', $seeds);
    }

    /**
     * 种子树，把数字按笔画拆分
     */
    private function seedTree() {
        return array(
            1 => array(24, 46, 46, 46),
            2 => array(12, 24, 34, 35, 56),
            3 => array(12, 24, 34, 46, 56),
            4 => array(13, 34, 24, 46, 13),
            5 => array(12, 13, 34, 46, 56),
            6 => array(12, 13, 34, 35, 46, 56),
            7 => array(12, 24, 46, 12, 46),
            8 => array(12, 13, 24, 34, 35, 46, 56),
            9 => array(12, 13, 24, 34, 46),
            0 => array(12, 13, 24, 35, 46, 56)
        );
    }

    /**
     * 写入文字的坐标
     */
    private function point($size) {
        return array(
            12 => array(1, 1, $size / 4, 1+1),
            13 => array(1, 1, 1+1, $size / 2),
            24 => array($size / 4, 1, $size / 4+1, $size / 2),
            34 => array(1, $size / 2, $size / 4, $size / 2+1),
            35 => array(1, $size / 2, 1+1, $size),
            46 => array($size / 4, $size / 2, $size / 4+1, $size),
            56 => array(1, $size, $size / 4, $size+1)
        );
    }

    /**
     * 匹配种子和种子树
     */
    private function allot() {
        $arr = [];
        foreach ($this->config['seeds'] as $seed) {
            $arr[] = $this->Sorter($this->config['seedTree'][$seed]);
        }
        return $arr;
    }

    /**
     * 给两个子对象平均分配种子树
     */
    private function Sorter($arr) {
        $ave = ceil(count($arr) / 2);
        $len1 = 0;
        $len2 = 0;
        foreach ($arr as $k => $v) {
            $rand = rand(1, 2);
            if ($len1 < $ave and $rand == 1) {
                $out[1][] = $v;
                $len1++;
                continue;
            } else if ($len2 < $ave) {
                $out[2][] = $v;
                $len2++;
            } else {
                $out[1][] = $v;
                $len1++;
            }
        }
        return $out;
    }

}

////调用
//$set = array(
//    'width' => 160,
//    'height' => 35,
//    'fontColor' => array(0, 0, 0),
//    'fontSize' => 30, //数字大小
//    'fontLen' => 6, //数字个数
//    'outPutDir' => BASE_PATH . PLUGIN_PATH . 'imgcode/imgcode/'//保存目录
//);
//$config = new Config($set);
//ImgCode::showImg('one', $config);
//ImgCode::showImg('two', $config);