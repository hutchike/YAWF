<?
// Copyright (c) 2009 Guanoo, Inc.
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.

// ------------------------------------------------------------
// The test controller is chosen in the URL, e.g. "/user_test".
// Tip: use ".part" content type like this "/simple_test.part".

// Check that we're using the "App_test" class to run some tests!

if (!method_exists($app, 'run_tests')) $app->redirect('', TRUE);
?>
<tests testing="<?= $testee_name ?>">
  <link rel="stylesheet" href="/styles/test/runner.css" type="text/css" />
  <div class="test_controller">
    <div class="test_controller_header">Testing <?= $testee_name ?></div>

<? foreach ($test_cases as $method => $cases): ?>
    <div class="test_method">
      <div class="test_method_header"><?= $method ?>()</div>
      <ul>
<? foreach ($cases as $case): ?>
        <li class="test_case">
<? if ($case->passed()): ?>
          <div class="test_case_passed">Should <?= $case->get_desc() ?>: passed</div>
<? else: ?>
          <div class="test_case_failed">Should <?= $case->get_desc() ?>: failed</div>
          <div class="test_case_data"><?= $case->get_data_as_text() ?></div>
<? endif // $case->passed?>
        </li>
<? endforeach // $cases as $case?>
      </ul>
    </div><!-- class="test_method" -->
<? endforeach // $test_cases as $method => $cases ?>
    <div class="test_results">
      <div class="test_results_header">Test results</div>
      <div class="test_cases_passed"><?= $count_passed ?> test case<?= $count_passed != 1 ? 's' : '' ?> passed</div>
<? if ($count_failed): ?>
      <div class="test_cases_failed"><?= $count_failed ?> test case<?= $count_failed != 1 ? 's' : '' ?> failed</div>
<? endif ?>
    </div><!-- class="test_results" -->
<? if ($test_run_output): ?>
    <div class="test_run_output">
      <div class="test_run_output_header">Test run output</div>
      <div class="test_run_output_data"><?= $test_run_output ?></div>
    </div><!-- class="test_run_output" -->
<? endif ?>
  </div><!-- class="test_controller" -->
</tests>
