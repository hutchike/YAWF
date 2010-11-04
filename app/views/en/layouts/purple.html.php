<div id="container">
    <div id="header">
        <?= HTML::link('', HTML::img('images/yawf-240x50.png', array('width' => 240, 'height' => 50, 'alt' => 'YAWF'))) ?>

        <div id="login">
            <p><strong><?= $greeting ?></strong> <?= HTML::link('admin/login', 'Log in') ?> or <?= HTML::link('admin/sign_up', 'sign up') ?> to get involved.</p>
        </div>
    </div>

    <div id="menu">
        <ul>
            <li<?= array_key($active_tab, 'default/index') ?>><?= HTML::link("default", 'Welcome') ?></li>
            <li<?= array_key($active_tab, 'project/news') ?>><?= HTML::link("project/news", 'News') ?></li>
            <li<?= array_key($active_tab, 'project/faq') ?>><?= HTML::link("project/faq", 'FAQ') ?></li>
            <li<?= array_key($active_tab, 'project/cookbook') ?>><?= HTML::link("project/cookbook", 'Cookbook') ?></li>
            <li<?= array_key($active_tab, 'project/code') ?>><?= HTML::link("project/code", 'Code browser') ?></li>
            <li<?= array_key($active_tab, 'phpdocs') ?>><?= HTML::link("phpdocs", 'PHP Docs') ?></li>
            <li<?= array_key($active_tab, 'project/download') ?>><?= HTML::link("project/download", 'Download') ?></li>
        </ul>
    </div>

    <div id="widgets">
        <!-- TODO -->
    </div>

    <div id="body">
        <div id="main_col">
            <div id="content">
                <h2 id="title"><?= $title ?></h2>
<?= $content ?>
            </div>
        </div>
        <div id="side_col">
            <div id="account">
                <!-- TODO -->
            </div>
        </div>
    </div>

    <div id="footer">
        Copyright &copy; <?= date('Y') ?> Guanoo, Inc. Released under the terms of the <?= HTML::link('http://www.gnu.org/licenses/gpl-3.0.html', 'GPL3') ?>. By using this site you accept our <?= HTML::link('project/terms', 'terms &amp; conditions') ?>. Please use our <?= HTML::link('project/contact', 'contact form') ?> to get in touch.
    </div>
</div>
