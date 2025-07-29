<x-app-layout>
<div class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">👗 Dresses Inventory</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
    @foreach($dresses as $dress)
    <div class="bg-white shadow rounded-md overflow-hidden text-sm">
        @if($dress->photo)
            <img src="{{ asset('storage/' . $dress->photo) }}" 
                 alt="Dress Photo" 
                 class="w-full h-32 object-cover">
        @else
            <div class="h-32 bg-gray-200 flex items-center justify-center text-gray-500">
                No Photo
            </div>
        @endif

        <div class="p-2">
            <h2 class="font-semibold text-gray-700 truncate">{{ $dress->name }}</h2>
            <p class="text-gray-500 text-xs truncate">{{ $dress->description }}</p>
            <p class="text-xs">Sell: <strong>Ksh {{ $dress->selling_price }}</strong></p>
            <p class="text-xs">Qty: <strong>{{ $dress->quantity }} of {{ $dress->original_quantity }}</strong></p>
            <p class="text-xs">
                Status: 
                @if($dress->is_sold)
                    <span class="text-red-600 font-semibold">Sold</span>
                @else
                    <span class="text-green-600 font-semibold">Available</span>
                @endif
            </p>

            <div class="flex justify-between items-center mt-1">
              
                @if($dress->quantity > 0 && !$dress->is_sold)
                    <button onclick="markSoldPrompt({{ $dress->id }}, {{ $dress->quantity }})"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-0.5 rounded text-[10px]">
                        ✔
                    </button>
                @endif

                @if(auth()->user()->role === 'super_admin')
                    <form method="POST" action="{{ route('dresses.destroy', $dress->id) }}" onsubmit="return confirm('Delete this item?');">
                        @csrf
                        @method('DELETE')
                        <button class="bg-red-500 text-white px-2 py-0.5 rounded text-[10px]">🗑</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

</div>

<script>
    async function markAsSold(id) {
        if (!navigator.onLine) {
            alert("Offline. Will sync later.");
            return;
        }

        try {
            const res = await fetch(`/dresses/${id}/sold`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            if (!res.ok) throw new Error("Server error");
            const data = await res.json();
            alert(data.message);
            location.reload();

        } catch (err) {
            alert("❌ " + err.message);
        }
    }
</script>
<script>
function markSoldPrompt(dressId, maxQty) {
    const qty = prompt(`Enter quantity to mark as sold (1 to ${maxQty}):`);

    if (!qty || isNaN(qty) || qty < 1 || qty > maxQty) {
        alert("Invalid quantity.");
        return;
    }

    fetch(`/dresses/${dressId}/mark-sold`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            quantity_sold: parseInt(qty)  // ✅ ensure matches Laravel controller param
        })
    })
    .then(response => {
        if (response.ok) {
            location.reload(); // ✅ refresh UI after marking
        } else {
            return response.json().then(data => {
                alert(data.message || "Error marking as sold.");
            });
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Network or server error.");
    });
}
</script>

</x-app-layout>
