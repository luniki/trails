<?php

# Copyright (c)  2007 - Marcus Lunzenauer <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.


# load required files
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/collector.php';
require_once 'simpletest/mock_objects.php';

require_once 'varstream.php';

# define TRAILS_ROOT
define('TRAILS_ROOT', dirname(__FILE__) . '/trails_root/app');

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . TRAILS_ROOT);

# load required files
$lib_path = dirname(__FILE__) . '/../lib/src/';
require_once $lib_path . 'dispatcher.php';
require_once $lib_path . 'response.php';
require_once $lib_path . 'controller.php';
require_once $lib_path . 'inflector.php';
require_once $lib_path . 'flash.php';
require_once $lib_path . 'exception.php';

require_once 'lib/mocks.php';

# collect all tests
$all = new TestSuite('All tests');
$all->collect(dirname(__FILE__).'/lib', new SimplePatternCollector('/test.php$/'));

# use text reporter if cli
if (sizeof($_SERVER['argv']))
  $all->run(new TextReporter());

# use html reporter if cgi
else
  $all->run(new HtmlReporter());
