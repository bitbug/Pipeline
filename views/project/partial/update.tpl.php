<?phpinclude_once TEMPLATE_PATH.'/site/helper/format.php';$project = $SOUP->get('project');$accepted = $SOUP->get('accepted');$update = $SOUP->get('update');$updates = $SOUP->get('updates');$uploads = $SOUP->get('uploads');$task = $SOUP->get('task', null);$comments = $SOUP->get('comments');// only update creator may edit or create$hasPermission = ( Session::isAdmin() ||				($update->getCreatorID() == Session::getUserID()) );$fork = $SOUP->fork();$fork->set('title', 'Contribution');$fork->set('id', 'update');$fork->set('editable', $hasPermission);$fork->set('editLabel', 'Edit');$fork->startBlockSet('body');?><?php if($hasPermission): ?><script type="text/javascript">$(document).ready(function(){		$('#selStatus').val('<?= $accepted->getStatus() ?>');		// $('#btnEditUpdate').click(function(){		// buildPost({			// 'processPage':'<?= Url::updateProcess($update->getID()) ?>',			// 'info': $('#frmEditItem').serialize(),			// 'buttonID':'#btnEditUpdate'		// });	// });		$("#update .editButton").click(function(){		$(this).hide();		$("#update .view").hide();		$("#update .edit").fadeIn();		initializeUploader();		$('#txtTitle').focus();				});		$("#btnCancelUpdate").click(function(){		$("#update .edit").hide();		$("#update .view").fadeIn();		$("#update .editButton").fadeIn();	});		});function uploadComplete() {	buildPost({		'processPage':'<?= Url::updateProcess($update->getID()) ?>',		'info': $('#frmEditItem').serialize(),		'buttonID':'#btnEditUpdate'	});}</script><div class="edit hidden"><form id="frmEditItem"><input type="hidden" name="action" value="edit-update" /><div class="clear">	<label for="txtTitle">Title<span class="required">*</span></label>	<div class="input">		<input type="text" id="txtTitle" name="txtTitle" maxlength="255" value="<?= $update->getTitle() ?>" />		<p>Short title for the contribution</p>	</div></div><div class="clear">	<label for="txtMessage">Message<span class="required">*</span></label>	<div class="input">		<textarea id="txtMessage" name="txtMessage"><?= $update->getMessage() ?></textarea>		<p>Write your contribution here, <a class="help-link" href="<?= Url::help() ?>#help-html-allowed">some HTML allowed</a></p>	</div></div><?php if($update->isLatestUpdate()): ?><div class="clear">	<label for="selStatus">Status<span class="required">*</span></label>	<div class="input">		<select id="selStatus" name="selStatus">			<option value="<?= Accepted::STATUS_PROGRESS ?>"><?= Accepted::getStatusName(Accepted::STATUS_PROGRESS) ?></option>			<option value="<?= Accepted::STATUS_FEEDBACK ?>"><?= Accepted::getStatusName(Accepted::STATUS_FEEDBACK) ?></option>			<option value="<?= Accepted::STATUS_COMPLETED ?>"><?= Accepted::getStatusName(Accepted::STATUS_COMPLETED) ?></option>		</select>	</div></div><?php endif; ?><div class="clear">	<label>Attached Files</label>	<div class="input">		<input type="button" id="btnSelectFiles" value="Add Files" />		<p>Max size 100 MB each</p>		<div id="filelist"></div>		<?php 			$SOUP->render('project/partial/editUploads',array(			));		?>	</div></div><div class="clear">	<div class="input">		<input id="btnEditUpdate" type="button" value="Save" />		<input id="btnCancelUpdate" type="button" value="Cancel" />	</div></div></form><?php	$SOUP->render('site/partial/newUpload', array(		'uploadButtonID' => 'btnEditUpdate',		'formID' => 'frmEditItem'	));?></div><!-- end .edit --><?php endif; ?><div class="view"><div class="person-box">	<?= formatUserPicture($update->getCreatorID(), 'small') ?>	<div class="text">		<p class="caption">posted by</p>		<p class="username"><?= formatUserLink($update->getCreatorID(), $project->getID()) ?></p>	</div></div><h5><?= $update->getTitle() ?></h5><?phpif($update->isLatestUpdate()) {	$status = Accepted::getStatusName($accepted->getStatus());} else {	$status = 'old';}?><p><span class="status"><?= $status ?></span> <span class="slash">/</span> posted <?= formatTimeTag($update->getDateCreated()) ?></p><div class="line"></div><p><?= formatUpdate($update->getMessage()) ?></p><?php	$SOUP->render('site/partial/newUploads', array(	//	'uploads' => $uploads	));?></div><!-- end .view --><?php	$SOUP->render('project/partial/comments', array(		'comments' => $comments,		'processURL' => Url::updateProcess($update->getID()),		'parentID' => $update->getID(),		'size' => 'large',		'id' => 'comments'	));?><?php$fork->endBlockSet();$fork->render('site/partial/panel');