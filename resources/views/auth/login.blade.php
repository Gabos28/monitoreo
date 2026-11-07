<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10 text-slate-900">
    <main class="w-full max-w-md rounded-xl bg-white p-8 shadow-lg">
        <h1 class="mb-6 text-2xl font-semibold">Iniciar sesión</h1>

        <form class="flex flex-col gap-5" method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium" for="email">Correo electrónico</label>
                <input class="rounded-md border border-slate-300 px-3 py-2 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <p class="text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium" for="password">Contraseña</label>
                <input class="rounded-md border border-slate-300 px-3 py-2 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20" id="password" name="password" type="password" required>
                @error('password')
                    <p class="text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input name="remember" type="checkbox" value="1">
                Recordarme
            </label>

            <button class="rounded-md bg-blue-700 px-4 py-2 font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2" type="submit">Entrar</button>
        </form>

    </main>
</body>
</html>
