@extends('vida.saude.layouts.app')

@section('title', 'Novo Paciente')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">

    <h1 class="text-2xl font-bold">Cadastrar Paciente</h1>

    <form method="POST" action="{{ route('pacientes.store') }}"
          class="bg-[#071427] border border-white/10 rounded-2xl p-6 space-y-4">

        @csrf

        <input type="text"
               name="nome"
               placeholder="Nome completo"
               class="w-full bg-black/20 p-3 rounded-lg">

        <input type="text"
               name="cpf"
               placeholder="CPF"
               class="w-full bg-black/20 p-3 rounded-lg">

        <input type="date"
               name="data_nascimento"
               class="w-full bg-black/20 p-3 rounded-lg">

        <button class="bg-[#1976d2] px-4 py-2 rounded-xl">
            Salvar
        </button>

    </form>

</div>

@endsection