<?php
/**
 * @brief		Instagram Login Handler
 * @author		<a href='https://recouse.github.io'>Firdavs Khaydarov</a>
 * @copyright	(c) Firdavs Khaydarov
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		17 Jun 2018
 */

namespace IPS\instagramlh\Login\Handler\OAuth2;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Instagram Login Handler
 */
class _Instagram extends \IPS\Login\Handler\OAuth2
{
    /**
     * Get title
     *
     * @return	string
     */
    public static function getTitle()
    {
        return 'login_handler_Instagram';
    }

    /**
     * Should client credentials be sent as an "Authoriation" header, or as POST data?
     *
     * @return	string
     */
    protected function _authenticationType()
    {
        return static::AUTHENTICATE_POST;
    }

    /**
     * ACP Settings Form
     *
     * @return	array	List of settings to save - settings will be stored to core_login_methods.login_settings DB field
     * @code
    return array( 'savekey'	=> new \IPS\Helpers\Form\[Type]( ... ), ... );
     * @endcode
     */
    public function acpForm()
    {
        \IPS\Member::loggedIn()->language()->words['login_acp_desc'] = \IPS\Member::loggedIn()->language()->addToStack('login_acp_will_reauth');
        \IPS\Member::loggedIn()->language()->words['oauth_client_id'] = \IPS\Member::loggedIn()->language()->addToStack('login_instagram_client');
        \IPS\Member::loggedIn()->language()->words['oauth_client_client_secret'] = \IPS\Member::loggedIn()->language()->addToStack('login_instagram_secret');

        return array_merge(
            array(
                'real_name'	=> new \IPS\Helpers\Form\Radio( 'login_real_name', isset( $this->settings['real_name'] ) ? $this->settings['real_name'] : 1, FALSE, array(
                    'options' => array(
                        1			=> 'login_real_name_instagram',
                        0			=> 'login_real_name_disabled',
                    ),
                    'toggles' => array(
                        1			=> array( 'login_update_name_changes_inc_optional' ),
                    )
                ), NULL, NULL, NULL, 'login_real_name' ),
            ),
            parent::acpForm()
        );

        return $return;
    }

    /**
     * Get the button color
     *
     * @return	string
     */
    public function buttonColor()
    {
        return '#003569';
    }

    /**
     * Get the button icon
     *
     * @return	string
     */
    public function buttonIcon()
    {
        return 'instagram';
    }

    /**
     * Get button text
     *
     * @return	string
     */
    public function buttonText()
    {
        return 'login_instagram';
    }

    /**
     * Get button class
     *
     * @return	string
     */
    public function buttonClass()
    {
        return 'ipsSocial_instagram';
    }

    /**
     * Get logo to display in information about logins with this method
     * Returns NULL for methods where it is not necessary to indicate the method, e..g Standard
     *
     * @return	\IPS\Http\Url
     */
    public function logoForDeviceInformation()
    {
        return \IPS\Theme::i()->resource( 'logos/login/Instagram.png', 'instagramlh', 'interface' );
    }

    /**
     * Grant Type
     *
     * @return	string
     */
    protected function grantType()
    {
        return 'authorization_code';
    }

    /**
     * Get scopes to request
     *
     * @param	array|NULL	$additional	Any additional scopes to request
     * @return	array
     */
    protected function scopesToRequest( $additional=NULL )
    {
        return array(
            'basic'
        );
    }

    /**
     * Authorization Endpoint
     *
     * @param	\IPS\Login	$login	The login object
     * @return	\IPS\Http\Url
     */
    protected function authorizationEndpoint( \IPS\Login $login )
    {
        return \IPS\Http\Url::external('https://api.instagram.com/oauth/authorize');
    }

    /**
     * Token Endpoint
     *
     * @return	\IPS\Http\Url
     */
    protected function tokenEndpoint()
    {
        return \IPS\Http\Url::external('https://api.instagram.com/oauth/access_token');
    }

