<?php

/**
 * Created by PhpStorm.
 * User: will
 * Date: 9/24/14
 * Time: 15:46
 */
class PinyinUtil
{
    public $useYac = true;
    public $table = null;
    public $yac = null;
    public $yacResult = null;

    static $multiToneChar = array('行', '阿', '藏', '的', '茄', '校', '壳', '绿', '模', '厦', '调', '长', '秘', '洞', '乐', '蚌');
    static $multiToneMap = [
        '行' => [
            'prefix' => ['银', '投', '拍卖', '招', '商', '央', '工', '建', '农', '中'],
            'suffix' => ['业'],
            'value' => 'hang'
        ],
        '阿' => [
            'prefix' => ['东'],
            'suffix' => ['胶'],
            'value' => 'e'
        ],
        '藏' => [
            'prefix' => ['西', '宝'],
            'value' => 'zang'
        ],
        '的' => [
            'prefix' => ['目', '标'],
            'suffix' => ['士', '确'],
            'value' => 'di'
        ],
        '茄' => [
            'prefix' => ['雪'],
            'value' => 'jia'
        ],
        '校' => [
            'prefix' => ['调'],
            'suffix' => ['正', '订', '对', '勘'],
            'value' => 'jiao'
        ],
        '壳' => [
            'prefix' => ['地'],
            'suffix' => ['牌'],
            'value' => 'qiao'
        ],
        '绿' => [
            'prefix' => ['鸭'],
            'suffix' => ['林'],
            'value' => 'lu'
        ],
        '模' => [
            'suffix' => ['具', '型', '样', '子', '板'],
            'value' => 'mu'
        ],
        '厦' => [
            'suffix' => ['门'],
            'value' => 'xia'
        ],
        '调' => [
            'prefix' => ['情', '格', '曲', '蓝', '征', '外'],
            'suffix' => ['查', '动', '研', '换'],
            'value' => 'diao'
        ],
        '长' => [
            'prefix' => ['局', '科', '处', '部', '厅', '工', '军', '旅', '师', '团', '营', '连', '排', '班', '组', '会', '生', '省', '市', '区', '县', '乡', '镇', '村', '警', '事', '馆', '所', '校', '院', '官', '士', '员', '学', '店', '厂'],
            'suffix' => ['官', '大'],
            'value' => 'zhang'
        ],
        '秘' => [
            'suffix' => ['鲁'],
            'value' => 'bi'
        ],
        '洞' => [
            'prefix' => ['洪'],
            'value' => 'tong'
        ],
        '乐' => [
            'prefix' => ['音', '器', '声', '弦', '管'],
            'suffix' => ['曲', '风', '器', '谱'],
            'value' => 'yue'
        ],
        '蚌' => [
            'suffix' => ['埠'],
            'value' => 'beng'
        ]
    ];

    public function __construct($flush = false) {

        $mc = new MemCacheBase();
        if ($this->useYac)
        {
            $this->yac = new Yac('PinYin_');
            if($flush)
            {
                $this->yac->flush();
            }

            YafDebug::log('yac info is '.json_encode($this->yac->info()));
            $this->yacResult = new Yac('PYR_');
            $tbl = $this->yac->get('flag');
        }
        else
        {
            $tbl = $mc->getByIdAndTag(8329, MemCacheBase::INFO_PINYIN_TABLE, 30 * 24 * 3600);
            $this->table = $tbl;
        }
        if (empty($tbl)) {
            $tbl = unserialize(file_get_contents(sprintf('%s/pinyin/pinyin_table.txt', LIB_PATH)));
            if ($this->useYac)
            {
                $this->yac->set('flag', time(), 0);
                $this->yac->set($tbl, 0);
//                $temp = $this->yac->info()['slots_used'];
//                if($this->yac->info()['slots_used'] <= count($tbl))
//                {
//                    $this->yac->flush();
//                    throw new Exception('Yac Cache Failed');
//                }
            }
            else
            {
                $mc->setByIdAndTag(8329, MemCacheBase::INFO_PINYIN_TABLE, $tbl);
                $this->table = $tbl;
            }
        }
    }

    /**
     * @param string $str
     * @param int $len
     * @return string
     */
    public function strToPinyin($str, $len=40)
    {
//        if(strlen($str) > 40){
//            $str = substr($str, 0 ,40);
//        }
//        if($this->useYac) {
//            $cacheResult = $this->yacResult->get($str);
//            if ($cacheResult) {
//                return $cacheResult;
//            }
//        }
        $str_arr = $this->utf8_str_split($str);
        if ($str_arr == false)
            return $str;
        $result = "";
        $index = 0;
        foreach ($str_arr as $chr) {
            if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $chr)) {
                $result .= $chr;
            } else {
                $chrPinYin = $this->getChrPinyin($chr);
                if ($this->isArrayItemDiff($chrPinYin)) {
                    if (in_array($chr, self::$multiToneChar)) {
                        $map = self::$multiToneMap[$chr];
                        if (isset($map['prefix']) && $index > 0) {
                            $pre = $str_arr[$index - 1];
                            if (in_array($pre, $map['prefix'])) {
                                $result .= $map['value'];
                                continue;
                            }
                        }
                        if (isset($map['suffix']) && $index < count($str_arr) - 1) {
                            $suff = $str_arr[$index + 1];
                            if (in_array($suff, $map['suffix'])) {
                                $result .= $map['value'];
                                continue;
                            }
                        }

                        $result .= $chrPinYin[0];
                    } else {
                        $result .= $chrPinYin[0];
                    }
                } else {
                    $result .= $chrPinYin[0];
                }
            }
            if (strlen($result) > $len)
                break;
            $index++;
        }

//        if($this->useYac){
//            $this->yacResult->set($str, $result, MemCacheBase::EXPIRE_MONTH_TERM);
//        }
        return $result;
    }

    function isArrayItemDiff($arr)
    {
//        if (!is_array($arr) || count($arr) == 0)
//            return false;
//        if (count($arr) == 1)
//            return false;
        if(!isset($arr[1]))
            return false;
        $val = $arr[0];
        foreach ($arr as $item) {
            if ($item != $val)
                return true;
        }
        return false;
    }

    // 作用类似于 str_split ，兼容UTF-8字符
    function utf8_str_split($str, $split_len = 1)
    {
        if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1) {
            return FALSE;
        }
        $len = mb_strlen($str, 'UTF-8');
        if ($len <= $split_len) {
            return array($str);
        }
        preg_match_all('/.{' . $split_len . '}|[^\x00]{1,' . $split_len . '}$/us', $str, $ar);
        return $ar[0];
    }

    private function getChrPinyin($chr) {
        if ($this->useYac) {
            return $this->yac->get($chr);
        } else {
            return $this->table[$chr];
        }
    }
}
