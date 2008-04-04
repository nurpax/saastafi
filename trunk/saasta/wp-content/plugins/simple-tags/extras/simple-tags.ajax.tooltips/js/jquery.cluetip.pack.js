(function($){var $cluetip,$cluetipInner,$cluetipOuter,$cluetipTitle,$cluetipArrows,$dropShadow,imgCount;$.fn.cluetip=function(options){var defaults={width:275,height:'auto',cluezIndex:97,positionBy:'auto',topOffset:15,leftOffset:15,local:false,hideLocal:true,attribute:'rel',titleAttribute:'title',splitTitle:'',showTitle:true,cluetipClass:'default',hoverClass:'',waitImage:true,cursor:'help',arrows:false,dropShadow:true,dropShadowSteps:6,sticky:false,mouseOutClose:false,activation:'hover',closePosition:'top',closeText:'Close',truncate:0,fx:{open:'show',openSpeed:''},hoverIntent:{sensitivity:3,interval:50,timeout:0},onActivate:function(e){return true},onShow:function(ct,c){},ajaxCache:true,ajaxProcess:function(data){data=$(data).not('style, meta, link, script, title');return data},ajaxSettings:{dataType:'html'}};if(options&&options.ajaxSettings){$.extend(defaults.ajaxSettings,options.ajaxSettings);delete options.ajaxSettings}if(options&&options.fx){$.extend(defaults.fx,options.fx);delete options.fx}if(options&&options.hoverIntent){$.extend(defaults.hoverIntent,options.hoverIntent);delete options.hoverIntent}$.extend(defaults,options);return this.each(function(){var cluetipContents=false;var cluezIndex=parseInt(defaults.cluezIndex,10)-1;var isActive=false;if(!$cluetip){$cluetipInner=$('<div id="cluetip-inner"></div>');$cluetipTitle=$('<h3 id="cluetip-title"></h3>');$cluetipOuter=$('<div id="cluetip-outer"></div>').append($cluetipInner).prepend($cluetipTitle);$cluetip=$('<div></div>').attr({'id':'cluetip'}).css({zIndex:defaults.cluezIndex}).append($cluetipOuter).append('<div id="cluetip-extra"></div>')[insertionType](insertionElement).hide();$('<div id="cluetip-waitimage"></div>').css({position:'absolute',zIndex:cluezIndex-1}).insertBefore('#cluetip').hide();$cluetip.css({position:'absolute',zIndex:cluezIndex});$cluetipOuter.css({position:'relative',zIndex:cluezIndex+1});$cluetipArrows=$('<div id="cluetip-arrows" class="cluetip-arrows"></div>').css({zIndex:cluezIndex+1}).appendTo('#cluetip')}var dropShadowSteps=(defaults.dropShadow)?+defaults.dropShadowSteps:0;if(!$dropShadow){$dropShadow=$([]);for(var i=0;i<dropShadowSteps;i++){$dropShadow=$dropShadow.add($('<div></div>').css({zIndex:cluezIndex-i-1,opacity:.1,top:1+i,left:1+i}))};$dropShadow.css({position:'absolute',backgroundColor:'#000'}).prependTo($cluetip)}var $this=$(this);var tipAttribute=$this.attr(defaults.attribute),ctClass=defaults.cluetipClass;if(!tipAttribute&&!defaults.splitTitle)return true;if(defaults.local&&defaults.hideLocal){$(tipAttribute+':first').hide()}var tOffset=parseInt(defaults.topOffset,10),lOffset=parseInt(defaults.leftOffset,10);var tipHeight,wHeight;var defHeight=isNaN(parseInt(defaults.height,10))?'auto':(/\D/g).test(defaults.height)?defaults.height:defaults.height+'px';var sTop,linkTop,posY,tipY,mouseY;var tipWidth=parseInt(defaults.width,10)+parseInt($cluetip.css('paddingLeft'))+parseInt($cluetip.css('paddingRight'))+dropShadowSteps;if(isNaN(tipWidth))tipWidth=275;var linkWidth=this.offsetWidth;var linkLeft,posX,tipX,mouseX,winWidth;var tipParts;var tipTitle=(defaults.attribute!='title')?$this.attr(defaults.titleAttribute):'';if(defaults.splitTitle){tipParts=tipTitle.split(defaults.splitTitle);tipTitle=tipParts.shift()}var localContent;var activate=function(event){if(!defaults.onActivate($this)){return false}isActive=true;$cluetip.removeClass().css({width:defaults.width});if(tipAttribute==$this.attr('href')){$this.css('cursor',defaults.cursor)}$this.attr('title','');if(defaults.hoverClass){$this.addClass(defaults.hoverClass)}linkTop=posY=$this.offset().top;linkLeft=$this.offset().left;mouseX=event.pageX;mouseY=event.pageY;if($this[0].tagName.toLowerCase()!='area'){sTop=$(document).scrollTop();winWidth=$(window).width()}if(defaults.positionBy=='fixed'){posX=linkWidth+linkLeft+lOffset;$cluetip.css({left:posX})}else{posX=(linkWidth>linkLeft&&linkLeft>tipWidth)||linkLeft+linkWidth+tipWidth+lOffset>winWidth?linkLeft-tipWidth-lOffset:linkWidth+linkLeft+lOffset;if($this[0].tagName.toLowerCase()=='area'||defaults.positionBy=='mouse'||linkWidth+tipWidth>winWidth){if(mouseX+20+tipWidth>winWidth){posX=(mouseX-tipWidth-lOffset)>=0?mouseX-tipWidth-lOffset:mouseX-(tipWidth/2)}else{posX=mouseX+lOffset}var pY=posX<0?event.pageY+tOffset:event.pageY}$cluetip.css({left:(posX>0&&defaults.positionBy!='bottomTop')?posX:(mouseX+(tipWidth/2)>winWidth)?winWidth/2-tipWidth/2:Math.max(mouseX-(tipWidth/2),0)})}wHeight=$(window).height();tipAttribute=fixTargetAjax(tipAttribute);if(tipParts){for(var i=0;i<tipParts.length;i++){if(i==0){$cluetipInner.html(tipParts[i])}else{$cluetipInner.append('<div class="split-body">'+tipParts[i]+'</div>')}};cluetipShow(pY)}else if(!defaults.local&&tipAttribute.indexOf('#')!=0){if(cluetipContents&&defaults.ajaxCache){$cluetipInner.html(cluetipContents);cluetipShow(pY)}else{var ajaxSettings=defaults.ajaxSettings;ajaxSettings.url=tipAttribute;ajaxSettings.beforeSend=function(){$cluetipOuter.children().empty();if(defaults.waitImage){$('#cluetip-waitimage').css({top:mouseY-10,left:parseInt(posX+(tipWidth/2),10)}).show()}};ajaxSettings.error=function(){if(isActive){$cluetipInner.html('<i>sorry, the contents could not be loaded</i>')}};ajaxSettings.success=function(data){cluetipContents=defaults.ajaxProcess(data);if(isActive){$cluetipInner.html(cluetipContents)}};ajaxSettings.complete=function(){imgCount=$('#cluetip-inner img').length;if(imgCount){$('#cluetip-inner img').load(function(){imgCount--;if(imgCount<1){$('#cluetip-waitimage').hide();if(isActive)cluetipShow(pY)}})}else{$('#cluetip-waitimage').hide();if(isActive)cluetipShow(pY)}};$.ajax(ajaxSettings)}}else if(defaults.local){var $localContent=$(tipAttribute+':first');var localCluetip=$.fn.wrapInner?$localContent.wrapInner('<div></div>').children().clone(true):$localContent.html();$.fn.wrapInner?$cluetipInner.empty().append(localCluetip):$cluetipInner.html(localCluetip);cluetipShow(pY)}};var cluetipShow=function(bpY){$cluetip.addClass('cluetip-'+ctClass);if(defaults.truncate){var $truncloaded=$cluetipInner.text().slice(0,defaults.truncate)+'...';$cluetipInner.html($truncloaded)}function doNothing(){};tipTitle?$cluetipTitle.show().html(tipTitle):(defaults.showTitle)?$cluetipTitle.show().html('&nbsp;'):$cluetipTitle.hide();if(defaults.sticky){var $closeLink=$('<div id="cluetip-close"><a href="#">'+defaults.closeText+'</a></div>');(defaults.closePosition=='bottom')?$closeLink.appendTo($cluetipInner):(defaults.closePosition=='title')?$closeLink.prependTo($cluetipTitle):$closeLink.prependTo($cluetipInner);$closeLink.click(function(){cluetipClose();return false});if(defaults.mouseOutClose){$cluetip.hover(function(){doNothing()},function(){$closeLink.trigger('click')})}else{$cluetip.unbind('mouseout')}}var direction='';$cluetipOuter.css({overflow:defHeight=='auto'?'visible':'auto',height:defHeight});tipHeight=defHeight=='auto'?$cluetip.outerHeight():parseInt(defHeight,10);tipY=posY;if(defaults.positionBy=='fixed'){tipY=posY-defaults.dropShadowSteps+tOffset}else if((posX<mouseX&&Math.max(posX,0)+tipWidth>mouseX)||defaults.positionBy=='bottomTop'){if(posY+tipHeight+tOffset>sTop+wHeight&&mouseY-sTop>tipHeight+tOffset){tipY=mouseY-tipHeight-tOffset;direction='top'}else{tipY=mouseY+tOffset;direction='bottom'}}else if(posY+tipHeight+tOffset>sTop+wHeight){tipY=(tipHeight>=wHeight)?sTop:sTop+wHeight-tipHeight-tOffset}else if($this.css('display')=='block'||$this[0].tagName.toLowerCase()=='area'||defaults.positionBy=="mouse"){tipY=bpY-tOffset}else{tipY=posY-defaults.dropShadowSteps}if(direction==''){posX<linkLeft?direction='left':direction='right'}$cluetip.css({top:tipY+'px'}).removeClass().addClass('clue-'+direction+'-'+ctClass).addClass(' cluetip-'+ctClass);if(defaults.arrows){var bgY=(posY-tipY-defaults.dropShadowSteps);$cluetipArrows.css({top:(/(left|right)/.test(direction)&&posX>=0&&bgY>0)?bgY+'px':/(left|right)/.test(direction)?0:''}).show()}else{$cluetipArrows.hide()}$dropShadow.hide();$cluetip.hide()[defaults.fx.open](defaults.fx.open!='show'&&defaults.fx.openSpeed);if(defaults.dropShadow)$dropShadow.css({height:tipHeight,width:defaults.width}).show();defaults.onShow($cluetip,$cluetipInner)};var inactivate=function(){isActive=false;$('#cluetip-waitimage').hide();if(!defaults.sticky){cluetipClose()};if(defaults.hoverClass){$this.removeClass(defaults.hoverClass)}};var cluetipClose=function(){$cluetipOuter.parent().hide().removeClass().end().children().empty();if(tipTitle){$this.attr('title',tipTitle)}$this.css('cursor','');if(defaults.arrows)$cluetipArrows.css({top:''})};if(defaults.activation=='click'||defaults.activation=='toggle'){$this.click(function(event){if($cluetip.is(':hidden')){activate(event)}else{inactivate(event)}this.blur();return false})}else{$this.click(function(){if(tipAttribute==$this.attr('href')){return false}});if($.fn.hoverIntent&&defaults.hoverIntent){$this.hoverIntent({sensitivity:defaults.hoverIntent.sensitivity,interval:defaults.hoverIntent.interval,over:function(event){activate(event)},timeout:defaults.hoverIntent.timeout,out:function(event){inactivate(event)}})}else{$this.hover(function(event){activate(event)},function(event){inactivate(event)})}}})};var insertionType='appendTo',insertionElement='body';$.cluetip={};$.cluetip.setup=function(options){if(options&&options.insertionType&&(options.insertionType).match(/appendTo|prependTo|insertBefore|insertAfter/)){insertionType=options.insertionType}if(options&&options.insertionElement){insertionElement=options.insertionElement}}})(jQuery);