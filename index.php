<?php
// ===== FILE: index.php =====
require_once 'config.php';

// Si ya está logueado, redirigir a dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💪 Mi Progreso Fit — Acceso</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <!-- Spline 3D Viewer -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.82/build/spline-viewer.js"></script>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary":           "#ec5b13",
                        "primary-light":     "#ff8c42",
                        "background-dark":   "#221610",
                    },
                    fontFamily: { "display": ["Public Sans", "sans-serif"] },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>

    <style>
        body { font-family: 'Public Sans', sans-serif; }
        /* El visor de Spline ocupa todo el fondo */
        spline-viewer {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none; /* no bloquear el formulario */
        }

        .glass-card {
            background: rgba(34, 22, 16, 0.55);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(236, 91, 19, 0.18);
            box-shadow: 0 32px 80px rgba(0,0,0,0.55);
        }
        .bg-overlay {
            background: linear-gradient(to bottom, rgba(0,0,0,0.35), rgba(0,0,0,0.72));
        }
        .input-glass {
            width: 100%;
            padding: 1rem;
            background: rgba(15, 8, 4, 0.80);
            border: 1px solid rgba(255,255,255,0.30);
            border-radius: 0.75rem;
            color: #c72222ff;
            font-family: 'Public Sans', sans-serif;
            font-size: 0.95rem;
            outline: none;
            -webkit-text-fill-color: #b81e1eff;
            /* CLAVE: retrasa infinite el cambio de fondo del autofill */
            transition: background-color 99999s ease-in-out,
                        border-color 0.2s,
                        box-shadow 0.2s;
        }
        .input-glass::placeholder { color: rgba(255,255,255,0.50); }
        .input-glass:focus {
            border-color: #ec5b13;
            box-shadow: 0 0 0 3px rgba(236,91,19,0.22);
            background: rgba(15, 8, 4, 0.90);
        }
        /* ===== AUTOFILL - TODOS LOS ESTADOS =====
           El navegador inyecta fondo blanco al autocompletar.
           Usamos inset box-shadow para taparlo en cualquier estado. */
        .input-glass:-webkit-autofill,
        .input-glass:-webkit-autofill:hover,
        .input-glass:-webkit-autofill:focus,
        .input-glass:-webkit-autofill:active,
        .input-glass:-internal-autofill-selected {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0 1000px rgba(15, 8, 4, 0.95) inset !important;
            box-shadow:         0 0 0 1000px rgba(15, 8, 4, 0.95) inset !important;
            border-color: rgba(255,255,255,0.30) !important;
            background-color: rgba(15, 8, 4, 0.95) !important;
            transition: background-color 99999s ease-in-out 0s !important;
            caret-color: #fff;
        }
        .input-glass:-webkit-autofill:focus {
            border-color: #ec5b13 !important;
        }
        .icon-abs { display: none; }
        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(to right, #ec5b13, #ff8c42);
            color: #fff;
            font-weight: 800;
            border-radius: 0.75rem;
            letter-spacing: 0.05em;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(236,91,19,0.35);
            transition: all 0.2s;
            font-family: 'Public Sans', sans-serif;
            font-size: 0.95rem;
        }
        .btn-primary:hover {
            box-shadow: 0 12px 32px rgba(236,91,19,0.55);
            filter: brightness(1.08);
        }
        .btn-primary:active { transform: scale(0.98); }
        .btn-primary:disabled { opacity: 0.65; cursor: not-allowed; }

        /* Animate in */
        .animate-in { animation: fadeUp 0.65s ease-out both; }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(28px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* Message */
        #message { display:none; }
        #message.error {
            display:block;
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.4);
            color: #fca5a5;
            padding: .7rem 1rem;
            border-radius: .6rem;
            font-size: .875rem;
        }
        #message.success {
            display:block;
            background: rgba(236,91,19,0.15);
            border: 1px solid rgba(236,91,19,0.4);
            color: #fdba74;
            padding: .7rem 1rem;
            border-radius: .6rem;
            font-size: .875rem;
        }
    </style>
</head>

