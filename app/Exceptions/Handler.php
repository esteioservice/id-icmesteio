<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $error = $exception->getMessage();
        if ($error == "Unauthenticated.") {
          return redirect()->route("login");
        } else if ($error == "The payload is invalid.") {
          if ($request->is("usuario/addToGoogle")) {
            $route = "usuario.index";
          } else {
            $route = "login";
          }
          return redirect()->route($route)->with("error", "não foi possível descriptografar a senha.");
        }
        if(is_string($error) && strpos($error, "Duplicate entry") !== false){
          if ($request->is('usuario/atualizarEmail')) {
            $route = "home";
          } else {
            $route = "usuario.index";
          }
          return redirect()->route($route)->with("error", "não é possível alterar, o e-mail já existe.");
        }
        if ($error == "(hasMember) missing required param: 'groupKey'") {
          if ($request->is('equipe/remover')) {
            $route = "equipe.permissao";
          } else {
            $route = "emailDelegado.permissao";
          }
          return redirect()->route($route)->with("error", "membro não encontrado no grupo da Google.");
        }
        $json = json_decode($error);
        if ($json && $json->error) {
          $matrix = $json->error->errors;
          if ($matrix && is_array($matrix)) {
            $vector = $matrix[0];
            if ($vector) {
              $value = $vector->message;
              if ($value) {
                if ($request->is('usuario/atualizarSenha')) {
                  $route = "home";
                } else if ($request->route()->getName() == "emailDelegado.addToGoogle") {
                  $route = "emailDelegado.solicitar";
                } else  if ($request->route()->getName() == "emailDelegado.store") {
                  $route = "emailDelegado.solicitar";
                } else if ($request->route()->getName() == "emailDelegado.remover" || $request->route()->getName() == "emailDelegado.adicionar") {
                  $route = "emailDelegado.permissao";
                } else if ($request->route()->getName() == "equipe.remover" || $request->route()->getName() == "equipe.adicionar") {
                  $route = "equipe.permissao";
                } else {
                  $route = "usuario.index";
                }
                if ($value == "Invalid Password") {
                  return redirect()->route($route)->with("error", "não foi possível alterar na Google Cloud (senha inválida).");
                } else if ($value == "Entity already exists.") {
                  return redirect()->route($route)->with("error", "não foi possível alterar na Google Cloud (email já existe).");
                } else if ($value == "Invalid Input: groupKey") {
                  return redirect()->route($route)->with("error", "não foi possível alterar na Google Cloud (email inválido).");
                } else if ($value == "Invalid Credentials") {
                  return redirect()->route("oauth2.logout");
                } else if ($value == "Resource Not Found: groupKey") {
                  return redirect()->route($route)->with("error", "esse grupo não foi encontrado na Google Cloud.");
                } else if ($value == "Missing required field: memberKey") {
                  return redirect()->route($route)->with("error", "membro não encontrado no grupo da Google.");
                } else if ($value == "Invalid Given/Family Name: FamilyName") {
                  return redirect()->route($route)->with("error", "Sobrenome inválido para um grupo da Google.");
                } else if ($value == "Invalid Input: primary_user_email") {
                  return redirect()->route($route)->with("error", "Endereço de e-mail inválido para o grupo da Google.");
                }
              }
            }
          }
        }
        return parent::render($request, $exception);
    }
}
