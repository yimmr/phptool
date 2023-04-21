<?php

include __DIR__ . '/imdebug/load.php';

use \Imon\Debug;

Debug::log(123);
Debug::log([1, 2, 3], 'dd');
Debug::logHTML([new Debug, 789]);
Debug::logHTML(new Debug);