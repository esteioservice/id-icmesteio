<?php

namespace App\Http\Controllers;


use App\library\MailClass;
use App\Models\GrupoUsuarioModel;
use App\Models\AdministracaoModel;
use App\Models\EmailDelegadoAcessoModel;
use App\Models\EmailDelegadoModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Google;

class EmailDelegadoController extends Controller
{
  private function removeAcentos($text)
  {
    $lower = strtolower(preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"), $text));
    $sem_acento = ucwords($lower);
    return $sem_acento;
  }

  private function foundGroupOnGoogle($email, $list)
  {
    foreach ($list as $ngroup) {
      if ($ngroup->getEmail() ==  $email) {
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
    $delegado = DB::table('email_delegado')->where('email', $email)->first();
    $client = new Google\Client();
    $client->setAccessToken($access_token);
    $directory = new Google\Service\Directory($client);
    $group     = new Google\Service\Directory\Group($directory);
    $group->email = $this->removeAcentos($delegado->email);
    $group->name  = $this->removeAcentos($delegado->nome);
    $myObj = (object) array();
    $myObj->delegador = auth()->user()->email_institucional;
    if ($delegado->status == 1) {
      $description = "Aprovado";
    } elseif ($delegado->status == 2) {
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
        $message = "Não é possível incluir porque o email já existe no Google Cloud.";
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
    $client = new Google\Client();
    $client->setAccessToken($access_token);
    $directory = new Google\Service\Directory($client);
    $group     = new Google\Service\Directory\Group($directory);
    if (!$is_status) {
      $group->email = $this->removeAcentos($request->email);
      $group->name  = $this->removeAcentos($request->nome);
    }
    $myObj = (object) array();
    $myObj->delegador = auth()->user()->email_institucional;
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
          $message = "Não é possível remover o grupo, porque o email não existe no Google Cloud.";
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
  /**
    * Add group to Google cloud.
    *
    * @param  \Illuminate\Http\Request  $request
    */
  public function addToGoogle(Request $request) {
    $access_token = $request->session()->get('access_token');
    $request->session()->keep(['access_token']);
    $email = urldecode($request->get('email'));
    if (empty($email)) {
      return redirect()->to("/emailDelegado/solicitar")->with("error", "não existe e-mail institucional");
    }
    if ($this->addLoggedGroup($access_token, $email, $message)) {
      return redirect()->to("/emailDelegado/solicitar")->with("success","grupo adicionado na Google com sucesso!")->with('added', 'true')->with('email', urlencode($email));
    }
    if (!is_array($message)) {
      return redirect()->to("/emailDelegado/solicitar")->with("error", $message);
    }
    Log::info(json_encode($message));
    return redirect()->to("/emailDelegado/solicitar")->with("error", "não foi possível adicionar o grupo na Google Cloud.");
  }
   /**
     * Remove group from Google cloud.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function deleteFromGoogle(Request $request)
    {
      $email = urldecode($request->get('email'));
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if ($this->deleteLoggedGroup($access_token, $email, $message)) {
        return redirect()->to("/emailDelegado/solicitar")->with("success","grupo removido da Google Cloud com sucesso!")->with('deleted', 'true')->with('email', urlencode($email));
      }
      if (!is_array($message)) {
        return redirect()->to("/emailDelegado/solicitar")->with("error", $message);
      }
      Log::info(json_encode($message));
      return redirect()->to("/emailDelegado/solicitar")->with("error", "não foi possível remover o grupo da Google Cloud.");
    }
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @param  Request  $request
     */
    public function solicitar(Request  $request)
    {
      //$administracao = AdministracaoModel::findOrFail(1);
      //$sufixo = $administracao->grupo;
      $emails = EmailDelegadoModel::all();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $google_groups = null;
      if ($access_token) {
        $google_groups = $this->getLoggedGroupList($access_token);
      }
      return view("emaildelegado/solicitar",compact('emails','access_token', 'google_groups'));
    }

    public function permissao(Request  $request)
    {
        $permissao = GrupoUsuarioModel::where("perfil_id","=",auth()->user()->perfil)->first();
        if(auth()->user()->perfil == 1 || ((auth()->user()->perfil == 2 && $permissao->adiciona)))
            $emails = EmailDelegadoModel::where("status","=","1")->get();
        else
            $emails = EmailDelegadoModel::select('email_delegado.*')->join("email_delegado_acesso","email_delegado_acesso.emaildelegado_id","=","email_delegado.id")->where("email_delegado_acesso.user_id","=",auth()->id())->where("email_delegado.status","=","1")->get();

        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        return view("emaildelegado/permissao",compact('emails', 'access_token'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAcesso(Request $request)
    {
        $permissao = GrupoUsuarioModel::where("perfil_id","=",auth()->user()->perfil)->first();
        $emails = EmailDelegadoAcessoModel::select('email_delegado_acesso.id','email_institucional','name','registro')->join("users","users.id","=","email_delegado_acesso.user_id")->where("emaildelegado_id","=",$request->id)->get();
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
        $request->session()->flash('group', $request->email);
        echo $result;
    }

    public function adicionar(Request $request)
    {
      $group = $request->session()->get('group');
      $user = User::Where("email_institucional","=",$request->chave)->first();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if (!empty($user)){
        $existe =  EmailDelegadoAcessoModel::where("user_id","=",$user->id)->where("emaildelegado_id","=",$request->id)->first();
        if (empty($existe)) {
          $acesso = new EmailDelegadoAcessoModel();
          $acesso->user_id = $user->id;
          $acesso->emaildelegado_id = $request->id;
          $acesso->save();
          if ($access_token != null && !$this->createMemberFor($access_token, $group, $request->chave, $message)) {
            if (!is_array($message)) {
               return redirect()->to("/emailDelegado/permissao")
                 ->with("error", $message)
                 ->with("access_token", $access_token);
            }
            Log::info(json_encode($message));
            return redirect()->to("/emailDelegado/permissao")
              ->with("error", "não foi possível adicionar o membro na Google Cloud.")
              ->with("access_token", $access_token);
          }
          return redirect()->route("emailDelegado.permissao")
            ->with("success","E-mail delegado com sucesso.")
            ->with("access_token", $access_token);
        } else {
          return redirect()->route("emailDelegado.permissao")
            ->with("error","Vinculo já existente.")
            ->with("access_token", $access_token);
         }
      } else {
        return redirect()->route("emailDelegado.permissao")
          ->with("error","Usuário não encontrado.")
          ->with("access_token", $access_token);
      }
    }

    public function remover(Request $request)
    {
      EmailDelegadoAcessoModel::where("id","=",$request->id)->delete();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $groupMail = $request->group;
      $groupMail = "delega." . $groupMail;
      $userMail  = $request->user;
      if ($access_token != null && !$this->destroyMemberTo($access_token, $groupMail, $userMail, $message)) {
        if (!is_array($message)) {
          return redirect()->to("/emailDelegado/permissao")
            ->with("error", $message)
            ->with("access_token", $access_token);
        }
        Log::info(json_encode($message));
        return redirect()->to("/emailDelegado/permissao")
          ->with("error", "não foi possível remover o membro na Google Cloud.")
          ->with("access_token", $access_token);
      }
      return redirect()->route("emailDelegado.permissao")
        ->with("success","E-mail delegado removido com sucesso.")
        ->with("access_token", $access_token);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    use AuthenticatesUsers;

    public function store(Request $request)
    {
      $administracao = AdministracaoModel::findOrFail(1);
      $prefixo = "delega.";
      $sufixo = $administracao->grupo;
      $existe = EmailDelegadoModel::where("email","=", $prefixo.$request->email."@{$sufixo}")->first();
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      if(!$existe) {
        $administracao = AdministracaoModel::find(1);
        $emailDelegado = new EmailDelegadoModel();
        $emailDelegado->nome  = $request->nome;
        $emailDelegado->email  = $prefixo . $request->email."@{$sufixo}";
        $emailDelegado->status  = 0;
        $emailDelegado->user_id  = auth()->user()->id;
        $emailDelegado->save();
        //$lastID = $emailDelegado->id;
        //$acesso = new EmailDelegadoAcessoModel();
        //$acesso->user_id = auth()->user()->id;
        //$acesso->emaildelegado_id = $lastID;
        $acesso->save();
        if ($access_token && !$this->addLoggedGroup($access_token, $emailDelegado->email, $message)) {
          return redirect()->route("emailDelegado.solicitar")->with("error", "não foi possível criar o grupo na Google Cloud.");
        }
        //Envia e-mail
        $mail = new MailClass();
        if ($mail->sendEmail($administracao->usuario, $administracao->nome,"Nova solicitação de e-mail delegado","Há uma nova solicitação para criação de e-mail delegado em {$administracao->grupo}.",$administracao->usuario)) {
          return redirect()->route("emailDelegado.solicitar")
            ->with("success","Solicitação enviada com sucesso.");
            //->with("add", ($access_token) ? "true" : 'false')
            //->with("source", $sourceEmail)
            //->with("target", $targetEmail)
            //->with('token', is_array($access_token) ? $access_token['access_token'] : '');
        }
      } else {
        return redirect()->route("emailDelegado.solicitar")->with("error","O e-mail informado já está cadastrado.")->with("add", "false");
      }
    }

    public function solicitaRemocao(Request $request)
    {
      $email = EmailDelegadoModel::findOrFail($request->id);
      $email->status = 2;
      $email->save();
      $administracao = AdministracaoModel::find(1);
      $mail = new MailClass();
      if($mail->sendEmail($administracao->usuario, $administracao->nome,"Nova solicitação de exclusão e-mail delegado","Há uma nova solicitação para exclusão de e-mail delegado em {$administracao->grupo}.","solicitacao@escoteirosonline.com.br")) {
        return redirect()->route('emailDelegado.solicitar')->with("success", "Solicitação de remoção enviada com sucesso.");
      }
    }

    public function excluir(Request $request)
    {
      $group = EmailDelegadoModel::findOrFail($request->id);
      $access_token = $request->session()->get('access_token');
      $request->session()->keep(['access_token']);
      $message = "";
      $success = true;
      if ($access_token) {
        $email = $group->email;
        if (!$this->deleteLoggedGroup($access_token, $email, $message, false)) {
          $success = false;
        }
      }
      EmailDelegadoModel::where("id","=",$request->id)->delete();
      if (!$success) {
        if (!is_array($message)) {
          return redirect()->to("/emailDelegado.solicitar")->with("error", $message);
        }
        Log::info(json_encode($message));
        return redirect()->to("/emailDelegado.solicitar")->with("error", "não foi possível remover o grupo da Google Cloud.");
      }
      return redirect()->route('emailDelegado.solicitar')
        ->with("success", "Grupo excluído com sucesso.");
        //->with("delete", ($access_token) ? "true" : 'false')
        //->with("target", $targetEmail)
        //->with('token', is_array($access_token) ? $access_token['access_token'] : '');
    }

    public function updateSituacao(Request $request)
    {
        $email = EmailDelegadoModel::findOrFail($request->id);
        if($request->status == 1)
        {
            $usuario = User::findOrFail($email->user_id);

            $administracao = AdministracaoModel::find(1);

            //Envia e-mail
            $mail = new MailClass();
            $mail->sendEmail($administracao->usuario, $administracao->nome,"Aprovação de e-mail delegado"," Sua solicitação de criação de e-mail delegado foi aprovada em {$administracao->grupo}.",$usuario->email,$usuario->name);

        }
        $email->status = $request->status;
        $email->save();
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        if ($access_token) {
          if (!$this->updateLoggedGroup($request, $access_token, true, $message, $email->email)) {
            if (!is_array($message)) {
              return redirect()->route("emailDelegado.solicitar")->with("error", $message);
            }
            Log::info(json_encode($message));
            return redirect()->route("emailDelegado.solicitar")->with("error", "não foi possível atualizar o grupo da Google Cloud.");
          }
        }
        return redirect()->route('emailDelegado.solicitar')->with("success","Status atualizado com sucesso.");
    }
}
