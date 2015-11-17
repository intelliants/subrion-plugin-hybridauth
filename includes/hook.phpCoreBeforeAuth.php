<?php
//##copyright##

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	try {
		if ($iaCore->requestPath)
		{
			require_once dirname(__FILE__) . '/Hybrid/Auth.php';

			$providerName = $iaCore->requestPath[0];

			$hybridauthConfig = array(
				"base_url" => IA_URL . 'hybridauth/',

				"providers" => array(
					// openid providers
					"OpenID" => array(
						"enabled" => true
					),

					"Google" => array(
						"enabled" => (bool)$iaCore->get('hybrid_google'),
						"keys"    => array("id" => $iaCore->get('hybrid_google_id'), "secret" => $iaCore->get('hybrid_google_secret')),
						"scope"   => "https://www.googleapis.com/auth/userinfo.profile " .
									 "https://www.googleapis.com/auth/userinfo.email"
					),

					"Facebook" => array(
						"enabled" => (bool)$iaCore->get('hybrid_facebook'),
						"keys"    => array("id" => $iaCore->get('hybrid_facebook_id'), "secret" => $iaCore->get('hybrid_facebook_secret')),
						"display" => "popup",
						"trustForwarded" => true
					),

					"Github" => array(
						"enabled" => (bool)$iaCore->get('hybrid_github'),
						"keys"    => array("id" => $iaCore->get('hybrid_github_id'), "secret" => $iaCore->get('hybrid_github_secret'))
					),
				),

				// if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
				"debug_mode" => (bool)$iaCore->get('hybrid_debug_mode'),
				"debug_file" => IA_TMP . 'hybridauth.txt',
			);

			$hybridauth = new Hybrid_Auth($hybridauthConfig);

			if (!in_array($providerName, array('twitter', 'google', 'facebook', 'github')))
			{
				throw new Exception("Incorrect provider name.");
			}

			$provider = $hybridauth->authenticate(ucfirst($providerName));

			if ($user_profile = $provider->getUserProfile())
			{
				$iaUsers = $iaCore->factory('users');

				$authNeeded = false;
				if ($memberInfo = $iaCore->iaDb->row_bind(iaDb::ALL_COLUMNS_SELECTION, "`{$providerName}_id` = :id", array('id' => $user_profile->identifier), iaUsers::getTable()))
				{
					$authNeeded = true;
				}
				elseif ($memberInfo = $iaCore->iaDb->row_bind(iaDb::ALL_COLUMNS_SELECTION, "`email` = :email_address", array('email_address' => $user_profile->email), iaUsers::getTable()))
				{
					$authNeeded = true;

					// sync members by email
					$iaUsers->update(array('id' => $memberInfo['id'], $providerName . '_id' => $user_profile->identifier));
				}
				else
				{
					// register new member
					$memberRegInfo['username'] = '';
					$memberRegInfo['email'] = $user_profile->email;
					$memberRegInfo['fullname'] = $user_profile->displayName;
					// $memberRegInfo['avatar'] = $user_profile->photoURL;
					$memberRegInfo['disable_fields'] = true;
					$memberRegInfo[$providerName . '_id'] = $user_profile->identifier;

					$memberInfo['id'] = $iaUsers->register($memberRegInfo);

					// no need to validate address
					$iaUsers->update(array('id' => $memberInfo['id'], 'sec_key' => '', 'status' => iaCore::STATUS_ACTIVE));

					$authNeeded = true;
				}

				// authorize found member
				if ($authNeeded)
				{
					$iaUsers->getAuth($memberInfo['id']);
				}
			}
			else
			{
				throw new Exception("User is not logged in.");
			}
		}
	}

	catch (Exception $e)
	{
		$iaCore->iaView->setMessages("HybridAuth error: " . $e->getMessage(), iaView::ERROR);
	}
}