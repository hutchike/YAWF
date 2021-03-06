<?
// Copyright (c) 2010 Guanoo, Inc.
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

// Base controller "lib/Controller.php" tests here
// See this test controller run at URL "/lib_test"

/**
 * The Controller_test_controller class tests the "Controller" and "App"
 * classes in the YAWF "lib" directory by checking that they provide the
 * services expected, e.g. flashes, cookies, sessions, params, redirects.
 * Basically, it's a controller to test controllers. All a bit recursive.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Controller_test_controller extends Controller
{
    const TEST_VIEW = 'test_view'; // in the "default" folder so we can find it
    const TEST_TEXT = 'Just some text to test things';
    const TEST_FLASH = 'A notice to set in the flash';
    const STALE_SECS = 10;

    // Test that we can set flash, cookie and session variables (use a redirect)
    // IMPORTANT: This MUST be the first test to avoid side-effects from others!

    public function flash_and_cookie_and_session_test()
    {
        $time_in_cookie = $this->cookie->remember_the_time_now;
        $time_in_session = $this->session->remember_the_time_now;
        $time_now = time();
        $cookie_is_stale = $time_now - $time_in_cookie > self::STALE_SECS;
        $session_is_stale = $time_now - $time_in_session > self::STALE_SECS;
        if ($cookie_is_stale || $session_is_stale)
        {
            $this->flash->notice = 'Flash should work';
            $this->cookie->remember_the_time_now = $time_now;
            $this->session->remember_the_time_now = $time_now;
            $type = $this->app->get_content_type();
            header('Location: ' . uri("controller_test.$type?$time_now"));
            exit;
        }

        $this->should('set a "flash" notice',
                      $this->flash->notice === 'Flash should work', $this->flash->notice);

        $this->flash->now(array('notice2' => 'Flash now should also work'));
        $this->should('set a "flash now" notice',
                      $this->flash->notice2 === 'Flash now should also work', $this->flash->notice2);

        $this->flash->now = 'Flash now (conventional) should also work';
        $this->should('set a "flash now" (conventional) notice',
                      $this->flash->notice === 'Flash now (conventional) should also work', $this->flash->notice);

        $this->should('set a cookie variable',
                      !$cookie_is_stale, $time_in_cookie);

        $this->should('set a session variable',
                      !$session_is_stale, $time_in_session);
    }

    // Test that we can set up this controller for the application

    public function setup_for_app_test()
    {
        $render = new Object();
        $this->setup_for_app($this->app, $render);

        $this->should('have an "App_test" object called $this->app',
                      $this->app instanceof App_test, $this->app);
        $this->should('have "index" in $this->view',
                      $this->view === 'index', $this->view);
        $this->should('have an "Object" called $this->render',
                      $this->render instanceof Object, $this->render);
        $this->should('be able to cast $this->render into an empty array',
                      is_array((array)$this->render) && count((array)$this->render) === 0, $this->render);
        $this->should('have an "Object" called $this->params',
                      $this->params instanceof Object, $this->params);
        $this->should('have "' . DEFAULT_LANGUAGE . '" set as $this->lang()',
                      $this->lang() === DEFAULT_LANGUAGE, $this->lang());
        $this->should('have a seesion',
                      is_array($_SESSION), $_SESSION);
        $this->should('have a flash object', $this->flash instanceof Controller_flash, $this->flash);
        $this->should('have a cookie object', $this->cookie instanceof Request_cookie, $this->cookie);
        $this->should('have a server object', $this->server instanceof Request_server, $this->server);
        $this->should('have a session object', $this->session instanceof Request_session, $this->session);
    }

    // Test that we can render a view containing the content we expect

    public function render_test()
    {
        $original_view = $this->view;

        $this->set_view(self::TEST_VIEW);
        $this->render->title = self::TEST_TEXT;
        $html = $this->render();
        $this->should('render the index page with our test text',
                      strstr($html, self::TEST_TEXT) !== FALSE, $html);

        $this->set_view($original_view);
    }

    protected function test_view() // for render_test() above
    {
        $this->render->text = Controller_test_controller::TEST_TEXT;
    }

    // Test that we can setup render data with "app", "view" and "params" data

    public function setup_render_data_test()
    {
        $render = new Object();
        $this->setup_render_data($render);

        $this->should('have an "App_test" object called $render->app',
                      $render->app instanceof App_test, $render->app);
        $this->should('have "index" in $render->view',
                      $render->view === 'index', $render->view);
        $this->should('have an "Object" called $render->params',
                      $render->params instanceof Object, $render->params);
    }

    // Test that the default "before()" method doesn't change our state

    protected function before() {}
    public function before_test()
    {
        $vars_before_before = $this->get_state();
        parent::before();
        $this->should('do nothing',
                      $vars_before_before === $this->get_state(), $this->get_state());
    }

    // Test that the fefault "after()" method doesn't change our state

    protected function after() {}
    public function after_test()
    {
        $vars_before_after = $this->get_state();
        parent::after();
        $this->should('do nothing',
                      $vars_before_after === $this->get_state(), $this->get_state());
    }

    protected function get_state() // for before_test() and after_test() above
    {
        return serialize($this) . ' ' . serialize($_COOKIE) . ' ' . serialize($_SESSION);
    }

    // Test that we can set the view

    public function set_view_test()
    {
        $original_view = $this->view;

        $this->set_view(self::TEST_VIEW);
        $this->should('set the view to our test view',
                      $this->view === self::TEST_VIEW, $this->view);

        $this->view = $original_view;
    }

    // Test that we can set our params

    public function set_params_test()
    {
        $this->set_params(array('test' => '   "nice"'), // to be trimmed
                          array('trim_whitespace' => TRUE,
                                'format_as_html' => FALSE));
        $this->should('auto-trim the params',
                      $this->params->test === '"nice"', $this->params);

        $this->set_params(array('test' => '"nice"'), // to be HTML-formatted
                          array('trim_whitespace' => FALSE,
                                'format_as_html' => TRUE));
        $this->should('HTML-format the params',
                      $this->params->test === '&quot;nice&quot;', $this->params);
    }

    // Test that we can set the language code

    public function set_lang_test()
    {
        $this->app->set_lang('de');
        $this->should_not('set an unsupported language code',
                          $this->lang() === 'de', $this->lang());

        $this->app->set_lang('de', 'en,es,de');
        $this->should('set the language code',
                      $this->lang() === 'de', $this->lang());

        $this->app->set_lang(DEFAULT_LANGUAGE); // for the next test, and to render the results
    }

    // Test that we can get the language code

    public function get_lang_test()
    {
        $this->should('return the current language code',
                      $this->lang() === DEFAULT_LANGUAGE);
    }

    // Test that we can get the URI parts

    public function get_parts_of_the_uri_test()
    {
        $type = $this->app->get_content_type();

        $this->should('get the first part of the URI',
                      $this->part(0) === 'controller_test.' . $type, $this->part(0) . " but content type is $type");

        $this->should('get the first part of the URI with no extension',
                      $this->part(0, TRUE) === 'controller_test', $this->part(0, TRUE));
    }

    // Test that we cannot redirect when testing

    public function redirect_test()
    {
        $state_before = $this->get_state();
        $this->redirect('controller_test');
        $state_after = $this->get_state();
        $this->should('do nothing',
                      $state_before === $state_after);
    }

    // Test that we can report errors

    public function report_errors_test()
    {
        $this->app->add_error_message(self::TEST_TEXT);
        $mail = $this->report_errors();
        $this->should('report errors',
                      strpos($mail, self::TEST_TEXT) > 0, $mail);
    }

    // Test that we can send mail

    public function send_mail_test()
    {
        $render = array(
                    'from'      => 'test@localhost',
                    'to'        => 'test@localhost',
                    'subject'   => 'test email',
                    'errors'    => NULL,
                );
        $this->set_params(array('param1', 1)); // "errors" mail contains params
        $mail = $this->send_mail('errors', $render);
        $this->should('send mail',
                      strpos($mail, 'param1') > 0, $mail);
    }

    // Test we can set and get flash messages

    public function flash_test()
    {
        $this->should_not("have a flash notice yet", $this->flash->notice);
        $this->flash->now = self::TEST_FLASH;
        $this->should("have a flash notice now", $this->flash->notice == self::TEST_FLASH);
        $this->flash->warning = $this->flash->error = self::TEST_FLASH;
        $this->flash = new Controller_flash(); // simulate a page load
        $this->should("have a flash warning set", $this->flash->warning == self::TEST_FLASH);
        $this->should("have a flash error set", $this->flash->error == self::TEST_FLASH);
    }

    // Test that the "Text" helper works as expected

    public function text_helper_test()
    {
        $this->should('pluralize "person" as "people"',
                      Text::pluralize('person') === 'people');

        $this->should('singularize "people" as "person"',
                      Text::singularize('people') === 'person');

        $this->should('titleize "the_people WereCool" as "The People Were Cool"',
                      Text::titleize('the_people WereCool') === 'The People Were Cool');

        $this->should('camelize "my_var_name" as "MyVarName"',
                      Text::camelize('my_var_name') === 'MyVarName');

        $this->should('underscore "ThisOneWorks" as "this_one_works"',
                      Text::underscore('ThisOneWorks') === 'this_one_works');

        $this->should('humanize "just_another_day" as "Just another day"',
                      Text::humanize('just_another_day') === 'Just another day');

        $this->should('variablize "give and take" as "giveAndTake"', // ugly!!!
                      Text::variablize('give and take') === 'giveAndTake');

        $this->should('tableize "CoolPerson" as "cool_people"',
                      Text::tableize('CoolPerson') === 'cool_people');

        $this->should('classify "cool_people" as "CoolPerson"',
                      Text::classify('cool_people') === 'CoolPerson');

        $this->should('ordinalize "12" as "12th"',
                      Text::ordinalize('12') === '12th');

        $this->should('urlize " just another link " as "just_another_link"',
                      Text::urlize(' just another link ') === 'just_another_link');
    }

    // Can we run "should" tests? Hmmm

    public function should_test()
    {
        $this->should('pass in the test output', TRUE);
    }

    // Can we run "should_not" tests? :-)

    public function should_not_test()
    {
        $this->should_not('fail in the test output', FALSE);
    }
}

// End of Controller_test.php
