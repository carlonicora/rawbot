<?php
require_once '../../vendor/autoload.php';

use CarloNicora\Minimalism\Minimalism;
use CarloNicora\Minimalism\Raw\Models\Setup;

$minimalism = new Minimalism();
$minimalism->render(Setup::class);