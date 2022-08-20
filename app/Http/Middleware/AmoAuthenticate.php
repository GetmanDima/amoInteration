<?php

namespace App\Http\Middleware;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use App\Http\Requests\AmoRequest;
use App\Models\AmoUser;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;

class AmoAuthenticate
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $accessTokenParams = $request->session()->get('amo_access_token');
        $refreshToken = $request->cookie('amo_refresh_token');
        $clientId = $request->cookie('amo_client_id');
        $clientSecret = $request->cookie('amo_client_secret');
        $baseDomain = $request->cookie('amo_base_domain');

        if (isset($accessTokenParams)) {
            $accessToken = new AccessToken($accessTokenParams);

            if ($accessToken->hasExpired()) {
                try {
                    $accessToken = $this->getAccessToken($clientId, $clientSecret, $accessToken->getRefreshToken());
                } catch (IdentityProviderException $e) {
                    return redirect('/amo/auth');
                }
            }
        } else if (isset($refreshToken)) {
            try {
                $accessToken = $this->getAccessToken($clientId, $clientSecret, $refreshToken);
            } catch (IdentityProviderException $e) {
                return redirect('/amo/auth');
            }
        } else {
            return redirect('/amo/auth');
        }

        $apiClient = new \AmoCRM\Client\AmoCRMApiClient($clientId, $clientSecret, null);
        $apiClient->setAccessToken($accessToken);
        $apiClient->setAccountBaseDomain($baseDomain);

        $amoUser = Amouser::where('baseDomain', $baseDomain)->first();

        if (is_null($amoUser)) {
            return redirect('/amo/auth');
        }

        $request->amoUser = $amoUser;

        $request->amoApiClient = $apiClient;

        $request->amoCredentials = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'baseDomain' => $baseDomain,
            'accessToken' => $accessToken
        ];

        return $next($request);
    }

    /**
     * @throws IdentityProviderException
     */
    public function getAccessToken($clientId, $clientSecret, $refreshToken) {
        $provider = new AmoCRM([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);

        $accessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken
        ]);

        session()->put('amo_access_token', $accessToken->jsonSerialize());
        cookie()->forever('amo_refresh_token', $accessToken->getRefreshToken());

        return $accessToken;
    }
}