    /**
     * Get authenticated user's identifier (may not be a number)
     *
     * @param	string	$accessToken	Access Token
     * @return	string
     */
    protected function authenticatedUserId( $accessToken )
    {
        return $this->_userData( $accessToken )['data']['id'];
    }

    /**
     * Get authenticated user's username
     * May return NULL if server doesn't support this
     *
     * @param	string	$accessToken	Access Token
     * @return	string|NULL
     */
    protected function authenticatedUserName( $accessToken )
    {
        if ( isset( $this->settings['real_name'] ) and $this->settings['real_name'] )
        {
            return $this->_userData( $accessToken )['data']['full_name'];
        }
        return NULL;
    }

    /**
     * Get user's profile photo
     * May return NULL if server doesn't support this
     *
     * @param	\IPS\Member	$member	Member
     * @return	\IPS\Http\Url|NULL
     * @throws	\IPS\Login\Exception	The token is invalid and the user needs to reauthenticate
     * @throws	\DomainException		General error where it is safe to show a message to the user
     * @throws	\RuntimeException		Unexpected error from service
     */
    public function userProfilePhoto( \IPS\Member $member )
    {
        if ( !( $link = $this->_link( $member ) ) )
        {
            throw new \IPS\Login\Exception( NULL, \IPS\Login\Exception::INTERNAL_ERROR );
        }

        $photoUrl = $this->_userData( $link['token_access_token'] )['data']['profile_picture'];

        return \IPS\Http\Url::external( $photoUrl );
    }

    /**
     * Get user's profile name
     * May return NULL if server doesn't support this
     *
     * @param	\IPS\Member	$member	Member
     * @return	string|NULL
     * @throws	\IPS\Login\Exception	The token is invalid and the user needs to reauthenticate
     * @throws	\DomainException		General error where it is safe to show a message to the user
     * @throws	\RuntimeException		Unexpected error from service
     */
    public function userProfileName( \IPS\Member $member )
    {
        if ( !( $link = $this->_link( $member ) ) )
        {
            throw new \IPS\Login\Exception( NULL, \IPS\Login\Exception::INTERNAL_ERROR );
        }

        return $this->_userData( $link['token_access_token'] )['data']['full_name'];
    }

    /**
     * Syncing Options
     *
     * @param	\IPS\Member	$member			The member we're asking for (can be used to not show certain options iof the user didn't grant those scopes)
     * @param	bool		$defaultOnly	If TRUE, only returns which options should be enabled by default for a new account
     * @return	array
     */
    public function syncOptions( \IPS\Member $member, $defaultOnly = FALSE )
    {
        $return = array();

        if ( isset( $this->settings['update_name_changes'] ) and $this->settings['update_name_changes'] === 'optional' and isset( $this->settings['real_name'] ) and $this->settings['real_name'] )
        {
            $return[] = 'name';
        }

        $return[] = 'photo';

        return $return;
    }

    /**
     * @brief	Cached user data
     */
    protected $_cachedUserData = array();

    /**
     * Get user data
     *
     * @param	string	$accessToken	Access Token
     * @throws	\IPS\Login\Exception	The token is invalid and the user needs to reauthenticate
     * @throws	\RuntimeException		Unexpected error from service
     */
    protected function _userData( $accessToken )
    {
        if ( !isset( $this->_cachedUserData[ $accessToken ] ) )
        {
            $response = \IPS\Http\Url::external( "https://api.instagram.com/v1/users/self" )
                ->setQueryString(
                    array(
                        'access_token' => $accessToken
                    )
                )
                ->request()
                ->get()
                ->decodeJson();

            if ( isset( $response['error'] ) )
            {
                throw new \IPS\Login\Exception( $response['error']['message'], \IPS\Login\Exception::INTERNAL_ERROR );
            }

            $this->_cachedUserData[ $accessToken ] = $response;
        }
        return $this->_cachedUserData[ $accessToken ];
    }
}