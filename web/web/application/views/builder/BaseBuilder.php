<?php
class BaseBuilder 
{

    const AVATAR_ROOT = 'http://img.zhaopin.weibo.com';
    const AVATAR_TEMP_ROOT = 'http://img.zhaopin.yolu.com';

    static function getImageUrl($name)
    {
        $config = HaloEnv::get('config');
        $imgRoot = $config['img']['root'];
        if(strlen($imgRoot) === 0)
        {
            $array = explode("/", $name);
            $name = end($array);
            $pos =  strpos($name,'.');
            if($pos !== false && strpos($name,'.',$pos+1) !== false);
            {
                $name = substr($name,$pos+1);
            }
            $array[count($array)-1] = $name;
            $name = implode('/',$array);
        }
        return $imgRoot.'/images/'.$name;
    }

    static function formatSalaryStr($info ,$needSpace = true)
    {

        if($info['is_salary_negotiable'])
        {
            $str = '面议';
        }
        else
        {
//            if($info['salary_start'] > 1000)
//            {
//            $info['salary_start'] = floor($info['salary_start'] / 1000) . 'K';
//            $info['salary_end'] = floor($info['salary_end'] / 1000 ) . 'K';
//            }
            if($needSpace)
            {
                $str = $info['salary_start'] . ' - '. $info['salary_end'] ."元";
            }
            else
            {
                $str = $info['salary_start'] . '-'. $info['salary_end']."元";
            }
        }
        return $str;
    }

    static public function  formatSearchJobList($list)
    {
        if(!$list)
        {
            return $list;
        }
        $tempList = array();
        foreach($list as $key => $value)
        {
            $item = array();

            $item['location'] = self::formatLocation($value['location']);
            $item['salary'] = self::formatSalaryStr($value);
            $item['title'] = $value['title'];
            $item['category'] = WeChatOption::getValueWithId(WeChatOption::JOB_CATEGORY,$value['category'],'不限');
            $item['job_id'] = $value['job_id'];
            if($value['company_user'])
            {
                $item['company_user'] = $value['company_user'];
            }
            $item['company'] = self::getCompanyName($value);
            $item['status'] = $value['status'];
            array_push($tempList,$item);
        }
        return $tempList;
    }
    static public function formatJobDetail($info)
    {
        $detail = $info['job'];
        $default = '不限';
        $tempDetail = array();
//        $tempDetail['applied'] = $info['applied'];
        $tempDetail['salary'] = self::formatSalaryStr($detail['job_detail']);
        $tempDetail['location'] = self::formatLocation($detail['job_detail']['location']);
        $tempDetail['title'] = $detail['job_detail']['title'];
        $tempDetail['category'] = WeChatOption::getValueWithId(WeChatOption::JOB_CATEGORY,$detail['job_detail']['category'],$default);
        $tempDetail['experience'] = WeChatOption::getValueWithId(WeChatOption::WORK_EXPERIENCE_JOB,$detail['job_detail']['experience'],'工作经验不限');
        $tempDetail['degree'] = WeChatOption::getValueWithId(WeChatOption::DEGREE,$detail['job_detail']['education_degree'],'学历不限');
        $tempDetail['company_user'] = $detail['company_user'];
        $tempDetail['department'] = $detail['job_detail']['department'];
        $tempDetail['desc'] = nl2br($detail['job_detail']['desc']);
        $tempDetail['welfare'] = $detail['job_welfare_list'];
        $tempDetail['job_id'] = $detail['job_id'];
        $tempDetail['skill_list'] = $detail['job_skill_list'];
        $tempDetail['watch_count'] = $detail['job_status']['click_count'];
        $tempDetail['sub_title'] = self::getCompanyName(array('company_user'=>$detail['company_user'],'department'=>$detail['job_detail']['department']));

//        $today = mktime(0,0,0,date('n'),date('j'),date('Y'));




        $status = 0;
        $publishTime = $detail['job_status']['publish_time'];
        if($detail['job_status']['publish_status'] == -1)
        {
            $status = -1;
            $publishTime = $detail['job_status']['closed_time'];
        }
        elseif($info['applied'])
        {
            $status = 1;
        }
        else
        {
            $status = 0;
        }
        $tempDetail['job_status'] = $status;
        $tempDetail['del'] = $detail['job_status']['del'];


//        if(date('Y-n-j') === date('Y-n-j',$publishTime))
//        {
//            $day = '今天';
//        }
//        else if($publishTime >=  $today - 86400 && $publishTime < $today)
//        {
//            $day = '昨天';
//        }
//        else
//        {
//            $day = date('Y-n-j',$publishTime);
//        }

        switch($detail['job_status']['publish_status'])
        {
            case -1:
                $str = '关闭';
                break;
            case 1:
                $str = '发布';
                break;

            case 2:
                $str = '重新发布';
                break;

            default:
                $str = '发布';
        }
        $tempDetail['publish'] = BaseBuilder::formatTime($publishTime).' '.$str;
        return $tempDetail;
    }

