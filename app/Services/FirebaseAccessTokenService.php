<?php

namespace App\Services;

use Google\Auth\OAuth2;
use Illuminate\Support\Facades\File;

class FirebaseAccessTokenService
{
	protected $credentials;

	public function __construct()
	{
		$this->credentials = json_decode(File::get(config('firebase.credentials_file')), true);
	}

	public function getAccessToken()
	{
		$oauth = new OAuth2([
			'audience' => 'https://oauth2.googleapis.com/token',
			'issuer' => $this->credentials['client_email'],
			'signingAlgorithm' => 'RS256',
			'signingKey' => $this->credentials['private_key'],
			'tokenCredentialUri' => $this->credentials['token_uri'],
			'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
		]);

		$token = $oauth->fetchAuthToken();
		return $token['access_token'];
	}
}
