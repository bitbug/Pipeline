<?phprequire_once("../../global.php");$slug = Filter::text($_GET['slug']);$project = Project::getProjectFromSlug($slug);$taskID = Filter::numeric($_GET['t']);$task = Task::load($taskID);// kick us out if slug invalidif($project == null){	header('Location: '.Url::error());	exit();}// get accepted for this task$accepted = Accepted::getByTaskID($taskID);// get latest updates for this task$latestUpdates = array();if($accepted != null) {	foreach($accepted as $a) {		$updates = Update::getByAcceptedID($a->getID());		if($updates != null) {			$latestUpdate = reset($updates);			array_push($latestUpdates, $latestUpdate);		}	}}$events = Event::getTaskEvents($taskID, 5);$uploads = Upload::getByTaskID($taskID, false);$comments = Comment::getByTaskID($taskID);$soup = new Soup();$soup->set('project', $project);$soup->set('task', $task);$soup->set('accepted', $accepted);$soup->set('events', $events);$soup->set('uploads', $uploads);$soup->set('comments', $comments);$soup->set('latestUpdates', $latestUpdates);$soup->render('project/page/task');