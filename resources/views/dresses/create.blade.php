<x-app-layout>
<div class="max-w-2xl mx-auto p-4">
    <h1 class="text-xl font-bold mb-4">Add New Dress</h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('dresses.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Name</label>
            <input type="text" name="name" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Description</label>
            <textarea name="description" class="w-full border p-2 rounded"></textarea>
        </div>

        <div class="mb-4">
            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
           <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1"
       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
        </div>


        <div>
            <label class="block mb-1 font-medium">Buying Price</label>
            <input type="number" name="buying_price" step="0.01" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Selling Price</label>
            <input type="number" name="selling_price" step="0.01" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Photo</label>
            <input type="file" name="photo" class="w-full border p-2 rounded">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow w-full">📥 Save Dress</button>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', async function(e) {
        if (!navigator.onLine) {
            e.preventDefault();

            const formData = new FormData(this);
            const payload = {
                name: formData.get('name'),
                description: formData.get('description'),
                buying_price: formData.get('buying_price'),
                selling_price: formData.get('selling_price'),
            };

            await saveOfflineAction({
                type: 'create_dress',
                payload: payload
            });

            // Handle photo separately
            const file = formData.get('photo');
            if (file && file.size > 0) {
                const reader = new FileReader();
                reader.onload = async () => {
                    await saveOfflineAction({
                        type: 'upload_photo',
                        payload: {
                            base64: reader.result,
                            dress_id: '__last' // we will assign the last created dress later
                        }
                    });
                };
                reader.readAsDataURL(file);
            }

            alert('Saved offline. Will sync when back online.');
            this.reset();
        }
    });
</script>

</x-app-layout>
