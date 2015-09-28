<?php
class EmptyBuilder extends BaseBuilder {
	
	private static $HEIGHT = array(
		'box' => array('height'=>238,'margin'=>50),
		'list' => array('height'=>500,'margin'=>120),
		'large_list' => array('height'=>518,'margin'=>50),
		'huge_list' => array('height'=>1020,'margin'=>50),
		'opptab' => array('height'=>360,'margin'=>80),
		'box_short' => array('height'=>150,'margin'=>0),
		'list_short' => array('height'=>350,'margin'=>0),
		'table' => array('height'=>140,'margin'=>0),
		'dialog'=> array('height'=>200,'margin'=>0,'width'=>'90%'),
		'resume_simple' => array('height'=>140,'margin'=>0),
				
	);
	private static function doToEmptyHtml($image , $style, $fmt, $info = null)
	{
		if($info)
		{
			foreach($info as $text=>$url)
				$params []= "<a href='{$url}' class='f14'>{$text}</a>";
			$content = vsprintf($fmt, $params);
		}
		else 
			$content = $fmt;
		$image = "empty_icon_".$image;
		$height = self::$HEIGHT[$style]['height'];
// 		$margin = self::$HEIGHT[$style]['margin'];
		$width =  self::$HEIGHT[$style]['width'];
		$iconHeight = 110;
		$margin = ($height - $iconHeight)/2 - 20;
		if($margin <= 0)
			$margin = self::$HEIGHT[$style]['margin'];
		
		if (!$width) 
			$width = '100%';
		return "<div class='div_table' style='height:{$height}px;'>
					<div  class='div_table_cell' id='empty_div'>						
						<div class='blank_prompt_icon'>
							<div class='{$image}'></div>						
							<div class='blank_prompt_text'>
							{$content}
							</div>
						</div>
					</div>
				</div>";
	}
// 	private static function doToEmptyHtml($text = null, $style = null)
// 	{
// 		if($text == null)
// 			$text =  '列表为空';
// 		if($style == null)
// 			$style =  self::$STYLE_LIST;
// 		return "<div class='p2 f18 ta_c' style='{$style}'>{$text}</div>";
// 	}
	public static function toEmptyHtml($tag, $user=null)
	{
		switch($tag)
		{
			// -- main --
			case 'review_box_main':
				return self::doToEmptyHtml('review', 'box', '没有评价内容');
			case 'hot_review_box_main':
				return self::doToEmptyHtml('review', 'box', '没有评价内容');
			case 'opp_box_recommend':
// 				return self::doToEmptyHtml('opp', 'box', '没有发现您可能感兴趣的机遇，查看 %s', array("热门机遇"=>"/opportunities?tab=hot"));
				return self::doToEmptyHtml('opp', 'box', '没有发现您可能感兴趣的机遇');
			case 'opp_box_index':
				return self::doToEmptyHtml('opp', 'opptab', '没有发现您可能感兴趣的机遇');
			case 'opp_box_fav':
				return self::doToEmptyHtml('rec_user', 'list', '您还没有收藏任何机遇');
			case 'opp_box_filter':
				return self::doToEmptyHtml('opp', 'large_list', '没有符合条件的机遇，您可以 %s', array("发布一个机遇"=>"/opportunity/new"));
			case 'opp_box_hot':
				return self::doToEmptyHtml('opp', 'box', '没有热门机遇');
			case 'opp_box_connections':
// 				return self::doToEmptyHtml('opp', 'box', '没有人脉发布的机遇，查看 %s', array("热门机遇"=>"/opportunities?tab=hot"));
				return self::doToEmptyHtml('opp', 'box', '没有人脉发布的机遇');
			case 'status_box':
// 				return self::doToEmptyHtml('status', 'box', '没有人脉动态，试试 %s 吧', array("导入微博人脉"=>"/find/weibo"));
				return self::doToEmptyHtml('status', 'box', '没有人脉动态');
			case 'status_list':
// 				return self::doToEmptyHtml('status', 'list', '没有人脉动态，试试 %s 吧', array("导入微博人脉"=>"/find/weibo"));
				return self::doToEmptyHtml('status', 'list', '没有人脉动态');
			case 'distribution':
				return self::doToEmptyHtml('distribution', 'box', '您的人脉信息太少，无法生成图谱');
			case 'rec_user_box':
				return self::doToEmptyHtml('rec_user', 'box', '抱歉，没有找到您可能感兴趣的人，<br/>试试 %s 吧',array("热门人物"=>"/hot-user?tab=day"));
			case 'rec_user_list':
				return self::doToEmptyHtml('rec_user', 'list', '抱歉，没有找到您可能感兴趣的人，<br/>试试 %s 吧',array("热门人物"=>"/hot-user?tab=day"));
			case 'rec_company_box':
				return self::doToEmptyHtml('rec_company', 'box', '抱歉，没有找到您感兴趣的公司或机构');
			case 'rec_company_list':
				return self::doToEmptyHtml('rec_company', 'list', '抱歉，没有找到您感兴趣的公司或机构');
			case 'visitor':
				return self::doToEmptyHtml('friend', 'list', '没有最近来访');
			case 'post_visitor':
				return self::doToEmptyHtml('friend', 'list', '还没有浏览此话题的人');
			case 'opp_visitor':
				return self::doToEmptyHtml('friend', 'list', '还没有浏览此机遇的人');
		
			// -- relation -- 
			case 'user':
				return self::doToEmptyHtml('friend', 'list', '人脉列表为空');
			case 'group_list':
				return self::doToEmptyHtml('friend', 'list', '这个分组是空的'); 
			case 'user_connections':
				return self::doToEmptyHtml('friend', 'list', '您还没有人脉');
				
			// -- opp --
			case 'opp_list_recommend':
// 				return self::doToEmptyHtml('opp', 'list', '没有发现您可能感兴趣的机遇，查看 %s', array("热门机遇"=>"/opportunities?tab=hot"));
				return self::doToEmptyHtml('opp', 'list', '没有发现您可能感兴趣的机遇');
			case 'opp_box_page_recommend':
				return self::doToEmptyHtml('opp', 'list', '没有发现您可能感兴趣的机遇');
			case 'opp_list_hot':
				return self::doToEmptyHtml('opp', 'list', '没有热门机遇');
			case 'opp_list_connections':
// 				return self::doToEmptyHtml('opp', 'list', '没有人脉发布的机遇，查看 %s', array("热门机遇"=>"/opportunities?tab=hot"));
				return self::doToEmptyHtml('opp', 'list', '没有人脉发布的机遇');
			case 'opp_inquire_public':
				return self::doToEmptyHtml('opp_inquire', 'opptab', '没有公开询问');
			case 'opp_inquire_private':
				return self::doToEmptyHtml('opp_inquire', 'opptab', '没有私下询问');
			case 'opp_inquire_mine':
				return self::doToEmptyHtml('opp_inquire', 'opptab', '您未询问过');
			case 'opp_refer':
				return self::doToEmptyHtml('opp_refer', 'opptab', '还未收到引荐');
			case 'opp_resume':
				return self::doToEmptyHtml('opp_resume', 'opptab', '还未收到简历');
			case 'opp_recd_user':
				return self::doToEmptyHtml('friend', 'opptab', '没有智能推荐人脉');
			case 'opp_recd_opp':
				return self::doToEmptyHtml('opp', 'opptab', '没有智能推荐机遇');
			
			// -- profile --
			case 'review_box_profile_in':
				return self::doToEmptyHtml('review', 'box', '还未收到任何评价');
			case 'review_box_profile_main':
				return self::doToEmptyHtml('review', 'box', '还未收到任何评价');
			case 'review_box_profile_sent':
				return self::doToEmptyHtml('review', 'box', '还未发出任何评价');
			case 'opp_box_profile':
				$name = isset($user) ? $user['name'] : '您';
				return self::doToEmptyHtml('opp', 'box', $name.'还未发布任何机遇');			
			case 'opp_box_new_profile':
				$name = isset($user) ? $user['name'] : '您';
				return self::doToEmptyHtml('opp', 'large_list', $name.'还未发布任何机遇');
            case 'opp_box_new_org_profile':
                return self::doToEmptyHtml('opp', 'large_list', '没有该公司相关的机遇');
			case 'opp_list_profile':
				return self::doToEmptyHtml('opp', 'list', '还未发布任何机遇');
			case 'opp_list_fav':
				return self::doToEmptyHtml('fav_opp', 'list', '您还没有收藏任何机遇');	
			case 'friend_list_fav':
				return self::doToEmptyHtml('fav_friend', 'list', '您还没有收藏任何人');	
			case 'basic_box_profile':
				return self::doToEmptyHtml('main_profile_basic', 'box_short', '未填写');
			case 'career_box_profile':
				return self::doToEmptyHtml('main_profile_career', 'box_short', '未填写任何经历');
			case 'resume_box_simple':
				return self::doToEmptyHtml('profile_career', 'resume_simple', '您的简历是空的');
			case 'skill_box_profile':
				return self::doToEmptyHtml('main_profile_skill', 'box_short', '未添加任何技能');
			case 'tag_box_profile':
				return self::doToEmptyHtml('profile_tag', 'box_short', '未添加任何标签');
			case 'contact_box_profile':
				return self::doToEmptyHtml('profile_contact', 'box_short', '未填写任何联系方式');
			case 'skill_list':
				return self::doToEmptyHtml('skill', 'list', '未添加任何技能');
			case 'skill_list_profile':
				return self::doToEmptyHtml('skill', 'list_short', '未添加任何技能');
			case 'contact_list_profile':
				return self::doToEmptyHtml('profile_contact', 'list_short', '未填写联系方式');
			case 'tag_list_profile':
				return self::doToEmptyHtml('profile_tag', 'list_short', '未添加任何标签');
			case 'occu_list_profile':
				return self::doToEmptyHtml('profile_career', 'list_short', '未添加任何职业经历');
			case 'project_list_profile':
				return self::doToEmptyHtml('project', 'list_short', '未添加任何项目经历');
			case 'edu_list_profile':
				return self::doToEmptyHtml('profile_edu', 'list_short', '未添加任何教育经历');
			case 'skill_review':
				return self::doToEmptyHtml('skill_review', 'list_short', '没有人对该技能评分');	
			case 'review_list_in':
				return self::doToEmptyHtml('review', 'list', '未收到任何评价');
			case 'review_list_sent':
				return self::doToEmptyHtml('review', 'list', '未发出任何评价');
			case 'friend_table_one':
// 				return self::doToEmptyHtml('friend', 'table', '您还没有人脉，尝试 %s 吧', array("导入微博人脉"=>"/find/weibo"));
				return self::doToEmptyHtml('friend', 'table', '您还没有人脉');
			case 'friend_table_two':
// 				return self::doToEmptyHtml('friend', 'table', '您还没有人脉，尝试 %s 吧', array("导入微博人脉"=>"/find/weibo"));
				return self::doToEmptyHtml('friend', 'table', '您目前没有二度人脉');
			case 'friend_dialog':
// 				return self::doToEmptyHtml('friend', 'table', '您还没有人脉，尝试 %s 吧', array("导入微博人脉"=>"/find/weibo"));
				return self::doToEmptyHtml('friend', 'dialog', '您还没有人脉');
			case 'friend_dialog_search':
// 				return self::doToEmptyHtml('friend', 'table', '您还没有人脉，尝试 %s 吧', array("导入微博人脉"=>"/find/weibo"));
				return self::doToEmptyHtml('friend', 'dialog', '未搜索到人脉');
			case 'friend_table_common':
				return self::doToEmptyHtml('friend_common', 'table', '没有共同人脉');
            case 'friend_box_common':
                return self::doToEmptyHtml('friend_common', 'box', '没有共同人脉');
			case 'friend_table_company':
				return self::doToEmptyHtml('friend', 'table', '您在该公司没有人脉');
			case 'friend_table_area':
				return self::doToEmptyHtml('friend', 'table', '您在该地区没有人脉');
			case 'friend_distribution_weibo':
				return self::doToEmptyHtml('friend', 'box', '没有人脉或互相关注的人');
			case 'friend_distribution_wcontact':
				return self::doToEmptyHtml('friend', 'box', '没有人脉');
			case 'company_weibo_product':
				return self::doToEmptyHtml('weibo', 'box_short', '未发现官方微博');
			case 'company_weibo_manager':
				return self::doToEmptyHtml('weibo', 'box_short', '未绑定高管微博');
			case 'company_follower_main':
				return self::doToEmptyHtml('friend', 'box_short', '没有关注者');	
			case 'company_follower_list':
				return self::doToEmptyHtml('friend', 'list', '没有关注者');	
			case 'black_list':
				return self::doToEmptyHtml('friend', 'list', '黑名单为空');		
			case 'company_list':
				return self::doToEmptyHtml('rec_company', 'list', '公司列表为空');		
			case 'product_weibo':
				return self::doToEmptyHtml('weibo', 'list', '未绑定官方微博');		
			case 'manager_weibo':
				return self::doToEmptyHtml('weibo', 'list', '未绑定高管微博');		
			case 'following_company':
				return self::doToEmptyHtml('rec_company', 'list', '未关注任何公司');		
			
			// -- search --
			case 'search':
				return self::doToEmptyHtml('search', 'list', '无搜索结果');
			case 'opp_list_search':
				return self::doToEmptyHtml('search', 'list', '没有搜索结果');
			
				
			// -- invite --
			case 'invite_step2_weibo':
				return self::doToEmptyHtml('invite', 'list', '没有找到微博上关注的人');
			case 'invite_step2_gmail':
				return self::doToEmptyHtml('invite', 'list', '没有找到 Gmail 联系人');
			case 'invite_step2_live':
				return self::doToEmptyHtml('invite', 'list', '没有找到 Msn 好友');
			case 'invite_step3_weibo':
				return self::doToEmptyHtml('invite', 'list', '没有找到还未加入微人脉的人');
			case 'invite_step3_gmail':
				return self::doToEmptyHtml('invite', 'list', '没有找到还未加入微人脉的 Gmail 联系人');
			case 'invite_step3_live':
				return self::doToEmptyHtml('invite', 'list', '没有找到还未加入微人脉的 Msn 好友');
			case 'invite_bilateral_user':
				return self::doToEmptyHtml('invite', 'list', '没有找到未加入微人脉的微博好友');

			// -- review --
			case 'hot_review_list':
				return self::doToEmptyHtml('review', 'list', '没有评价内容');
			case 'review_list':
				return self::doToEmptyHtml('review', 'list', '没有评价内容');
			
			// -- feed --
			case 'notification':
				return self::doToEmptyHtml('notification', 'list', '还未收到通知');
            case 'request':
                return self::doToEmptyHtml('request', 'list', '没有未处理的新请求');
            case 'request_friend':
                return self::doToEmptyHtml('request', 'list', '没有未处理的添加人脉请求');
            case 'msg':
                return self::doToEmptyHtml('msg1', 'list', '还未收或发出到站内信');
				
			// -- project --
			case 'potential_member':
				return self::doToEmptyHtml('friend', 'dialog', '没有找到可邀请的人');
				
			// -- post --
			case 'post_box':
				return self::doToEmptyHtml('post', 'box', '没有热门话题');
			case 'post_list_short':
				return self::doToEmptyHtml('post', 'opptab', '没有热门话题');
			case 'post_fav_list':
				return self::doToEmptyHtml('post', 'list', '您还未收藏任何话题');
			case 'post_new_list':
			case 'post_new_short_list':
				return self::doToEmptyHtml('post',$tag=='post_new_list' ? 'huge_list' : 'short_list', "此行业还没有话题，您可以 %s", array('发布一个话题'=>'/post/new'));
			case 'post_hot_list':
			case 'post_hot_short_list':
				return self::doToEmptyHtml('post',$tag=='post_hot_list' ? 'list' : 'short_list', '此行业还没有热门话题');
			case 'post_detail_comment':
				return self::doToEmptyHtml('comment', 'list', '还没有评论');
			case 'post_praised_list_comment':
				return self::doToEmptyHtml('comment', 'list', '此行业还没有热门评论');
			case 'comment_hot_box':
				return self::doToEmptyHtml('comment', 'box_short', '此行业还没有热门评论');				
			case 'comment_praised_box':
				return self::doToEmptyHtml('comment', 'box_short', '还没有赞过的评论');				
			case 'comment_praised_list':
				return self::doToEmptyHtml('comment', 'list', '还没有赞过的评论');				
			case 'post_praised_box':
				return self::doToEmptyHtml('post', 'box', '没有被赞的话题');
			case 'post_draft_box';
				return self::doToEmptyHtml('drafts', 'box_short', '没有草稿');
			case 'post_hot_box':
				return self::doToEmptyHtml('post', 'box_short', '此行业还没有热门话题');
			case 'post_box_empty':
				return self::doToEmptyHtml('post', 'box', $user);
			case 'post_list_empty':
				return self::doToEmptyHtml('post', 'list', $user);
			case 'industry_hot_people':
			case 'industry_hot_people_list':
				return self::doToEmptyHtml('rec_user', $tag ==  'industry_hot_people' ? 'box' : 'list', '此行业还没有热门人物');
            case 'ranking_colleague':
                return self::doToEmptyHtml('ranking', 'list', '您的同事中还没有丽人上榜');
            case 'ranking_connection':
                return self::doToEmptyHtml('ranking', 'list', '您的人脉中还没有丽人上榜');
            case 'ranking_colleagues_table':
                return self::doToEmptyHtml('ranking', 'table', '您的同事中还没有丽人上榜');
            case 'ranking_connections_table':
                return self::doToEmptyHtml('ranking', 'table', '您的人脉中还没有丽人上榜');
            case 'ranking_industry':
                return self::doToEmptyHtml('ranking', 'list', '此行业暂无丽人上榜，快来提名吧');
            case 'ranking_hot':
            case 'ranking_new':
            case 'ranking_all':
            case 'ranking_related':
                return self::doToEmptyHtml('ranking', 'list', '这里还没有丽人，快来提名吧');
            case 'ranking_voter':
                return self::doToEmptyHtml('rec_user', 'list', '还没有人为她投票');
            case 'ranking_canvasser':
                return self::doToEmptyHtml('rec_user', 'list', '还没有人为她拉票');
            case 'ranking_photo_praisers':
                return self::doToEmptyHtml('rec_user', 'list', '还没有人赞过此照片');
			default:
				return self::doToEmptyHtml('rec_user', 'list', $tag.'列表为空');
		}
	}
}	
  
    	                		        