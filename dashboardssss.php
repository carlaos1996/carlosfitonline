<?php
// ===== FILE: dashboard.php =====
require_once 'config.php';
requireAuth();

// Obtener datos del usuario
$db = getDB();
$stmt = $db->prepare("SELECT name, email, initial_weight, goal_weight FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💪 Mi Progreso Fit</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#00f2ff',
                            secondary: '#39ff14',
                            accent: '#ff5e3a',
                            dark: '#0a0a0c',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .glass-nav {
            background: rgba(10, 10, 12, 0.80);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.07);
        }
        .bg-dashboard {
            background:
                linear-gradient(rgba(10, 10, 12, 0.88), rgba(10, 10, 12, 0.88)),
                url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .animate-entry {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        /* Tab styles */
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        /* Ring charts */
        .ring-bg    { fill: none; stroke: rgba(255,255,255,0.08); stroke-width: 10; }
        .ring-track { fill: none; stroke-width: 10; stroke-linecap: round;
                      transform: rotate(-90deg); transform-origin: 50% 50%; transition: stroke-dashoffset 0.7s ease; }
        /* Food list */
        .food-entry { display:flex; justify-content:space-between; align-items:center;
                      padding:.6rem 1rem; border-radius:.75rem; background:rgba(255,255,255,0.04);
                      border:1px solid rgba(255,255,255,0.06); margin-bottom:.5rem; }
        /* Toast */
        #toast { position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999;
                 padding:.85rem 1.5rem; border-radius:1rem; font-weight:700; font-size:.875rem;
                 background:#00f2ff; color:#0a0a0c; opacity:0; transition:opacity .4s; pointer-events:none; }
        #toast.show { opacity:1; }
        /* Rating buttons */
        .rating-buttons { display:flex; gap:.4rem; flex-wrap:wrap; }
        .rating-buttons input[type=radio] { display:none; }
        .rating-buttons label { cursor:pointer; padding:.4rem .9rem; border-radius:.6rem;
                                background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1);
                                font-weight:600; font-size:.85rem; transition:all .2s; }
        .rating-buttons input[type=radio]:checked + label { background:#00f2ff; color:#0a0a0c; border-color:#00f2ff; }
        /* KPI grid */
        .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        /* Photo gallery */
        .photo-gallery { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:.75rem; }
        .photo-thumb   { border-radius:.75rem; overflow:hidden; aspect-ratio:3/4; position:relative; cursor:pointer; }
        .photo-thumb img { width:100%; height:100%; object-fit:cover; transition:transform .4s; }
        .photo-thumb:hover img { transform:scale(1.07); }
        /* Workout plan */
        .workout-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; }
        .workout-day  { padding:1.25rem; border-radius:1rem; background:rgba(255,255,255,0.04);
                        border:1px solid rgba(255,255,255,0.06); }
        /* Scrollbar */
        ::-webkit-scrollbar { width:6px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#00f2ff33; border-radius:3px; }

        /* ===== CONTRASTE DE INPUTS ===== */
        /* Todos los campos de texto, selects y areas de texto
           tienen fondo oscuro s\u00f3lido para que el texto sea legible */
        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select,
        textarea {
            background: rgba(12, 8, 5, 0.75) !important;
            border-color: rgba(255,255,255,0.25) !important;
            color: #f1f5f9 !important;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            border-color: #00f2ff !important;
            background: rgba(12, 8, 5, 0.90) !important;
        }
        select option {
            background: #0f0c0a;
            color: #f1f5f9;
        }
        /* Autofill del navegador */
        input:-webkit-autofill,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: #f1f5f9 !important;
            -webkit-box-shadow: 0 0 0 1000px rgba(12,8,5,0.92) inset !important;
            caret-color: #fff;
        }
        /* File input */
        input[type="file"] {
            background: rgba(12, 8, 5, 0.75) !important;
            border-color: rgba(255,255,255,0.25) !important;
            color: #94a3b8 !important;
        }
        input[type="file"]::file-selector-button {
            background: rgba(0,242,255,0.12);
            color: #00f2ff;
            border: 1px solid rgba(0,242,255,0.3);
            border-radius: .5rem;
            padding: .3rem .75rem;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-dashboard text-slate-100 font-sans min-h-screen overflow-x-hidden">
<div class="flex min-h-screen">

    <!-- ===== SIDEBAR ===== -->
    <aside class="w-20 lg:w-64 glass-nav hidden md:flex flex-col sticky top-0 h-screen z-50 shrink-0">
        <!-- Logo -->
        <div class="p-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-brand-primary to-brand-secondary rounded-xl flex items-center justify-center shadow-lg shadow-brand-primary/20 shrink-0">
                <span class="text-brand-dark font-extrabold text-lg">💪</span>
            </div>
            <div class="hidden lg:block">
                <span class="font-extrabold text-base tracking-tight leading-tight block">Mi Progreso Fit</span>
                <span class="text-[10px] text-slate-400 leading-tight block">Tu app de progreso fitness</span>
            </div>
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 px-3 space-y-1">
            <button data-tab="today"
                class="tab-nav-btn w-full flex items-center gap-4 p-3 rounded-2xl bg-white/5 text-brand-primary border border-white/10 transition-all text-left"
                onclick="switchTab('today')">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
                <span class="hidden lg:block font-semibold text-sm">Hoy</span>
            </button>

            <button data-tab="workouts"
                class="tab-nav-btn w-full flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-all text-slate-400 hover:text-white text-left"
                onclick="switchTab('workouts')">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
                <span class="hidden lg:block font-semibold text-sm">Entrenamientos</span>
            </button>
            <button data-tab="progress"
                class="tab-nav-btn w-full flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-all text-slate-400 hover:text-white text-left"
                onclick="switchTab('progress')">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
                <span class="hidden lg:block font-semibold text-sm">Progreso</span>
            </button>
        </nav>

        <!-- User info -->
        <div class="p-5 border-t border-white/5 mt-auto">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center font-extrabold text-brand-dark shrink-0">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div class="hidden lg:block overflow-hidden">
                    <p class="text-sm font-bold truncate"><?php echo htmlspecialchars($user['name']); ?></p>
                    <a href="api/logout.php" class="text-xs text-slate-500 hover:text-brand-primary transition-colors">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="flex-1 p-5 lg:p-9 w-full min-w-0">

        <!-- Header -->
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 animate-entry" style="animation-delay:.1s">
            <!-- Title row: name + logout icon (mobile only) -->
            <div class="flex items-start justify-between w-full sm:w-auto">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight">
                        Hola, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?> 👋
                    </h1>
                    <p class="text-slate-400 mt-1 text-sm">Tu resumen de hoy en Mi Progreso Fit</p>
                </div>
                <!-- Logout button: visible only on mobile -->
                <a href="api/logout.php"
                   class="md:hidden flex items-center justify-center w-10 h-10 rounded-2xl bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500/25 transition-all shrink-0 ml-3 mt-1"
                   title="Cerrar Sesión">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                </a>
            </div>
            <!-- Mobile nav pills -->
            <div class="flex gap-2 md:hidden flex-wrap">
                <button onclick="switchTab('today')"   class="tab-mob-btn px-4 py-2 rounded-xl bg-brand-primary/20 text-brand-primary border border-brand-primary/30 text-sm font-bold">Hoy</button>
                <button onclick="switchTab('workouts')"  class="tab-mob-btn px-4 py-2 rounded-xl bg-white/5 text-slate-300 border border-white/10 text-sm font-bold">Entrenos</button>
                <button onclick="switchTab('progress')"  class="tab-mob-btn px-4 py-2 rounded-xl bg-white/5 text-slate-300 border border-white/10 text-sm font-bold">Progreso</button>
            </div>
        </header>

        <!-- ============================================================ -->
        <!-- TAB: HOY -->
        <!-- ============================================================ -->
        <div id="tab-today" class="tab-pane active">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-entry" style="animation-delay:.2s">

                <!-- Peso y Medidas -->
                <div class="glass-card p-7 rounded-3xl">
                    <h3 class="text-base font-bold mb-5 flex items-center gap-2">
                        <span class="text-brand-primary">⚖️</span> Peso y Medidas
                    </h3>
                    <form id="metricsForm" class="space-y-4">
                        <input type="hidden" name="date" id="metrics-date">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Peso (kg)</label>
                            <input type="number" name="weight" step="0.01" required placeholder="70.0"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Cintura (cm)</label>
                                <input type="number" name="waist" step="0.01" placeholder="80.0"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Cadera (cm)</label>
                                <input type="number" name="hip" step="0.01" placeholder="95.0"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 bg-brand-primary text-brand-dark rounded-xl font-bold hover:brightness-110 hover:scale-[1.01] active:scale-[.99] transition-all">
                            Guardar Medidas
                        </button>
                    </form>
                </div>

                <!-- Cómo me siento -->
                <div class="glass-card p-7 rounded-3xl">
                    <h3 class="text-base font-bold mb-5 flex items-center gap-2">
                        <span class="text-brand-secondary">😊</span> ¿Cómo me siento?
                    </h3>
                    <form id="feelingsForm" class="space-y-4">
                        <input type="hidden" name="date" id="feelings-date">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block">Energía (1-5)</label>
                            <div class="rating-buttons">
                                <input type="radio" name="energy" value="1" id="energy1" required>
                                <label for="energy1">1</label>
                                <input type="radio" name="energy" value="2" id="energy2">
                                <label for="energy2">2</label>
                                <input type="radio" name="energy" value="3" id="energy3">
                                <label for="energy3">3</label>
                                <input type="radio" name="energy" value="4" id="energy4">
                                <label for="energy4">4</label>
                                <input type="radio" name="energy" value="5" id="energy5">
                                <label for="energy5">5</label>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block">Hambre (1-5)</label>
                            <div class="rating-buttons">
                                <input type="radio" name="hunger" value="1" id="hunger1" required>
                                <label for="hunger1">1</label>
                                <input type="radio" name="hunger" value="2" id="hunger2">
                                <label for="hunger2">2</label>
                                <input type="radio" name="hunger" value="3" id="hunger3">
                                <label for="hunger3">3</label>
                                <input type="radio" name="hunger" value="4" id="hunger4">
                                <label for="hunger4">4</label>
                                <input type="radio" name="hunger" value="5" id="hunger5">
                                <label for="hunger5">5</label>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block">Adherencia (1-5)</label>
                            <div class="rating-buttons">
                                <input type="radio" name="adherence" value="1" id="adherence1" required>
                                <label for="adherence1">1</label>
                                <input type="radio" name="adherence" value="2" id="adherence2">
                                <label for="adherence2">2</label>
                                <input type="radio" name="adherence" value="3" id="adherence3">
                                <label for="adherence3">3</label>
                                <input type="radio" name="adherence" value="4" id="adherence4">
                                <label for="adherence4">4</label>
                                <input type="radio" name="adherence" value="5" id="adherence5">
                                <label for="adherence5">5</label>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Vasos de agua</label>
                            <input type="number" name="water_glasses" min="0" max="20" value="0"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Notas</label>
                            <textarea name="note" rows="2" placeholder="¿Cómo te sientes hoy?"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all resize-none"></textarea>
                        </div>
                        <button type="submit" class="w-full py-3 bg-brand-secondary/20 text-brand-secondary border border-brand-secondary/30 rounded-xl font-bold hover:bg-brand-secondary/30 transition-all">
                            Guardar Sensaciones
                        </button>
                    </form>
                </div>
            </div>

            <!-- Fotos de hoy -->
            <div class="glass-card p-7 rounded-3xl mt-6 animate-entry" style="animation-delay:.3s">
                <h3 class="text-base font-bold mb-5 flex items-center gap-2">
                    <span class="text-brand-accent">📸</span> Fotos de Hoy
                </h3>
                <form id="photoForm" enctype="multipart/form-data" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[140px]">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Tipo</label>
                        <select name="type" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                            <option value="front">Frontal</option>
                            <option value="side">Lateral</option>
                            <option value="post_workout">Post-Entreno</option>
                            <option value="meal">Comida</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Seleccionar foto</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-primary/20 file:text-brand-primary file:font-bold file:px-3 file:py-1 cursor-pointer outline-none">
                    </div>
                    <button type="submit"
                        class="px-6 py-3 bg-brand-accent/20 text-brand-accent border border-brand-accent/30 rounded-xl font-bold hover:bg-brand-accent/30 transition-all whitespace-nowrap">
                        Subir Foto
                    </button>
                </form>
                <div id="todayPhotos" class="photo-gallery mt-5"></div>
            </div>
        </div>

        <!-- TAB NUTRICIÓN ELIMINADA -->
        <div id="tab-nutrition" class="tab-pane" style="display:none;">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-entry" style="animation-delay:.2s">

                <!-- Macros Objetivo (Anillos) -->
                <div class="glass-card p-7 rounded-3xl">
                    <h3 class="text-base font-bold mb-6 flex items-center gap-2">
                        <span class="text-brand-primary">🎯</span> Objetivo Diario
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <?php
                        $macros = [
                            ['id'=>'calories','label'=>'Calorías','color'=>'#00f2ff','max'=>1800],
                            ['id'=>'protein','label'=>'Proteína (g)','color'=>'#39ff14','max'=>150],
                            ['id'=>'carbs','label'=>'Carbos (g)','color'=>'#ff5e3a','max'=>180],
                            ['id'=>'fats','label'=>'Grasas (g)','color'=>'#a855f7','max'=>50],
                        ];
                        $circumference = 2 * M_PI * 40; // r=40 → ≈251.3
                        foreach ($macros as $m):
                        ?>
                        <div class="flex flex-col items-center gap-2">
                            <div class="relative w-24 h-24">
                                <svg viewBox="0 0 100 100" class="w-full h-full">
                                    <circle cx="50" cy="50" r="40" class="ring-bg"/>
                                    <circle cx="50" cy="50" r="40" class="ring-track"
                                        id="<?php echo $m['id']; ?>-ring"
                                        stroke="<?php echo $m['color']; ?>"
                                        stroke-dasharray="<?php echo round($circumference,2); ?>"
                                        stroke-dashoffset="<?php echo round($circumference,2); ?>"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-lg font-black" id="<?php echo $m['id']; ?>-current">0</span>
                                    <span class="text-[9px] text-slate-500 font-bold">/ <?php echo $m['max']; ?></span>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 font-semibold text-center"><?php echo $m['label']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Registrar Comida -->
                <div class="glass-card p-7 rounded-3xl">
                    <h3 class="text-base font-bold mb-5 flex items-center gap-2">
                        <span class="text-brand-secondary">➕</span> Registrar Comida
                    </h3>
                    <form id="foodForm" class="space-y-4">
                        <input type="hidden" name="date" id="food-date">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Comida</label>
                            <select name="meal_type" required
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                                <option value="breakfast">Desayuno</option>
                                <option value="mid_morning">Media Mañana</option>
                                <option value="lunch">Almuerzo</option>
                                <option value="afternoon">Merienda</option>
                                <option value="dinner">Cena</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Nombre del alimento</label>
                            <input type="text" name="name" required placeholder="Ej: Pechuga de pollo"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Calorías</label>
                                <input type="number" name="calories" required placeholder="200"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Proteína (g)</label>
                                <input type="number" name="protein" step="0.1" required placeholder="30"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Carbos (g)</label>
                                <input type="number" name="carbs" step="0.1" required placeholder="10"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Grasas (g)</label>
                                <input type="number" name="fats" step="0.1" required placeholder="5"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary outline-none transition-all">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 bg-brand-primary text-brand-dark rounded-xl font-bold hover:brightness-110 hover:scale-[1.01] active:scale-[.99] transition-all">
                            Agregar Alimento
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de comidas del día -->
            <div class="glass-card p-7 rounded-3xl mt-6 animate-entry" style="animation-delay:.3s">
                <h3 class="text-base font-bold mb-4 flex items-center gap-2">
                    <span class="text-brand-accent">📋</span> Comidas de Hoy
                </h3>
                <div id="todayFoods" class="space-y-2"></div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB: ENTRENAMIENTOS -->
        <!-- ============================================================ -->
        <div id="tab-workouts" class="tab-pane">
            <div class="glass-card p-7 rounded-3xl animate-entry" style="animation-delay:.2s">
                <h3 class="text-base font-bold mb-6 flex items-center gap-2">
                    <span class="text-brand-primary">🏋️</span> Plan Semanal
                </h3>
                <div class="workout-grid">
                    <div class="workout-day">
                        <h4 class="font-bold text-sm mb-1 text-brand-primary">Lunes</h4>
                        <p class="text-xs text-slate-400 mb-3">Piernas · Sentadillas, Peso muerto, Zancadas</p>
                        <button onclick="markWorkout('legs')"
                            class="px-4 py-2 bg-brand-primary/15 text-brand-primary border border-brand-primary/30 rounded-lg text-xs font-bold hover:bg-brand-primary/25 transition-all">
                            ✔ Marcar
                        </button>
                    </div>
                    <div class="workout-day">
                        <h4 class="font-bold text-sm mb-1 text-brand-secondary">Miércoles</h4>
                        <p class="text-xs text-slate-400 mb-3">HIIT · Cardio de alta intensidad 20 min</p>
                        <button onclick="markWorkout('hiit')"
                            class="px-4 py-2 bg-brand-secondary/15 text-brand-secondary border border-brand-secondary/30 rounded-lg text-xs font-bold hover:bg-brand-secondary/25 transition-all">
                            ✔ Marcar
                        </button>
                    </div>
                    <div class="workout-day">
                        <h4 class="font-bold text-sm mb-1 text-brand-accent">Viernes</h4>
                        <p class="text-xs text-slate-400 mb-3">Espalda Z2 · Dominadas, Remo, Pulldowns</p>
                        <button onclick="markWorkout('back_z2')"
                            class="px-4 py-2 bg-brand-accent/15 text-brand-accent border border-brand-accent/30 rounded-lg text-xs font-bold hover:bg-brand-accent/25 transition-all">
                            ✔ Marcar
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===== COMENTARIOS DEL ENTRENO ===== -->
            <div class="glass-card p-7 rounded-3xl mt-6 animate-entry" style="animation-delay:.3s">
                <h3 class="text-base font-bold mb-5 flex items-center gap-2">
                    <span class="text-brand-secondary">📝</span> Notas del Entreno de Hoy
                </h3>
                <p class="text-xs text-slate-500 mb-4">Anota cómo te fue: cargas, fatiga, sensaciones, PR's. Estos datos aparecen en tu historial de progreso.</p>
                <form id="workoutNotesForm" method="post" action="#" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Entreno realizado</label>
                            <select name="template" required
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-1 focus:ring-brand-secondary focus:border-brand-secondary outline-none transition-all">
                                <option value="legs">🦵 Piernas</option>
                                <option value="hiit">⚡ HIIT</option>
                                <option value="back_z2">🏋️ Espalda Z2</option>
                                <option value="other">💪 Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Esfuerzo percibido (1-10)</label>
                            <input type="number" name="effort" min="1" max="10" placeholder="Ej: 8"
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-secondary focus:border-brand-secondary outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 block">Comentarios / Notas detalladas</label>
                        <textarea name="notes" rows="4"
                            placeholder="Ej: Sentadilla 60kg x5, mejoré vs semana pasada. Rodilla bien. Cardio final 15 min intensidad baja."
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:ring-1 focus:ring-brand-secondary focus:border-brand-secondary outline-none transition-all resize-none"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full py-3 bg-brand-secondary/20 text-brand-secondary border border-brand-secondary/30 rounded-xl font-bold hover:bg-brand-secondary/30 hover:scale-[1.01] transition-all">
                        💾 Guardar Notas del Entreno
                    </button>
                </form>
            </div>

            <!-- HISTORIAL -->
            <div class="glass-card p-7 rounded-3xl mt-6 animate-entry" style="animation-delay:.4s">
                <h3 class="text-base font-bold mb-4 flex items-center gap-2">
                    <span class="text-slate-400">📜</span> Historial de Entrenamientos
                </h3>
                <div id="workoutHistory" class="space-y-2"></div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB: PROGRESO -->
        <!-- ============================================================ -->
        <div id="tab-progress" class="tab-pane">

            <!-- KPIs -->
            <div class="kpi-grid animate-entry" style="animation-delay:.2s">
                <div class="glass-card p-6 rounded-3xl text-center hover:border-brand-primary/40 transition-all">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Peso Actual</p>
                    <p class="text-3xl font-black" id="kpi-current-weight">--</p>
                </div>
                <div class="glass-card p-6 rounded-3xl text-center hover:border-brand-secondary/40 transition-all">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pérdida Total</p>
                    <p class="text-3xl font-black text-brand-secondary" id="kpi-total-loss">--</p>
                </div>
                <div class="glass-card p-6 rounded-3xl text-center hover:border-brand-accent/40 transition-all">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">% Hacia Meta</p>
                    <p class="text-3xl font-black text-brand-accent" id="kpi-progress">--</p>
                </div>
                <div class="glass-card p-6 rounded-3xl text-center hover:border-brand-primary/40 transition-all">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Entrenos/Semana</p>
                    <p class="text-3xl font-black" id="kpi-workouts">--</p>
                </div>
            </div>

            <!-- Gráfico de peso -->
            <div class="glass-card p-7 rounded-3xl mt-6 animate-entry" style="animation-delay:.3s">
                <h3 class="text-base font-bold mb-4 flex items-center gap-2">
                    <span class="text-brand-primary">📈</span> Evolución de Peso (últimos 30 días)
                </h3>
                <div class="w-full h-64 relative">
                    <canvas id="weightChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Galería de fotos -->
            <div class="glass-card p-7 rounded-3xl mt-6 animate-entry" style="animation-delay:.4s">
                <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                    <h3 class="text-base font-bold flex items-center gap-2">
                        <span class="text-brand-accent">📸</span> Galería de Progreso
                    </h3>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:.75rem;color:rgba(255,255,255,0.45);font-weight:600;">Orden:</span>
                        <style>
                            #gallery-sort-btn {
                                padding:6px 14px;
                                border-radius:.6rem;
                                border:1px solid rgba(0,242,255,0.45);
                                background:rgba(0,242,255,0.09);
                                color:#00f2ff;
                                font-size:.78rem;
                                font-weight:700;
                                cursor:pointer;
                                transition:background .2s;
                                white-space:nowrap;
                                -webkit-tap-highlight-color: transparent;
                            }
                            #gallery-sort-btn:hover,
                            #gallery-sort-btn:active {
                                background:rgba(0,242,255,0.25);
                            }
                        </style>
                        <button id="gallery-sort-btn">
                            🕐 Más reciente primero
                        </button>
                    </div>
                </div>
                <div id="photoGallery" class="photo-gallery"></div>
            </div>
        </div>

    </main>
