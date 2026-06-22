<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Absensi RFID')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a; /* Slate 900 */
            color: #f1f5f9; /* Slate 100 */
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7); /* Slate 800 with opacity */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.03);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar -->
    <aside class="w-full md:w-64 glass-panel border-r border-slate-800 flex flex-col">
        <!-- Logo / Header -->
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20">
                    RFID
                </div>
                <div>
                    <h1 class="font-bold text-base tracking-tight text-white leading-none">Absensi IoT</h1>
                    <span class="text-xs text-slate-400">Panel @if(auth()->user()->role === 'admin') Admin @else Dosen @endif</span>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-1">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-500 pl-3' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/></svg>
                    Dashboard Admin
                </a>
                <a href="{{ route('admin.mahasiswa.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.mahasiswa.*') ? 'bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-500 pl-3' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Data Mahasiswa
                </a>
                <a href="{{ route('admin.dosen.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dosen.*') ? 'bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-500 pl-3' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    Kelola Dosen
                </a>
            @endif

            @if(auth()->user()->role === 'dosen')
                <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dosen.dashboard') ? 'bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-500 pl-3' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z"/></svg>
                    Rekap & Laporan
                </a>
                <a href="{{ route('dosen.live') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dosen.live') ? 'bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-500 pl-3' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    Live Monitor
                </a>
            @endif
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="truncate">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 bg-slate-950/80">
        <!-- Top Navbar -->
        <header class="h-16 border-b border-slate-800/60 glass-panel flex items-center justify-between px-6 md:px-8">
            <h2 class="text-lg font-semibold text-white">@yield('page_title', 'Dashboard')</h2>
            <div class="flex items-center gap-6">
                <!-- Toggle RFID Device -->
                @if(auth()->user()->role === 'admin')
                <div class="flex items-center gap-3 bg-slate-900/50 px-3 py-1.5 rounded-full border border-slate-800">
                    <span class="text-xs font-medium text-slate-400">RFID Device</span>
                    <button type="button" id="rfidToggleBtn" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ Cache::get('device_status', 'on') === 'on' ? 'bg-emerald-500' : 'bg-slate-600' }}" role="switch" aria-checked="{{ Cache::get('device_status', 'on') === 'on' ? 'true' : 'false' }}">
                        <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ Cache::get('device_status', 'on') === 'on' ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <span id="rfidStatusText" class="text-xs font-bold {{ Cache::get('device_status', 'on') === 'on' ? 'text-emerald-400' : 'text-slate-400' }}">{{ Cache::get('device_status', 'on') === 'on' ? 'ON' : 'OFF' }}</span>
                </div>
                @endif
                <div class="text-sm text-slate-400 hidden sm:block">
                    Hari ini: <span class="text-slate-200 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <div class="flex-1 p-6 md:p-8 overflow-y-auto">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @if(auth()->user()->role === 'admin')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('rfidToggleBtn');
            const statusText = document.getElementById('rfidStatusText');
            const toggleCircle = toggleBtn.querySelector('span');
            
            toggleBtn.addEventListener('click', function() {
                const isCurrentlyOn = toggleBtn.getAttribute('aria-checked') === 'true';
                const newStatus = isCurrentlyOn ? 'off' : 'on';
                
                // Optimistic UI update
                toggleBtn.setAttribute('aria-checked', !isCurrentlyOn);
                if (newStatus === 'on') {
                    toggleBtn.classList.replace('bg-slate-600', 'bg-emerald-500');
                    toggleCircle.classList.replace('translate-x-0', 'translate-x-5');
                    statusText.textContent = 'ON';
                    statusText.classList.replace('text-slate-400', 'text-emerald-400');
                } else {
                    toggleBtn.classList.replace('bg-emerald-500', 'bg-slate-600');
                    toggleCircle.classList.replace('translate-x-5', 'translate-x-0');
                    statusText.textContent = 'OFF';
                    statusText.classList.replace('text-emerald-400', 'text-slate-400');
                }

                // Send to server
                fetch('{{ route('admin.device.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status !== 'success') {
                        console.error('Failed to update device status');
                        // Revert UI on failure could be implemented here
                    }
                })
                .catch(err => console.error(err));
            });
        });
    </script>
    @endif

</body>
</html>