    static  public function  formatTime($timeStamp)
    {
        $today = mktime(0,0,0,date('n'),date('j'),date('Y'));
        $day = '';
        if(date('Y-n-j') === date('Y-n-j',$timeStamp))
        {
            $day = '今天';
        }
        else if($timeStamp >=  $today - 86400 && $timeStamp < $today)
        {
            $day = '昨天';
        }
        else
        {
            $day = date('Y-n-j',$timeStamp);
        }
        return $day." ".date('H:i',$timeStamp);
    }

    static public  function formatIndustry($industry)
    {
        $temp = array();
        foreach($industry as $key=> $group)
        {
            foreach($group['data'] as $groupKey =>$value)
            {
                $value['group'] = $group['name'];
                $temp[] = $value;
            }
        }
        return $temp;
    }

    static public function formatResume($info,$options = false)
    {
        if($options)
        {
            if(isset($info['basic_info'])){

                $basicInfo = &$info['basic_info'];

                //industry
                if(!$basicInfo['industries'])
                {
                    $basicInfo['industries'] =  array(array('id'=>0,'value'=>'请选择'));
                }
                $response = HaloClient::singleton()->getData(HaloClient::GET_INDUSTRY_LIST);
                if($response['code'] == 0)
                {
                    $industryOptions = $response['data'];
                    array_unshift($industryOptions,array('id'=>0,'name'=>'请选择','data'=>array(array('id'=>0,'name'=>'请选择'))));
                    $industryOptions = BaseBuilder::formatIndustry($industryOptions);
                }
                $basicInfo['industries'] = array('value'=>$basicInfo['industries'],'options'=>$industryOptions);


                //work_experience
                if(!$basicInfo['work_experience'])
                {
                    $basicInfo['work_experience'] = 0;
                }
                $workOptions = WeChatOption::getWorkExperience();
                array_unshift($workOptions,array('id'=>0,'value'=>'请选择'));
                $info['basic_info']['work_experience'] = array('value'=>$info['basic_info']['work_experience'],'options'=>$workOptions);




                if(!$basicInfo['marriage'])
                {
                    $basicInfo['marriage'] = 0;
                }
                $marriageOptions = WeChatOption::getMarriage();
                array_unshift($marriageOptions,array('id'=>0,'value'=>'请选择'));
                $info['basic_info']['marriage'] = array('value'=>$info['basic_info']['marriage'],'options'=>$marriageOptions);


                if(!$basicInfo['gender'])
                {
                    $basicInfo['gender'] = 0;
                }
                $genderOptions = WeChatOption::getGender();
                array_unshift($genderOptions,array('id'=>0,'value'=>'请选择'));
                $info['basic_info']['gender'] = array('value'=>$info['basic_info']['gender'],'options'=>$genderOptions);


                if($basicInfo['avatar'])
                {
                    if(!self::checkUrlImageExist(self::AVATAR_ROOT.$basicInfo['avatar']))
                    {
                        $basicInfo['avatar'] = self::AVATAR_TEMP_ROOT.$basicInfo['avatar'];
                    }
                    else
                    {
                        $basicInfo['avatar'] = self::AVATAR_ROOT.$basicInfo['avatar'];
                    }
                }

                if($basicInfo['birthday'])
                {
                    $basicInfo['birthday'] = BaseBuilder::timestampToStr($basicInfo['birthday']);
                }


                if(isset($info['basic_info']['location']))
                {
                    $info['basic_info']['location'] = self::formatLocation($info['basic_info']['location'],$options);
                }
                else
                {
                    $info['basic_info']['location'] = self::formatLocation(array('province'=>array('id'=>0)),$options);
                }

            }

            if(isset($info['job_objective']))
            {
                $job = &$info['job_objective'];
                if(!$job['status'])
                {
                    $job['status'] = 2;
                }
                $job['status'] = array('value'=>$job['status'],'options'=>WeChatOption::getJobStatus());

                if(!$job['expect_titles'])
                {
                    $job['expect_titles'] = array();
                }
                $temp = array();
                foreach($job['expect_titles'] as $position)
                {
                    array_push($temp,$position);
                }
                $job['expect_titles'] = $temp;



                if(!$job['expect_locations'])
                {
                    $job['expect_locations'] = array();
                }
                if(count($job['expect_locations']) == 0)
                {
                    array_push($job['expect_locations'],array('city'=>array('id'=>0,'name'=>'请选择'),'province'=>array('id'=>0,'name'=>'请选择')));
                }
                foreach($job['expect_locations'] as $key => $value)
                {
                    $job['expect_locations'][$key] = self::formatLocation($value,$options);;
                }


//                if(isset($info['job_objective']['current_salary']))
//                {
//                    $info['job_objective']['current_salary'] = $info['job_objective']['current_salary'] / 1000 ;
//                }
//                if(isset($info['job_objective']['expect_salary']))
//                {
//                    $info['job_objective']['expect_salary'] = $info['job_objective']['expect_salary'] / 1000 ;
//                }

            }

            if(isset($info['occupation']))
            {
                $occupation = &$info['occupation'];
                if(!$occupation['location'])
                {
                    $occupation['location'] = array('province'=>array('id'=>0,'name'=>'请选择'));
                }
                $occupation['location'] = self::formatLocation($occupation['location'],$options);
                if($occupation['not_ended'] == 1)
                {
                    unset($occupation['end_time']);
                }

                if($occupation['start_time'])
                {
                    $occupation['start_time'] = self::timestampToStr($occupation['start_time']);
                }
                if($occupation['end_time'])
                {
                    $occupation['end_time'] = self::timestampToStr($occupation['end_time']);
                }


                if($occupation['projects'] && count($occupation['projects']))
                {
                    foreach($occupation['projects'] as $key=>$value)
                    {
                        if($value['website'])
                        {
                            $occupation['projects'][$key]['website_url'] = self::convertUrl($value['website']);
                        }
                    }
                }
            }

            if(isset($info['education']))
            {
                $education = &$info['education'];
                if($education['department'])
                {
                    $education['department'] = $education['department'];
                }
                else
                {
                    $education['department'] = '';
                }


                if($education['major'])
                {
                    $education['major'] = $education['major'];
                }
                else
                {
                    $education['major'] = '';
                }


                if($education['school'])
                {
                    $education['school'] = $education['school'];
                }
                else
                {
                    $education['school'] = '';
                }


                if($education['start_time'])
                {
                    $education['start_time'] = BaseBuilder::timestampToStr($education['start_time']);
                }

                if($education['end_time'])
                {
                    $education['end_time'] = BaseBuilder::timestampToStr($education['end_time']);
                }

                if(!$education['degree'])
                {
                    $education['degree'] = 1;
                }
                $education['degree'] = array('value'=>$education['degree'],'options'=>WeChatOption::getDegree());


                if($education['projects'] && count($education['projects']))
                {
                    foreach($education['projects'] as $key=>$value)
                    {
                        if($value['website'])
                        {
                            $education['projects'][$key]['website_url'] = self::convertUrl($value['website']);
                        }
                    }
                }
            }
        }
        else
        {
            if(isset($info['contact']))
            {
                $contact = &$info['contact'];
                if($contact['blog'])
                {
                    $contact['blog_url'] = self::convertUrl($contact['blog']);
                }
                if($contact['website'])
                {
                    $contact['website_url'] = self::convertUrl($contact['website']);
                }
            }

            if(isset($info['basic_info'])){

                if($info['basic_info']['avatar'])
                {
                    if(!self::checkUrlImageExist(self::AVATAR_ROOT.$info['basic_info']['avatar']))
                    {
                        $info['basic_info']['avatar'] = self::AVATAR_TEMP_ROOT.$info['basic_info']['avatar'];

                    }
                    else
                    {
                        $info['basic_info']['avatar'] = self::AVATAR_ROOT.$info['basic_info']['avatar'];
                    }
                }

                if(isset($info['basic_info']['work_experience']))
                {
                    if($info['basic_info']['work_experience'] == 1)
                    {
                        $info['basic_info']['work_experience'] = WeChatOption::getValueWithId(WeChatOption::WORK_EXPERIENCE,$info['basic_info']['work_experience']);
                    }
                    else
                    {
                        $info['basic_info']['work_experience'] = WeChatOption::getValueWithId(WeChatOption::WORK_EXPERIENCE,$info['basic_info']['work_experience']).'工作经验';
                    }

                }
                if(isset($info['basic_info']['gender']))
                {
                    if($info['basic_info']['gender'] == 0)
                    {
                        unset($info['basic_info']['gender']);
                    }
                    else
                    {
                        $info['basic_info']['gender'] = WeChatOption::getValueWithId(WeChatOption::GENDER,$info['basic_info']['gender']);
                    }
                }
                if(isset($info['basic_info']['marriage']))
                {
                    if($info['basic_info']['marriage'] == 0)
                    {
                        unset($info['basic_info']['marriage']);
                    }
                    else
                    {
                        $info['basic_info']['marriage'] = WeChatOption::getValueWithId(WeChatOption::MARRIAGE, $info['basic_info']['marriage']);
                    }
                }
                if($info['basic_info']['birthday'])
                {
                    if(intval(date('md')) >= intval(date('md',$info['basic_info']['birthday'])))
                    {
                        $old = date('Y') - date('Y',$info['basic_info']['birthday']);
                    }
                    else
                    {
                        $old = date('Y') - date('Y',$info['basic_info']['birthday']) - 1;
                    }

                    $info['basic_info']['birthday'] = array(
                        'str'=>date('Y年n月j日',$info['basic_info']['birthday']),
                        'old'=> $old,
                        'constellation' => self::getConstellation( $info['basic_info']['birthday'])
                    );
                }


                if(isset($info['basic_info']['industries']))
                {
                    $tempArray = array();
                    foreach($info['basic_info']['industries'] as $key=>$value)
                    {
                        $tempArray[] = $value['name'];

                    }
                    $info['basic_info']['industries'] = $tempArray;
                }
                else
                {
                    $info['basic_info']['industries'] = array();
                }

                if(count($info['occupations']) )
                {
                    $occu = &$info['occupations'][0];
                    if($occu['company'] )
                    {
                        $info['basic_info']['show_str1'] = $occu['company'];
                    }
                    if($occu['title'] )
                    {
                        $info['basic_info']['show_str2'] = $occu['title'];
                    }

                }
                else if(count($info['educations']))
                {
                    $edu = $info['educations'][0];
                    if($edu['school'])
                    {
                        $info['basic_info']['show_str1'] = $edu['school'];
                    }
                    if($edu['degree'])
                    {
                        $info['basic_info']['show_str2'] = WeChatOption::getValueWithId(WeChatOption::DEGREE,$edu['degree']);
                    }
                }


            }

            if(isset($info['occupations']))
            {
                foreach($info['occupations'] as &$item)
                {
                    if($item['start_time'] && ($item['not_ended'] || $item['end_time']))
                    {

                        if($item['not_ended'])
                        {
                            $last  = (date('Y') - date('Y',$item['start_time'])) * 12 + (date('n') - date('n',$item['start_time']));
                        }
                        else
                        {
                            $last  = (date('Y',$item['end_time']) - date('Y',$item['start_time'])) * 12 + (date('n',$item['end_time']) - date('n',$item['start_time']));
                        }
                        if($last < 12)
                        {
                            $lastStr = $last . '个月';
                        }
                        elseif($last % 12 != 0)
                        {
                            $lastStr = (int)($last / 12) .'年'.$last % 12 . '个月';
                        }
                        else
                        {
                            $lastStr = (int)($last / 12) .'年';
                        }
                        $item['last'] =$lastStr;


                        $start = $item['start_time'];
                        $startStr = date('Y年n月',$start);

                        if($item['not_ended'])
                        {
                            $endStr = '至今';
                        }
                        else
                        {
                            $endStr = ' - '.date('Y年n月',$item['end_time']);
                        }

                        $item['start_time'] = $startStr;
                        $item['end_time'] = $endStr;

                    }

//                    $item['company'] = $item['company']['name'];
//                    $item['title'] = $item['title']['name'];

                    if($item['projects'] && count($item['projects']))
                    {
                        foreach($item['projects'] as $key=>$value)
                        {
                            if($item['projects'][$key]['website'])
                            {
                                $item['projects'][$key]['website_url'] = self::convertUrl($value['website']);
                            }
                        }
                    }
                }
            }

            if(isset($info['job_objective']))
            {
                if(isset($info['job_objective']['status']))
                {
                    $info['job_objective']['status'] = WeChatOption::getValueWithId(WeChatOption::JOB_STATUS,$info['job_objective']['status']);
                }
                else if(count($info['job_objective']) > 0)
                {
                    $info['job_objective']['status'] = null;
                }
                if(isset($info['job_objective']['expect_titles']) && count($info['job_objective']['expect_titles']) > 0)
                {
                    $tempTitles = array();
                    foreach($info['job_objective']['expect_titles'] as $key=>$value)
                    {
                        array_push($tempTitles,$value);
                    }
                    $info['job_objective']['expect_titles'] = $tempTitles;
                }
                else if(count($info['job_objective']) > 0)
                {
                    $info['job_objective']['expect_titles'] = array();
                }

                if(isset($info['job_objective']['current_salary']))
                {
                    $info['job_objective']['current_salary'] = $info['job_objective']['current_salary'] . "元" ;
                }
                if(isset($info['job_objective']['expect_salary']))
                {
                    $info['job_objective']['expect_salary'] = $info['job_objective']['expect_salary']. "元" ;
                }

            }
            if(isset($info['educations']))
            {
                if(count($info['educations']) == 0 )
                {
                    $info['educations'] = array(array('school'=>null,'degree'=>null,'id'=>0));
                }
                else
                {
                    foreach($info['educations'] as &$education)
                    {
                        if(isset($education['degree']))
                        {
                            $education['degree'] = WeChatOption::getValueWithId(WeChatOption::DEGREE,$education['degree']);
                        }
//                        if($education['school'] && $education['school']['name'] )
//                        {
//                            $education['school'] = $education['school']['name'];
//                        }

                        if(isset($education['start_time']))
                        {
                            $education['start_time'] = date('Y年n月',$education['start_time']);
                        }

                        if($education['not_ended'])
                        {
                            $education['end_time'] = '至今';
                        }
                        else
                        {
                            $education['end_time'] = date(' - Y年n月',$education['end_time']);
                        }

                        $departmentMajor = array();
                        if($education['department'] )
                        {
                            $departmentMajor[] = $education['department'];
                            unset($education['department']);
                        }
                        if($education['major'] )
                        {
                            $departmentMajor[] = $education['major'];
                            unset($education['major']);
                        }
                        if(count($departmentMajor) != 0)
                        {
                            $education['depart_major'] = implode(',',$departmentMajor);
                        }

                        if($item['projects'] && count($item['projects']))
                        {
                            foreach($item['projects'] as $key=>$value)
                            {
                                if($value['website'])
                                {
                                    $item['projects'][$key]['website_url'] = self::convertUrl($value['website']);
                                }
                            }
                        }
                    }
                }
            }
            if(isset($info['projects']))
            {
                if(count($info['projects']))
                {
                    foreach($info['projects'] as $key=>$value)
                    {
                        if($value['website'])
                        {
                            $info['projects'][$key]['website_url'] = self::convertUrl($value['website']);
                        }
                    }
                }
            }

            if(isset($info['skills']) && count($info['skills']) > 0)
            {
                $info['skills'] =  array_unique($info['skills']);
            }

            if(isset($info['self_evaluate']))
            {
                $info['self_evaluate'] = nl2br(htmlspecialchars($info['self_evaluate']));
            }
        }
        return $info;
    }

