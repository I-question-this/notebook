<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\Notebook\AppInfo\Application::APP_ID, OCA\Notebook\AppInfo\Application::APP_ID . '-main');
Util::addStyle(OCA\Notebook\AppInfo\Application::APP_ID, OCA\Notebook\AppInfo\Application::APP_ID . '-main');

?>

<div id="notebook"></div>
