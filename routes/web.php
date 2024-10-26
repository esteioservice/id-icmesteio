<?php
  //Artisan::call('storage:link');
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',"HomeController@index");

Auth::routes();
Route::middleware(['auth'])->group(function () {
    Route::post('usuario/updateSituacao', 'UsuarioController@updateSituacao')->name("usuario.updateSituacao");
    Route::post('usuario/atualizarSenha', 'UsuarioController@atualizarSenha')->name("usuario.atualizarSenha");
    Route::post('usuario/atualizarEmail', 'UsuarioController@atualizarEmail')->name("usuario.atualizarEmail");
    Route::post('usuario/atualizarEmailGrupo', 'UsuarioController@atualizarEmailGrupo')->name("usuario.atualizarEmailGrupo");

    Route::get('usuario/addToGoogle', 'UsuarioController@addToGoogle')->name('usuario.addToGoogle');
    Route::get('usuario/deleteFromGoogle', 'UsuarioController@deleteFromGoogle')->name('usuario.deleteFromGoogle');

    Route::resource('usuario', 'UsuarioController');

    Route::get('emailDelegado/solicitar', 'EmailDelegadoController@solicitar')->name("emailDelegado.solicitar");
    Route::post('emailDelegado/solicitaRemocao', 'EmailDelegadoController@solicitaRemocao')->name("emailDelegado.solicitaRemocao");
    Route::post('emailDelegado/excluir', 'EmailDelegadoController@excluir')->name("emailDelegado.excluir");
    Route::post('emailDelegado/updateSituacao', 'EmailDelegadoController@updateSituacao')->name("emailDelegado.updateSituacao");
    Route::get('emailDelegado/permissao', 'EmailDelegadoController@permissao')->name("emailDelegado.permissao");
    Route::post('emailDelegado/getAcesso', 'EmailDelegadoController@getAcesso')->name("emailDelegado.getAcesso");
    Route::post('emailDelegado/adicionar', 'EmailDelegadoController@adicionar')->name("emailDelegado.adicionar");
    Route::post('emailDelegado/remover', 'EmailDelegadoController@remover')->name("emailDelegado.remover");
    Route::get('emailDelegado/addToGoogle', 'EmailDelegadoController@addToGoogle')->name('emailDelegado.addToGoogle');
    Route::get('emailDelegado/deleteFromGoogle', 'EmailDelegadoController@deleteFromGoogle')->name('emailDelegado.deleteFromGoogle');

    Route::resource('emailDelegado', 'EmailDelegadoController');

    Route::get('equipe/solicitar', 'EquipeController@solicitar')->name("equipe.solicitar");
    Route::post('equipe/solicitaRemocao', 'EquipeController@solicitaRemocao')->name("equipe.solicitaRemocao");
    Route::post('equipe/excluir', 'EquipeController@excluir')->name("equipe.excluir");
    Route::post('equipe/updateSituacao', 'EquipeController@updateSituacao')->name("equipe.updateSituacao");
    Route::get('equipe/permissao', 'EquipeController@permissao')->name("equipe.permissao");
    Route::post('equipe/getAcesso', 'EquipeController@getAcesso')->name("equipe.getAcesso");
    Route::post('equipe/adicionar', 'EquipeController@adicionar')->name("equipe.adicionar");
    Route::post('equipe/remover', 'EquipeController@remover')->name("equipe.remover");
    Route::get('equipe/addToGoogle', 'EquipeController@addToGoogle')->name('equipe.addToGoogle');
    Route::get('equipe/deleteFromGoogle', 'EquipeController@deleteFromGoogle')->name('equipe.deleteFromGoogle');

    Route::resource('equipe', 'EquipeController');

    Route::get('administracao', 'AdministracaoController@index')->name("administracao");
    Route::post('administracao/update', 'AdministracaoController@update')->name("administracao.update");
    Route::post('administracao/alterarGrupo', 'AdministracaoController@alterarGrupo')->name("administracao.alterarGrupo");

    Route::get('/home', 'HomeController@index')->name('home');
    Route::post("email_institucional", "Auth\RegisterController@salvarEmail")->name('salvarEmail');

    Route::get('/oauth2', 'Oauth2Controller@index')->name('oauth2');
    Route::get('/oauth2/callback', 'Oauth2Controller@callback')->name('oauth2.callback');
    Route::get('/oauth2/logout', 'Oauth2Controller@logout')->name('oauth2.logout');
});

Route::middleware(['bindings'])->group(function () {
  Route::get('/docs/termos', 'DocumentsController@termos')->name('termos');
  Route::get('/docs/politicas', 'DocumentsController@politicas')->name('politicas');
});
