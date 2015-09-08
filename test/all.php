<?php

/*****************************************************************************\
	This is the testing framework for PUDL. This testing framework is
	designed to be launched exclusively form within an Altaform based
	application. The instructions on how to set this up will be provided
	at a later time. This file, however, still gives some clear examples
	as to the types of SQL statements that can be generated through the
	PUDL library.

	IMPORTANT NOTE: The ->string() part of these queries means that they
	will *NOT* be executed, but instead ONLY return an object containing
	the SQL query statement generated. Removing ->string() from each line
	will allow execution of the generated statement. This is simply added
	here to compare the generated statements to their expected results, to
	ensure that all queries are generated by PUDL correctly.
\*****************************************************************************/


function pudlTest($expected) {
	global $db;
	if ($db->query() === $expected) return;
	$trace = debug_backtrace()[0];
	echo "ERROR: FAILED!!\n\n";
	echo "FILE: $trace[file]\n";
	echo "LINE: $trace[line]\n\n";
	echo "EXPECTED:\n";
	echo "'" . $expected . "'\n\n";
	echo "GOT:\n";
	echo "'" . $db->query() . "'\n\n";
	exit;
}


//BASIC QUERIES, NOT USING THE CUSTOM GENERATOR
require 'basic.php';

//RETURNED COLUMNS
require 'column.php';

//FROM TABLES
require 'table.php';

//JOIN TABLES
require 'join.php';

//WHERE/HAVING CLAUSES
require 'clause.php';

//INSERT STATEMENTS
require 'insert.php';

//UPDATE STATEMENTS
require 'update.php';

//SHORTHAND NOTATION FOR SELECT STATEMENTS
//RETURN A SINGLE CELL
require 'cell.php';

//SHORTHAND NOTATION FOR SELECT STATEMENTS
//RETURN A SINGLE ROW OR ROWS
require 'row.php';

//SHOTHAND NOTATION FOR UPDATE STATEMENTS
//INCREMENT A SINGLE COLUMN'S VALUE
require 'increment.php';