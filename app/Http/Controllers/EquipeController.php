<?php

namespace App\Http\Controllers;

use App\library\MailClass;
use App\Models\AdministracaoModel;
use App\Models\EquipeModel;
use App\Models\EquipeUsuarioModel;
use App\Models\GrupoUsuarioModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Google;

class EquipeController extends Controller
{
    private function removeAcentos($text)
    {
      $lower = strtolower(preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"), $text));
      $sem_acento = ucwords($lower);
      return $sem_acento;
    }

    private function foundGroupOnGoogle($equipe, $list)
    {
      foreach ($list as $ngroup) {
        if ($ngroup->getEmail() ==  $equipe) {
          return true;
        }
      }
      return false;
    }

    private function getLoggedGroupList($access_token)
    {
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $group      = new Google\Service\Directory\Group($directory);
      $params = array(
        'customer' => 'my_customer',
        'maxResults' => 100,
        'orderBy' => 'email',
      );
      $results = $directory->groups->listGroups($params);
      if (count($results->getGroups()) == 0) {
        return null;
      }
      return $results->getGroups();
    }

    private function addLoggedGroup($access_token, $email, &$message)
    {
      $equipe = DB::table('equipe')->where('equipe', $email)->first();
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $group     = new Google\Service\Directory\Group($directory);
      $group->email = $this->removeAcentos($equipe->equipe);
      $group->name  = $this->removeAcentos($equipe->nome);
      $myObj = (object) array();
      $myObj->proprietario = $equipe->user_id;
      if ($equipe->status == 1) {
        $description = "Aprovado";
      } elseif ($equipe->status == 2) {
        $description = "A excluir";
      } else {
        $description = "Pendente";
      }
      $myObj->situacao = $description;
      $myJSON = json_encode($myObj);
      $group->description = $myJSON;
      try {
        $list = $this->getLoggedGroupList($access_token);
        if ($this->foundGroupOnGoogle($email, $list)) {
          $message = "Não é possível incluir porque a equipe já existe no Google Cloud.";
          return false;
        }
        $result = $directory->groups->insert($group);
        sleep(4);
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }

    private function updateLoggedGroup(Request $request, $access_token, $is_status, &$message, $email)
    {
      $equipe = DB::table('equipe')->where('equipe', $email)->first();
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $group     = new Google\Service\Directory\Group($directory);
      if (!$is_status) {
        $group->email = $this->removeAcentos($request->equipe);
        $group->name  = $this->removeAcentos($request->nome);
      }
      $myObj = (object) array();
      $myObj->proprietario = $equipe->user_id;
      if ($request->situacao == 1) {
        $description = "Aprovado";
      } elseif ($request->situacao == 2) {
        $description = "A excluir";
      } else {
        $description = "Pendente";

      }
      $myObj->situacao = $description;
      $myJSON = json_encode($myObj);
      $group->description = $myJSON;
      try {
        $list = $this->getLoggedGroupList($access_token);
        if ($this->foundGroupOnGoogle($email, $list)) {
          $result = $directory->groups->update($email, $group);
        }
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }

    private function deleteLoggedGroup($access_token, $email, &$message, $found = true)
    {
      $client = new Google\Client();
      $client->setAccessToken($access_token);
      $directory = new Google\Service\Directory($client);
      $group     = new Google\Service\Directory\Group($directory);
      try {
        $list = $this->getLoggedGroupList($access_token);
        if (!$this->foundGroupOnGoogle($email, $list)) {
          if ($found) {
            $message = "Não é possível remover o grupo, porque a equipe não existe no Google Cloud.";
            return false;
          }
          return true;
        }
        $result = $directory->groups->delete($email);
        sleep(4);
      } catch (Exception $e) {
        $message = $e->getMessage();
        return false;
      }
      return true;
    }

  private function createMemberFor($access_token, $groupMail, $userMail, &$message)
  {
    $client = new Google\Client();
    $client->setAccessToken($access_token);
    $directory = new Google\Service\Directory($client);
    $group     = new Google\Service\Directory\Group($directory);
    $member    = new Google\Service\Directory\Member($directory);
    try {
      $result = $directory->members->hasMember($groupMail, $userMail);
      if ($result->isMember) {
        $message = "Já existe no grupo da Google um membro com esse e-mail.";
        return false;
      }
      $member->email = $userMail;
      $member->role  = "MEMBER";
      $member->type  = "USER";
      $result = $directory->members->insert($groupMail, $member);
    } catch (Exception $e) {
      $message = $e->getMessage();
      return false;
    }
    return true;
  }

  private function destroyMemberTo($access_token, $groupMail, $userMail, &$message)
  {
    $client = new Google\Client();
    $client->setAccessToken($access_token);
    $directory = new Google\Service\Directory($client);
    $group     = new Google\Service\Directory\Group($directory);
    $member    = new Google\Service\Directory\Member($directory);
    try {
      $result = $directory->members->hasMember($groupMail, $userMail);
      if (!$result->isMember) {
        $message = "Não existe no grupo da Google um membro com esse e-mail.";
        return false;
      }
      $result = $directory->members->delete($groupMail, $userMail);
    } catch (Exception $e) {
      $message = $e->getMessage();
      return false;
    }
    return true;
  }

    public function addToGoogle(Request $request)
    {
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $email = urldecode($request->get('equipe'));
      if (empty($email)) {
        return redirect()->to("/equipe/solicitar")->with("error", "a equipe não existe");
      }
      if ($this->addLoggedGroup($access_token, $email, $message)) {
        return redirect()->to("/equipe/solicitar")->with("success","grupo adicionado na Google com sucesso!")->with('added', 'true')->with('equipe', urlencode($email));
      }
      if (!is_array($message)) {
        return redirect()->to("/equipe/solicitar")->with("error", $message);
      }
      Log::info(json_encode($message));
      return redirect()->to("/equipe/solicitar")->with("error", "não foi possível adicionar o grupo na Google Cloud.");
    }

     /**
       * Remove group from Google cloud.
       *
       * @param  \Illuminate\Http\Request  $request
       */

    public function deleteFromGoogle(Request $request)
    {
      $email = urldecode($request->get('equipe'));
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if ($this->deleteLoggedGroup($access_token, $email, $message)) {
        return redirect()->to("/equipe/solicitar")->with("success","grupo removido da Google Cloud com sucesso!")->with('deleted', 'true')->with('equipe', urlencode($email));
      }
      if (!is_array($message)) {
        return redirect()->to("/equipe/solicitar")->with("error", $message);
      }
      Log::info(json_encode($message));
      return redirect()->to("/equipe/solicitar")->with("error", "não foi possível remover o grupo da Google Cloud.");
    }

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function solicitar(Request  $request)
    {
        $administracao = AdministracaoModel::findOrFail(1);
        $grupo = "@".$administracao->grupo;
        $equipes = EquipeModel::all();
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        $google_groups = null;
        if ($access_token) {
          $google_groups = $this->getLoggedGroupList($access_token);
        }
        return view("equipe/solicitar",compact('equipes','grupo', 'access_token', 'google_groups'));
    }

    public function permissao(Request  $request)
    {
        $permissao = GrupoUsuarioModel::where("perfil_id","=",auth()->user()->perfil)->first();
        if(auth()->user()->perfil == 1 || ((auth()->user()->perfil == 2 && $permissao->adiciona)))
            $equipes = EquipeModel::where("status","=","1")->get();
        else
            $equipes = EquipeModel::select("equipe.*")->join("equipe_usuario","equipe_usuario.equipe_id","=","equipe.id")->where("equipe.status","=","1")->where("equipe_usuario.user_id","=",auth()->id())->get();;

        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        return view("equipe/permissao",compact('equipes', 'access_token'));
    }
    public function getAcesso(Request $request)
    {
        $permissao = GrupoUsuarioModel::where("perfil_id","=",auth()->user()->perfil)->first();
        $emails = EquipeUsuarioModel::select('equipe_usuario.id','email_institucional','name','registro')->join("users","users.id","=","equipe_usuario.user_id")->where("equipe_id","=",$request->id)->get();
        $space = auth()->user()->perfil == 1 ? 4 : 5;
        if( auth()->user()->perfil == 2 && $permissao->remover)
        {
            $space = 4;
        }
        $result = "<div class='row titulo'>";
        $result .= "    <div class='col-md-2'>";
        $result .= "        <span>Registro</span>";
        $result .= "    </div>";
        $result .= "    <div class='col-md-{$space}'>";
        $result .= "        <span>Nome</span>";
        $result .= "    </div>";
        $result .= "    <div class='col-md-{$space}'>";
        $result .= "        <span>Email Grupo</span>";
        $result .= "    </div>";
        $result .= "</div>";
        foreach ($emails as $email)
        {
            $result .= "<div class='row'>";
            $remover = "";
            if(auth()->user()->perfil == 1 || (auth()->user()->perfil == 2 && $permissao->remover))
            {
                $remover .= "<div class='col-md-2'>";
                $remover .= "<button class='btn btn-danger remover' id='".$email->email_institucional."' onclick='setUserMail(event);$(\"#idRemover\").val(".$email->id.");$(\"#frmRemover\").submit();' data-id='".$email->id."'>Remover</button>";
                $remover .= "</div>";
            }

            $result .= "<div class='col-md-2'>".$email->registro."</div>";
            $result .= "<div class='col-md-{$space}'>".$email->name."</div>";
            $result .= "<div class='col-md-{$space}'>".$email->email_institucional."</div>";
            $result .= $remover;

            $result .= "</div>";
        }
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        $request->session()->flash('group', $request->equipe);
        echo $result;
    }
    public function adicionar(Request $request)
    {
      $group = $request->session()->get('group');
      $user = User::Where("email_institucional","=",$request->chave)->first();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if (!empty($user)){
        $existe = EquipeUsuarioModel::where("user_id","=",$user->id)->where("equipe_id","=",$request->id)->first();
        if (empty($existe)) {
          $acesso = new EquipeUsuarioModel();
          $acesso->user_id = $user->id;
          $acesso->equipe_id = $request->id;
          $acesso->save();
          if ($access_token != null && !$this->createMemberFor($access_token, $group, $request->chave, $message)) {
            if (!is_array($message)) {
               return redirect()->to("/equipe/permissao")
                 ->with("error", $message)
                 ->with("access_token", $access_token);
            }
            Log::info(json_encode($message));
            return redirect()->to("/equipe/permissao")
              ->with("error", "não foi possível adicionar o membro na Google Cloud.")
              ->with("access_token", $access_token);
          }
          return redirect()->route("equipe.permissao")
            ->with("success","Usuário atribuido a equipe com sucesso.")
            ->with("access_token", $access_token);
        } else {
          return redirect()->route("equipe.permissao")
            ->with("error","Vinculo já existente.")
            ->with("access_token", $access_token);
        }
      } else {
        return redirect()->route("equipe.permissao")
          ->with("error","Usuário não encontrado.")
          ->with("access_token", $access_token);
      }
    }

    public function remover(Request $request)
    {
      EquipeUsuarioModel::where("id","=",$request->id)->delete();
      $administracao = AdministracaoModel::findOrFail(1);
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $groupMail = $request->group . "@" . $administracao->grupo;
      $userMail  = $request->user;
      if ($access_token != null && !$this->destroyMemberTo($access_token, $groupMail, $userMail, $message)) {
        if (!is_array($message)) {
          return redirect()->to("/equipe/permissao")
            ->with("error", $message)
            ->with("access_token", $access_token);
        }
        Log::info(json_encode($message));
        return redirect()->to("/equipe/permissao")
          ->with("error", "não foi possível remover o membro na Google Cloud.")
          ->with("access_token", $access_token);
      }
      return redirect()->route("equipe.permissao")
        ->with("success","Usuário removido da equipe com sucesso.")
        ->with("access_token", $access_token);
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
      $administracao = AdministracaoModel::findOrFail(1);
      $grupo = "@".$administracao->grupo;
      $prefixo = "equipe-";
      $existe = EquipeModel::where("equipe","=", $prefixo . $request->equipe . $grupo)->first();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if(!$existe) {
        $equipe = new EquipeModel();
        $equipe->equipe = $prefixo . $request->equipe . $grupo;
        $equipe->nome = $request->nome;
        $equipe->status = 0;
        $equipe->user_id = auth()->id();
        $equipe->save();
        //$lastID = $equipe->id;
        //$usuario = new EquipeUsuarioModel();
        //$usuario->user_id = auth()->id();
        //$usuario->equipe_id = $lastID;
        //$usuario->save();
        if ($access_token && !$this->addLoggedGroup($access_token, $equipe->equipe, $message)) {
          return redirect()->route("equipe.solicitar")->with("error", "não foi possível criar o grupo na Google Cloud.");
        }
        //Envia e-mail
        $mail = new MailClass();
        if($mail->sendEmail($administracao->usuario, $administracao->nome,"Nova solicitação de equipe"," Há uma nova solicitação para criação de equipe em {$administracao->grupo}.","solicitacao@escoteirosonline.com.br")) {
          return redirect()->route('equipe.solicitar')->with("success", "Solicitação enviada com sucesso.");
        }
      } else {
        return redirect()->route("equipe.solicitar")->with("error","Equipe informada já está cadastrada.");
      }
    }

    public function solicitaRemocao(Request $request)
    {
        $equipe = EquipeModel::findOrFail($request->id);
        $equipe->status = 2;
        $equipe->save();
        $administracao = AdministracaoModel::findOrFail(1);

        //Envia e-mail
        $mail = new MailClass();
        if($mail->sendEmail($administracao->usuario, $administracao->nome,"Nova solicitação de exclusão equipe","Há uma nova solicitação para exclusão de equipe em {$administracao->grupo}.","solicitacao@escoteirosonline.com.br"))
            return redirect()->route('equipe.solicitar')->with("success", "Solicitação de remoção enviada com sucesso.");
    }
    public function excluir(Request $request)
    {
      $group = EquipeModel::findOrFail($request->id);
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $message = "";
      $success = true;
      if ($access_token) {
        $email = $group->equipe;
        if (!$this->deleteLoggedGroup($access_token, $email, $message, false)) {
          $success = false;
        }
      }
      EquipeModel::where("id","=",$request->id)->delete();
      EquipeUsuarioModel::where("equipe_id", "=", $request->id)->delete();
      if (!$success) {
        if (!is_array($message)) {
          return redirect()->to("/equipe.solicitar")->with("error", $message);
        }
        Log::info(json_encode($message));
        return redirect()->to("/equipe.solicitar")->with("error", "não foi possível remover o grupo da Google Cloud.");
      }
      return redirect()->route('equipe.solicitar')->with("success", "Equipe excluída com sucesso.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function updateSituacao(Request $request)
    {
        $equipe = EquipeModel::findOrFail($request->id);
        if($request->status == 1)
        {
            $usuario = User::findOrFail($equipe->user_id);

            $administracao = AdministracaoModel::find(1);

            //Envia e-mail
            $mail = new MailClass();
            $mail->sendEmail($administracao->usuario, $administracao->nome,"Aprovação de equipe","Sua solicitação de criação de equipe foi aprovada em {$administracao->grupo}.",$usuario->email,$usuario->name);

        }
        $equipe->status = $request->status;
        $equipe->save();
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        if ($access_token) {
          if (!$this->updateLoggedGroup($request, $access_token, true, $message, $equipe->equipe)) {
            if (!is_array($message)) {
              return redirect()->route("equipe.solicitar")->with("error", $message);
            }
            Log::info(json_encode($message));
            return redirect()->route("equipe.solicitar")->with("error", "não foi possível atualizar o grupo da Google Cloud.");
          }
        }
        return redirect()->route('equipe.solicitar')->with("success","Status atualizado com sucesso.");
    }
}
