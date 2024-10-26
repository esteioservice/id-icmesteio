<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\library\MailClass;
use App\Models\AdministracaoModel;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = "/email_institucional";


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $this->middleware('guest');
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'registro' => ['required', 'string','unique:users', 'max:255'],
            'email' => ['required', 'string', 'email','unique:users','max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $this->middleware('guest');
        return User::create([
            'name' => $data['name'],
            'registro' => $data['registro'],
            'situacao' => 0,
            'perfil' => 0,
            'email' => $data['email'],
            'password' => Crypt::encryptString($data['password']) // Hash::make($data['password'])
        ]);
    }
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $request->session()->flash('logged', auth()->user());
        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $this->generateEmail($request);
    }
    public function generateEmail(Request $request)
    {
        $this->middleware('authenticate');
        $partesNome = explode(" ",$request->name);
        $administracao = AdministracaoModel::findOrFail(1);
        $sufix = "@".$administracao->grupo;
        $emails = [];
        if (count($partesNome) > 2) {
          $pEmail = $this->tirarAcentos($partesNome[0].".".$partesNome[1].$sufix);
          if (!$this->emailExists($pEmail)) {
            $emails[] = $pEmail;
          } else {
            $emails[] = $this->emailCount($pEmail);;
          }
          $sEmail = $this->tirarAcentos($partesNome[0].".".end($partesNome).$sufix);
          if (!$this->emailExists($sEmail)) {
            $emails[] = $sEmail;
          } else {
            $emails[] = $this->emailCount($sEmail);;
          }
          $tEmail = $this->tirarAcentos($partesNome[0].".".substr($partesNome[1],0,1).substr($partesNome[2],0,1).$sufix);
          if (!$this->emailExists($tEmail)) {
            $emails[] = $tEmail;
          } else {
            $emails[] = $this->emailCount($tEmail);;
          }
          $qEmail = $this->tirarAcentos($partesNome[0].".".$partesNome[1].".".$partesNome[2].$sufix);
          if (!$this->emailExists($qEmail)) {
            $emails[] = $qEmail;
          } else {
            $emails[] = $this->emailCount($qEmail);;
          }
        } else if (count($partesNome) == 2) {
          $pEmail = $this->tirarAcentos($partesNome[0].".".end($partesNome).$sufix);
          if (!$this->emailExists($pEmail)) {
            $emails[] = $pEmail;
          } else {
            $emails[] = $this->emailCount($pEmail);;
          }
          $sEmail = $this->tirarAcentos($partesNome[0].".".substr($partesNome[0],0,1).substr($partesNome[1],0,1).$sufix);
          if (!$this->emailExists($sEmail)) {
            $emails[] = $sEmail;
          } else {
            $emails[] = $this->emailCount($sEmail);;
          }
        } else if (count($partesNome) == 1) {
          $pEmail =   $emails[] = $this->tirarAcentos($partesNome[0].$sufix);
          if (!$this->emailExists($pEmail)) {
            $emails[] = $pEmail;
          } else {
            $emails[] = $this->emailCount($pEmail);;
          }
        }
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        $noExit = true;
        return view("auth/email_institucional",compact('emails', 'access_token', 'noExit'));
    }
    public function salvarEmail(Request $request)
    {
        $user = User::findOrFail(auth()->id());
        $user->email_institucional = $request->email;
        $user->save();

        auth()->logout();
        $logged = $request->session()->get('logged');
        if (isset($logged) && !empty($logged)) {
          auth()->login($logged);
        }

        $administracao = AdministracaoModel::find(1);

        $gerentes = User::where('perfil','=',2)->where('situacao','=',1)->get();
        foreach ($gerentes as $gerente)
        {
            //Envia e-mail
            $mail = new MailClass();
            $mail->sendEmail($administracao->usuario, $administracao->nome,"Novo usuário criado","Um novo usuário foi criado e está aguardando aprovação em {$administracao->grupo}.",$gerente->email,$gerente->name);
        }
        $access_token = $request->session()->get('access_token');
        $request->session()->keep(['access_token']);
        $hiddenNavBar = true;;
        return view("auth/email_institucional", compact('access_token', 'hiddenNavBar'));
    }
    private function tirarAcentos($string)
    {
      $lower = strtolower(preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"), $string));
      return $lower;
    }
    private function emailExists($email)
    {
        return User::where('email_institucional','=',$email)->exists();

    }
    private function emailCount($pEmail) {
      $parts = explode("@", $pEmail);
      $part1 = $parts[0];
      $part2 = $parts[1];
      for ($i = strlen($part1)-1; $i >= 0; $i--) {
        $num = $part1[$i];
        if (is_numeric($num)) {
          $part1 = rtrim($part1, $num);
        }
      }
      $query = User::where('email_institucional', 'like', $part1.'%')->where('email_institucional', 'like', '%'.$part2)->get();
      $count = count($query);
      $result = $parts[0] . $count . "@" . $parts[1];
      return $result;
    }
}
