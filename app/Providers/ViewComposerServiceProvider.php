<?php

    namespace App\Providers;

    use App\Models\AdministracaoModel;
    use App\Models\GrupoUsuarioModel;
    use Illuminate\Support\Facades\View;
    use Illuminate\Support\ServiceProvider;
    class ViewComposerServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            //
            View::composer('layouts/app', function ($view) {
                $administracao = AdministracaoModel::first();
                $view->with(['logo' => $administracao->logo]);
            });
            View::composer(['home','emaildelegado/solicitar','emaildelegado/permissao','equipe/solicitar','equipe/permissao','usuario/list'], function ($view) {
                $administracao = AdministracaoModel::first();
                $permissao = GrupoUsuarioModel::where("perfil_id","=",auth()->user()->perfil)->first();
                $view->with(['permissoes' => $permissao, 'grupo' => "@" . $administracao->grupo]);
            });

        }

        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //
        }
    }
