<?php

$db->string()->select('*', ['x' => ['table1',
	['join' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['right' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` RIGHT JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['inner' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` INNER JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['outer' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` OUTER JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['natural' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` NATURAL JOIN `table2` AS `y`');



//NOTE: 'hack' will LEFT JOIN a group of tables with ZERO SQL SAFETY
$db->string()->select('*', ['xx' => ['table1',
	['hack' => 'table2 AS yy JOIN table3 AS zz'],
]]);
pudlTest('SELECT * FROM `table1` AS `xx` LEFT JOIN (table2 AS yy JOIN table3 AS zz)');




$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'using'=>'column'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` USING (`column`)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'on'=>'x.column=y.column'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=`y`.`column`)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=y.column'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=`y`.`column`)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=0'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=0)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=1'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=1)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=-1'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=-1)');
