<?php
$project = $SOUP->get('project');

$fork = $SOUP->fork();

$fork->set('project', $project);
$fork->set('pageTitle', $project->getTitle());
$fork->set('headingURL', Url::project($project->getID()));

$fork->set('selected', "discussions");
$fork->set('breadcrumbs', Breadcrumbs::discussionNew($project->getID()));

$fork->startBlockSet('body');
?>

<div class="left">

<?php
	$SOUP->render('project/partial/discussionNew', array(
		));
?>

</div>

<div class="right">


</div>


<?

$fork->endBlockSet();
$fork->render('site/partial/page');

