<?php

error_reporting(E_ALL);

include "common.php";

echo "Installing ...<br/>";

if ($g_backend == "sqlite3")
{
  if (file_exists($g_db_name))
    die("Database already exists, stopping installation!");
  unlink($g_db_name);//Deleting database
}

if (!CreateDatabase())
	echo "Couldn't create database.<br/>";


if ($g_backend == "sqlite3")
  $autoincrement = "AUTOINCREMENT";
else if ($g_backend == "mysql")
  $autoincrement = "AUTO_INCREMENT";


if (!$db->query(
"CREATE TABLE `accounts` ("
  ."`id` INTEGER PRIMARY KEY $autoincrement,"
  ."`email` varchar(128) NOT NULL,"//email used to login
  ."`username` varchar(128) NOT NULL,"//Username used to display the name
  ."`password` varchar(128) NOT NULL,"
  ."`status` INTEGER NOT NULL,"//User, moderator, admin
  ."`created-at` DATETIME NOT NULL,"
  ."`last-login` DATETIME NOT NULL"//Timestamp of last login
.");"))
	die("Couldn't create table 'accounts'");
else
  echo "table 'accounts' created.<br/>";



if (!$db->query(
"CREATE TABLE `witty` ("
  ."`id` INTEGER PRIMARY KEY $autoincrement,"
  ."`name` varchar(128) NOT NULL,"//Name of the witty
  ."`account` INTEGER NOT NULL,"//Created by this account
  ."`created-at` DATETIME NOT NULL"
.");"))
  die("Couldn't create table 'witty'");
else
  echo "table 'witty' created.<br/>";


//Relational table to connect witty <-> questions

if (!$db->query(
"CREATE TABLE `witty_questions` ("
  ."`id` INTEGER PRIMARY KEY $autoincrement,"
  ."`witty` INTEGER NOT NULL,"
  ."`question` INTEGER NOT NULL"
.");"))
  die("Couldn't create table 'witty_questions'");
else
  echo "table 'witty_questions' created.<br/>";


if (!$db->query(
"CREATE TABLE `questions` ("
  ."`id` INTEGER PRIMARY KEY $autoincrement,"
  ."`text` TEXT NOT NULL"
.");"))
  die("Couldn't create table 'questions'");
else
  echo "table 'questions' created.<br/>";


//Relational table to connect questions <-> answers 

if (!$db->query(
"CREATE TABLE `questions_answers` ("
  ."`id` INTEGER PRIMARY KEY $autoincrement,"
  ."`question` INTEGER NOT NULL,"
  ."`answer` INTEGER NOT NULL"
.");"))
  die("Couldn't create table 'questions_answers'");
else
  echo "table 'questions_answers' created.<br/>";


if (!$db->query(
"CREATE TABLE `answers` ("
  ."`id` INTEGER PRIMARY KEY $autoincrement,"
  ."`text` TEXT NOT NULL,"
  ."`is_correct` INTEGER"//1 if correct answer
.");"))
  die("Couldn't create table 'answer'");
else
  echo "table 'answer' created.<br/>";


if (!$db->query(
"CREATE TABLE `sessions` ("
  ."`id` INTEGER PRIMARY KEY $autoincrement,"
  ."`witty` INTEGER NOT NULL,"//ID of the witty
  ."`status` INTEGER NOT NULL,"//Current status
  ."`created-at` DATETIME NOT NULL"
.");"))
  die("Couldn't create table 'session'");
else
  echo "table 'session' created.<br/>";


echo "Creating account 'wresch'.<br/>";
CreateAccount("wresch@math.uni-bielefeld.de", "wresch", HashPassword("1234"), ADMIN);


CloseDatabase();
echo "Installation complete.";

?>