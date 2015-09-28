<?php

class SecurityUtils
{
    public static function extract ( $html )
    {
        $content = trim(strip_tags(str_replace('&nbsp;', ' ', $html))); // utf-8 ?
        return $content;
    }
    
	public static function escape($str)
	{
        if (is_string($str) == 'string')
		    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        else
            return "";
	}
	
	public static function unescape($str)
	{
		return htmlspecialchars_decode($str, ENT_QUOTES);
	}
	
    public static function escapeData($data, $tags)
    {
    	if($data == null)
    		return null;
    	foreach($tags as $k=>$v)
    		if(isset($data[$v]))
	    		$data[$v] = self::escape($data[$v]);
        return $data;
    }
    public static function escapeProperty($data)
    {
    	if($data == null)
    		return null;
    	foreach($data as $k=>$v)
	    		$data[$k] = self::escape($v);
        return $data;
    }
    
	// alex
    public static function escapeOpp($data)
    {
    	$data['user'] = self::escapeUser($data['user']);
		$tags = array('title', 'content');
    	if(isset($data['company']))
    	{
	    	$data['company']['name'] = self::escape($data['company']['name']);
	    	$data['company']['alias_name'] = self::escape($data['company']['alias_name']);
	    	$data['company']['title'] = self::escape($data['company']['title']);
    	}
    	return self::escapeData($data, $tags);
    }
    
    public static function escapeReview($data)
    {
    	$data['user'] = self::escapeUser($data['user']);
    	$data['text'] = self::escape($data['text']);
        return $data;
    }
    public static function escapeNotification($data)
    {
        return $data;
    }
    public static function escapeRequest($data)
    {
        return $data;
    }
    
    // hy
    public static function escapeUser($data)
    {
    	$tags = array('name', 'nickname', 'org', 'job', 'desc', 'identity');
    	return self::escapeData($data, $tags);
    }
    public static function escapeWeiboInfo($data)
    {
    	$tags = array('name', 'nickname', 'org', 'job', 'desc');
    	return self::escapeData($data, $tags);
    }
    public static function escapeCompany($data)
    {
    	$tags = array('name', 'alias_name');
    	return self::escapeData($data, $tags);
    }
    public static function escapeSkill($data)
    {
    	$tags = array('name', 'content');
    	
    	return self::escapeData($data, $tags);
    }
    public static function escapeOccupation($data)
    {
    	$tags = array('name', 'org', 'job', 'desc');
    	
    	return self::escapeData($data, $tags);
    }
    public static function escapeProject($project)
    {
        if (isset($project['members']) && isset($project['members']['members']))
        {
            foreach ($project['members']['members'] as $k=>$v)
            {
                $project['members']['members'][$k]['user'] = SecurityUtils::escapeUser($v['user']);
                $project['members']['members'][$k]['function'] = SecurityUtils::escape($v['function']);
            }
        }
        
        if (isset($project['obj_member']) &&  isset($project['obj_member']['function']))
        {
        	$project['obj_member']['function'] = SecurityUtils::escape($project['obj_member']['function']);
        }
    	$tags = array('title', 'desc', 'company', 'url');
    	return self::escapeData($project, $tags);
    }
    public static function escapeEducation($data)
    {
    	$tags = array('name', 'department', 'major', 'desc');
    	
    	return self::escapeData($data, $tags);
    }
    public static function escapeMsg($data)
    {
    	$tags = array('text');
    	return self::escapeData($data, $tags);
    }
    
    public static function escapeTags($data)
    {
    	if(!empty($data))
    	{
	    	foreach ($data as $k => $v) 
	    	{
	    		$data[$k] = SecurityUtils::escapeTag($v);
	    	}
    	}
    	return $data;
    }

    public static function escapeStatus($data)
    {
        foreach((array)$data['data'] as $k => $v)
        {
            $data['data'][$k]['value'] = SecurityUtils::escape($v['value']);
        }

        return $data;
    }

    public static function escapeTag($data)
    {
    	$tags = array('text');
    	return self::escapeData($data, $tags);
    }
    
    public static  function escapeContact($data)
    {
    	$tags = array('custom', 'value','property','type');
    	return self::escapeData($data, $tags);
    }
    
