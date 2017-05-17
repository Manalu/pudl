<?php

//SELECT statement shortcut to get a single cell value
//Returns string of the cell's value (false if not found)
$db->string()->cell('table', 'column');
pudlTest('SELECT column FROM `table` LIMIT 1');




//SELECT statement shortcut to get a single cell value using a clause
//Returns string of the cell's value (false if not found)
$db->string()->cell('table', 'column', 'id=col');
pudlTest('SELECT column FROM `table` WHERE (`id`=`col`) LIMIT 1');




//SELECT statement shortcut to get a single cell value using a clause
//Returns string of the cell's value (false if not found)
$db->string()->cell('table', 'column', ['id'=>'value']);
pudlTest("SELECT column FROM `table` WHERE (`id`='value') LIMIT 1");




//SELECT statement shortcut to get a single cell value by another column's value
//Returns string of the cell's value (false if not found)
$db->string()->cellId('table', 'column', 'id', 'value');
pudlTest("SELECT column FROM `table` WHERE (`id`='value') LIMIT 1");




//SELECT statement shortcut to get a single cell value by another column's value
//Returns string of the cell's value (false if not found)
$db->string()->cellId('table', 'column', 'id', pudl::unhex('abcdef1230'));
pudlTest("SELECT column FROM `table` WHERE (`id`=UNHEX('abcdef1230')) LIMIT 1");




$db->string()->count('table');
pudlTest("SELECT COUNT(*) FROM `table` LIMIT 1");




$db->string()->count('table', 'cell=1');
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`=1) LIMIT 1");




$db->string()->count('table', ['cell'=>10]);
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`=10) LIMIT 1");




$db->string()->count('table', 'cell > 1');
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`>1) LIMIT 1");




$db->string()->count('table', 'cell < 1');
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`<1) LIMIT 1");




$db->string()->count('table', 'cell >= 1');
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`>=1) LIMIT 1");




$db->string()->count('table', 'cell <= 1');
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`<=1) LIMIT 1");




$db->string()->count('table', 'cell != 1');
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`!=1) LIMIT 1");




$db->string()->count('table', 'cell <=> 1');
pudlTest("SELECT COUNT(*) FROM `table` WHERE (`cell`<=>1) LIMIT 1");
