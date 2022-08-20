<?php

namespace App\Http\Controllers;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use App\Jobs\AmoJob;
use App\Models\AmoUser;
use App\Rules\AmoDomain;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class AmoController extends Controller
{
    public function authForm()
    {
        return view('amo/auth');
    }

    public function auth(Request $request)
    {
        $request->validate([
            'client_secret' => 'required|max:250',
            'client_id' => 'required|max:250',
            'auth_code' => 'required|max:1000',
            'base_domain' => ['required', 'max:250', new AmoDomain()],
            'redirect_uri' => 'required|max:250'
        ]);

        $clientSecret = $request->post('client_secret');
        $clientId = $request->post('client_id');
        $authCode = $request->post('auth_code');
        $baseDomain = $request->post('base_domain');
        $redirectUri = $request->post('redirect_uri');

        if (str_starts_with($baseDomain, "https://")) {
            $baseDomain = substr($baseDomain, 8);
        }

        $provider = new AmoCRM([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
        ]);

        $provider->setBaseDomain($baseDomain);

        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $authCode
            ]);

            $refreshToken = $accessToken->getRefreshToken();

            session()->put('amo_access_token', $accessToken->jsonSerialize());

            $cookies = [
                cookie()->forever('amo_refresh_token', $refreshToken),
                cookie()->forever('amo_client_id', $clientId),
                cookie()->forever('amo_client_secret', $clientSecret),
                cookie()->forever('amo_base_domain', $baseDomain),
            ];

            AmoUser::firstOrCreate([
                'baseDomain' => $baseDomain
            ]);

            return Redirect::to('/amo/dashboard')->withCookies($cookies);
        } catch (IdentityProviderException $e) {
            dd($e);
        }
    }

    public function dashboard()
    {
        return view('amo/dashboard');
    }

    public function updateData(Request $request)
    {
        /** @var array $amoCredentials */
        $amoCredentials = $request->amoCredentials;
        AmoJob::dispatch($amoCredentials, $request->amoUser->id);

        return redirect('/amo/dashboard');
    }
}
