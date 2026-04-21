<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '受注管理システム')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>

<body class="bg-gray-50 text-gray-900 antialiased">

    <div class="flex min-h-screen">

        {{-- サイドバー --}}
        <aside class="w-56 bg-gray-900 text-gray-100 flex flex-col shrink-0">
            <div class="px-5 py-4 border-b border-gray-700">
                <span class="text-lg font-semibold tracking-wide">受注システム</span>
            </div>
            <nav class="flex-1 py-4 space-y-1 px-3">
                @php
                $navItems = [
                ['route' => 'dashboard', 'label' => 'ダッシュボード'],
                ['route' => 'orders.index', 'label' => '受注管理'],
                ['route' => 'invoices.index', 'label' => '請求書'],
                ];
                @endphp
                @foreach ($navItems as $item)
                @php $active = request()->routeIs($item['route']) @endphp
                <a href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition
                          {{ $active ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    {{ $item['label'] }}
                </a>
                @endforeach
            </nav>
            <div class="px-4 py-3 border-t border-gray-700 text-xs text-gray-500 flex justify-between items-center">
                <span>{{ auth()->user()->name ?? '' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white text-xs">ログアウト</button>
                </form>
            </div>
        </aside>

        {{-- メインコンテンツ --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
                <h1 class="text-base font-semibold text-gray-800">@yield('page-title', 'ダッシュボード')</h1>
                <div class="flex items-center gap-3">
                    @yield('header-actions')
                </div>
            </header>

            @if (session('success'))
            <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
            @endif
            @if ($errors->any())
            <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