    public static function escapeAddr($data)
    {
    	$tags = array('custom', 'addr', 'postcode');
    	return self::escapeData($data, $tags);
    }
    
    public static function escapeResumeOverview($data)
    {
    	if( isset($data['orgs']) )
    	{
    		foreach ($data['orgs'] as $k => $v) 
    		{
    			$data['orgs'][$k] = SecurityUtils::escape($v);
    		} 
    	}
    	if( isset($data['skills']) )
    	{
    		foreach ($data['skills'] as $k => $v)
    		{
    			$data['skills'][$k] = SecurityUtils::escape($v);
    		}
    	}
    	if( isset($data['projects']) )
    	{
    		foreach ($data['projects'] as $k => $v)
    		{
    			$data['projects'][$k]['name'] = SecurityUtils::escape($v['name']);
    		}
    	}
    	return $data;
    }
    
    //zlh
    public static function escapePost($data)
    {
    	$data['user'] = self::escapeUser($data['user']);
    	if(!empty($data['praise']['users']))
    	{
    		foreach ($data['praise']['users'] as $k => $user) 
    		{
    			$data['praise']['users'][$k] = self::escapeUser($user);
    		}
    	}
    	if(!empty($data['comments']['data']))
    	{
    		foreach ($data['comments']['data'] as $k => $comment)
    		{
	    		$data['comments']['data'][$k] = self::escapePostComment($comment);
    		}
    	}
    	$tags = array('title');
    	$data = self::escapeData($data, $tags);
    	return $data;
    }
    
    public static function escapePostComment($data)
    {
    	$data['user'] = self::escapeUser($data['user']);
    	if(isset($data['post']))
    		$data['post'] = self::escapeTinyPost($data['post']);
    	return $data;
    }
    
    public static function escapePostReply($data)
    {
    	$data['user'] = self::escapeUser($data['user']);
    	$data['obj_user'] = self::escapeUser($data['obj_user']);
    	if(isset($data['post']))
    		$data['post'] = self::escapeTinyPost($data['post']);
    	$tags = array('content');
    	return self::escapeData($data, $tags);
    }
    
    public static function escapeTinyPost($data)
    {
    	if(!empty($data) && !empty($data['title']))
    		$data['title'] = self::escape($data['title']);
    	
		return $data;
    }

    public static function escapeRanking($data)
    {
        if(!empty($data))
        {
            $tags = array('name', 'org', 'promoter_name');
            $data = self::escapeData($data, $tags);
        }
        return $data;
    }

    public static function escapeLink($data)
    {
        if(!empty($data))
        {
            $tags = array('title','description');
            $data = self::escapeData($data,$tags);
        }
        return $data;
    }
    
    public static function replaceEmbededVideoWithPreview($input)
    {
        $reg = '/<embed\s.*data-poster=\"([^\"]*)\"[^>]*>/iU';
        $str = $input;
        if (preg_match($reg, $input, $results))
        {
            $str = preg_replace_callback($reg, function($matches){
                $src = $matches[1];
                $replaceHtml = '<div class="comment_smallpic"><a href="javascript:void(0);" id="comment_thumbnail" target="_top"><img src="' . $src . '" max-width="200px" max-height="150px" class="fl margin_10_r"></a><div class="playVideo"></div></div>';
                return $replaceHtml;
            }, $str);
        }
        return $str;
    }
    
    public static function getFirstImageSrc($input)
    {
    	$imageSrc = '';
    	
    	$reg = '/<img\s.*src=\"([^\"]*)\"[^>]*>/iU';
    	if (preg_match($reg, $input, $results))
    	{
    		$imageSrc = $results[1];
    	}
    	
    	return $imageSrc;
    }
    
    public static function includeIlllegalChar ( $subject )
    {
        $pattern = '/^[\x{4E00}-\x{9FA5}a-zA-Z0-9\s\.\,，\(\)\+#\-]+$/u';
        return !(preg_match($pattern, $subject));
    }
    
    public static function isLegalTag ( $subject )
    {
        $pattern = '/^[\x{4E00}-\x{9FA5}a-zA-Z0-9\s]+$/u';
        return (preg_match($pattern, $subject));
    }

