<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
        window.toggleGlosaFields = function(element) {
            const glosaFields = document.getElementById('glosaClinicaCampos');
            const motivo = document.getElementById('glosa_motivo');
            const valor = document.getElementById('glosa_valor'); // Corrigido o ID aqui
            
            if (glosaFields && motivo && valor) {
                // Mostra/esconde os campos
                glosaFields.style.display = element.value === '1' ? 'block' : 'none';
                
                // Atualiza o atributo required
                motivo.required = element.value === '1';
                valor.required = element.value === '1';
                
                // Limpa os campos se desmarcado
                if (element.value === '0') {
                    motivo.value = '';
                    valor.value = '';
                }
            }
        }
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
