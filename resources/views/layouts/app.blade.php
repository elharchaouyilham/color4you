<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color4Y</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex flex-col font-sans text-slate-900 antialiased">

    <nav class="bg-white border-b border-slate-100 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('catalogue') }}" class="text-xl font-black tracking-wider text-indigo-600 flex items-center gap-2">
                        <span class="bg-indigo-600 text-white w-8 h-8 rounded-lg flex items-center justify-center text-sm">C4Y</span>
                        Color4Y
                    </a>
                </div>
                
                <div class="flex items-center gap-4">
                    @auth
                        <span class="text-xs font-semibold bg-slate-100 text-slate-700 px-3 py-1 rounded-full">
                            {{ Auth::user()->prenom }}
                        </span>
                        
                        @if(Auth::user()->hasRole('Administrateur'))
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:underline">Dashboard Admin</a>
                        @endif
                        @if(Auth::user()->hasRole('Formateur'))
                            <a href="{{ route('formateur.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:underline">Espace Formateur</a>
                        @endif
                        @if(Auth::user()->hasRole('Client'))
                            <a href="{{ route('client.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:underline">Mon Espace Client</a>
                        @endif

                        <a href="{{ route('profile.show') }}" class="text-sm text-slate-500 hover:text-slate-800">Profil</a>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-rose-600 font-bold hover:underline">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600">Connexion</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white text-xs font-bold px-4 py-2 rounded-xl">S'inscrire</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if(session('success') || session('error'))
        <div class="max-w-7xl mx-auto px-4 w-full mt-4">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm font-medium border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 text-rose-800 rounded-xl text-sm font-medium border border-rose-200">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-400">
        &copy; 2026 Color4Y.
    </footer>
</body>
</html>