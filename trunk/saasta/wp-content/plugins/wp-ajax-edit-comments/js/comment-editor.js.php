<?php 
/*WP Ajax Edit Comments Editor Interface Script
--Created by Ronald Huereca
--Created on: 05/04/2008
--Last modified on: 06/09/2008
--Relies on jQuery, wp-ajax-edit-comments, wp-ajax-response, thickbox
	
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
require_once("../../../../wp-config.php");
header('Content-Type: text/javascript; charset='.get_option('blog_charset').'');
?>
jQuery(document).ready(function(){
   AjaxCommentEditor.init();
	 
});
var AjaxCommentEditor = function() {
	var $j = jQuery;
	var PluginUrl = "<?php bloginfo('wpurl') ?>/wp-content/plugins/wp-ajax-edit-comments";
  function initialize_events() {
  	//Read in cookie values and adjust the toggle box
  	var cookieValue = readCookie('ajax-edit-comments-options');
    if (cookieValue) {
    	$j("#comment-options").attr("class", cookieValue);
    }
    
    //The "more options" button
  	$j("#comment-options h3").bind("click", function() { 
    	$j("#comment-options").toggleClass("closed"); 
      createCookie('ajax-edit-comments-options', $j("#comment-options").attr("class"), 365);
      return false; 
    });
    //Cancel button
    $j("#cancel,#status a, #close a").bind("click", function() { self.parent.tb_remove();
    return false; });
    //Title for new window
    $j("#title a").bind("click", function() { window.open(this.href); return false; } );
    //Save button event
  }
  //Loads the comment and displays the contents to the user
  function load_comment() {
  	//Pre-process data
  	var cid = parseInt($j("#commentID").attr("value"));
    var pid = parseInt($j("#postID").attr("value"));
    var action = $j("#action").attr("value");
  	var s = {};
    s.element = $j("#comment-edit-header").attr("id");
    s.response = 'ajax-response';
    s.cid = cid;
    s.pid = pid;
    s.action = action;
    s.type = "POST";
    s.url = PluginUrl + "/php/AjaxEditComments.php";
    s.data = $j.extend({ action: s.action, cid: s.cid,pid:s.pid },'');
    s.global = false;
    s.timeout = 30000;
  	//Change the edit text and events
    $j("#status").show();
    $j("#status").attr("class", "success");
  $j("#message").html("<?php _e('Loading...', $WPrapAjaxEditComments->localizationName) ?>");
  	//todo - Possibly do something on failure here
    s.success = function(r) {
    	var res = wpAjax.parseAjaxResponse(r, s.response,s.element);
      //Add event for save button
      var error = false;
      $j("#save").bind("click", function() { save_comment(s); return false; });
      $j.each( res.responses, function() {
        	if (this.what == "error") { //error
          	error = true;
          	$j("#status").attr("class", "error");
            $j("#message").html(this.data);
            $j("#close-option").show();
            //remove event for save button
            $j("#save").unbind("click");
          } else { //success
            //Load content
            switch(this.what) {
              case "comment_content":
               	$j("#comment").html(this.data); //For everyone else
                $j("#comment").attr("value",this.data); //For Opera
                break;
              case "comment_author":
                $j("#name").attr("value", this.data);
                break;
              case "comment_author_email":
                $j("#e-mail").attr("value", this.data);
                break;
              case "comment_author_url":
                $j("#URL").attr("value", this.data);
                break;
              case "gravatar":
                $j("#gravatar").html(this.data).show();
                break;
              <?php do_action('wp_ajax_comments_js_load_comment'); ?>
          	}
          }
      });
      if (!error) {
      	//Enable the buttons
        $j("#save, #cancel").removeAttr("disabled");
      	//Update status message
        $j("#status").attr("class", "success");
        $j("#message").html("<?php _e('Comment Loaded Successfully', $WPrapAjaxEditComments->localizationName) ?>");
      }
     
    }
    $j.ajax(s);
  } //end load_comment
  function save_comment(data) {
  	//Update status message
    $j("#status").attr("class", "success");
    $j("#message").html("<?php _e('Saving...', $WPrapAjaxEditComments->localizationName) ?>");
    $j("#save").attr("disabled", "disabled");
  	data.action = "savecomment";
    var error = false;
    //Read in dom values
    var name = encodeURIComponent($j("#name").attr("value"));
    var email = encodeURIComponent($j("#e-mail").attr("value"));
    var url = encodeURIComponent($j("#URL").attr("value"));
    var comment = encodeURIComponent($j("#comment").attr("value")); 
    var nonce = $j("#_wpnonce").attr("value");
    data.data = $j.extend({ comment_content: comment, comment_author: name, comment_author_email: email, comment_author_url: url,action: data.action, cid: data.cid,pid:data.pid, _wpnonce: nonce },'');
    data.data = $j.extend(data.data, {mycode: "value", mycode2: "value"}); //Extend example
    <?php do_action('wp_ajax_comments_js_save_before'); ?>
    //todo - possibly do something with failure also
    data.success = function(r) {
    	var res = wpAjax.parseAjaxResponse(r, data.response,data.element);
    	$j.each( res.responses, function() {
        	if (this.what == "error") { //error
          	error = true;
            $j("#save").removeAttr("disabled");
          	$j("#status").attr("class", "error");
            $j("#message").html(this.data);
            $j("#close-option").show();
          } else { //success 
          	switch(this.what) {
              case "comment_content":
              	comment = this.data;
                break;
              case "comment_author":
                name = this.data;
                break;
              case "comment_author_url":
                url = this.data;
                break;
               <?php do_action('wp_ajax_comments_js_save_after'); ?>
          	}
          }
      });
      if (!error) {
      	try {
        	self.parent.AjaxEditComments.update_comment("edit-comment" + data.cid,comment);
          self.parent.AjaxEditComments.update_author("edit-author" + data.cid,name, url);
        } catch (err) {}
      	$j("#status").attr("class", "success");
        $j("#message").html("<?php _e('Comment Successfully Saved', $WPrapAjaxEditComments->localizationName) ?>");
        self.parent.tb_remove(); 
        //$j("#close-option").show();
      }
    };
    $j.ajax(data);
  }
  //Cookie code conveniently stolen from http://www.quirksmode.org/js/cookies.html
	function createCookie(name,value,days) {
    if (days) {
      var date = new Date();
      date.setTime(date.getTime()+(days*24*60*60*1000));
      var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
	}
  function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
  }
	return {
			init : function() { 
      	initialize_events();
        load_comment();
			}
	};
	
}();