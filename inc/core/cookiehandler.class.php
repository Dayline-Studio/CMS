<?phpclass CookieHandler{	public static function set_cookie ($name, $value, $expire = 60*60*24, $timestamp = false)	{		$expire = $timestamp ? $expire : $expire+time();		return setcookie($name , $value , $expire);	}}