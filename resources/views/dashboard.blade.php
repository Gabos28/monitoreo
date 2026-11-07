<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio - Monitor del servidor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10 text-slate-900">
    <main class="w-full max-w-xl rounded-xl bg-white p-8 text-center shadow-lg">
        <h1 class="text-2xl font-semibold">Monitor del servidor</h1>
        <p class="mt-3 text-slate-600">Sesión iniciada correctamente.</p>

        <a class="mt-6 inline-flex rounded-md bg-blue-700 px-5 py-3 font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2" href="{{ route('monitor.index') }}">
            Ver panel de monitoreo
        </a>

        <form class="mt-4" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="rounded-md px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500" type="submit">
                Cerrar sesión
            </button>
        </form>
    </main>
</body>
</html>
