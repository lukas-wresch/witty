<?php

include "config.php";

$db;

$g_status = 0;//Status level of the logged in user (0 if not logged in)
$g_email;
$g_username;
$g_user_id;


define("USER",            "1");
define("MODERATOR",       "2");
define("ADMIN",           "3");


session_start();

if (isset($_SESSION['status']))
	$g_status=$_SESSION['status'];

if (isset($_SESSION['email']))
	$g_email=$_SESSION['email'];

if (isset($_SESSION['username']))
	$g_username=$_SESSION['username'];

if (isset($_SESSION['user_id']))
	$g_user_id=$_SESSION['user_id'];


function Connect2Database()
{
	global $db, $g_backend, $g_db_host, $g_db_username, $g_db_password, $g_db_name;

	if ($g_backend == "sqlite3")
	{
		$db = new SQLite3("database.db", SQLITE3_OPEN_READWRITE);
		$db->busyTimeout(5000);//Wait up to 5s if the datebase is locked
		$db->exec('PRAGMA journal_mode = wal;');//Write ahead mode
	}
	else if ($g_backend == "mysql")
		$db = new mysqli($g_db_host, $g_db_username, $g_db_password, $g_db_name);

	if (!$db)
		die("Could not connect to database! Try again later.");

	return true;
}



function CreateDatabase()
{
	global $db, $g_backend, $g_db_host, $g_db_username, $g_db_password, $g_db_name;

	if ($g_backend == "sqlite3")
	{
		$db = new SQLite3($g_db_name);
		return $db;
	}
	else if ($g_backend == "mysql")
	{
		$db = new mysqli($g_db_host, $g_db_username, $g_db_password);

		if (!$db)
			die("Could not connect to database! Try again later.");

		$query = "CREATE DATABASE $g_db_name";
		$db->exec($query);

		return $db->select_db($g_db_name);
	}
}



function CloseDatabase()
{
	global $db;

	if ($db)
		$db->close();
	unset($db);
}



function HasStatus($MinimalStatus)
{
	global $g_status;

	return $g_status >= $MinimalStatus;
}



function HashPassword($Password)
{
	return hash("sha256", $password.'Witty Password Hash');
}



function Logout()
{
	global $g_user_id, $g_email, $g_username, $g_status;

	unset($g_status);
	unset($g_user_id);
	unset($g_email);
	unset($g_username);
	
	session_destroy();
}



function FetchResults($Results)
{
	global $g_backend;

	if (!$Results)
		return NULL;

	if ($g_backend == "sqlite3")
		return $Results->fetchArray();
	else if ($g_backend == "mysql")
		return $Results->fetch_array();
	return NULL;
}



function ResetResults($Results)
{
	global $g_backend;

	if ($g_backend == "sqlite3")
		$Results->reset();
	else if ($g_backend == "mysql")
		$Results->data_seek(0);
	return NULL;
}



function GetNoOfRows($Results)
{
	$no = 0;

	while ($row = FetchResults($Results))
		$no++;

	ResetResults($Results);
	return $no;
}



function GetLastRowID()
{
	global $db, $g_backend;

	if ($g_backend == "sqlite3")
		return sqlite3_last_insert_rowid($db);//DEPRECATED AND REMOVED
	else if ($g_backend == "mysql")
		mysql_insert_id($db);
	return NULL;
}



function GetLastError() 
{
	return $db->lastErrorMsg();
}



function CreateAccount($Email, $Username, $Password, $Status = 1)
{
	global $db, $g_status;

	if ($Status > USER)//Important accounts can only be created by an admin
	{
		if (!HasStatus(ADMIN))//If not admin
			return false;
	}

	$timestamp = time();
	return $db->query("INSERT INTO `accounts` (`email`, `username`, `password`, `status`, `created-at`)"
		                            ."VALUES ('$Email', $Username', '$Password', '$Status', '$timestamp');");
}



function Login($Email, $Password)
{
	global $db, $g_email, $g_username, $g_status, $g_user_id;

	$results = $db->query("SELECT `id`, `username`, `status` FROM `accounts` WHERE `email` = '$Email' AND `password` = '$Password';");
	$row = FetchResults($results);
	
	if ($row)
	{
		$g_email    = $Email;
		$g_username = $row['username'];
		$g_status   = $row['status'];
		$g_user_id  = $row['id'];

		$timestamp = time();
		$db->query("UPDATE `accounts` SET `last-login` = '$timestamp' WHERE `id` = '$user_id';");

		return true;
	}

	return false;
}



function GetAllAccounts()
{
	global $db;

	if (!HasStatus(ADMIN))
		return NULL;

	$results = $db->query("SELECT * FROM `accounts`;");

	return $results;
}

?>