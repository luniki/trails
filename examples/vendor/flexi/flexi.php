<?php

/*
 * flexi.php - bootstrapping flexi
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

define('FLEXI_VERSION', '0.1.4');

/**
 * Bootstrapping file for flexi. Just include this to get going.
 *
 * @package   flexi
 */

require_once 'template.php';
require_once 'template_factory.php';
require_once 'php_template.php';
require_once 'js_template.php';

require_once 'helper/js_helper.php';
require_once 'helper/json.php';
require_once 'helper/prototype_helper.php';
require_once 'helper/scriptaculous_helper.php';
require_once 'helper/tag_helper.php';
require_once 'helper/text_helper.php';