    static public function getConstellation($timeStamp)
    {
        $constellationArray  = array(
            "01"=>array("摩羯座","水瓶座",20),
            "02"=>array("水瓶座","双鱼座",19),
            "03"=>array("双鱼座","白羊座",21),
            "04"=>array("白羊座","金牛座",21),
            "05"=>array("金牛座","双子座",21),
            "06"=>array("双子座","巨蟹座",22),
            "07"=>array("巨蟹座","狮子座",23),
            "08"=>array("狮子座","处女座",23),
            "09"=>array("处女座","天秤座",23),
            "10"=>array("天秤座","天蝎座",23),
            "11"=>array("天蝎座","射手座",22),
            "12"=>array("射手座","摩羯座",22));
        $month = date("m",$timeStamp);
        $day = intval(date("d",$timeStamp));
        if($day >= $constellationArray[$month][2])
        {
            return $constellationArray[$month][1];
        }
        else
        {
            return $constellationArray[$month][0];
        }
    }

    static public function formatLocation($location,$options = false)
    {
        if($options)
        {
            $response = HaloClient::singleton()->getData(HaloClient::GET_PROVINCE);
            if($response['code'] == 0)
            {
                $provinceOptions = $response['data'];
                array_unshift($provinceOptions,array('id'=>0,'name'=>'请选择'));
            }
            $cityOptions = array();
            if($location['province']['id'] > 0)
            {
                $response = HaloClient::singleton()->getData(HaloClient::GET_CITY,array('id' =>$location['province']['id']));
                if($response['code'] == 0 && isset($response['data']))
                {
                    $cityOptions = $response['data'];
                }
                else
                {
                    $cityOptions = array();
                }
            }
            else
            {
                $location['province']['id'] = 0;
            }
            if( !isset($location['city']) || !isset($location['city']['id']))
            {
                $location['city'] = array('id'=>0);
            }

            array_unshift($cityOptions,array('id'=>0,'name'=>'请选择'));
            $location['province'] = array('value'=> $location['province']['id'],'options'=>$provinceOptions);
            $location['city'] = array('value'=>$location['city']['id'],'options'=>$cityOptions);
            return  $location;

        }
        else
        {
            $nameArray = array();
            if($location['country'] && $location['country']['id'] != 1)
            {
                array_push($nameArray,$location['country']['name']);
            }
            if($location['province'])
            {
                array_push($nameArray,$location['province']['name']);
            }
            if($location['city'] &&  $location['city']['id'] != 100)
            {
                array_push($nameArray,$location['city']['name']);
            }

            return implode(',',$nameArray);
        }
    }

