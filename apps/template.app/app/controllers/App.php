<?
class App_controller extends Controller
{
    public function before()
    {
        $this->render->session_user_id = $this->session->user_id;
        $this->render->session_user_email = $this->session->user_email;
        $this->render->user_email = $this->cookie->user_email;
        $this->render->title = '';
        $this->render->onload = '';
        $this->render->layout = $this->layout();
    }

    public function layout()
    {
        if ($this->session->layout) return $this->session->layout;

        $layout = 'laptop';
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, 'Android') > 0)
            $layout = 'Android';
        elseif (strpos($agent, 'iPhone') || strpos($agent, 'iPod') > 0)
            $layout = 'iPhone';
        elseif (strpos($agent, 'iPad') > 0)
            $layout = 'iPad';
        elseif (strpos($agent, 'Symbian') > 0)
            $layout = 'Symbian';

        return ($this->session->layout = $layout);
    }
}

// End of App.php
