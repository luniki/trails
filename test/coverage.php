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


$PHPCOVERAGE_HOME = '/usr/share/php/spikephpcoverage/';
$PHPCOVERAGE_REPORT_DIR = '/tmp/report/';
$PHPCOVERAGE_APPBASE_PATH = '/tmp/report/';

require_once $PHPCOVERAGE_HOME . 'phpcoverage.inc.php';
require_once $PHPCOVERAGE_HOME . 'CoverageRecorder.php';
require_once $PHPCOVERAGE_HOME . 'reporter/HtmlCoverageReporter.php';

$reporter = new HtmlCoverageReporter('Code Coverage Report', '', $PHPCOVERAGE_REPORT_DIR);

$includePaths = array('../lib');
$excludePaths = array('');
$cov = new CoverageRecorder($includePaths, $excludePaths, $reporter);

$cov->startInstrumentation();
include 'all_tests.php';
$cov->stopInstrumentation();

$cov->generateReport();
$reporter->printTextSummary();