    static public function formatResumeMust($resume)
    {
        $default = '待补充';
        $must = array(
            'basic_info'=>array('name'=>0,'work_experience'=>0,'industries'=>0),
            'contact'=>array('email'=>0,'mobile'=>0),
            'job_objective'=>array('status'=>0,'expect_salary'=>0,'expect_titles'=>0),
            'occupations'=>array(array('company'=>0,'title'=>0,'last'=>0,'projects'=>array(array('name'=>0,'role'=>0)))),
            'educations'=>array(array('school'=>0,'degree'=>0,'projects'=>array(array('name'=>0,'role'=>0)))),
            'projects'=>array(array('name'=>0,'role'=>0))
        );

        self::formatMust($resume,$must,$default);

        return array('resume'=>$resume,'must'=>$must);
//        var_dump($must);

    }
    static public function formatMust(&$resume,&$keyArray,$default)
    {
        if($resume === null)
        {
            return;
        }
        if(array_values($keyArray) === $keyArray )
        {
            if(count($resume))
            {
                $keyItem = $keyArray[0];
                unset($keyArray[0]);
                $i = 0;
                foreach($resume as $key => &$value)
                {
                    $tempKey = $keyItem;
                    self::formatMust($value,$tempKey,$default);
                    $keyArray[$i] = $tempKey;
                    $i++;
                }
            }
        }
        else
        {
            foreach($keyArray as $key=>&$value)
            {
                if(is_array($value))
                {
                    if($resume[$key] === null && $key !== 'job_objective'){
                        $resume[$key] = array();
                    }
                    self::formatMust($resume[$key],$value,$default);
                }
                elseif($key === 'expect_salary' && $resume[$key] === 0 )
                {
                }
                elseif(!$resume[$key] || $resume[$key] === $default)
                {
                    if(is_array( $resume[$key]))
                    {
                        $resume[$key] = array($default);
                    }
                    else
                    {
                        $resume[$key] = $default;
                    }
                    $value = 1;
                }
            }

        }
    }