</div>

<!-- Toast -->
<div id="toast"></div>

<!-- Scripts -->
<script src="assets/app.js"></script>

<script>
// ===== Workout Notes Form =====
document.addEventListener('DOMContentLoaded', () => {
    const notesForm = document.getElementById('workoutNotesForm');
    if (notesForm) {
        notesForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type=submit]');
            const orig = btn.textContent;
            btn.textContent = 'Guardando…'; btn.disabled = true;

            const formData = new FormData(e.target);
            formData.append('date', new Date().toISOString().split('T')[0]);
            formData.append('completed', '1');

            try {
                const res  = await fetch('api/save_workout.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    showToast('✅ Notas del entreno guardadas', 'success');
                    e.target.reset();
                    if (typeof loadDashboardData === 'function') loadDashboardData();
                } else {
                    showToast(data.error || 'Error al guardar', 'error');
                }
            } catch { showToast('Error de conexión', 'error'); }
            finally { btn.textContent = orig; btn.disabled = false; }
        });
    }
});

// ===== Tab switching =====
function switchTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');

    // Sidebar buttons
    document.querySelectorAll('.tab-nav-btn').forEach(btn => {
        const active = btn.dataset.tab === tabId;
        btn.classList.toggle('bg-white/5', active);
        btn.classList.toggle('text-brand-primary', active);
        btn.classList.toggle('border', active);
        btn.classList.toggle('border-white/10', active);
        btn.classList.toggle('text-slate-400', !active);
        btn.classList.toggle('hover:text-white', !active);
    });

    // If switching to progress, render chart (with retry if data not loaded yet)
    if (tabId === 'progress') {
        if (window._weightHistory && window._weightHistory.length > 0) {
            renderWeightChart();
        } else {
            // Data not loaded yet — retry every 300ms up to 3 seconds
            let attempts = 0;
            const waitForData = setInterval(() => {
                attempts++;
                if (window._weightHistory && window._weightHistory.length > 0) {
                    clearInterval(waitForData);
                    renderWeightChart();
                } else if (attempts >= 10) {
                    clearInterval(waitForData);
                    renderWeightChart(); // render anyway (will show empty state)
                }
            }, 300);
        }
    }
}