    public static function isLegalSkill ( $subject )
    {
        $pattern = '/^[\x{4E00}-\x{9FA5}a-zA-Z].*$/u';
        return (preg_match($pattern, $subject));
    }

    public static function isChineseSurname($name)
    {
        $pattern = ("/^(赵|钱|孙|李|周|吴|郑|王|冯|陈|楮|卫|蒋|沈|韩|杨|朱|秦|尤|许|何|吕|施|张|孔|曹|严|华|金|魏|陶|姜|戚|谢|邹|喻|柏|水|窦|章|云|苏|潘|葛|奚|范|彭|郎|鲁|韦|昌|马|苗|凤|花|方|俞|任|袁|柳|酆|鲍|史|唐|费|廉|岑|薛|雷|贺|倪|汤|滕|殷|罗|毕|郝|邬|安|常|乐|于|时|傅|皮|卞|齐|康|伍|余|元|卜|顾|孟|平|黄|和|穆|萧|尹|姚|邵|湛|汪|祁|毛|禹|狄|米|贝|明|臧|计|伏|成|戴|谈|宋|茅|庞|熊|纪|舒|屈|项|祝|董|梁|杜|阮|蓝|闽|席|季|麻|强|贾|路|娄|危|江|童|颜|郭|梅|盛|林|刁|锺|徐|丘|骆|高|夏|蔡|田|樊|胡|凌|霍|虞|万|支|柯|昝|管|卢|莫|经|房|裘|缪|干|解|应|宗|丁|宣|贲|邓|郁|单|杭|洪|包|诸|左|石|崔|吉|钮|龚|程|嵇|邢|滑|裴|陆|荣|翁|荀|羊|於|惠|甄|麹|家|封|芮|羿|储|靳|汲|邴|糜|松|井|段|富|巫|乌|焦|巴|弓|牧|隗|山|谷|车|侯|宓|蓬|全|郗|班|仰|秋|仲|伊|宫|宁|仇|栾|暴|甘|斜|厉|戎|祖|武|符|刘|景|詹|束|龙|叶|幸|司|韶|郜|黎|蓟|薄|印|宿|白|怀|蒲|邰|从|鄂|索|咸|籍|赖|卓|蔺|屠|蒙|池|乔|阴|郁|胥|能|苍|双|闻|莘|党|翟|谭|贡|劳|逄|姬|申|扶|堵|冉|宰|郦|雍|郤|璩|桑|桂|濮|牛|寿|通|边|扈|燕|冀|郏|浦|尚|农|温|别|庄|晏|柴|瞿|阎|充|慕|连|茹|习|宦|艾|鱼|容|向|古|易|慎|戈|廖|庾|终|暨|居|衡|步|都|耿|满|弘|匡|国|文|寇|广|禄|阙|东|欧|殳|沃|利|蔚|越|夔|隆|师|巩|厍|聂|晁|勾|敖|融|冷|訾|辛|阚|那|简|饶|空|曾|毋|沙|乜|养|鞠|须|丰|巢|关|蒯|相|查|后|荆|红|游|竺|权|逑|盖|益|桓|公|万俟|司马|上官|欧阳|夏侯|诸葛|闻人|东方|赫连|皇甫|尉迟|公羊|澹台|公冶|宗政|濮阳|淳于|单于|太叔|申屠|公孙|仲孙|轩辕|令狐|锺离|宇文|长孙|慕容|鲜于|闾丘|司徒|司空|丌官|司寇|仉|督|子车|颛孙|端木|巫马|公西|漆雕|乐正|壤驷|公良|拓拔|夹谷|宰父|谷梁|晋|楚|阎|法|汝|鄢|涂|钦|段干|百里|东郭|南门|呼延|归|海|羊舌|微生|岳|帅|缑|亢|况|后|有|琴|梁丘|左丘|东门|西门|商|牟|佘|佴|伯|赏|南宫|墨|哈|谯|笪|年|爱|阳|佟|第五|言|福)/");
        return (preg_match($pattern, $name));
    }

    public static function isChineseName($name)
    {
        $pattern = '/^[\x{4E00}-\x{9FA5}]{2,4}$/u';
        return (preg_match($pattern, $name));
    }


}
