<?= HTML::form_open('login', 'user/login') ?>
Email <?= HTML::input('user->email') ?>
<br/>
Password <?= HTML::password('user->password') ?>
<br/>
<?= HTML::submit('login', 'Login') ?>
<?= HTML::checkbox('user->remember_me', array('checked' => TRUE)) ?> remember me
<?= HTML::form_close() ?>
