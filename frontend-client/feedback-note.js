!function(e){function t(){var e,t=navigator.appName,o=navigator.userAgent,n=o.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);return n&&null!=(e=o.match(/version\/([\.\d]+)/i))&&(n[2]=e[1]),n=n?[n[1],n[2]]:[t,navigator.appVersion,"-?"]}function o(){return e(document).height()}function n(){return e(document).width()}e(window).resize(function(){a.feedbackButton.refresh()});var i={Views:{},Models:{},Collection:{},Templates:{}},a={};i.Templates.note_small_box='     <div class="feedback_note_wraper">     <div class="noselect button-group feedback_note_small_box" style="left:<%= x_percent * pixel_width %>px;    top:<%= y_percent * pixel_height %>px;" >     <div class="feedback_note_small_box_expand round <%= (owner)?"owner":"" %> <%= (closed == 1)?"closed":"" %>">     <a class ="small button" data-id="<%= id %>" data-width="<%= pixel_width %>" data-height="<%= pixel_height %>"><%= counter %></a></div>     </div>     </div>     ',i.Templates.feedback_note_button='     <div class="feedback_note_wraper">     <div class="noselect feedback_note_side_button feedback_note_center_vertical">     <a class="button">FEED<br>BACK<br>NOTE</a>     </div>     </div>     ',i.Templates.feedback_note_big_box_new='     <div class="feedback_note_wraper">     <div class="panel feedback_note_big_box_new" style="left:<%= x_pos %>px;     top:<%= y_pos %>px;">     <a class="close_button feedback_note_new_note_dismiss"></a>     <div class="noselect button-group feedback_note_small_box <%= arrow_direction %>" >    <div class="round">     <a class="small button feedback_note_small_box_expand"> </a>     </div>     </div>     <div class="popup">     <div class="arrow <%= arrow_direction %>"></div>     <form id="feedback_note_new_note_form">     <fieldset>    <legend>New Note</legend>     <input name="action" type="hidden" value="feedback_api_save"/>     <input name="feedback_note_new_note_url" type="hidden" value="<%= url %>"/>     <input name="feedback_note_new_note_x_percent"     type="hidden" value="<%= x_percent %>"/><input name="feedback_note_new_note_y_percent"     type="hidden" value="<%= y_percent %>"/>     <input name="feedback_note_new_note_pixel_width"     type="hidden" value="<%= pixel_width %>"/>     <input name="feedback_note_new_note_pixel_height"     type="hidden" value="<%= pixel_height %>"/>     <input name="feedback_note_new_note_browser"     type="hidden" value="<%= browser %>"/>     <input name="feedback_note_new_note_user_agent"     type="hidden" value=\'<%= JSON.stringify(user_agent) %>\'/>     <textarea id="feedback_note_new_note_text" name="feedback_note_new_note_text" placeholder="Write your note..."></textarea>     </fieldset>    <fieldset>     <label>Select Category</label>     <select name="feedback_note_new_note_category">     <option value="General">General</option>     <option value="Text change">Text change</option>     <option value="Image change">Image change</option>     <option value="Functional error">Functional error</option>     <option value="Suggestion">Suggestion</option>     <option value="Question">Question</option>     <option value="Idea">Idea</option>     </select> 	</fieldset>     <fieldset>     <input id="feedback_note_new_note_save_button"     class="button large-12 expand" type="button" value="Save"/>     </fieldset>    </form>     </div>     </div>     </div>     </div>     ',i.Templates.feedback_note_big_box_update='     <div class="feedback_note_wraper">     <div class="panel feedback_note_big_box_update" style="left:<%= x_pos %>px;     top:<%= y_pos  %>px;">     <a class="close_button feedback_note_new_note_dismiss"></a>     <div class="popup">     <div class="arrow <%= arrow_direction %>"></div>     <form id="feedback_note_update_note_form_status">     <fieldset>    <input name="action" type="hidden" value="feedback_api_update_status"/>     <input name="feedback_note_update_note_id" type="hidden" value="<%= id %>"/>     <input type="checkbox" name="status" value="1" <%= (closed == 1)?"checked":"" %>> Mark as completed    </fieldset>    </form>     <div class="left user_img">     <div class="round-img">    <%= user_img %>     </div>     </div>     <div class="left">     <p><span>Added by: <%= user_nicename %> an <%= jQuery.timeago(created) %></span><br/>     <span class="browser">Using: <%= browser %></span><br/>     <span class="browser">With Screen size: <%= pixel_width_original %> x <%= pixel_height_original %></span><br/>     <span class="browser">Your Current screen size: <%= pixel_width %> x <%= pixel_height %></span><br/></p>     <% if(!owner) { %>     <p class="note-text"><%= text %></p>     <% } %>     </div>     <div class="clear"></div>     <% if(owner) { %>     <form id="feedback_note_update_note_form">     <fieldset>    <legend>Update Note</legend>     <input name="action" type="hidden" value="feedback_api_update"/>     <input name="feedback_note_update_note_id" type="hidden" value="<%= id %>"/>     <textarea name="feedback_note_update_note_text"><%= text %></textarea>     </fieldset>	<fieldset>    <label>Select Category</label>     <select name="feedback_note_update_note_category">     <option value="General" <%= (category == "General")?"selected":""  %>>General</option>     <option value="Text change" <%= (category == "Text change")?"selected":""  %>>Text change</option>     <option value="Image change" <%= (category == "Image change")?"selected":""  %>>Image change</option>     <option value="Functional error" <%= (category == "Functional error")?"selected":""  %>>Functional error</option>     <option value="Suggestion" <%= (category == "Suggestion")?"selected":""  %>>Suggestion</option>     <option value="Question" <%= (category == "Question")?"selected":""  %>>Question</option>     <option value="Idea" <%= (category == "idea")?"selected":""  %>>Idea</option>     </select>     </fieldset>	<fieldset>    <input id="feedback_note_update_note_button"     class="button large-12 expand" type="button" value="Update"/>     &nbsp;    <a class="button gray feedback_note_new_note_delete">Delete</a>    </fieldset>    </form>    <% } %>     <% if(_.size(comments) > 0) { %>     <hr/>     <legend>Comments</legend>     <ul> 	<% _.each(comments, function(comment) { %> 	<li class="comments-list"> 	<div class="left user_img">     <div class="round-img">    <%= comment.user_img %>     </div>     </div> 	<div class="left">     <strong><%= comment.user_nicename %></strong>     <span class="time-ago"><%= jQuery.timeago(comment.created) %></span><br/>     <p class="note-text"><%= comment.text %></p>     </div> 	</li> 	<% }) %>     </ul>     <% } %>     <hr/>     <form id="feedback_note_new_note_comment_form">     <fieldset>    <input name="action" type="hidden" value="feedback_api_comment_save"/>     <input name="note_id" type="hidden" value="<%= id %>"/>     <textarea name="text" placeholder="Write your comment..."></textarea>     </fieldset>    <fieldset>    <input id="feedback_note_new_note_comment_button"     class="button large-12 expand" type="button" value="Add Comment"/>     </fieldset>    </form>     </div>    </div>    ',i.Templates.feedback_note_menu='     <div class="feedback_note_wraper">     <div class="noselect panel feedback_note_menu feedback_note_center_vertical">     <a class="button expand feedback_note_dismiss_menu">     <p> Click anywhere on the <br/> website to add a note. </p>    </a>     </div>     </div>     ',i.Templates.feedback_grey_bg_layer='     <div class="feedback_note_wraper">     <div class="grey_bg_layer_wrapper">     <div class="grey_bg_layer">     </div>     <div class="mouse-follow">Click to add a Feedback Note</div>     </div>     </div>     ',i.Models.Note=Backbone.Model.extend({defaults:{id:"0"},toJSON:function(){var e=_.clone(this.attributes);return e},createView:function(){this.set("pixel_width_original",this.attributes.pixel_width),this.set("pixel_height_original",this.attributes.pixel_height),this.set("pixel_width",n()),this.set("pixel_height",o());var e=new i.Views.Note({model:this});this.set("noteView",e)},bindDraggable:function(){var e=this.get("noteView");null!=e&&e.bindDraggable()},removeView:function(){this.get("noteView").removeView()},removeUpdateView:function(){this.get("noteView").removeUpdateView()},deleteNote:function(){var t=this;e.post(feedback_note_ajax_script.feedback_note_ajax_url,{action:"feedback_api_delete",id:t.get("id")},function(e){var o=JSON.parse(e);0==o.status?(t.get("noteView").removeView(),a.notes.remove(t)):alert("Couldn't remove note!")})}}),i.Collection.Notes=Backbone.Collection.extend({model:i.Models.Note,url:feedback_note_ajax_script.feedback_note_ajax_url}),i.Views.NewNote=Backbone.View.extend({initialize:function(e){var i=new UAParser;this.model=new Backbone.Model({x_percent:(e.x_position+1e-4)/n(),y_percent:(e.y_position+1e-4)/o(),pixel_width:n(),pixel_height:o(),browser:t(),user_agent:i.getResult(),url:window.location.pathname,form_id:"#feedback_note_new_note_form",form_comment_id:"#feedback_note_new_note_comment_form"});var a=this.model.attributes.x_percent*this.model.attributes.pixel_width,s=this.model.attributes.y_percent*this.model.attributes.pixel_height,d=a+490>n()?!0:!1;this.model.set("x_pos",a+(d?-490:0)),this.model.set("y_pos",s),this.model.set("arrow_direction",d?"right-arrow":"left-arrow"),this.render()},template:_.template(i.Templates.feedback_note_big_box_new),render:function(){return this.$el.html(this.template(this.model.attributes)),e("body").append(this.el),e(this.el).find("textarea").focus(),this},events:{"click #feedback_note_new_note_save_button":"save","click .feedback_note_new_note_dismiss":"dismiss",keydown:"keydownHandler"},keydownHandler:function(e){if(1==e.shiftKey)return!0;switch(e.which){case 27:a.feedbackButton.removeNewNote();break;case 13:this.save()}},dismiss:function(){a.feedbackButton.removeNewNote()},save:function(){var t=e(this.model.get("form_id")).serialize();e.post(feedback_note_ajax_script.feedback_note_ajax_url,t,function(e){var t=JSON.parse(e);0==t.status?a.feedbackButton.refresh():alert("Couldn't save note!")})},removeView:function(){this.undelegateEvents(),this.$el.removeData().unbind(),this.remove()}}),i.Views.Note=Backbone.View.extend({initialize:function(){this.render()},template:_.template(i.Templates.note_small_box),bindDraggable:function(){var i=this;0!=i.model.get("owner")&&1!=i.model.get("closed")&&this.$(".feedback_note_small_box").draggable({cursor:"move",cursorAt:{left:function(){var e=i.$(".feedback_note_small_box").width();return e}(),top:function(){var e=i.$(".feedback_note_small_box").height();return e}()},scroll:!0,start:function(){a.feedbackButton.removeNewNote(),a.feedbackButton.removeAllUpdateViews()},stop:function(){var a=i.$(".feedback_note_small_box").position(),s="action=feedback_api_update_position&id=";s+=i.model.get("id")+"&x_percent="+(a.left+1e-5)/n(),s+="&y_percent="+(a.top+1e-5)/o(),s+="&pixel_width="+n(),s+="&pixel_height="+o(),s+="&browser="+t(),e.post(feedback_note_ajax_script.feedback_note_ajax_url,s,function(e){var t=JSON.parse(e);0!=t.status&&alert("Couldn't update note position!")}),i.model.set("x_position",a.left),i.model.set("y_position",a.top),i.model.set("x_percent",(a.left+1e-5)/n()),i.model.set("y_percent",(a.top+1e-5)/o()),i.model.set("pixel_width",n()),i.model.set("pixel_height",o());var d=i.model.attributes.x_percent*i.model.attributes.pixel_width,l=i.model.attributes.y_percent*i.model.attributes.pixel_height,_=d+490>n()?!0:!1;i.model.set("x_pos",d+(_?-490:0)),i.model.set("y_pos",l),i.model.set("arrow_direction",_?"right-arrow":"left-arrow")}})},render:function(){return this.$el.html(this.template(this.model.attributes)),e("body").append(this.el),this.bindDraggable(),this},events:{"click .feedback_note_small_box_expand":"expand","click .feedback_note_small_box_delete":"delete"},expand:function(){a.feedbackButton.removeNewNote(),a.feedbackButton.removeAllUpdateViews();var e=new i.Views.UpdateNote({model:this.model,parent:this});this.model.set("update_note",e)},show:function(){this.$(".feedback_note_small_box").css("display","block")},"delete":function(){this.model.deleteNote()},removeUpdateView:function(){var e=this.model.get("update_note");null!=e&&(e.removeView(),this.model.unset("update_note")),this.show()},removeView:function(){a.feedbackButton.removeNewNote(),a.feedbackButton.removeAllUpdateViews(),this.undelegateEvents(),this.$el.removeData().unbind(),this.remove()}}),i.Views.UpdateNote=Backbone.View.extend({initialize:function(e){this.model.set("form_id","#feedback_note_update_note_form"),this.model.set("form_status_id","#feedback_note_update_note_form_status"),this.model.set("form_comment_id","#feedback_note_new_note_comment_form"),this.model.set("parent",e.parent);var t=this.model.attributes.x_percent*this.model.attributes.pixel_width,i=this.model.attributes.y_percent*this.model.attributes.pixel_height,a=t+490>n()?!0:!1;this.model.set("x_pos",t+(a?-490:0)),this.model.set("y_pos",i),this.model.set("arrow_direction",a?"right-arrow":"left-arrow"),this.model.set("delta_w",n()-this.model.attributes.pixel_width_original),this.model.set("delta_h",o()-this.model.attributes.pixel_height_original),this.render()},template:_.template(i.Templates.feedback_note_big_box_update),render:function(){return console.log(this.model.attributes.y_pos+" "+this.model.attributes.y_pos),this.$el.html(this.template(this.model.attributes)),e("body").append(this.el),e(this.el).find("textarea").first().focus(),this},removeView:function(){this.model.get("parent").show(),this.undelegateEvents(),this.$el.removeData().unbind(),this.remove()},events:{"click #feedback_note_update_note_button":"update","click .feedback_note_update_note_dismiss":"removeView","click .feedback_note_new_note_delete":"delete","click #feedback_note_new_note_comment_button":"newComment","click #feedback_note_update_note_form_status":"updateStatus","click .feedback_note_update_note_form_resize":"resize","click .feedback_note_new_note_dismiss":"removeView",keydown:"keydownHandler"},keydownHandler:function(t){return 1==t.shiftKey?!0:(27==t.which&&this.removeView(),void(13==t.which&&(e("#feedback_note_update_note_form textarea").is(":focus")&&this.update(),e("#feedback_note_new_note_comment_form textarea").is(":focus")&&this.newComment())))},resize:function(){console.log("resize"),window.resizeTo(100,200)},"delete":function(){this.model.deleteNote()},update:function(){var t=this;e.post(feedback_note_ajax_script.feedback_note_ajax_url,e(this.model.get("form_id")).serialize(),function(e){var o=JSON.parse(e);0==o.status?a.feedbackButton.refresh():(alert("Couldn't update note!"),t.removeView())})},updateStatus:function(){var t=this;e.post(feedback_note_ajax_script.feedback_note_ajax_url,e(this.model.get("form_status_id")).serialize(),function(e){var o=JSON.parse(e);0==o.status?a.feedbackButton.refresh():(alert("Couldn't update note!"),t.removeView())})},newComment:function(){var t=this;e.post(feedback_note_ajax_script.feedback_note_ajax_url,e(this.model.get("form_comment_id")).serialize(),function(e){var o=JSON.parse(e);0==o.status?a.feedbackButton.refresh():(alert("Couldn't update note!"),t.removeView())})}}),i.Views.GreyBGLayer=Backbone.View.extend({initialize:function(){this.render()},template:_.template(i.Templates.feedback_grey_bg_layer),render:function(){return this.$el.html(this.template()),e("body").append(this.el),this},removeView:function(){this.undelegateEvents(),this.$el.removeData().unbind(),this.remove()}}),i.Views.FeedbackMenu=Backbone.View.extend({initialize:function(e){this.model=new Backbone.Model({fb_button:e.fb_button}),_.bindAll(this,"dismiss"),this.render()},template:_.template(i.Templates.feedback_note_menu),render:function(){return this.$el.html(this.template()),e("body").append(this.el),this},events:{"click .feedback_note_dismiss_menu":"dismiss"},dismiss:function(){var e=this.model.get("fb_button");e.close()},removeView:function(){this.undelegateEvents(),this.$el.removeData().unbind(),this.remove()}}),i.Views.FeedbackButton=Backbone.View.extend({initialize:function(){this.model=new Backbone.Model({}),this.model.set("expanded",!1),_.bindAll(this,"clickNewNote"),e("body").bind("click",this.clickNewNote);var t=this;e(document).scroll(function(){t.rebindDraggableOnNotes()}),this.render()},rebindDraggableOnNotes:function(){this.model.get("expanded")&&a.notes.each(function(e){e.bindDraggable()})},template:_.template(i.Templates.feedback_note_button),render:function(){return this.$el.html(this.template()),e("body").append(this.el),this},events:{"click .feedback_note_side_button":"expand"},expand:function(){if(!this.model.get("expanded")){if(this.model.set("expanded",!0),null==this.model.get("grey_overlay")){var t=new i.Views.GreyBGLayer;this.model.set("grey_overlay",t)}if(a.notes=new i.Collection.Notes([]),a.notes.fetch({data:{action:"feedback_api_list",relative_url:window.location.pathname},type:"POST",async:!1}),a.notes.each(function(e,t){e.set("counter",t+1)}),a.notes.each(function(e){e.createView()}),null==this.model.get("menu")){var o=new i.Views.FeedbackMenu({fb_button:this});this.model.set("menu",o),e(".feedback_note_side_button").css("display","none")}var n=e(".mouse-follow");e(".feedback_note_wraper .grey_bg_layer").on("mousemove",function(t){e(n).css({left:t.pageX+15,top:t.pageY-15})})}},close:function(){if(this.model.get("expanded")){this.removeNotes(a.notes),this.removeNewNote(),this.model.get("menu").removeView(),this.model.unset("menu"),e(".feedback_note_side_button").css("display","block"),this.model.set("expanded",!1);var t=this.model.get("grey_overlay");null!=t&&(t.removeView(),this.model.unset("grey_overlay"))}e(".feedback_note_wraper .grey_bg_layer").off("mousemove")},removeNotes:function(e){e.each(function(e){e.removeView()}),e.reset()},removeNewNote:function(){var e=this.model.get("newnote");null!=e&&e.removeView(),this.model.unset("newnote")},refresh:function(){this.removeNotes(a.notes),this.model.set("expanded",!1),this.expand(),this.removeNewNote()},clickNewNote:function(t){if(this.model.get("expanded")){var o=e(t.target),n=o.hasClass("feedback_note_side_button")||o.parents(".feedback_note_side_button").size(),a=o.hasClass("feedback_note_small_box")||o.parents(".feedback_note_small_box").size(),s=o.hasClass("feedback_note_big_box_new")||o.parents(".feedback_note_big_box_new").size(),d=o.hasClass("feedback_note_menu")||o.parents(".feedback_note_menu").size(),l=o.hasClass("feedback_note_big_box_update")||o.parents(".feedback_note_big_box_update").size();if(!(n||a||s||d||l)){this.removeNewNote(),this.removeAllUpdateViews();var _=new i.Views.NewNote({x_position:t.pageX,y_position:t.pageY});this.model.set("newnote",_)}}},removeAllUpdateViews:function(){a.notes.each(function(e){e.removeUpdateView()})}}),e(document).ready(function(){a.feedbackButton=new i.Views.FeedbackButton;var t=(RegExp("[#|&]feedbacknote=(.+?)(&|$)").exec(location.hash)||[,null])[1];if(null!=t){console.log(t),e(".feedback_note_side_button").trigger("click");var o=e(".feedback_note_small_box").find(".feedback_note_small_box_expand a[data-id="+t+"]");e(o).trigger("click"),e("html, body").animate({scrollTop:e(".feedback_note_big_box_update").offset().top-80},1e3)}})}(jQuery);