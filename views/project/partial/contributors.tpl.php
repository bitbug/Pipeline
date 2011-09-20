<?phpinclude_once TEMPLATE_PATH.'/site/helper/format.php';$project = $SOUP->get('project');$contributors = $SOUP->get('contributors');$openTasks = $project->getTasks(Task::STATUS_OPEN);// any contributor or above can invite contributors$hasCreatePermission = ( Session::isAdmin() ||					ProjectUser::isOrganizer(Session::getUserID(), $project->getID()) ||					ProjectUser::isCreator(Session::getUserID(), $project->getID()) ||					ProjectUser::isContributor(Session::getUserID(), $project->getID()) );// only organizers or creator may edit contributors$hasEditPermission = ( Session::isAdmin() ||					ProjectUser::isOrganizer(Session::getUserID(), $project->getID()) ||					ProjectUser::isCreator(Session::getUserID(), $project->getID()) );$fork = $SOUP->fork();$fork->set('id', 'contributors');$fork->set('title', 'Contributors');$fork->set('editable', $hasEditPermission);$fork->set('editLabel', 'Edit Contributors');$fork->set('creatable', $hasCreatePermission);$fork->set('createLabel', 'Invite Contributors');$fork->set('extraButton', $hasCreatePermission);$fork->set('extraButtonLabel', 'Show Invited');$fork->startBlockSet('body');?><script type="text/javascript">$(document).ready(function(){<?php if($hasEditPermission): ?>	$("#contributors .editButton").click(function(){		var buttons = $("#contributors div.view input[type='button']");		if($(buttons).is(":hidden")) {			$(buttons).fadeIn();		} else {			$(buttons).fadeOut();		}	});		$("#contributors input.ban").click(function(){		var id = $(this).attr('id').substring(4);		buildPost({			'processPage': '<?= Url::peopleProcess($project->getID()) ?>',			'info': {				'action': 'ban',				'userID': id			},			'buttonID': $(this)		});	});		$("#contributors input.organizer").click(function(){		var id = $(this).attr('id').substring(10);		buildPost({			'processPage': '<?= Url::peopleProcess($project->getID()) ?>',			'info': {				'action': 'make-organizer',				'userID': id			},			'buttonID': $(this)		});	});<?php endif; ?>	<?php if($hasCreatePermission): ?>		$("#contributors .createButton").click(function(){		var invite = $("#contributors .invite");		var view = $("#contributors .view");		toggleEditView(view, invite);		if($(view).is(":hidden"))			$('#txtInviteContributors').focus();	});		$("#btnCancelContributors").click(function(){		$("#contributors .invite").hide();		$("#contributors .view").fadeIn();	});		$( "#txtInviteContributors" )		// don't navigate away from the field on tab when selecting an item		.bind( "keydown", function( event ) {			if ( event.keyCode === $.ui.keyCode.TAB &&					$( this ).data( "autocomplete" ).menu.active ) {				event.preventDefault();			}		})		.autocomplete({			source: function( request, response ) {				$.getJSON( '<?= Url::peopleSearch($project->getID()) ?>/possible-contributors', {					term: extractLast( request.term )				}, response );			},			search: function() {				// custom minLength				var term = extractLast( this.value );				if ( term.length < 2 ) {					return false;				}			},			focus: function() {				// prevent value inserted on focus				return false;			},			select: function( event, ui ) {				var terms = split( this.value );				// remove the current input				terms.pop();				// add the selected item				terms.push( ui.item.value );				// add placeholder to get the comma-and-space at the end				terms.push( "" );				this.value = terms.join( ", " );				return false;			}		});					$('#btnInviteContributors').click(function() {		buildPost({			'processPage': '<?= Url::peopleProcess($project->getID()) ?>',			'info': {				'action': 'invite-contributors',				'invitees': $('#txtInviteContributors').val(),				'taskID': $('#selInviteContributorsTask').val(),				'message': $('#txtInviteContributorsMessage').val()			},			'buttonID': '#btnInviteFollowers'		});	});			<?php endif; ?>		});</script><div class="view"><?php if($contributors != null) {	echo '<ul class="segmented-list users">';	foreach($contributors as $c) {		echo '<li>';		if($hasEditPermission) {			echo '<input id="ban-'.$c->getID().'" type="button" class="ban hidden" value="Ban" />';			echo '<input id="organizer-'.$c->getID().'" type="button" class="organizer hidden" value="Make Organizer" />';		}		echo formatUserPicture($c->getID(), 'small');		echo '<h6 class="primary">'.formatUserLink($c->getID()).'</h6>';		echo '<p class="secondary">contributor</p>';		echo '</li>';	}	echo '</ul>';} else {	echo '<p>(none)</p>';}?></div><?php if($hasCreatePermission): ?><div class="invite hidden">	<div class="clear">		<label for="txtInviteContributors">People to Invite<span class="required">*</span></label>		<div class="input">			<input type="text" id="txtInviteContributors" />			<p>Usernames or email addresses, separated by commas</p>		</div>	</div>	<div class="clear">		<label for="selInviteContributorsTask">Task<span class="required">*</span></label>		<div class="input">			<select id="selInviteContributorsTask">			<?php				foreach($openTasks as $ot) {					echo '<option value="'.$ot->getID().'">'.html_entity_decode($ot->getTitle(), ENT_QUOTES, 'ISO-8859-15').'</option>';				}			?>			</select>			<p>The task you're inviting the recipient(s) to contribute to</p>		</div>	</div>	<div class="clear">		<label for="txtInviteContributorsMessage">Message</label>		<div class="input">			<textarea id="txtInviteContributorsMessage"></textarea>			<p>Why the recipient(s) should contribute to this task</p>		</div>	</div>		<div class="clear">		<div class="input">			<input type="button" id="btnInviteContributors" value="Invite" />			<input type="button" id="btnCancelContributors" value="Cancel" />		</div>	</div></div><?php endif; ?><?php$fork->endBlockSet();$fork->render('site/partial/panel');