<?php 
/*WP Ajax Edit Script
--Created by Ronald Huereca
--Created on: 03/28/2007
--Last modified on: 05/04/2008
--Relies on jQuery, wp-ajax-response, thickbox
	Copyright 2007,2008  Ronald Huereca  (email : ron alfy [a t ] g m ail DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if (!function_exists('add_action'))
{
	require_once("../../../../wp-config.php");
	header('Content-Type: text/javascript; charset='.get_option('blog_charset').'');
}
?>
//Overwrite thickbox variables
tb_pathToImage = "<?php bloginfo('wpurl') ?>/wp-includes/js/thickbox/loadingAnimation.gif";
tb_closeImage = "<?php bloginfo('wpurl') ?>/wp-includes/js/thickbox/tb-close.png";
jQuery(document).ready(function(){
   AjaxEditComments.init();
   
});
var AjaxEditComments = function() {
	var $j = jQuery;
	var timers = new Array(); //keeps track of timers
  var timerObjs = new Array(); //keeps track of timer data objects
	var PluginUrl = "<?php bloginfo('wpurl') ?>/wp-content/plugins/wp-ajax-edit-comments";
	var CommentAuthorName = "editAuthor";
	var CommentClassName = "editComment";
  
	//Initializes the edit links
	function initialize_links() {
  	//Leave the style in for Safari
     
  	$j(".edit-comment-admin-links").attr("style", "display: block");
    $j(".edit-comment-user-link").attr("style", "display: block");
    /* For Crappy IE */
    $j(".edit-comment-admin-links").show();
    $j(".edit-comment-user-link").show();
    <?php if ($WPrapAjaxEditComments->can_scroll()) : ?>
    var location = "" + window.location;
    var pattern = /(#[^-]*\-[^&]*)/;
    if (pattern.test(location)) {
    	location = $j("" + window.location.hash);
			var targetOffset = location.offset().top;
			$j('html,body').animate({scrollTop: targetOffset}, 1000);
    }
    <?php endif; ?>
   get_time_left();
  }
  //Finds an area (if applicable) and displays the time left to comment
  function get_time_left() {
  	$j("." + 'ajax-edit-time-left').each(function() { 
    	data = pre_process($j(this).prev());
    	data.data = $j.extend({ action: 'gettimeleft', cid: data.cid,pid:data.pid, _ajax_nonce: data.nonce },'');
    	data.action = 'gettimeleft';
    	data.success = function(r) {
      	var res = wpAjax.parseAjaxResponse(r, data.response,data.element);
        jQuery.each( res.responses, function() {
        	if (this.what == "error" || this.what == "success") {
          	return;
          }
        	if (this.what == "minutes") {
          	minutes = parseInt(this.data);
          }
          if (this.what == "seconds") {
          	seconds = parseInt(this.data);
          }
        });
        cid = data.cid;
        element = $j("#ajax-edit-time-left-" + data.cid);
        data.timer = $j.extend({minutes: minutes, seconds: seconds, cid: data.cid, element: element},'');
        timerObjs[data.cid] = data;
        timers[data.cid] = setTimeout(function() {get_time_left_timer(data.timer) }, 1000);
      }
			$j.ajax(data);
    	return;
    })
  
  }
  //Updates the UI with the correct time left to edit
  //Parameters - timer (obj with timer data)
  function get_time_left_timer(timer) {
  	clearTimeout(timers[timer.cid]);
    seconds = timer.seconds - 1;
    minutes = timer.minutes;
    element = timer.element;
    //Check to see if the time has run out
		if (minutes <=0 && seconds <= 0) { 
			$j("#edit" + timer.cid).unbind();
      element.remove();
      $j("#edit-comment-user-link-" + timer.cid).remove();
      tb_remove(); //for iframe
      clearTimeout(timers[timer.cid]);
			return;
		} 
		if (seconds < 0) { minutes -= 1; seconds = 59; }
    //Create timer text
		var text = "";
		if (minutes >= 1) {
			if (minutes >= 2) { text = minutes + " <?php _e('minutes', $WPrapAjaxEditComments->localizationName) ?>"; } else { text = minutes + " <?php _e('minute', "WPAjaxEditComments") ?>"; }
			if (seconds > 0) { text += " <?php _e('and', $WPrapAjaxEditComments->localizationName) ?> "; }
		}
		if (seconds > 0) {
			if (seconds >= 2) { text += seconds + " <?php _e('seconds', $WPrapAjaxEditComments->localizationName) ?>"; } else { text += seconds + " <?php _e('second', $WPrapAjaxEditComments->localizationName) ?>"; }
		}
    //Output the timer to the user
    try {
    	//This try statement is for the iFrame
      //Iframe code from:  http://xkr.us/articles/dom/iframe-document/
      if (document.getElementById('TB_iframeContent') != undefined) {
      	var oIframe = document.getElementById('TB_iframeContent');
        var oDoc = (oIframe.contentWindow || oIframe.contentDocument);
        if (oDoc.document) oDoc = oDoc.document;
        $j("#timer" + timer.cid, oDoc).html("&nbsp;(" + text + ")");
      }
    } catch(err) { }
    $j("#ajax-edit-time-left-" + timer.cid).html("&nbsp;(" + text + ")");
    timer.minutes = minutes;
    timer.seconds = seconds;
    timerObjs[timer.cid] = timer;
    timers[timer.cid] = setTimeout(function() { get_time_left_timer(timer) }, 1000);
  }
  //Returns a data object for ajax calls
  function pre_process(element) {
  	var s = {};
    s.element = element.attr("id");
    s.response = 'ajax-response';
    var url = wpAjax.unserialize(element.attr('href'));
    s.nonce = url._wpnonce;
    s.cid = url.c;
    s.pid = url.p;
    s.action = url.action;
    s.type = "POST";
    s.url = PluginUrl + "/php/AjaxEditComments.php";
    s.data = $j.extend({ action: s.action, cid: s.cid,pid:s.pid, _ajax_nonce: s.nonce },'');
    s.global = false;
    s.timeout = 30000;
    return s;
  }
  function Moderate(data) {
  	data.success = function(r) {
    	if (r == 1) { 
      	//Yay, comment is unapproved - Show visual
        var li = $j("#" + "comment-" + data.cid);
        if (li.is("li") || li.is("div") ) {
          li.addClass("ajax-unapprove");
          li.slideUp(1000, function() { li.remove(); });
      	}
        return;
     	}
      //Unapproval wasn't a success, display error
      alert(r);
    }
    if (confirm("<?php _e('Mark for Moderation?', $WPrapAjaxEditComments->localizationName) ?>")) {
    	$j.ajax(data);
    }
  }
  function Approve(data) {
  	data.success = function(r) {
    	if (r == 1) { 
      	//Yay, comment is approved - Show visual
        var li = $j("#" + "comment-" + data.cid);
        if (li.is("li") || li.is("div") ) {
          li.addClass("ajax-approve");
          li.slideUp(1000, function() { li.remove(); });
      	}
        return;
     	}
      //Approval wasn't a success, display error
      alert(r);
    }
    if (confirm("<?php _e('Approve Comment?', $WPrapAjaxEditComments->localizationName) ?>")) {
    	$j.ajax(data);
    }
  }
  function Spam(data) {
  	data.success = function(r) {
    	if (r == 1) { 
      	//Yay, comment was marked as spam.  Try to show a visual
        var li = $j("#" + "comment-" + data.cid);
        if (li.is("li") || li.is("div") ) {
          li.addClass("ajax-delete");
          li.slideUp(1000, function() { li.remove(); });
      	}
        return;
     	}
      //Spamation wasn't a success, display error
      alert(r);
    }
    if (confirm("<?php _e('Mark as Spam?', $WPrapAjaxEditComments->localizationName) ?>")) {
    	$j.ajax(data);
    }
  }
  function Delete(data) {
  	data.success = function(r) {
    	if (r == 1) { 
      	//Yay, comment was deleted.  Try to show a visual
        var li = $j("#" + "comment-" + data.cid);
        if (li.is("li") || li.is("div") ) {
          li.addClass("ajax-delete");
          li.slideUp(1000, function() { li.remove(); });
      	}
        return;
     	}
      //Deletion wasn't a success, display error
      alert(r);
    }
    if (confirm("<?php _e('Delete this comment?', $WPrapAjaxEditComments->localizationName) ?>")) {
    	$j.ajax(data);
    }
  }
	return {
			init : function() { 
      	initialize_links();
      },
      update_comment: function(id, content) {
      	$j("#" + id).html(content);
      },
      update_author: function(id, author, url) {
      	if ( url == '' || 'http://' == url ) {
        	if (author == '') { $j("#" + id).html('<?php _e('Anonymous', $WPrapAjaxEditComments->localizationName)?>'); return; }
        	$j("#" + id).html(author);
        } else if (author == '') {
        	$j("#" + id).html('<?php _e('Anonymous', $WPrapAjaxEditComments->localizationName)?>');
        } else {
        	$j("#" + id).html("<a href='" + url + "'>" + author + "</a>");
        }
      },
      edit: function(obj) {
      	obj = $j(obj);
      	var data = pre_process(obj);
        //For the Thickbox
        obj.addClass("thickbox");
        var t = obj.attr("title")|| obj.attr("name") || null;
        var a = obj.attr("href") || obj.attr("alt");
        var g = obj.attr("rel") || false;
        tb_show(t,a,g);
        obj.blur();
      },
      approve: function(obj) {
      	var data = pre_process($j(obj));
        Approve(data);
      },
      spam: function(obj) {
      	var data = pre_process($j(obj));
        Spam(data);
      },
      moderate: function(obj) {
      	var data = pre_process($j(obj));
        Moderate(data);
      },
      delete_comment: function(obj) {
      	var data = pre_process($j(obj));
        Delete(data);
      }
	};
	
}();