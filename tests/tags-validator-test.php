<?php

declare(strict_types=1);

require dirname(__DIR__).'/tools/validate-tags.php';

validateTags(dirname(__DIR__));
fwrite(STDOUT, "public project catalogue: valid\n");
