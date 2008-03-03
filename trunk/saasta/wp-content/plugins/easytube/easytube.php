<?php

/*

Plugin Name: Easy Tube
Plugin URI: http://www.ejump.co.uk/wordpress/easytube-plugin-for-wordpress/
Description: Plugin to easliy place YouTube objects in Wordpress Content
Date: 2007, March, 26
Author: Paul Bain
Author URI: http://www.paulbain.co.uk
Version: 0.2.2

*/

/*

Author: Paul Bain
Website: http://www.paulbain.co.uk

Copyright 2007 Paul Bain All Rights Reserved.

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

*/

add_filter('the_content','tube_content');
add_filter('the_content','googlevideo_content');

# 2008-02-06 jjhellst - add support for comments
add_filter('comment_text','tube_content');
add_filter('comment_text','googlevideo_content');

function tube_content($content) {
    $regex = '/\[youtube:(.*?)]/i';

	preg_match_all( $regex, $content, $matches );
	for($x=0; $x<count($matches[0]); $x++)
	{
		$parts = explode(" ", $matches[1][$x]);
		if(count($parts) > 1)
		{
			$vid= explode('=',$parts[0],2);
			$vid = $vid[1];
			$width = $parts[1];
			if(count($parts) > 2)
			{
				$height = $parts[2];
			}
			else
			{
				$height = "";
			}
            $replace='<object width="'.$width.'" height="'.$height.'"><param name="movie" value="http://www.youtube.com/v/'.$vid.'&rel=1&border=0"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$vid.'&rel=1&border=0" type="application/x-shockwave-flash" wmode="transparent"width="425" height="355"></embed></object>';
		}
		else
		{
			$vid= explode('=',$matches[1][$x]);
			$vid = $vid[1];

            $replace='<object width="425" height="355"><param name="movie" value="http://www.youtube.com/v/'.$vid.'&rel=1&border=0"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$vid.'&rel=1&border=0" type="application/x-shockwave-flash" wmode="transparent"width="425" height="355"></embed></object>';
		}
		$content = str_replace($matches[0][$x], $replace, $content);
	}

	return $content;
}

// TODO janne: this stuff here may not be using the most recent Google
// Video embed code and may thus not work on all browsers as expected.
function googlevideo_content($content)
{
    $regex = '/\[googlevideo:(.*?)]/i';
	preg_match_all( $regex, $content, $matches );
	for($x=0; $x<count($matches[0]); $x++)
	{
		$parts = explode(" ", $matches[1][$x]);
		if(count($parts) > 1)
		{
			$vid= explode('=',$parts[0]);
			$vid = $vid[1];
			$width = $parts[1];
			if(count($parts) > 2)
			{
				$height = $parts[2];
			}
			else
			{
				$height = "";
			}
			$replace = '<object class="embed" width="'.$width.'" height="'.$height.'" type="application/x-shockwave-flash" data="http://video.google.com/googleplayer.swf?docId='.$vid.'">v<param name="movie" value="http://video.google.com/googleplayer.swf?docId='.$vid.'" /><em>You need to have flashplayer enabled to watch this Google video</em></object>';
		}
		else
		{
			$vid= explode('=',$matches[1][$x]);
			$vid = $vid[1];
			$replace = '<object class="embed" width="425" height="350" type="application/x-shockwave-flash" data="http://video.google.com/googleplayer.swf?docId='.$vid.'"><param name="wmode" value="transparent" /<param name="movie" value="http://video.google.com/googleplayer.swf?docId='.$vid.'" /><em>You need to have flashplayer enabled to watch this Google video</em></object>';
		}

		$content = str_replace($matches[0][$x], $replace, $content);
	}

	return $content;
}


?>
