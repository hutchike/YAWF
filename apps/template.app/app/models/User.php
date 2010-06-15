<?
class User extends Model
{
    public function setup()
    {
        $this->set_timestamp('created_at', 'updated_at');
        $this->validates('email', 'is_valid_email');
        $this->validates('email', 'is_valid_length', array('shortest' => 6, 'longest' => 70));
    }
}

// End of User.php
