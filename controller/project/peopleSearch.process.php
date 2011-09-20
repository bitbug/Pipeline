<?phprequire_once('./../../global.php');$slug = Filter::text($_GET['slug']);	// check project$project = Project::getProjectFromSlug($slug);if($project == null) {	Session::setMessage('That project does not exist.');	header('Location: '.Url::error());	exit();}$relationship = Filter::text($_GET['relationship']);if($relationship == "organizers") {	$organizers = $project->getOrganizers();	$creator = $project->getCreator();		$json = array();	$json[] = $creator->getUsername();	foreach($organizers as $o) {		$json[] = $o->getUsername();	}	echo json_encode($json);} elseif ($relationship == "not-affiliated") {	$usernames = User::getUnaffiliatedUsernames($project->getID());	echo json_encode($usernames);} elseif ($relationship == 'possible-contributors') {	$usernames = User::getPossibleContributorUsernames($project->getID());	echo json_encode($usernames);}