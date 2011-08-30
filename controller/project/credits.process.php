<?phprequire_once('./../../global.php');require_once TEMPLATE_PATH.'/site/helper/format.php'; // for formatCount$slug = Filter::text($_GET['slug']);	// check project$project = Project::getProjectFromSlug($slug);if($project == null) {	Session::setMessage('That project does not exist.');	header('Location: '.Url::error());	exit();}$action = Filter::text($_POST['action']);if( ($action == 'revoke-organizer') ||	($action == 'make-organizer') ||	($action == 'ban') ||	($action == 'unban') ) {	// get user	$userID = Filter::numeric($_POST['userID']);	$user = User::load($userID);	// get project user	$pu = ProjectUser::find($userID, $project->getID());}	if($action == 'revoke-organizer') {	if($pu != null) {		$pu->delete();	}	// was this organizer leading any tasks?	$tasks = Task::getByLeaderID($project->getID(), $userID);	if($tasks != null) {		foreach($tasks as $t) {			// revert task leader to project creator			$oldLeaderID = $t->getLeaderID();			$newLeaderID = $project->getCreatorID();			if($oldLeaderID != $newLeaderID) {				// save it				$t->setLeaderID($newLeaderID);				$t->save();				// log it				$logEvent = new Event(array(					'event_type_id' => 'edit_task_leader',					'project_id' => $project->getID(),					'user_1_id' => Session::getUserID(),					'user_2_id' => $newLeaderID,					'item_1_id' => $t->getID(),					'data_1' => $oldLeaderID,					'data_2' => $newLeaderID				));				$logEvent->save();			}		}	}		// log it	$logEvent = new Event(array(		'event_type_id' => 'revoke_organizer',		'project_id' => $project->getID(),		'user_1_id' => Session::getUserID(),		'user_2_id' => $userID	));	$logEvent->save();		// send us back	Session::setMessage($user->getUsername().' is no longer an organizer.');	$json = array('success' => '1');	echo json_encode($json);} elseif($action == 'make-organizer') {	if($pu != null) {		$pu->setRelationship(ProjectUser::ORGANIZER);	} else {		$pu = new ProjectUser(array(			'user_id' => $userID,			'project_id' => $project->getID(),			'relationship' => ProjectUser::ORGANIZER		));	}	$pu->save();		// log it	$logEvent = new Event(array(		'event_type_id' => 'make_organizer',		'project_id' => $project->getID(),		'user_1_id' => Session::getUserID(),		'user_2_id' => $userID	));	$logEvent->save();			// send us back	Session::setMessage($user->getUsername().' is now an organizer.');	$json = array('success' => '1');	echo json_encode($json);} elseif($action == 'ban') {	if($pu != null) {		$pu->setRelationship(ProjectUser::BANNED);	} else {		$pu = new ProjectUser(array(			'user_id' => $userID,			'project_id' => $project->getID(),			'relationship' => ProjectUser::BANNED		));	}	$pu->save();		// log it	$logEvent = new Event(array(		'event_type_id' => 'ban_user',		'project_id' => $project->getID(),		'user_1_id' => Session::getUserID(),		'user_2_id' => $userID	));	$logEvent->save();			// send us back	Session::setMessage($user->getUsername().' is now banned.');	$json = array('success' => '1');	echo json_encode($json);} elseif($action == 'unban') {	if($pu != null) {		$pu->delete();	}		// log it	$logEvent = new Event(array(		'event_type_id' => 'unban_user',		'project_id' => $project->getID(),		'user_1_id' => Session::getUserID(),		'user_2_id' => $userID	));	$logEvent->save();			// send us back	Session::setMessage($user->getUsername().' is no longer banned.');	$json = array('success' => '1');	echo json_encode($json);} elseif( ($action == 'invite-organizers') ||	($action == 'invite-followers') ){	$invitees = Filter::text($_POST['invitees']);	$message = Filter::text($_POST['message']);	$invitees = explode(',', $invitees);		if($action == 'invite-organizers') {		$relationship = ProjectUser::ORGANIZER;		$relationshipMsg = 'help organize';	} else {		$relationship = ProjectUser::FOLLOWER;		$relationshipMsg = 'follow';	}		// these arrays will hold valid users and emails to invite	$users = array();	$emails = array();		// first, make sure everyone in the list is valid	if($invitees != null) {		foreach($invitees as $i) {			$i = trim($i);			if($i == '') continue;			if(filter_var($i, FILTER_VALIDATE_EMAIL)) {				// it's an email address				$user = User::loadByEmail($i);				if($user != null) {					// email address found; user exists					$validate = validateUser($user, $project->getID());					if($validate === true) {						$users[] = $user;					} else {						exit(json_encode($validate));					}								} else {					// email address not found					$emails[] = $i;				}			} else {				$user = User::loadByUsername($i);				if($user != null) {					// make sure user is unaffiliated with this project					$validate = validateUser($user, $project->getID());					if($validate === true) {						$users[] = $user;					} else {						exit(json_encode($validate));					}				} else {					// invalid user					$json = array('error' => '"'.$i.'" is not a valid username or email address.');					exit(json_encode($json));									}			}		}	} else {		$json = array('error' => 'You must provide at least one username or email address.');		exit(json_encode($json));			}		// now actually invite the validated users/emails	foreach($users as $u) {		// send invitation		$invite = new Invitation(array(			'inviter_id' => Session::getUserID(),			'invitee_id' => $u->getID(),			'project_id' => $project->getID(),			'relationship' => $relationship,			'invitation_message' => $message		));		$invite->save();		}		foreach($emails as $e) {		// generate code		$code = sha1(microtime(true).mt_rand(10000,90000));		// send invitation		$invite = new Invitation(array(			'inviter_id' => Session::getUserID(),			'invitee_email' => $e,			'project_id' => $project->getID(),			'relationship' => $relationship,			'invitation_code' => $code,			'invitation_message' => $message		));		$invite->save();				// compose email		$msg = "<p>".formatUserLink(Session::getUserID()).' invited you to '.$relationshipMsg.' the project '.formatProjectLink($project->getID()).' on '.PIPELINE_NAME.'.</p>';		if($message != null) {			$msg .= '<blockquote>'.$message.'</blockquote>';		}		$msg .= '<p>Use the link above to learn more about the project. To accept this invitation, <a href="'.Url::registerWithCode($code).'"><b>click here</b></a> and follow the instructions for setting up your account.</p>';		$email = array(			'to' => $e,			'subject' => 'Invitation to '.$relationshipMsg.' the project '.$project->getTitle(),			'message' => $msg		);		// send email		Email::send($email);	}		// send us back	$numInvitations = count($users) + count($emails);	Session::setMessage(formatCount($numInvitations,'invitation','invitations').' sent.');	$json = array('success' => '1');	echo json_encode($json);	} elseif($action == 'new-bans') {	$banlist = Filter::text($_POST['banlist']);	$banlist = explode(',', $banlist);	$numBanned = 0;	if($banlist != null) {		foreach($banlist as $b) {			$b = trim($b);			if($b == '') continue;			$user = User::loadByUsername($b);			if($user != null) {				// make sure user is unaffiliated with this project				$validate = validateUser($user, $project->getID());				if($validate === true) {					// ban the user					$pu = new ProjectUser(array(						'user_id' => $user->getID(),						'project_id' => $project->getID(),						'relationship' => ProjectUser::BANNED					));					$pu->save();								// log it					$logEvent = new Event(array(						'event_type_id' => 'ban_user',						'project_id' => $project->getID(),						'user_1_id' => Session::getUserID(),						'user_2_id' => $user->getID()					));					$logEvent->save();					$numBanned++;				} else {					exit(json_encode($validate));				}			} else {				// invalid user				$json = array('error' => '"'.$b.'" is not a valid username.');				exit(json_encode($json));								}		}				// send us back		Session::setMessage(formatCount($numBanned,'user','users').' banned.');		$json = array('success' => '1');		echo json_encode($json);			} else {		$json = array('error' => 'You must provide at least one username.');		exit(json_encode($json));			}} else {	$json = array('error' => 'Invalid action.');	exit(json_encode($json));	}function validateUser($user=null, $projectID=null) {	if( ($user === null) ||		($projectID === null) ) {		return null;	}		$json = null;	if(ProjectUser::isCreator($user->getID(), $projectID)) {		// user is the project creator		$json = array('error' => '"'.$user->getUsername().'" is the creator this project.');		} elseif(ProjectUser::isOrganizer($user->getID(), $projectID)) {		// user is already an organizer		$json = array('error' => '"'.$user->getUsername().'" is an organizer of this project.');	} elseif(ProjectUser::isContributor($user->getID(), $projectID)) {		// user is banned		$json = array('error' => '"'.$user->getUsername().'" is a contributor to this project.');	} elseif(ProjectUser::isFollower($user->getID(), $projectID)) {		// user is already a follower		$json = array('error' => '"'.$user->getUsername().'" is following this project.');	} elseif(ProjectUser::isBanned($user->getID(), $projectID)) {		// user is banned		$json = array('error' => '"'.$user->getUsername().'" is banned from this project.');	}		if($json != null) {		return $json;	} else {		return true;	}}