<?php

namespace App\Http\Controllers;

use App\library\MailClass;
use App\Models\AdministracaoModel;
use App\Models\User;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Google;

class UsuarioController extends Controller
{

    private function removeAcentos($text)
    {
      $lower = strtolower(preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"), $text));
      $sem_acento = ucwords($lower);
      return $sem_acento;
    }

    private function foundUserOnGoogle($email, $list)
    {
      foreach ($list as $nuser) {
        if ($nuser->getPrimaryEmail() ==  $email) {
          return true;
        }
      }
      return false;
    }

    private function getLoggedUserList($access_token)
    {
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $user      = new Google\Service\Directory\User($directory);
      $params = array(
        'customer' => 'my_customer',
        'maxResults' => 100,
        'orderBy' => 'email',
      );
      $results = $directory->users->listUsers($params);
      if (count($results->getUsers()) == 0) {
        return null;
      }
      return $results->getUsers();
    }

    private function addLoggedUser($access_token, $email, &$message)
    {
      $usuario = DB::table('users')->where('email_institucional', $email)->first();
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $user      = new Google\Service\Directory\User($directory);
      $user->primaryEmail = $usuario->email_institucional;
      $user->recoveryEmail = $usuario->email;
      $user->password = Crypt::decryptString($usuario->password);
      $sem_acento = $this->removeAcentos($usuario->name);
      $names = explode(" ", $sem_acento);
      $family = '';
      for ($i = 1; $i < count($names); $i++) {
        $family .= $names[$i];
        if ($i < (count($names) -1)) {
          $family .= " ";
        }
      }
      $user->name = array('familyName' => $family, 'givenName' => $names[0], 'fullName' => $usuario->name);
      if ($usuario->situacao != 1) {
        $user->suspended = true;
        if ($usuario->situacao == 2) {
            $user->suspensionReason = "Suspenso";
        } else if ($usuario->situacao == 0) {
            $user->suspensionReason = "Pendente";
        }
      } else {
        $user->suspended = false;
      }
      $user->notes = $usuario->registro;
      try {
        $list = $this->getLoggedUserList($access_token);
        if ($this->foundUserOnGoogle($email, $list)) {
          $message = "Não é possível incluir porque o email já existe no Google Cloud.";
          return false;
        }
        $result = $directory->users->insert($user);
        sleep(4);
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }
    private function updateLoggedUser(Request $request, $access_token, $is_status, &$message, $email, $newEmail)
    {
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $user      = new Google\Service\Directory\User($directory);
      if (!$is_status) {
        $sem_acento = $this->removeAcentos($request->name);
        $names = explode(" ", $sem_acento);
        $family = '';
        for ($i = 1; $i < count($names); $i++) {
          $family .= $names[$i];
          if ($i < (count($names) -1)) {
            $family .= " ";
          }
        }
        $user->name = array('familyName' => $family, 'givenName' => $names[0], 'fullName' => $request->name);
        $user->notes = $request->registro;
        if (isset($request->password)) {
          $user->password = $request->password;
        }
        $user->recoveryEmail = $request->email;
      }
      if ($newEmail != null) {
        $user->primaryEmail = $newEmail;
      }
      if ($request->situacao != 1) {
        $user->suspended = true;
        if ($request->situacao == 2) {
          $user->suspensionReason = "Suspenso";
        } else if ($request->situacao == 0) {
          $user->suspensionReason = "Pendente";
        }
      } else {
        $user->suspended = false;
      }
      try {
        $list = $this->getLoggedUserList($access_token);
        if ($this->foundUserOnGoogle($email, $list)) {
          $result = $directory->users->update($email, $user);
        }
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }

    private function updateEmailOrPasswordLoggedUser($access_token, $old_email, $email, $password, &$message)
    {
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $user      = new Google\Service\Directory\User($directory);
      try {
        $list = $this->getLoggedUserList($access_token);
        if ($this->foundUserOnGoogle($old_email, $list)) {
          if ($password) {
            $user->password = $password;
          } else if ($email) {
            $user->recoveryEmail = $email;
          }
          $result = $directory->users->update($old_email, $user);
        }
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }

    private function updateEmailGroupLoggedUser($access_token, $old_email, $email, &$message)
    {
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $user      = new Google\Service\Directory\User($directory);
      try {
        $list = $this->getLoggedUserList($access_token);
        if ($this->foundUserOnGoogle($old_email, $list)) {
          $user->primaryEmail = $email;
          $result = $directory->users->update($old_email, $user);
        }
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }
    private function deleteLoggedUser($access_token, $email, &$message, $found = true)
    {
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $user      = new Google\Service\Directory\User($directory);
      try {
        $list = $this->getLoggedUserList($access_token);
        if (!$this->foundUserOnGoogle($email, $list)) {
          if ($found) {
            $message = "Não é possível remover porque o email não existe no Google Cloud.";
            return false;
          }
          return true;
        }
        $result = $directory->users->delete($email);
        sleep(4);
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        $usuarios = User::where("id","!=",1)
          ->orderBy('situacao', 'ASC')
          ->orderBy('email_institucional', 'ASC')->get();
        $google_users = null;
        if ($access_token) {
          $google_users = $this->getLoggedUserList($access_token);
        }
        return view("usuario/list", compact('usuarios', 'access_token', 'google_users'));
    }

   /**
    * Add user to Google cloud.
    *
    * @param  \Illuminate\Http\Request  $request
    */
    public function addToGoogle(Request $request) {
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $email = urldecode($request->get('email'));
      if (empty($email)) {
        return redirect()->to("/usuario")->with("error", "usuário não possui um e-mail institucional");
      }
      if ($this->addLoggedUser($access_token, $email, $message)) {
        return redirect()->to("/usuario")->with("success","usuário adicionado na Google com sucesso!")->with('added', 'true')->with('email', urlencode($email));
      }
      if (!is_array($message)) {
        return redirect()->to("/usuario")->with("error", $message);
      }
      Log::info(json_encode($message));
      return redirect()->to("/usuario")->with("error", "não foi possível adicionar o usuário na Google Cloud.");
    }

    /**
     * Remove user from Google cloud.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function deleteFromGoogle(Request $request)
    {
      $email = urldecode($request->get('email'));
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if ($this->deleteLoggedUser($access_token, $email, $message)) {
        return redirect()->to("/usuario")->with("success","usuário removido da Google Cloud com sucesso!")->with('deleted', 'true')->with('email', urlencode($email));
      }
      if (!is_array($message)) {
        return redirect()->to("/usuario")->with("error", $message);
      }
      Log::info(json_encode($message));
      return redirect()->to("/usuario")->with("error", "não foi possível remover o usuário da Google Cloud.");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $usuario = User::findOrFail($id);
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        return view("usuario/edit",compact('usuario', 'access_token'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $email = $user->email_institucional;
        $user->name = $request->name;
        $user->registro = $request->registro;
        $user->email = $request->email;
        $user->situacao = $request->situacao;
        $user->perfil = $request->perfil;
        if (isset($request->email_institucional) && !empty($request->email_institucional)) {
          $user->email_institucional = $request->email_institucional . "@icmesteio.org.br";
          $newEmail = $user->email_institucional;
        } else {
          $newEmail = null;
        }
        if (isset($request->password)) {
          $user->password = Crypt::encryptString($request->password); // Hash::make($request->password)
        }
        $user->save();
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        if ($access_token) {
          if (!$this->updateLoggedUser($request, $access_token, false, $message, $email, $newEmail)) {
            if (!is_array($message)) {
              return redirect()->route("usuario.index")->with("error", $message);
            }
            Log::info(json_encode($message));
            return redirect()->route("usuario.index")->with("error", "não foi possível alterar o usuário da Google Cloud.");
          }
        }
        return redirect()->route("usuario.index")->with("success","Seu usuário foi alterado com sucesso!")->with('access_token', $access_token);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $message = "";
      $success = true;
      if ($access_token) {
        $user = User::findOrFail($id);
        $email = $user->email_institucional;
        if (!$this->deleteLoggedUser($access_token, $email, $message, false)) {
          $success = false;
        }
      }
      User::where("id","=",$id)->delete();
      if (!$success) {
        if (!is_array($message)) {
          return redirect()->to("/usuario")->with("error", $message);
        }
        Log::info(json_encode($message));
        return redirect()->to("/usuario")->with("error", "não foi possível remover o usuário da Google Cloud.");
      }
      return redirect()->route('usuario.index')->with("success", "Usuário excluído com sucesso.");
    }

    public function updateSituacao(Request $request)
    {
      $user = User::findOrFail($request->id);
      $email = $user->email_institucional;
      $administracao = AdministracaoModel::find(1);
      /*
      if($request->situacao == 3)
      {

          //Envia e-mail usuario
          $mail = new MailClass();
          $mail->sendEmail($administracao->usuario, $administracao->nome,"Cadastro aprovado","Seu cadastro foi aprovado em {$administracao->grupo}.",$user->email,$user->name);

          //Envia e-mail admin
          $mail = new MailClass();
          $mail->sendEmail($administracao->usuario, $administracao->nome,"Cadastro de usuário aprovado","Há um novo usuário aprovado aguardando a criação do e-mail em {$administracao->grupo}.","solicitacao@escoteirosonline.com.br");

      }
      else if($request->situacao == 1)
      {
          $gerentes = User::where('perfil','=',2)->where('situacao','=',1)->get();
          foreach ($gerentes as $gerente) {
            //Envia e-mail gerente
            $mail = new MailClass();
            $mail->sendEmail($administracao->usuario, $administracao->nome, "Cadastro de e-mail usuário criado", "O e-mail {$user->email_institucional} foi cadastrado com sucesso em {$administracao->grupo}.",$gerente->email);
          }
      }
      */
      $user->situacao = $request->situacao;
      $user->save();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if ($access_token) {
        if (!$this->updateLoggedUser($request, $access_token, true, $message, $email, null)) {
          if (!is_array($message)) {
            return redirect()->route("usuario.index")->with("error", $message);
          }
          Log::info(json_encode($message));
          return redirect()->route("usuario.index")->with("error", "não foi possível atualizar o usuário da Google Cloud.");
        }
      }
      return redirect()->route('usuario.index')->with("success","Usuário atualizado com sucesso.")->with('access_token', $access_token);
    }

    public function atualizarSenha(Request $request)
    {
      $user = User::findOrFail(auth()->id());
      $user->password = Crypt::encryptString($request->password); // Hash::make($request->password)
      $user->save();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if ($access_token) {
        if (!$this->updateEmailOrPasswordLoggedUser($access_token, $user->email_institucional, null, $request->password, $message)) {
          if (!is_array($message)) {
            return redirect()->route("home")->with("error", $message);
          }
          Log::info(json_encode($message));
          return redirect()->route("home")->with("error", "não foi possível atualizar a senha na Google Cloud.");
        }
      }
        return redirect()->route("home")->with("success","Sua senha foi atualizada com sucesso");
    }

    public function atualizarEmail(Request $request)
    {
      $user = User::findOrFail(auth()->id());
      $user->email = $request->email;
      $user->save();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if ($access_token) {
        if (!$this->updateEmailOrPasswordLoggedUser($access_token, $user->email_institucional, $request->email, null, $message)) {
          if (!is_array($message)) {
            return redirect()->route("home")->with("error", $message);
          }
          Log::info(json_encode($message));
          return redirect()->route("home")->with("error", "não foi possível atualizar o e-mail na Google Cloud.");
        }
      }
      return redirect()->route("home")->with("success","Seu e-mail foi atualizado com sucesso");
    }

    public function atualizarEmailGrupo(Request $request)
    {
      $user = User::findOrFail(auth()->id());
      $old_email = $user->email_institucional;
      $user->email_institucional = $request->email;
      $user->save();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if ($access_token) {
        if (!$this->updateEmailGroupLoggedUser($access_token, $old_email, $request->email, $message)) {
          if (!is_array($message)) {
            return redirect()->route("home")->with("error", $message);
          }
          Log::info(json_encode($message));
          return redirect()->route("home")->with("error", "não foi possível atualizar o email de grupo na Google Cloud.");
        }
      }
      return redirect()->route("home")->with("success","Seu e-mail de grupo foi atualizado com sucesso");
    }

}
