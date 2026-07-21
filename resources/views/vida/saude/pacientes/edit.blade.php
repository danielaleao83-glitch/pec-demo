@extends('vida.saude.layouts.app')

@section('title', 'Editar Paciente')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    <h1 class="text-2xl font-bold">Editar Paciente</h1>

    <form method="POST"
          action="{{ route('pacientes.update', $paciente->id) }}"
          class="bg-[#071427] border border-white/10 rounded-2xl p-6 space-y-4">

        @csrf
        @method('PUT')

        <input type="text"
               name="nome"
               value="{{ $paciente->nome }}"
               class="w-full bg-black/20 p-3 rounded-lg">

        <input type="text"
               name="cpf"
               value="{{ $paciente->cpf }}"
               class="w-full bg-black/20 p-3 rounded-lg">

        <input type="date"
               name="data_nascimento"
               value="{{ $paciente->data_nascimento }}"
               class="w-full bg-black/20 p-3 rounded-lg">

        <button class="bg-yellow-500 px-4 py-2 rounded-xl text-black">
            Atualizar
        </button>

    </form>

</div>

@endsection