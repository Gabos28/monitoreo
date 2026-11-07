<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Monitor del servidor</title>
<style>
body {
font-family: system-ui, sans-serif;
background: #f4f5f7;
margin: 0;
padding: 24px;
}
header {
display: flex;
align-items: center;
justify-content: space-between;
gap: 16px;
}
h1 { margin-top: 0; }
.grid {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
gap: 16px;
}
.card {
background: #fff;
border-radius: 8px;
padding: 16px;
box-shadow: 0 1px 3px rgba(0,0,0,.1);
}
.label { color: #666; font-size: 14px; }
.value { font-size: 24px; font-weight: 600; margin-top: 4px; }
button {
background: #fff;
border: 1px solid #cbd5e1;
border-radius: 6px;
padding: 8px 12px;
cursor: pointer;
}
table {
width: 100%;
border-collapse: collapse;
text-align: left;
}
th, td {
padding: 10px 8px;
border-bottom: 1px solid #e5e7eb;
}
</style>
</head>
@php
    $apiBaseUrl = request()->getSchemeAndHttpHost().request()->getBaseUrl();
@endphp
<body
    data-metrics-url="{{ $apiBaseUrl . route('api.metrics', [], false) }}"
    data-login-attempts-url="{{ $apiBaseUrl . route('api.login-attempts', [], false) }}"
>
<header>
    <h1>Monitor del servidor</h1>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
</header>
<p>Última lectura: <span id="at">{{ $data['at'] }}</span></p>
<p id="metrics-error" class="label" role="status"></p>
<div class="grid">
<div class="card">
<div class="label">Nombre del equipo</div>
<div class="value">{{ $data['hostname'] }}</div>
</div>
<div class="card">
<div class="label">Sistema operativo</div>
<div class="value">{{ $data['os'] }}</div>
</div>
<div class="card">
<div class="label">Versión de PHP</div>
<div class="value">{{ $data['php'] }}</div>
</div>
<div class="card">
<div class="label">Disco usado (%)</div>
<div class="value" id="disk">{{ $data['disk']['used_pct'] }}</div>
</div>
<div class="card">
<div class="label">Memoria usada (%)</div>
<div class="value" id="memory">{{ $data['memory']['used_pct'] ?? 'No disponible' }}</div>
</div>
<div class="card">
<div class="label">Uso de CPU (%)</div>
<div class="value" id="cpu">{{ $data['cpu_pct'] ?? 'No disponible' }}</div>
</div>
<div class="card">
<div class="label">Temperatura CPU (°C)</div>
<div class="value" id="temp">{{ $data['cpu_temp_c'] ?? 'No disponible' }}</div>
</div>
<div class="card">
<div class="label">Encendido hace (segundos)</div>
<div class="value" id="uptime">{{ $data['uptime_s'] ?? 'No disponible' }}</div>
</div>
</div>

<section class="card" style="margin-top: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
        <h2>Intentos fallidos de inicio de sesión</h2>
        <button id="refresh-login-attempts" type="button">Actualizar lista</button>
    </div>
    <p class="label">Últimos 10 intentos fallidos o bloqueados.</p>
    <p id="login-attempts-error" class="label" role="status"></p>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Dirección IP</th>
                    <th>Resultado</th>
                </tr>
            </thead>
            <tbody id="login-attempts">
                @forelse ($loginAttempts as $attempt)
                    <tr>
                        <td>{{ $attempt['created_at'] }}</td>
                        <td>{{ $attempt['ip_address'] }}</td>
                        <td>{{ $attempt['status'] === 'blocked' ? 'Bloqueado' : 'Fallido' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No hay intentos fallidos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<script>
function mostrarIntentosLogin(loginAttempts) {
    const tbody = document.getElementById('login-attempts');
    tbody.replaceChildren();

    if (loginAttempts.length === 0) {
        const row = tbody.insertRow();
        const cell = row.insertCell();
        cell.colSpan = 3;
        cell.textContent = 'No hay intentos fallidos registrados.';

        return;
    }

    for (const attempt of loginAttempts) {
        const row = tbody.insertRow();
        row.insertCell().textContent = attempt.created_at;
        row.insertCell().textContent = attempt.ip_address;
        row.insertCell().textContent = attempt.status === 'blocked' ? 'Bloqueado' : 'Fallido';
    }
}

async function actualizarIntentosLogin() {
    const res = await fetch(document.body.dataset.loginAttemptsUrl, {
        headers: { Accept: 'application/json' },
    });

    if (!res.ok) {
        throw new Error('No se pudo actualizar la lista de intentos.');
    }

    const data = await res.json();

    if (!Array.isArray(data.login_attempts)) {
        throw new Error('La respuesta del servidor no contiene una lista de intentos.');
    }

    mostrarIntentosLogin(data.login_attempts);
}

async function actualizar() {
    const res = await fetch(document.body.dataset.metricsUrl, {
        headers: { Accept: 'application/json' },
    });

    if (!res.ok) {
        throw new Error('No se pudieron actualizar las métricas.');
    }

    const d = await res.json();
    document.getElementById('at').textContent = d.at;
    document.getElementById('disk').textContent = d.disk.used_pct;
    document.getElementById('memory').textContent = d.memory?.used_pct ?? 'No disponible';
    document.getElementById('cpu').textContent = d.cpu_pct ?? 'No disponible';
    document.getElementById('temp').textContent = d.cpu_temp_c ?? 'No disponible';
    document.getElementById('uptime').textContent = d.uptime_s ?? 'No disponible';
}

function actualizarMetricasConErroresVisibles() {
    actualizar()
        .then(() => {
            document.getElementById('metrics-error').textContent = '';
        })
        .catch((error) => {
            document.getElementById('metrics-error').textContent = error.message;
        });
}

async function actualizarIntentosConErroresVisibles() {
    try {
        await actualizarIntentosLogin();
        document.getElementById('login-attempts-error').textContent = '';
    } catch (error) {
        document.getElementById('login-attempts-error').textContent = error.message;
    }
}

document.getElementById('refresh-login-attempts').addEventListener('click', async (event) => {
    const button = event.currentTarget;
    button.disabled = true;
    button.textContent = 'Actualizando...';

    try {
        await actualizarIntentosLogin();
        document.getElementById('login-attempts-error').textContent = 'Lista actualizada.';
    } catch (error) {
        document.getElementById('login-attempts-error').textContent = error.message;
    } finally {
        button.disabled = false;
        button.textContent = 'Actualizar lista';
    }
});

actualizarMetricasConErroresVisibles();
actualizarIntentosConErroresVisibles();
setInterval(() => {
    actualizarMetricasConErroresVisibles();
    actualizarIntentosConErroresVisibles();
}, 5000);
</script>
</body>
</html>
