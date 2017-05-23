;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.articlePages',{_currentPage:1,_pages:null,_articleID:'',initialize:function(){this.on('paginationClicked',this.paginationClicked);History.Adapter.bind(window,'statechange',_.bind(this.stateChange,this));this.setup();},setup:function(){this._articleID=this._getArticleID();this._setupPages();},stateChange:function(){var state=History.getState();if(_.isUndefined(state.data.controller)||state.data.controller!='article-'+this._articleID){return;}
var newPage=parseInt(state.data['page'+this._articleID]);if(_.isUndefined(this._pages[newPage-1])){return;}
this._pages.hide();this._currentPage=newPage;ips.utils.anim.go('fadeIn',$(this._pages[newPage-1]));this._checkButtons();},paginationClicked:function(e,data){if(data.originalEvent){data.originalEvent.preventDefault();data.originalEvent.stopPropagation();}
e.stopPropagation();var urlData={controller:'article-'+this._articleID};if(data.pageNo=='next'){urlData['page'+this._articleID]=this._currentPage+1;}else{urlData['page'+this._articleID]=this._currentPage-1;}
var url=this._buildURL(urlData['page'+this._articleID]);History.pushState(urlData,document.title,url);},_getArticleID:function(){if(this.scope.attr('data-articleID')){return this.scope.attr('data-articleID');}else if(this.scope.closest('[data-commentID]')){return'comment'+this.scope.closest('[data-commentID]').attr('data-commentID');}else{return this.scope.identify().attr('id');}},_buildURL:function(pageNo){var urlObj=ips.utils.url.getURIObject();var url=urlObj.protocol+'://'+urlObj.host+(urlObj.port?(':'+urlObj.port):'')+urlObj.path+'?';urlObj.queryKey['page'+this._articleID]=pageNo;var params=_.clone(urlObj.queryKey);if(urlObj.file=='index.php'){_.each(params,function(val,key){if(key.startsWith('/')){url+=key;delete params[key];}});url+='&';}
if(!_.isEmpty(params)){url+=$.param(params);}
return url;},_checkButtons:function(){var indexedPage=this._currentPage-1;this.scope.find('.ipsPagination_prev').toggle(!(indexedPage<=0));this.scope.find('.ipsPagination_next').toggle(!(indexedPage>=(this._pages.length-1)));},_setupPages:function(){this._pages=this.scope.find('[data-role="contentPage"]');if(this._pages.length<2){return;}
this.scope.prepend(ips.templates.render('core.pagination'));this.scope.append(ips.templates.render('core.pagination'));this._pages.hide();if(!_.isUndefined(ips.utils.url.getParam('page'+this._articleID))){this._currentPage=parseInt(ips.utils.url.getParam('page'+this._articleID));}
$(this._pages[this._currentPage-1]).show();this._checkButtons();this.scope.find('[data-role="contentPageBreak"]').hide();$(document).trigger('contentChange',[this.scope]);}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.coverPhoto',{_image:null,_repositioning:false,_existingPosition:0,_tooltip:null,initialize:function(){if(this.scope.closest('.cWidgetContainer').length)
{$('#elEditPhoto').hide();}
var self=this;this.on('menuItemSelected',function(e,data){switch($(data.originalEvent.target).attr('data-action')){case'removeCoverPhoto':self.removePhoto(data);break;case'positionCoverPhoto':self.positionPhoto(data.originalEvent);break;}});this.on('click','[data-action="savePosition"]',this.savePosition);this.on('click','[data-action="cancelPosition"]',this.cancelPosition);$(window).on('resize',_.bind(this.resizeWindow,this));this.setup();},setup:function(){this._image=this.scope.find('.ipsCoverPhoto_photo');this._image.css({opacity:0.0001});this._offset=this.scope.attr('data-coverOffset')||0;if(this._image.length){this._image.imagesLoaded(_.bind(this._positionImage,this));}
var doPosition=ips.utils.url.getParam('_position');if(!_.isUndefined(doPosition)){this.positionPhoto();}
this.scope.find('a[data-action="positionCoverPhoto"]').parent().removeClass('ipsHide');},resizeWindow:function(){this._positionImage();},removePhoto:function(data){data.originalEvent.preventDefault();var self=this;ips.ui.alert.show({type:'confirm',icon:'warn',message:ips.getString('confirmRemoveCover'),callbacks:{ok:function(){ips.getAjax()($(data.originalEvent.target).attr('href')+'&wasConfirmed=1').done(function(){ips.utils.anim.go('fadeOut',self._image).done(function(){ips.ui.flashMsg.show(ips.getString('removeCoverDone'));});data.menuElem.find('[data-role="photoEditOption"]').hide();}).fail(function(err){window.location=$(data.originalEvent.target).attr('href');});}}});},savePosition:function(e){e.preventDefault();var natHeight=ips.utils.position.naturalHeight(this._image);var realHeight=this._image.outerHeight();var topPos=parseInt(this._image.css('top'))*-1;var percentage=(topPos / realHeight)*100;var newOffset=Math.floor((natHeight / 100)*percentage);this._offset=newOffset;this.scope.attr('data-coverOffset',newOffset);ips.getAjax()(this.scope.attr('data-url')+'&do=coverPhotoPosition'+'&offset='+newOffset).fail(function(err){this.scope.attr('data-url')+'&do=coverPhotoPosition'+'&offset='+newOffset;});this._resetImage();},cancelPosition:function(e){e.preventDefault();this._image.css({top:this._existingPosition+'px',});this._resetImage();},positionPhoto:function(e){if(!_.isUndefined(e)){e.preventDefault();}
var self=this;this.scope.find('[data-hideOnCoverEdit]').css({visibility:'hidden'});this._image.css({cursor:'move'});this._repositioning=true;this._existingPosition=parseInt(this._image.css('top'))||0;this.scope.find('.ipsCoverPhoto_container').append(ips.templates.render('core.coverPhoto.controls'));this._showTooltip();ips.loader.get(['core/interface/jquery/jquery-ui.js']).then(function(){self._image.draggable({axis:'y',scroll:false,stop:_.bind(self._dragStop,self)});});},_positionImage:function(){if(!this._image.length){return;}
var natHeight=ips.utils.position.naturalHeight(this._image);var realHeight=this._image.outerHeight();if(this._offset===0){this._image.animate({opacity:1},'fast');return;}
var percentage=((this._offset*1)/ natHeight)*100;var adjustedOffset=Math.floor((realHeight / 100)*percentage);this._image.css({position:'absolute',left:0,top:(adjustedOffset*-1)+'px',}).animate({opacity:1},'fast');},_resetImage:function(){if(this._image.draggable){this._image.draggable('destroy');}
this._image.css({cursor:'default'});this.scope.find('.ipsCoverPhoto_container [data-role="coverPhotoControls"]').remove();this.scope.find('[data-hideOnCoverEdit]').css({visibility:'visible',opacity:0.01}).animate({opacity:1});this._hideTooltip();History.pushState("",document.title,this.scope.attr('data-url'));},_showTooltip:function(msg){if(!this._tooltip){this._buildTooltip();}
this._tooltip.hide().text(ips.getString('dragCoverPhoto'));this._positionTooltip();},_hideTooltip:function(){if(this._tooltip&&this._tooltip.is(':visible')){ips.utils.anim.go('fadeOut',this._tooltip);}},_positionTooltip:function(){var positionInfo={trigger:this.scope.find('.ipsCoverPhoto_container'),target:this._tooltip,center:true,above:true};var tooltipPosition=ips.utils.position.positionElem(positionInfo);this._tooltip.css({left:tooltipPosition.left+'px',top:tooltipPosition.top+'px',position:(tooltipPosition.fixed)?'fixed':'absolute',zIndex:ips.ui.zIndex()});if(tooltipPosition.location.vertical=='top'){this._tooltip.addClass('ipsTooltip_top');}else{this._tooltip.addClass('ipsTooltip_bottom');}
this._tooltip.show();},_buildTooltip:function(){var tooltipHTML=ips.templates.render('core.tooltip',{id:'elCoverPhotoTooltip'});ips.getContainer().append(tooltipHTML);this._tooltip=$('#elCoverPhotoTooltip');},_dragStop:function(){var imageTop=parseInt(this._image.css('top'));if(imageTop>0){this._image.css({top:0,bottom:'auto',position:'absolute'});}else{var containerHeight=this.scope.find('.ipsCoverPhoto_container').outerHeight();var imageHeight=this._image.outerHeight();if((imageTop+imageHeight)<containerHeight){this._image.css({top:'auto',bottom:0,position:'absolute'});}}}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.followButton',{initialize:function(){this.setup();this.on(document,'followingItem',this.followingItemChange);},setup:function(){this._app=this.scope.attr('data-followApp');this._area=this.scope.attr('data-followArea');this._id=this.scope.attr('data-followID');this._feedID=this._area+'-'+this._id;this._button=this.scope.find('[data-role="followButton"]');},followingItemChange:function(e,data){if(data.feedID==this._feedID){this._reloadButton();}},_reloadButton:function(){this._button.addClass('ipsFaded ipsFaded_more');var self=this;var pos=ips.utils.position.getElemPosition(this._button);var dims=ips.utils.position.getElemDims(this._button);this.scope.append(ips.templates.render('core.follow.loading'));this.scope.css({position:'relative'}).find('.ipsLoading').css({width:dims.outerWidth+'px',height:dims.outerHeight+'px',top:0,left:0,position:'absolute',zIndex:ips.ui.zIndex()});ips.getAjax()(ips.getSetting('baseURL')+'index.php?app=core&module=system&controller=notifications&do=button',{data:_.extend({follow_app:this._app,follow_area:this._area,follow_id:this._id},(this.scope.attr('data-buttonType'))?{button_type:this.scope.attr('data-buttonType')}:{})}).done(function(response){self.scope.html(response);$(document).trigger('contentChange',[self.scope]);if($('input[data-toggle-id="auto_follow_toggle"]').length){var val=self.scope.find('[data-role="followButton"]').attr('data-following');if(val=='false'&&$('input[data-toggle-id="auto_follow_toggle"]').is(':checked')){$('input[data-toggle-id="auto_follow_toggle"]').prop('checked',false).change();}else if(val=='true'&&!$('input[data-toggle-id="auto_follow_toggle"]').is(':checked')){$('input[data-toggle-id="auto_follow_toggle"]').prop('checked',true).change();}}}).fail(function(){self._button.removeClass('ipsFaded ipsFaded_more');}).always(function(){self.scope.find('.ipsLoading').remove();});}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.followForm',{initialize:function(){this.on('submit',this.submitForm);this.on('click','[data-action="unfollow"]',this.unfollow);this.setup();},setup:function(){this._app=this.scope.attr('data-followApp');this._area=this.scope.attr('data-followArea');this._id=this.scope.attr('data-followID');},unfollow:function(e){e.preventDefault();this._doFollowAction($(e.currentTarget).attr('href'),{},true);},submitForm:function(e){e.preventDefault();this._doFollowAction(this.scope.attr('action'),this.scope.serialize(),false);},_doFollowAction:function(url,data,unfollow){var self=this;var dims=ips.utils.position.getElemDims(this.scope.parent('div'));this.scope.hide().parent('div').css({width:dims.outerWidth+'px',height:dims.outerHeight+'px'}).addClass('ipsLoading');ips.getAjax()(url,{data:data,type:'post'}).done(function(response){if(unfollow){self.trigger('followingItem',{feedID:self._area+'-'+self._id,unfollow:true});}else{self.trigger('followingItem',{feedID:self._area+'-'+self._id,notificationType:self.scope.find('[name="follow_type"]:checked').val(),anonymous:!self.scope.find('[name="follow_public_checkbox"]').is(':checked')});}
ips.ui.flashMsg.show(ips.getString('followUpdated'));}).fail(function(jqXHR,textStatus,errorThrown){window.location=url;}).always(function(){self.scope.parents('.ipsHovercard').remove();});}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.guestTerms',{initialize:function(){this.on('click','[data-action="dismissTerms"]',this.dismissBar);this.setup();},setup:function(){this.scope.toggle(!ips.utils.cookie.get('guestTermsDismissed'));$('body').toggleClass('cWithGuestTerms',!ips.utils.cookie.get('guestTermsDismissed'));},dismissBar:function(e){e.preventDefault();var self=this;ips.utils.cookie.set('guestTermsDismissed',1,true);self.scope.animate({opacity:0},'fast',function(){$('body').removeClass('cWithGuestTerms');if(self.scope.is('[data-ipsSticky]')){ips.ui.sticky.destruct(self.scope);}
self.scope.remove();});}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.moderation',{_editTimeout:0,_editingTitle:false,initialize:function(){this.on('submit','[data-role="moderationTools"]',this.moderationSubmit);this.on('mousedown','[data-role="editableTitle"]',this.editTitleMousedown);this.on('mouseup mouseleave','[data-role="editableTitle"]',this.editTitleMouseup);this.on('click','[data-role="editableTitle"]',this.editTitleMouseclick);},editTitleMousedown:function(e){var self=this;if(e.which!==1){return;}
this._editTimeout=setTimeout(function(){self._editingTitle=true;clearTimeout(this._editTimeout);var anchor=$(e.currentTarget);anchor.hide();var inputNode=$('<input/>').attr({type:'text'}).attr('data-role','editTitleField').val(anchor.text().trim());anchor.after(inputNode);inputNode.focus();inputNode.on('blur',function(){inputNode.addClass('ipsField_loading');ips.getAjax()(anchor.attr('href'),{method:'post',data:{do:'ajaxEditTitle',newTitle:inputNode.val()}}).done(function(response){anchor.text(inputNode.val());}).fail(function(response){ips.ui.alert.show({type:'alert',icon:'warn',message:response.responseJSON,});}).always(function(){inputNode.remove();anchor.show();self._editingTitle=false;});});inputNode.on('keypress',function(e){if(e.keyCode==ips.ui.key.ENTER){e.stopPropagation();e.preventDefault();inputNode.blur();return false;}});inputNode.on('keydown',function(e){if(e.keyCode==ips.ui.key.ESCAPE){inputNode.remove();anchor.show();self._editingTitle=false;return false;}});},1000);},editTitleMouseup:function(e){clearTimeout(this._editTimeout);},editTitleMouseclick:function(e){if(this._editingTitle){e.preventDefault();}},moderationSubmit:function(e){if(this._editingTitle){e.preventDefault();}
var action=this.scope.find('[data-role="moderationAction"]').val();switch(action){case'delete':this._modActionDelete(e);break;case'move':this._modActionDialog(e,'move','narrow');break;case'hide':this._modActionDialog(e,'hide','narrow');break;case'split':this._modActionDialog(e,'split','wide');break;case'merge':this._modActionDialog(e,'merge','medium');break;default:$(document).trigger('moderationSubmitted');break;}},_modActionDelete:function(e){var self=this;var form=this.scope.find('[data-role="moderationTools"]');if(self._bypassDeleteCheck){return;}
e.preventDefault();var count=parseInt(this.scope.find('[data-role="moderation"]:checked').length)+parseInt(this.scope.find('[data-role="moderation"]:hidden').length);ips.ui.alert.show({type:'confirm',icon:'warn',message:(count>1)?ips.pluralize(ips.getString('delete_confirm_many'),count):ips.getString('delete_confirm'),callbacks:{ok:function(){$(document).trigger('moderationSubmitted');self._bypassDeleteCheck=true;self.scope.find('[data-role="moderationTools"]').submit();}}});},_modActionDialog:function(e,title,size){e.preventDefault();var form=this.scope.find('[data-role="moderationTools"]');var moveDialog=ips.ui.dialog.create({url:form.attr('action')+'&'+form.serialize().replace(/%5B/g,'[').replace(/%5D/g,']'),modal:true,title:ips.getString(title),forceReload:true,size:size});moveDialog.show();$(document).trigger('moderationSubmitted');}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.poll',{initialize:function(){this.on('submit','form',this.submitPoll);this.on('click','[data-action="viewResults"]',this.viewResults);},viewResults:function(e){e.preventDefault();var url=$(e.currentTarget).attr('href')+'&fetchPoll=1&viewResults=1';if($(e.currentTarget).attr('data-viewResults-confirm')){var self=this;ips.ui.alert.show({type:'confirm',icon:'warn',message:ips.getString('generic_confirm'),subText:ips.getString('warn_allow_result_view'),callbacks:{ok:function(){self._viewResults(url+'&nullVote=1');}}});}else{this._viewResults(url);}},_viewResults:function(url){var self=this;self._setContentsLoading();ips.getAjax()(url).done(function(response){self.cleanContents();self.scope.html(response);$(document).trigger('contentChange',[self.scope]);});},_setContentsLoading:function(){var container=this.scope.find('[data-role="pollContents"]');var height=container.outerHeight();container.css({height:height+'px'}).html('').addClass('ipsLoading');},submitPoll:function(e){var form=$(e.currentTarget);if(form.attr('data-bypassAjax')){return}
e.preventDefault();var url=form.attr('action');var self=this;this.scope.find('button[type="submit"]').prop('disabled',true).text(ips.getString('votingNow'));if(url.match(/\?/)){url+='&';}else{url+='?';}
ips.getAjax()(url+'fetchPoll=1',{data:form.serialize(),type:'POST'}).done(function(response){self.cleanContents();self.scope.html(response);$(document).trigger('contentChange',[self.scope]);ips.ui.flashMsg.show(ips.getString('thanksForVoting'));}).fail(function(){form.attr('data-bypassAjax',true).submit();});}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.pollEditor',{initialize:function(){this.on('click','[data-action="removeChoice"]',this.removeChoice);this.on('click','[data-action="addChoice"]',this.addChoice);this.on('click','[data-action="addQuestion"]',this.addQuestion);this.on('click','[data-action="removeQuestion"]',this.removeQuestion);this.setup();},setup:function(){this._maxQuestions=this.scope.attr('data-maxQuestions');this._maxChoices=this.scope.attr('data-maxChoices');this._name=this.scope.attr('data-pollName');this._showCounts=this.scope.attr('data-showCounts')==='false'?false:true;var pollData=ips.getSetting('pollData');if(_.isArray(pollData)&&pollData.length){for(var i=0;i<pollData.length;i++){this._buildQuestion(pollData[i],i+1);}}else if(_.isObject(pollData)){for(var i in pollData){this._buildQuestion(pollData[i],i);}}else{this._addQuestion(1);this._checkQuestionButton();this._checkChoiceButton(this.scope.find('[data-questionID="1"]'));}},addQuestion:function(e){e.preventDefault();var maxQid=_.max(this.scope.find('[data-questionID]'),function(item){return parseInt($(item).attr('data-questionID'));});maxQid=parseInt($(maxQid).attr('data-questionID'));if(!_.isNumber(maxQid)||_.isNaN(maxQid)){maxQid=0;}
if(maxQid>=this._maxQuestions){ips.ui.alert.show({type:'alert',icon:'warn',message:ips.getString('noMoreQuestionsMlord'),callbacks:{ok:$.noop}});return;}
this._addQuestion(maxQid+1);ips.utils.anim.go('fadeIn',this.scope.find('[data-questionID="'+(maxQid+1)+'"]'));this._checkQuestionButton();},removeQuestion:function(e){e.preventDefault();var self=this;var question=$(e.currentTarget).closest('[data-questionid]');var removeQuestion=function(){question.remove();self._checkQuestionButton();};if(question.find('[data-role="questionTitle"]').val()!==''){ips.ui.alert.show({type:'confirm',icon:'question',message:ips.getString('removeQuestionConfirm'),callbacks:{ok:removeQuestion}});}else{removeQuestion();}},addChoice:function(e){e.preventDefault();var question=$(e.currentTarget).closest('[data-questionID]');var maxCid=_.max(question.find('[data-choiceID]'),function(item){return parseInt($(item).attr('data-choiceID'));});maxCid=parseInt($(maxCid).attr('data-choiceID'));if(!_.isNumber(maxCid)||_.isNaN(maxCid)){maxCid=0;}
if(maxCid>=this._maxChoices){ips.ui.alert.show({type:'alert',icon:'warn',message:ips.getString('noMoreChoices'),callbacks:{ok:$.noop}});return;}
this._addChoice(question,maxCid+1);ips.utils.anim.go('fadeIn',question.find('[data-choiceID="'+(maxCid+1)+'"]'));this._checkChoiceButton(question);},removeChoice:function(e){e.preventDefault();var self=this;var choice=$(e.currentTarget).closest('[data-choiceID]');var question=choice.closest('[data-questionID]');if(question.find('[data-choiceID]').length<=2){ips.ui.alert.show({type:'alert',icon:'warn',message:ips.getString('cantRemoveOnlyChoice'),callbacks:{ok:$.noop}});return;}
choice.animationComplete(function(){choice.remove();_.each(question.find('[data-choiceID]'),function(item,idx){$(item).attr('data-choiceID',idx+1).find('[data-role="choiceNumber"]').text(idx+1);});self._checkChoiceButton(question);});ips.utils.anim.go('fadeOut fast',choice);},_buildQuestion:function(data,qid){var choices=[];if(_.isArray(data.choices)&&data.choices.length){for(var i=0;i<data.choices.length;i++){choices.push(this._getChoiceHTML(i+1,qid,data.choices[i].title,data.choices[i].count));}}else if(_.isObject(data.choices)){for(var i in data.choices){choices.push(this._getChoiceHTML(i,qid,data.choices[i].title,data.choices[i].count));}}
this.scope.find('[data-role="pollContainer"]').append(ips.templates.render('core.pollEditor.question',{pollName:this._name,multiChoice:data.multiChoice,questionID:qid,question:data.title,choices:choices.join(''),removeQuestion:!(qid===1)}));},_addQuestion:function(qid){var choices=[];choices.push(this._getChoiceHTML(1,qid));choices.push(this._getChoiceHTML(2,qid));this.scope.find('[data-role="pollContainer"]').append(ips.templates.render('core.pollEditor.question',{pollName:this._name,questionTitle:ips.getString('questionTitle',{id:qid}),questionID:qid,showCounts:this._showCounts,choices:choices.join(''),removeQuestion:!(qid===1)}));},_addChoice:function(question,cid){var html=this._getChoiceHTML(cid,question.attr('data-questionID'),'');question.find('[data-role="choices"]').append(html);},_getChoiceHTML:function(cid,qid,name,count){return ips.templates.render('core.pollEditor.choice',{choiceID:cid,questionID:qid,pollName:this._name,choiceTitle:name,showCounts:this._showCounts,hideCounts:!this._showCounts,count:count});},_checkQuestionButton:function(){var questions=this.scope.find('[data-questionID]');this.scope.find('[data-action="addQuestion"]').toggleClass('ipsButton_disabled ipsFaded',(questions.length>=this._maxQuestions));},_checkChoiceButton:function(questionScope){var choices=questionScope.find('[data-choiceID]');questionScope.find('[data-action="addChoice"]').toggleClass('ipsButton_disabled ipsFaded',(choices.length>=this._maxChoices));questionScope.find('[data-choiceID] [data-action="removeChoice"]').toggleClass('ipsButton_disabled ipsFaded',(choices.length===2));}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.reviewForm',{initialize:function(){this.on('click','[data-action="writeReview"]',this.toggleReview);},toggleReview:function(e){e.preventDefault();this.scope.find('[data-role="reviewIntro"]').hide();this.scope.find('[data-role="reviewForm"]').show();}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.statusFeedWidget',{initialize:function(){this.on('editorWidgetInitialized','[data-role="statusFormArea"]',this.editorReady);this.on('focus','[data-role="statusFormArea"] .ipsComposeArea_dummy',this.focusNewStatus);this.on('submit','[data-role="statusFormArea"] form',this.submitNewStatus);this.setup();},setup:function(){},focusNewStatus:function(e){e.preventDefault();var self=this;$(e.currentTarget).text(ips.getString('loading')+"...");ips.getAjax()(ips.getSetting('baseURL')+'index.php?app=core&module=status&controller=ajaxcreate').done(function(response){self.scope.find('[data-role="statusEditor"]').html(response);$(document).trigger('contentChange',[self.scope.find('[data-role="statusEditor"]')]);});},editorReady:function(e,data){this.scope.find('[data-role="statusEditor"]').show();this.scope.find('[data-role="statusDummy"]').hide().find('.ipsComposeArea_dummy').text(ips.getString('whatsOnYourMind'));try{CKEDITOR.instances[data.id].focus();}catch(err){Debug.log(err);}},submitNewStatus:function(e){e.preventDefault();var self=this;var form=$(e.currentTarget);form.find('button[type="submit"]').prop('disabled',true).text(ips.getString('updatingStatus'));ips.getAjax()(form.attr('action'),{data:form.serialize(),type:'post',bypassRedirect:true}).done(function(response){var newStatus=$(response.content);self.scope.find('[data-role="statusDummy"]').show();self.scope.find('[data-role="statusEditor"]').hide();self.scope.find('[data-role="statusFeedEmpty"]').hide();self.scope.find('[data-role="statusFeed"]').prepend(newStatus).find('[data-statusID="'+response.id+'"]').hide().slideDown();$(document).trigger('contentChange',[self.scope.find('[data-role="statusFeed"]')]);}).always(function(){form.find('button[type="submit"]').prop('disabled',false).text(ips.getString('submitStatus'));});}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.tagEditor',{_minTags:null,_maxTags:null,_count:0,_tagEditID:'',initialize:function(){this.on('click','[data-action="removeTag"]',this.removeTag);this.on(document,'tagsUpdated',this.tagsUpdated);this.setup();},setup:function(){this._tagEditID=this.scope.attr('data-tagEditID');this._minTags=this.scope.attr('data-minTags')||null;this._maxTags=this.scope.attr('data-maxTags')||null;this._setCount();this._checkMinMax();},tagsUpdated:function(e,data){if(data.tagEditID!==this._tagEditID){return;}
this.scope.find('.ipsTag').closest('li').remove();this.scope.prepend(data.tags);var editablePrefix=$('body').find('[data-editablePrefix]');if(editablePrefix.length){if(data.prefix){editablePrefix.html(data.prefix).removeClass('ipsHide');}else{editablePrefix.html('').addClass('ipsHide');}}
this._setCount();this._checkMinMax();ips.ui.flashMsg.show(ips.getString('tagsUpdated'));},removeTag:function(e){e.preventDefault();var self=this;var remove=$(e.currentTarget);var url=remove.attr('href');var tagContainer=remove.closest('li');var tag=tagContainer.find('.ipsTag');tagContainer.fadeOut('fast');this._count--;this._checkMinMax();ips.getAjax()(url,{bypassRedirect:true}).done(function(){ips.ui.flashMsg.show(ips.getString('tagRemoved'));setTimeout(function(){tagContainer.remove();},200);}).fail(function(jqXHR,textStatus,errorThrown){tagContainer.stop().show().css({opacity:1});self._count++;if(jqXHR.responseJSON){ips.ui.alert.show({type:'alert',icon:'warn',message:jqXHR.responseJSON,callbacks:{}});}});},_checkMinMax:function(){var allowRemove=!(this._minTags&&this._count<=this._minTags);this.scope.find('[data-action="removeTag"]').toggle(allowRemove).end().find('.ipsTags_deletable').toggleClass('ipsTags_deletable',allowRemove);this.scope.find('.ipsTags_edit').toggle(!(this._maxTags&&this._count>=this._maxTags));},_setCount:function(){var prefix=this._getPrefix();var count=this.scope.find('.ipsTag').length;if(prefix.length&&prefix.is(':visible')){count++;}
this._count=count;},_getPrefix:function(){return $('body').find('[data-editablePrefix]');}});}(jQuery,_));;
;(function($,_,undefined){"use strict";ips.controller.register('core.front.core.tagEditorForm',{_placeholder:null,_formLoaded:false,_menuID:'',_tagEditID:'',initialize:function(){this.on(document,'menuOpened',this.menuOpened);this.on('submit','form',this.submitForm);this.setup();},setup:function(){this._menuID=this.scope.closest('.ipsMenu').attr('id').replace('_menu','');this._tagEditID=this._menuID.replace('elTagEditor_','');},menuOpened:function(e,data){if(data.elemID!=this._menuID){return;}
if(this._formLoaded){this._setLoading(true);}
this._formLoaded=true;var self=this;var url=$(data.originalEvent.currentTarget).attr('href');ips.getAjax()(url).done(function(response){self._setLoading(false);self.scope.html(response);$(document).trigger('contentChange',[self.scope]);}).fail(function(){window.location=url;});},submitForm:function(e){e.preventDefault();var self=this;var form=$(e.currentTarget);var autoComplete=this.scope.find('[data-ipsAutocomplete]');autoComplete.trigger('blur');setTimeout(function(){if(ips.ui.autocomplete.getObj(autoComplete).hasErrors()){e.preventDefault();return;}
self._setLoading(true);ips.getAjax()(form.attr('action'),{type:'post',data:form.serialize(),dataType:'json'}).done(function(response){self.scope.trigger('tagsUpdated',{tagEditID:self._tagEditID,tags:response.tags,prefix:response.prefix});self.scope.trigger('closeMenu');setTimeout(function(){self._setLoading(false);},200);}).fail(function(jqXHR,textStatus,errorThrown){if(jqXHR.responseJSON){ips.ui.alert.show({type:'alert',icon:'warn',message:jqXHR.responseJSON,callbacks:{}});}});},500);},_setLoading:function(loading){if(loading){if(!this._placeholder){this._buildPlaceholder();}
var width=this.scope.outerWidth();var height=this.scope.outerHeight();this.scope.hide();this._placeholder.show().css({width:width+'px',height:height+'px'});}else{if(this._placeholder){this._placeholder.hide();this.scope.show();}}},_buildPlaceholder:function(){this._placeholder=$('<div/>').addClass('ipsLoading').hide();this.scope.after(this._placeholder);}});}(jQuery,_));;