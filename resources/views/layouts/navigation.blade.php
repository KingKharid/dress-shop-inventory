<nav class="bg-white border-b border-gray-200 px-4 py-3 flex justify-between items-center">
    <div class="flex space-x-4">
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">📊 Dashboard</a>
        <a href="{{ route('dresses.index') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">👗 Dresses</a>
        <a href="{{ route('dresses.create') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">➕ Add Dress</a>

       @if(auth()->user()->role === 'super_admin')
            <a href="{{ route('dresses.index') }}" class="text-sm font-medium text-red-600 hover:text-red-800">🗑️ Manage</a>
        @endif

    </div>

    <div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-gray-500 hover:text-red-600">Logout</button>
        </form>
    </div>
</nav>

