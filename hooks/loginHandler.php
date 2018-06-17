//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class instagramlh_hook_loginHandler extends _HOOK_CLASS_
{
	/**
	 * Get all handler classes
	 */
	static public function handlerClasses()
	{
        $return = parent::handlerClasses();
        $return[] = 'IPS\instagramlh\Login\Handler\OAuth2\Instagram';

        return $return;
	}
}