    static public function timestampToStr($timestamp)
    {

        return date('Y-m-d',$timestamp);
    }

    static public function strToTimestamp($str)
    {
        return strtotime($str) ;
    }

    static public function convertUrl($url)
    {
        if(strpos($url,'http') === false)
        {
            return 'http://'.$url;
        }
        else
        {
            return $url;
        }
    }

    static public function formatHistoryJobList($list)
    {
        if(count($list) == 0 || !is_array($list))
        {
            return;
        }

        $tempArray = array();
        foreach($list as $key=>$value)
        {
            $temp = array();
            $temp['title'] = $value['title'];
            $temp['company'] = self::getCompanyName($value);
            $temp['time_str'] = BaseBuilder::formatTime($value['extra_info']);
            $temp['salary_str'] = BaseBuilder::formatSalaryStr($value);
            $temp['job_id'] = $value['job_id'];
            $tempArray[] = $temp;
        }
        return $tempArray;
    }

    static public function getCompanyName($info)
    {
        if(!$info['company_user'])
        {
            return '';
        }

        if($info['company_user']['uid'] == 2436548385)
        {
            return $info['department'];
        }
        else
        {
            $tempArray = array();
            $tempArray[] = $info['company_user']['name'];
            if($info['department'])
            {
                $tempArray[] = $info['department'];
            }
            return implode('-',$tempArray);
        }
    }

    static public function formatInviteList($list)
    {
        if(count($list) == 0)
        {
            return;
        }
        $tempArray = array();
        foreach($list as $key=>$value)
        {
            $temp = array();
            $temp['invite_time'] = BaseBuilder::formatTime($value['invite_time']);
            $temp['company'] = $value['job']['company_user']['name'].'邀请您投递';
            $temp['title'] = $value['job']['title'];
            $temp['location'] = self::formatLocation($value['job']['location']);
            $temp['job_id'] = $value['job']['job_id'];
            $temp['category'] = WeChatOption::getValueWithId(WeChatOption::JOB_CATEGORY,$value['job']['category'],'不限');
            $temp['salary'] = BaseBuilder::formatSalaryStr($value['job']);
            $tempArray[] = $temp;
        }
        return $tempArray;
    }

    static public function checkUrlImageExist($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_NOBODY, 1); // 不下载
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(curl_exec($ch)!==false)
            return true;
        else
            return false;
    }

}