<body class="bg-background-dark min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- ===== FONDO SPLINE 3D ===== -->
    <div class="fixed inset-0 z-0">
        <!-- Escena 3D interactiva -->
        <spline-viewer
            url="https://prod.spline.design/doYSK7Q5vPbJygWv/scene.splinecode"
            loading-anim-type="spinner-small-dark">
        </spline-viewer>
        <!-- Overlay oscuro para legibilidad del formulario -->
        <div class="absolute inset-0 pointer-events-none"
             style="background: linear-gradient(to bottom, rgba(20,10,5,0.55), rgba(20,10,5,0.70));"></div>
    </div>



    <!-- ===== CONTENEDOR PRINCIPAL ===== -->
    <div class="relative z-10 w-full max-w-md px-5 animate-in">
        <div class="glass-card rounded-xl p-8 flex flex-col gap-7">

            <!-- Logo & Título -->
            <div class="text-center">
                <div class="mb-4 inline-flex items-center justify-center w-16 h-16 rounded-full shadow-lg shadow-primary/40"
                     style="background: linear-gradient(135deg, #ec5b13, #ff8c42);">
                    <span class="material-symbols-outlined text-white" style="font-size:2rem;">fitness_center</span>
                </div>
                <h1 class="text-white text-4xl font-black tracking-tight leading-none mb-2">Mi Progreso Fit</h1>
                <p class="text-white/60 text-xs font-semibold uppercase tracking-widest">Tu app de progreso fitness personalizado</p>
            </div>

            <!-- ===== FORMULARIO LOGIN ===== -->
            <form id="loginForm" class="flex flex-col gap-4">

                <div class="flex flex-col gap-1">
                    <label class="text-white/80 text-xs font-bold uppercase tracking-widest ml-1">Email</label>
                    <div class="flex items-center">
                        <input class="input-glass" type="email" id="login-email" name="email"
                               required placeholder="tu@email.com">
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-white/80 text-xs font-bold uppercase tracking-widest">Contraseña</label>
                    </div>
                    <div class="flex items-center">
                        <input class="input-glass" type="password" id="login-password" name="password"
                               required placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-2">INICIAR SESIÓN</button>

                <p class="text-center text-white/50 text-sm pt-1">
                    ¿Sin cuenta?
                    <a href="#" id="showRegister" class="text-white font-bold hover:text-primary transition-colors">Crear cuenta</a>
                </p>
            </form>

            <!-- ===== FORMULARIO REGISTRO ===== -->
            <form id="registerForm" class="flex flex-col gap-4" style="display:none;">

                <div class="flex flex-col gap-1">
                    <label class="text-white/80 text-xs font-bold uppercase tracking-widest ml-1">Nombre</label>
                    <div class="flex items-center">
                        <input class="input-glass" type="text" id="register-name" name="name"
                               required placeholder="Tu nombre completo">
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-white/80 text-xs font-bold uppercase tracking-widest ml-1">Email</label>
                    <div class="flex items-center">
                        <input class="input-glass" type="email" id="register-email" name="email"
                               required placeholder="tu@email.com">
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-white/80 text-xs font-bold uppercase tracking-widest ml-1">Contraseña</label>
                    <div class="flex items-center">
                        <input class="input-glass" type="password" id="register-password" name="password"
                               required placeholder="••••••••">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-white/80 text-xs font-bold uppercase tracking-widest ml-1">Peso Inicial</label>
                        <div class="flex items-center">
                            <input class="input-glass" type="number" id="initial-weight" name="initial_weight"
                                   step="0.01" placeholder="70.0 kg">
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-white/80 text-xs font-bold uppercase tracking-widest ml-1">Peso Meta</label>
                        <div class="flex items-center">
                            <input class="input-glass" type="number" id="goal-weight" name="goal_weight"
                                   step="0.01" placeholder="65.0 kg">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-2">CREAR CUENTA</button>

                <p class="text-center text-white/50 text-sm">
                    ¿Ya tienes cuenta?
                    <a href="#" id="showLogin" class="text-white font-bold hover:text-primary transition-colors">Iniciar sesión</a>
                </p>
            </form>

            <!-- Mensaje de error/éxito -->
            <div id="message"></div>

        </div><!-- /glass-card -->

        <!-- Branding -->
        <div class="mt-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-black/25 rounded-full border border-white/10">
                <span class="text-white/40 text-xs font-medium uppercase tracking-widest">Powered by</span>
                <span class="text-white/75 text-sm font-bold">Carlosfit.online</span>
            </div>
        </div>

    </div><!-- /container -->

    <script>
        // ===== Toggle Login / Registro =====
        document.getElementById('showRegister').addEventListener('click', e => {
            e.preventDefault();
            document.getElementById('loginForm').style.display    = 'none';
            document.getElementById('registerForm').style.display = 'flex';
            clearMessage();
        });
        document.getElementById('showLogin').addEventListener('click', e => {
            e.preventDefault();
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display    = 'flex';
            clearMessage();
        });

        function showMessage(text, type = 'error') {
            const el = document.getElementById('message');
            el.textContent = text;
            el.className = type;
        }
        function clearMessage() {
            const el = document.getElementById('message');
            el.className = '';
            el.textContent = '';
        }

        // ===== Login =====
        document.getElementById('loginForm').addEventListener('submit', async e => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type=submit]');
            btn.textContent = 'Verificando…'; btn.disabled = true;
            try {
                const res  = await fetch('api/login.php', { method: 'POST', body: new FormData(e.target) });
                const data = await res.json();
                if (data.success) {
                    btn.textContent = 'Bienvenido/a 🏋️';
                    window.location.href = 'dashboard.php';
                } else {
                    showMessage(data.error || 'Email o contraseña incorrectos', 'error');
                    btn.textContent = 'INICIAR SESIÓN'; btn.disabled = false;
                }
            } catch {
                showMessage('Error de conexión. Intenta de nuevo.', 'error');
                btn.textContent = 'INICIAR SESIÓN'; btn.disabled = false;
            }
        });

        // ===== Registro =====
        document.getElementById('registerForm').addEventListener('submit', async e => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type=submit]');
            btn.textContent = 'Creando cuenta…'; btn.disabled = true;
            try {
                const res  = await fetch('api/register.php', { method: 'POST', body: new FormData(e.target) });
                const data = await res.json();
                if (data.success) {
                    btn.textContent = '¡Listo! 🎉';
                    window.location.href = 'dashboard.php';
                } else {
                    showMessage(data.error || 'Error al crear cuenta', 'error');
                    btn.textContent = 'CREAR CUENTA'; btn.disabled = false;
                }
            } catch {
                showMessage('Error de conexión. Intenta de nuevo.', 'error');
                btn.textContent = 'CREAR CUENTA'; btn.disabled = false;
            }
        });
    </script>

</body>
</html>