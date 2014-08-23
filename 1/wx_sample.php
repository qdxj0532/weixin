<?php
/**
  * wechat php test
  */
//sae_xhprof_start();
//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $eventType = $postObj->MsgType;
                $eventName = $postObj->Event;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";  

                $commInfo = "你可以做如下回复\n回复1;\n回复2;\n回复自己的名字;\n回复音乐;\n发送图片;\n发送您的位置;\n";
                $msgType = "text";//default respons type
                

                
                switch ($eventType) {
                	case "text":
                		if(!empty( $keyword ))
                		{
                			$msgType = "text";
                			if ($keyword == "1") {
                				$contentStr="我是你的小苹果";
                			}
                			elseif ($keyword == "2")
                			{
                				$contentStr = "我很2";
                			}
                			elseif ($keyword == "宋平")
                			{
//                 				$contentStr = "诗歌   你在我身边\n\n烧饼 裹着芝麻\n摊鸡蛋 带着香气\n火腿肠 园园的美味\n
//                 				\n一起打球\n一起嬉戏\n一起在雨中奔跑\n\n这些瞬间 真的非常美好\n但它们都比不过\n清晨醒来 我看见你在微笑";
                				
                				$newsTpl = "<xml>
											<ToUserName><![CDATA[%s]]></ToUserName>
											<FromUserName><![CDATA[%s]]></FromUserName>
											<CreateTime>%s</CreateTime>
											<MsgType><![CDATA[news]]></MsgType>
											<ArticleCount>1</ArticleCount>
											<Articles>
											<item>
											<Title><![CDATA[你在我身边]]></Title> 
											<Description><![CDATA[优美的诗歌]]></Description>
											<PicUrl><![CDATA[http://xiujian-weixinresource.stor.sinaapp.com/hesp.JPG]]></PicUrl>
											<Url><![CDATA[http://blog.sina.com.cn/s/blog_62aa6b3d0102uzv7.html]]></Url>
											</item>
											</Articles>
											</xml>";
                				
                				$resultStr = sprintf($newsTpl,$fromUsername,$toUsername,$time);
                				echo $resultStr;
                				
                			}
                			elseif ($keyword == "音乐")
                			{
                				$msgType = "music";
                				$musicTpl = "<xml>
											<ToUserName><![CDATA[%s]]></ToUserName>
											<FromUserName><![CDATA[%s]]></FromUserName>
											<CreateTime>%s</CreateTime>
											<MsgType><![CDATA[%s]]></MsgType>
											<Music>
											<Title><![CDATA[一直很安静]]></Title>
											<Description><![CDATA[送给你,平]]></Description>
											<MusicUrl><![CDATA[http://xiujian-weixinresource.stor.sinaapp.com/yzhaj.mp3]]></MusicUrl>
											<HQMusicUrl><![CDATA[http://xiujian-weixinresource.stor.sinaapp.com/yzhaj.mp3]]></HQMusicUrl>
											</Music>
											</xml>";
                				
                				$resultStr = sprintf($musicTpl,$fromUsername,$toUsername,$time,$msgType);
                				echo $resultStr;                				
                			}
                			else
                			{
                				$contentStr = $commInfo;
                				$contentStr .= $keyword;
                			}
                		}
                	break;
                	
                	case "image":
                		$msgType = "text";
                		$contentStr = "图片不错!";
                	break;
                	
                	case "location";
                		$latitude = $postObj->Location_X;//纬度
                		$longitude = $postObj->Location_Y;//经度
                		$contentStr = "你的纬度是{$latitude},经度是{$longitude},已经被锁定！";
                	break;
                	
                	case "event":
                		if ($eventName =="subscribe")
                		{
//                 			if ($fromUsername == "ojIHijvAQEtkL7_Rwwng3hAKH2Hs")//song ping
//                 			{
//                 				$contentStr = "宋平";
//                 			}
//                 			else 
//                 			{
//                 				//test
//                 				$contentStr = "修健";
//                 			}
                			
//                 			$contentStr = $fromUsername;

                			$contentStr = "欢迎来到漂移的世界!\n{$commInfo}";
                			//$contentStr = "欢迎来到漂移的世界!";
                			$msgType = "text";
                		}
                	break;

                	default:
                		$contentStr = "此功能尚未开发，敬请期待。";
                		$msgType = "text";
                	break;
                }               
                
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;

        }else {
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}
//sae_xhprof_end();
?>