<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'QuizApp') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    {{-- Sidebar --}}
    <aside class="w-56 bg-white border-r border-gray-200 flex flex-col shrink-0 hidden md:flex">
        <div class="h-14 flex items-center px-4 border-b border-gray-100">
            <a href="{{ route('quizzes.index') }}"
               class="flex items-center gap-2 font-bold text-indigo-600 text-base tracking-tight">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                QuizApp
            </a>
        </div>

        <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
            <a href="{{ route('quizzes.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 text-sm transition-colors
                      {{ request()->routeIs('quizzes.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                My Quizzes
            </a>
        </nav>

        {{-- User menu — popup goes UP to avoid pushing content --}}
        <div class="border-t border-gray-100 p-3 relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2 px-2 py-1.5 hover:bg-gray-50 transition-colors text-left">
                <div
                    class="w-7 h-7 rounded bg-indigo-600 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                </div>
                <svg class="w-3 h-3 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition style="display:none"
                 class="absolute bottom-full left-3 right-3 mb-1 bg-white border border-gray-200 shadow-md z-50">
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-14 bg-white border-b border-gray-200 flex items-center px-4 sm:px-6 gap-4 shrink-0">
            <button @click="sidebarOpen = true" class="md:hidden text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex-1 flex items-center justify-between">
                <div>@isset($header)
                        {{ $header }}
                    @endisset</div>
                @isset($headerActions)
                    <div class="flex items-center gap-2">{{ $headerActions }}</div>
                @endisset
            </div>
        </header>

        <main class="flex-1 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" style="display:none"
         class="fixed inset-0 z-40 bg-gray-900/50 md:hidden"
         @click="sidebarOpen = false"></div>
    <aside x-show="sidebarOpen" style="display:none"
           x-transition:enter="transition ease-out duration-200 transform"
           x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150 transform"
           x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 w-56 bg-white border-r border-gray-200 flex flex-col md:hidden">
        <div class="h-14 flex items-center justify-between px-4 border-b border-gray-100">
            <a href="{{ route('quizzes.index') }}" class="font-bold text-indigo-600">QuizApp</a>
            <button @click="sidebarOpen = false" class="text-gray-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <nav class="flex-1 px-2 py-3 space-y-0.5">
            <a href="{{ route('quizzes.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">My Quizzes</a>
        </nav>
    </aside>
</div>

@stack('scripts')
</body>
</html>
