<?php
include_once TEMPLATE_PATH.'/site/helper/format.php';

$project = $SOUP->get('project');
$cat = $SOUP->get('cat');
$title = $SOUP->get('title', 'Reflections');
$hasPermission = $SOUP->get('hasPermission', null);
$reflections = $SOUP->get('reflections', array());
$class = $SOUP->get('class');
$size = $SOUP->get('size');

// only admin, instructor, or project member can reflect
if($hasPermission === null) {
    $hasPermission = ( Session::isAdmin() ||
                      Session::isInstructor() ||
					$project->isMember(Session::getUserID()) );
}

$fork = $SOUP->fork();
$fork->set('title', $title);
$fork->set('class', $class .= ' discussions');
$fork->set('creatable', $hasPermission);
$fork->set('createLabel', 'New Reflection');

// generate URL for new discussion, if has permission
if($hasPermission) {
	$newURL = Url::reflectionNew($project->getID());
	$newURL .= ($cat != null) ? '/'.$cat : '';
}

// if($size == 'small') {
// //	$fork->set('createLabel', 'New');
	// $newURL = Url::discussionNew($project->getID()).'/'.$cat;
// } else {
// //	$fork->set('createLabel', 'New Discussion');
	// $newURL = Url::discussionNew($project->getID());
// }

$fork->startBlockSet('body');
?>

<?php if($hasPermission): ?>

<script type="text/javascript">

$(document).ready(function(){
	$('div.discussions .createButton').click(function(){
		window.location = '<?= $newURL ?>';
	});
});

</script>

<?php endif; ?>

<?php

if(empty($reflections)) {
	echo '<p>(none)</p>';
} elseif($size == 'small') {
	echo '<ul class="segmented-list discussions">';
	foreach($reflections as $d) {
		if(permissionCheck($d, Session::getUserID()) == false)
			continue;
		echo '<li>';
		$cssLock = ($d->getLocked()) ? ' locked' : '';
		echo '<h6 class="primary'.$cssLock.'"><a href="'.Url::discussion($d->getID()).'">'.$d->getTitle().'</a></h6>';
		echo '<p class="secondary">';
		$numReplies = count($d->getReplies());
		echo formatCount($numReplies,'reply','replies','no') . ' <span class="slash">/</span> ';
		if($numReplies>0) {
			$lastReply = $d->getLastReply();
			echo 'last reply '.formatTimeTag($lastReply->getDateCreated()).' by '.formatUserLink($lastReply->getCreatorID(), $project->getID());
		} else {
			echo 'posted '.formatTimeTag($d->getDateCreated()).' by '.formatUserLink($d->getCreatorID(), $project->getID());
		}
		echo '</p>';
		echo '</li>';
	}
	echo '</ul>';
} else {
?>
	<table class="items discussions">
		<tr>
			<th style="padding-left: 22px;">Reflection</th>
			<th>Replies</th>
			<th>Last Reply</th>
			<th>Visibility</th>
		</tr>
<?php
	foreach($reflections as $d) {
		if(permissionCheck($d, Session::getUserID()) == false)
			continue;
		echo '<tr>';
		echo '<td class="title">';
		$cssLock = ($d->getLocked()) ? ' class="locked"' : '';
		echo '<h6'.$cssLock.'><a href="'.Url::reflection($d->getID()).'">'.$d->getTitle().'</a></h6>';
		echo '<p>by '.formatUserLink($d->getCreatorID(), $d->getProjectID()).'</p>';
		echo '</td>';
		$numReplies = (count($d->getReplies()));
		echo '<td class="replies">'.$numReplies.'</td>';
		$lastReply = $d->getLastReply();
		if(!empty($lastReply)) {
			$lrDate = formatTimeTag($lastReply->getDateCreated());
			$lrCreator = formatUserLink($lastReply->getCreatorID(), $lastReply->getProjectID());
			echo '<td class="last-reply">'.$lrDate.'<br />by '.$lrCreator.'</td>';
		} else {
			echo '<td class="last-reply">--</td>';
		}
        // show visibility setting
        $visSetting = '';
        switch($d->getReflectionVisibility()) {
            case Discussion::REFLECT_VIS_ME:
                $visSetting .= '<span title="Me">M</span> ';
                break;
            case Discussion::REFLECT_VIS_ME_INSTR:
                $visSetting .= '<span title="Me">M</span> ';
                $visSetting .= '<span title="Instructor">I</span> ';
                break;
            case Discussion::REFLECT_VIS_ME_INSTR_PROJ_MEMB:
                $visSetting .= '<span title="Me">M</span> ';
                $visSetting .= '<span title="Instructor">I</span> ';
                $visSetting .= '<span title="Project Members">P</span> ';
                break;
            case Discussion::REFLECT_VIS_EVERYONE:
                $visSetting .= '<span title="Everyone">E</span> ';
                break;
            default:
                break;
        }
        echo '<td class="category">'.$visSetting.'</td>';
	}
?>
	</table>
<?php
}

$fork->endBlockSet();
$fork->render('site/partial/panel');