// ===== Weight Chart (canvas) — versión completa =====
function renderWeightChart(historyData) {
    const canvas = document.getElementById('weightChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    // Triple fallback para los datos reales
    let raw = (historyData && historyData.length > 0)
        ? [...historyData]
        : (window._weightHistory && window._weightHistory.length > 0)
            ? [...window._weightHistory]
            : (window.state && window.state.dashboardData && window.state.dashboardData.weight_history)
                ? [...window.state.dashboardData.weight_history]
                : [];

    // --- INYECCIÓN DE PESO INICIAL DINÁMICO ---
    // Agrega el peso inicial del perfil como el primer punto de la gráfica
    const kpis = window.state?.dashboardData?.kpis;
    if (kpis && kpis.initial_weight) {
        let sDate = kpis.start_date;
        // Si hay datos previos a su start_date (ej: registro retroactivo o error de fecha), 
        // usamos esa fecha inicial u obligamos a insertarlo antes.
        if (raw.length > 0 && (!sDate || raw[0].date < sDate)) {
            let d = new Date(raw[0].date);
            d.setDate(d.getDate() - 1);
            sDate = d.toISOString().split('T')[0];
        }

        if (raw.length === 0 || raw[0].date !== sDate) {
            raw.push({ date: sDate || '2024-01-01', weight: kpis.initial_weight });
        } else {
            raw[0].weight = kpis.initial_weight;
        }

        // Reordenar por si acaso para garantizar ASCendente exacto
        raw.sort((a,b) => new Date(a.date).getTime() - new Date(b.date).getTime());
    }


    // Ajustar resolución al dispositivo (pantallas Retina)
    const dpr = window.devicePixelRatio || 1;
    const parent = canvas.parentElement;
    const cssW = parent.clientWidth;
    const cssH = parent.clientHeight;
    canvas.width  = cssW * dpr;
    canvas.height = cssH * dpr;
    canvas.style.width  = cssW + 'px';
    canvas.style.height = cssH + 'px';
    ctx.scale(dpr, dpr);
    const W = cssW, H = cssH;



    if (raw.length === 0) {
        ctx.fillStyle = 'rgba(255,255,255,0.3)';
        ctx.font = '14px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('No hay registros de peso aún', W/2, H/2);
        return;
    }

    const weights = raw.map(r => parseFloat(r.weight));
    const dates   = raw.map(r => {
        const [y,m,d] = r.date.split('-');
        return `${d}/${m}`;
    });

    // Márgenes para ejes
    const PAD_LEFT = 52, PAD_RIGHT = 16, PAD_TOP = 20, PAD_BOTTOM = 36;
    const chartW = W - PAD_LEFT - PAD_RIGHT;
    const chartH = H - PAD_TOP - PAD_BOTTOM;

    const minW = Math.min(...weights);
    const maxW = Math.max(...weights);
    const range = (maxW - minW) || 1;
    const valMin = minW - range * 0.15;
    const valMax = maxW + range * 0.15;
    const valRange = valMax - valMin;

    function xOf(i)   { return PAD_LEFT + (i / (raw.length - 1)) * chartW; }
    function yOf(val) { return PAD_TOP + chartH - ((val - valMin) / valRange) * chartH; }

    ctx.clearRect(0, 0, W, H);

    // --- GRID Y ETIQUETAS DEL EJE Y ---
    const steps = 5;
    for (let s = 0; s <= steps; s++) {
        const val = valMin + (valRange / steps) * s;
        const y   = yOf(val);
        // línea de cuadrícula
        ctx.beginPath();
        ctx.moveTo(PAD_LEFT, y);
        ctx.lineTo(W - PAD_RIGHT, y);
        ctx.strokeStyle = 'rgba(255,255,255,0.07)';
        ctx.lineWidth = 1;
        ctx.stroke();
        // etiqueta
        ctx.fillStyle = 'rgba(255,255,255,0.40)';
        ctx.font = `bold ${Math.round(10 * dpr / dpr)}px Inter, sans-serif`;
        ctx.textAlign = 'right';
        ctx.fillText(val.toFixed(1), PAD_LEFT - 6, y + 4);
    }

    // --- ETIQUETAS EJE X (fechas) ---
    // Mostrar máx ~8 etiquetas para no saturar
    const maxLabels = Math.min(raw.length, 8);
    const step = Math.ceil(raw.length / maxLabels);
    ctx.fillStyle = 'rgba(255,255,255,0.40)';
    ctx.font = `bold 10px Inter, sans-serif`;
    for (let i = 0; i < raw.length; i++) {
        if (i % step !== 0 && i !== raw.length - 1) continue;
        const x = xOf(i);
        ctx.textAlign = i === 0 ? 'left' : i === raw.length - 1 ? 'right' : 'center';
        ctx.fillText(dates[i], x, H - 8);
        // tick vertical
        ctx.beginPath();
        ctx.moveTo(x, PAD_TOP + chartH);
        ctx.lineTo(x, PAD_TOP + chartH + 4);
        ctx.strokeStyle = 'rgba(255,255,255,0.2)';
        ctx.lineWidth = 1;
        ctx.stroke();
    }

    // --- ÁREA DE RELLENO (gradiente) ---
    const grad = ctx.createLinearGradient(0, PAD_TOP, 0, PAD_TOP + chartH);
    grad.addColorStop(0, 'rgba(0,242,255,0.22)');
    grad.addColorStop(1, 'rgba(0,242,255,0.0)');
    ctx.beginPath();
    ctx.moveTo(xOf(0), PAD_TOP + chartH);
    ctx.lineTo(xOf(0), yOf(weights[0]));
    for (let i = 1; i < raw.length; i++) {
        const cpX = (xOf(i-1) + xOf(i)) / 2;
        ctx.bezierCurveTo(cpX, yOf(weights[i-1]), cpX, yOf(weights[i]), xOf(i), yOf(weights[i]));
    }
    ctx.lineTo(xOf(raw.length - 1), PAD_TOP + chartH);
    ctx.closePath();
    ctx.fillStyle = grad;
    ctx.fill();

    // --- LÍNEA PRINCIPAL ---
    ctx.beginPath();
    ctx.moveTo(xOf(0), yOf(weights[0]));
    for (let i = 1; i < raw.length; i++) {
        const cpX = (xOf(i-1) + xOf(i)) / 2;
        ctx.bezierCurveTo(cpX, yOf(weights[i-1]), cpX, yOf(weights[i]), xOf(i), yOf(weights[i]));
    }
    ctx.strokeStyle = '#00f2ff';
    ctx.lineWidth   = 2.5;
    ctx.lineJoin    = 'round';
    ctx.lineCap     = 'round';
    ctx.stroke();

    // --- PUNTOS + ETIQUETAS DE PESO ---
    weights.forEach((w, i) => {
        const x = xOf(i), y = yOf(w);
        const isMin = w === minW, isMax = w === maxW;

        // Punto
        ctx.beginPath();
        ctx.arc(x, y, isMin || isMax ? 6 : 4, 0, Math.PI * 2);
        ctx.fillStyle   = isMax ? '#ff5e3a' : isMin ? '#39ff14' : '#0a0a0c';
        ctx.fill();
        ctx.strokeStyle = isMax ? '#ff5e3a' : isMin ? '#39ff14' : '#00f2ff';
        ctx.lineWidth   = 2;
        ctx.stroke();

        // Valor siempre visible para min/max; otros solo si hay pocos puntos
        if (isMin || isMax || raw.length <= 7) {
            const label = w.toFixed(1) + ' kg';
            ctx.fillStyle = isMax ? '#ff5e3a' : isMin ? '#39ff14' : 'rgba(255,255,255,0.75)';
            ctx.font = `bold 10px Inter, sans-serif`;
            ctx.textAlign = i === 0 ? 'left' : i === raw.length-1 ? 'right' : 'center';
            const labelY = y - 10 < PAD_TOP + 12 ? y + 18 : y - 10;
            ctx.fillText(label, x, labelY);
        }
    });

    // --- TOOLTIP EN HOVER / TOUCH ---
    function showTooltip(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const mouseX  = (clientX - rect.left);
        // Encontrar el punto más cercano
        let closest = 0, minDist = Infinity;
        weights.forEach((_, i) => {
            const d = Math.abs(xOf(i) - mouseX);
            if (d < minDist) { minDist = d; closest = i; }
        });
        if (minDist > 40) { renderWeightChart(); return; }

        // Redibuja y agrega tooltip pill
        renderWeightChart._noRecurse = true; renderWeightChart(); renderWeightChart._noRecurse = false;

        const tx = xOf(closest), ty = yOf(weights[closest]);
        // Highlight del punto
        ctx.beginPath();
        ctx.arc(tx, ty, 8, 0, Math.PI * 2);
        ctx.fillStyle   = 'rgba(0,242,255,0.2)';
        ctx.fill();
        ctx.strokeStyle = '#00f2ff';
        ctx.lineWidth   = 2;
        ctx.stroke();

        // Pill de tooltip
        const txt  = `${dates[closest]}  ·  ${weights[closest].toFixed(2)} kg`;
        ctx.font   = 'bold 11px Inter, sans-serif';
        const tw   = ctx.measureText(txt).width;
        const pw = tw + 20, ph = 26, pr = 8;
        let px = tx - pw/2;
        if (px < PAD_LEFT) px = PAD_LEFT;
        if (px + pw > W - PAD_RIGHT) px = W - PAD_RIGHT - pw;
        const py = ty > PAD_TOP + 40 ? ty - 38 : ty + 14;

        ctx.fillStyle   = 'rgba(10,10,12,0.92)';
        ctx.strokeStyle = '#00f2ff';
        ctx.lineWidth   = 1;
        ctx.beginPath();
        ctx.roundRect(px, py, pw, ph, pr);
        ctx.fill(); ctx.stroke();

        ctx.fillStyle   = '#fff';
        ctx.textAlign   = 'center';
        ctx.fillText(txt, px + pw/2, py + 17);
    }

    canvas.onmousemove  = showTooltip;
    canvas.ontouchmove  = (e) => { e.preventDefault(); showTooltip(e); };
    canvas.onmouseleave = () => renderWeightChart();
}
</script>

</body>
</html>