@extends('layouts.app')

@section('content')
@if(!empty($emails))
    <div class="container">
        <div class="row justify-content-center">
            <div class="row" style="max-width: 850px">
                <div class="col-md-12 mb-5">
                    <span style="font-size: 25px;font-weight: 600">Crie seu E-mail Institucional</span>
                </div>
                <div class="col-md-6 mt-3">
                    <p>Chegou o momento de escolher o seu E-mail Institucional</p>
                    <p>Escolha ao lado o e-mail com o qual você mais se identifica e será seu e-mail institucional.</p>
                </div>
                <div class="col-md-6">
                    <div class="card" style="max-width: 400px">
                        <div class="card-body">
                            <form action="{{route("salvarEmail")}}" method="POST">
                                @csrf
                                @foreach($emails as $posicao => $email)
                                    <div class="form-check">
                                        <input type="radio" name="email" required id="{{$posicao}}" value="{{$email}}" class="form-check-input">
                                        <label for="{{$posicao}}" class="form-check-label" >{{$email}}</label>
                                    </div>
                                @endforeach
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-primary">Finalizar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="container">
        <div class="row justify-content-center">
            <div class="row" style="max-width: 850px">
                <div class="col-md-12 mb-5">
                    <span style="font-size: 25px;font-weight: 600">Concluído!</span>
                </div>
                <div class="col-md-12 mt-3">
                    <p>Seu E-mail Institucional está criado e está em análise, assim que o gestor aprovar,</p>
                    <p>ele será sincronizado com a Plataforma Google.</p>
                    <p>Você receberá a resposta por e-mail.</p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
