// src/Pages/Login.js
import React, { useState } from 'react';
import Input from '@/Components/Input';
import Button from '@/Components/Button';
import { useForm } from '@inertiajs/inertia-react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <div className="min-h-screen flex flex-col justify-center items-center bg-gray-900 px-4">
            {/* Título */}
            <h1 className="text-3xl font-bold text-white mb-12 text-center">
                Vida/Saúde Prontuário Eletrônico
            </h1>

            {/* Formulário */}
            <form onSubmit={submit} className="w-full max-w-md bg-gray-800 p-8 rounded-xl shadow-lg">
                <Input
                    id="email"
                    type="text"
                    placeholder="Digite seu usuário"
                    value={data.email}
                    onChange={e => setData('email', e.target.value)}
                    className="w-full p-3 rounded-lg text-gray-900 mb-6"
                    autoFocus
                />

                <Input
                    id="password"
                    type="password"
                    placeholder="Digite sua senha"
                    value={data.password}
                    onChange={e => setData('password', e.target.value)}
                    className="w-full p-3 rounded-lg text-gray-900 mb-6"
                />

                <div className="flex items-center mb-6">
                    <input
                        type="checkbox"
                        id="remember"
                        checked={data.remember}
                        onChange={e => setData('remember', e.target.checked)}
                        className="mr-2"
                    />
                    <label htmlFor="remember" className="text-white text-sm">Lembrar usuário</label>
                </div>

                <Button className="w-full bg-blue-800 hover:bg-blue-700 text-white font-bold py-3 rounded-lg mb-4" disabled={processing}>
                    Entrar
                </Button>

                <div className="flex justify-between text-sm text-gray-300">
                    <a href="/password/reset" className="hover:underline">Esqueceu sua senha?</a>
                </div>

                {errors.email && (
                    <div className="mt-4 text-red-500 text-sm">{errors.email}</div>
                )}
            </form>
        </div>
    );
}