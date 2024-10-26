<?php

namespace App\Http\Controllers;

use App\Models\EmailDelegadoModel;
use App\Models\EquipeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google;

class Oauth2Controller extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
  }
  /**
   * Index for OAuth2.
   *
   * @param  Request  $request
   */

  public function index(Request $request)
  {
    $state = bin2hex(random_bytes(128/8));
    $email = 'pascom@icmesteio.org.br';
    try {
      $client = new Google\Client();
      $client->setAccessType('offline');
      $client->setApprovalPrompt('none');
      $client->setIncludeGrantedScopes(true);
      $client->setState($state);
      $client->setLoginHint($email);
      $client->setSubject($email);
      $access_token = $request->session()->get('access_token');
      $part = '';
      if ($request->get('part')) {
        $request->session()->put('part', $request->get('part'));
        $request->session()->save();
      }
      if ($access_token) {
        $client->setAccessToken($access_token);
        if ($request->session()->get('part')) {
          $part = $request->session()->get('part');
          $request->session()->forget('part');
        }
        return redirect($request->getSchemeAndHttpHost().$part);
      } else {
        $piece  = "/oauth2/callback";
        return redirect($piece);
      }
    } catch (\Exception $e) {
      return "<pre>Ocorreu uma excessão carregando os módulos da Gooogle: <br>".$e->getMessage()."</pre>";
    }
    return null;
  }

  public function callback(Request $request)
  {
    if (Storage::missing('json/client_secret.json')) {
      return "<pre>Não foi possível encontrar as credenciais do cliente da Google.</pre>";
    }
    $path  = storage_path('app/public/json');
    try {
      $server = $request->getSchemeAndHttpHost();
      $piece  = "/oauth2/callback";
      $client = new Google\Client();
      $client->setAuthConfigFile($path . '/client_secret.json');
      $client->setRedirectUri($server . $piece);
      $client->addScope('https://www.googleapis.com/auth/admin.directory.group');
      $client->addScope('https://www.googleapis.com/auth/admin.directory.user');
      $client->addScope('https://www.googleapis.com/auth/drive');
      if ($request->has('code')) {
        $client->authenticate($request->input('code'));
        $request->session()->put('access_token', $client->getAccessToken());
        $request->session()->save();
        $filtered = filter_var($server."/oauth2", FILTER_SANITIZE_URL);
      } else {
        $authUrl = $client->createAuthUrl();
        $filtered = filter_var($authUrl, FILTER_SANITIZE_URL);
      }
      return redirect($filtered);
    } catch (\Exception $e) {
      return "<pre>Ocorreu uma excessão carregando os módulos da Gooogle: <br>".$e->getMessage()."</pre>";
    }
    return null;
  }

  public function logout(Request $request) {
    $request->session()->forget('access_token');
    $part = '';
    if ($request->get('part')) {
      $part = $request->get('part');
    }
    return redirect($request->getSchemeAndHttpHost().$part);
  }
}
