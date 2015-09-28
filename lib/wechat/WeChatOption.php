<?php
/**
 * Created by PhpStorm.
 * User: su
 * Date: 14-3-26
 * Time: 下午7:10
 */
class WeChatOption
{
    const SALARY_LIST = 1;
    const WORK_EXPERIENCE = 2;
    const GENDER = 3;
    const MARRIAGE = 4;
    const JOB_STATUS = 5;
    const DEGREE = 6;
    const SALARY_SEARCH = 7;
    const JOB_CATEGORY = 8;
    const WORK_EXPERIENCE_JOB = 9;
    const POCKET_TYPE = 10;
    const POCKET_STATUS_IMAGE_URL = 11;
    const POCKET_CASH_STATUS = 12;
    const RESUME_EXPERIENCE_TITLE = 13;

    static public function getSalaryList()
    {
        return array(
            array('id'=>1,'value'=>'2000元以下'),
            array('id'=>2,'value'=>'2000元-5000元'),
            array('id'=>3,'value'=>'5000元-10000元'),
            array('id'=>4,'value'=>'10000元-15000元'),
            array('id'=>5,'value'=>'15000元-25000元'),
            array('id'=>6,'value'=>'25000元-50000元'),
            array('id'=>7,'value'=>'50000元以上')
        );
    }

    static public function getSalarySearch()
    {
        return array(
            array('id'=>1,'value'=>array('salary_start'=>0,'salary_end'=>2000)),
            array('id'=>2,'value'=>array('salary_start'=>2000,'salary_end'=>5000)),
            array('id'=>3,'value'=>array('salary_start'=>5000,'salary_end'=>10000)),
            array('id'=>4,'value'=>array('salary_start'=>10000,'salary_end'=>15000)),
            array('id'=>5,'value'=>array('salary_start'=>15000,'salary_end'=>25000)),
            array('id'=>6,'value'=>array('salary_start'=>25000,'salary_end'=>50000)),
            array('id'=>7,'value'=>array('salary_start'=>50000))
        );
    }

    static public function getWorkExperience()
    {
        return array(
            array('id'=>1,'value'=>'应届毕业生'),
            array('id'=>2,'value'=>'1年以下'),
            array('id'=>3,'value'=>'1年'),
            array('id'=>4,'value'=>'2年'),
            array('id'=>5,'value'=>'3年'),
            array('id'=>6,'value'=>'4年'),
            array('id'=>7,'value'=>'5年'),
            array('id'=>8,'value'=>'6年'),
            array('id'=>9,'value'=>'7年'),
            array('id'=>10,'value'=>'8年'),
            array('id'=>11,'value'=>'9年'),
            array('id'=>12,'value'=>'10年'),
            array('id'=>13,'value'=>'10年以上')
        );
    }



    static public function getGender()
    {
        return array(
            array('id'=>1,'value'=>'男'),
            array('id'=>2,'value'=>'女')
        );
    }

    static public  function getMarriage()
    {
        return array(
            array('id'=>1,'value'=>'单身'),
            array('id'=>2,'value'=>'恋爱中'),
            array('id'=>3,'value'=>'已婚')
        );
    }

    static public function getJobStatus()
    {
        return array(
            array('id'=>1,'value'=>'在职，正在找工作'),
            array('id'=>2,'value'=>'在职，考虑好的就业机会'),
            array('id'=>3,'value'=>'在职，暂不考虑新的职业机会'),
            array('id'=>4,'value'=>'已离职，可快速到岗')
        );
    }

    static public function getDegree()
    {
        return array(
            array('id'=>1, 'value'=>'大专'),
            array('id'=>2, 'value'=>'本科'),
            array('id'=>3, 'value'=>'硕士'),
            array('id'=>4, 'value'=>'博士'),
            array('id'=>5, 'value'=>'博士后'),
        );
    }

    static public function getJobCategory()
    {
        return array(
            array('id'=>0, 'value'=>'全职'),
            array('id'=>1, 'value'=>'兼职'),
            array('id'=>2, 'value'=>'实习')
        );
    }

    static public function getWorkExperienceJob()
    {
        return array(
            array('id'=>1,'value'=>'应届'),
            array('id'=>2,'value'=>'1年以下'),
            array('id'=>3,'value'=>'1-2年'),
            array('id'=>4,'value'=>'3-5年'),
            array('id'=>5,'value'=>'6-10年'),
            array('id'=>6,'value'=>'10年以上')
        );
    }

    static public function getPacketType()
    {
        return array(
            array('id' => 0,'value' => '转发红包'),
            array('id' => 1,'value' => '推荐红包')
        );
    }

    static public function getPacketStatusImageUrl()
    {
        return array(
            array('id' => 0,'value' => '/images/red_n@2x.png'),
            array('id' => 1,'value' => '/images/red_u@2x.png')
        );
    }

    static public function getPacketCashStatus()
    {
        return array(
            array('id' => 0,'value' => '? 元'),
        );
    }

    static public function getResumeExperienceTitle()
    {
        return array(
            array('id' => 0,'value' => '无工作经验'),
            array('id' => 1,'value' => '1年工作经验'),
            array('id' => 2,'value' => '2年工作经验'),
            array('id' => 3,'value' => '3年工作经验'),
            array('id' => 4,'value' => '4年工作经验'),
            array('id' => 5,'value' => '5年工作经验'),
            array('id' => 6,'value' => '5年以上工作经验'),
        );
    }


    static public function getValueWithId($type,$id,$default = '待补充')
    {

        switch($type)
        {
            case self::SALARY_LIST:
                $array = self::getSalaryList();
                array_push($array,array('id'=>0,'value'=>$default));

                break;

            case self::WORK_EXPERIENCE:
                $array = self::getWorkExperience();
                array_push($array,array('id'=>0,'value'=>$default));
                break;

            case self::GENDER:
                $array = self::getGender();
                array_push($array,array('id'=>0,'value'=>$default));
                break;

            case self::MARRIAGE:
                $array = self::getMarriage();
                array_push($array,array('id'=>0,'value'=>$default));
                break;

            case self::JOB_STATUS:
                $array = self::getJobStatus();
                array_push($array,array('id'=>0,'value'=>$default));
                break;

            case self::DEGREE:
                $array = self::getDegree();
                array_push($array,array('id'=>0,'value'=>$default));
                break;

            case self::SALARY_SEARCH:
                $array = self::getSalarySearch();
                array_push($array,array('id'=>0,'value'=>$default));
                break;

            case self::JOB_CATEGORY:
                $array = self::getJobCategory();
                break;

            case self::WORK_EXPERIENCE_JOB:
                $array = self::getWorkExperienceJob();
                array_push($array,array('id'=>0,'value'=>$default));
                break;

            case self::POCKET_TYPE:
                $array = self::getPacketType();
                break;
            case self::POCKET_STATUS_IMAGE_URL:
                $array = self::getPacketStatusImageUrl();
                break;
            case self::POCKET_CASH_STATUS:
                $array = self::getPacketCashStatus();
                $default.=' 元';
                break;
            case self::RESUME_EXPERIENCE_TITLE:
                $array = self::getResumeExperienceTitle();
                break;

            default:
                $array =  array();
                break;
        }


        $obj = ArrayUtil::toHashmap($array,'id','value');
        if(isset($obj[$id]))
        {
            return $obj[$id];
        }
        else
        {
            return $default;
        }
    }

}