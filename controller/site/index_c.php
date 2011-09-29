<?php
require_once("../../global.php");

$soup = new Soup();

if(Session::isLoggedIn()) {	
	// dashboard
	$projects = Project::getByUserID(Session::getUserID());
	$user = User::load(Session::getUserID());
	$events = Event::getDashboardEvents($user->getID(), 10);
	// $updates = Update::getByUserID($user->getID());
	// $discussions = Discussion::getByUserID($user->getID());
	$invitations = Invitation::getByUserID(Session::getUserID());
	$unrespondedInvites = Invitation::getByUserID(Session::getUserID(), null, false);
	$yourTasks = Task::getYourTasks($user->getID());
	
	$soup->set('projects', $projects);
	$soup->set('user', $user);
	$soup->set('events', $events);
	// $soup->set('updates', $updates);
	// $soup->set('discussions', $discussions);
	$soup->set('invitations', $invitations);
	$soup->set('unrespondedInvites', $unrespondedInvites);
	$soup->set('tasks', $yourTasks);
	$soup->render('site/page/dashboard');
} else {	
	// home page
	$events = Event::getHomeEvents(10);
	$soup->set('events', $events);
	$soup->render('site/page/home');
}
