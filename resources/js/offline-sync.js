import localforage from "localforage";

// Offline storage setup
const dressStore = localforage.createInstance({
    name: "dress-inventory",
    storeName: "actions"
});

// Save any action (create, mark sold, photo upload)
window.saveOfflineAction = async function (action) {
    const timestamp = Date.now();
    await dressStore.setItem(`action-${timestamp}`, action);
    console.log("📦 Saved offline:", action);
};

// Convert base64 string to a Blob (for photo upload)
function base64ToBlob(base64Data) {
    const parts = base64Data.split(';base64,');
    const contentType = parts[0].split(':')[1];
    const byteCharacters = atob(parts[1]);
    const byteArrays = [];

    for (let offset = 0; offset < byteCharacters.length; offset += 512) {
        const slice = byteCharacters.slice(offset, offset + 512);
        const byteNumbers = Array.from(slice).map(c => c.charCodeAt(0));
        byteArrays.push(new Uint8Array(byteNumbers));
    }

    return new Blob(byteArrays, { type: contentType });
}

// Sync all saved offline actions
async function syncActions() {
    const keys = await dressStore.keys();

    for (let key of keys) {
        const action = await dressStore.getItem(key);

        try {
            // Handle different action types
            if (action.type === 'create_dress') {
                const res = await fetch('/api/sync', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(action)
                });

                if (res.ok) {
                    const responseData = await res.json();
                    console.log('✅ Dress synced:', responseData);

                    // Save new dress ID for photo sync if needed
                    if (responseData.dress_id) {
                        action.payload._synced_dress_id = responseData.dress_id;

                        // Update all queued photo uploads that were linked to this
                        const photoKeys = await dressStore.keys();
                        for (let photoKey of photoKeys) {
                            const queued = await dressStore.getItem(photoKey);
                            if (queued?.type === 'upload_photo' && queued.payload.dress_id === '__last') {
                                queued.payload.dress_id = responseData.dress_id;
                                await dressStore.setItem(photoKey, queued);
                            }
                        }
                    }

                    await dressStore.removeItem(key);
                }

            } else if (action.type === 'mark_sold') {
                const res = await fetch('/api/sync', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(action)
                });

                if (res.ok) {
                    console.log('✅ Marked as sold:', action.payload.id);
                    await dressStore.removeItem(key);
                }

            } else if (action.type === 'upload_photo') {
                if (action.payload.dress_id === '__last') {
                    console.log('⏳ Waiting for dress_id...');
                    continue;
                }

                const formData = new FormData();
                formData.append('photo', base64ToBlob(action.payload.base64), 'photo.jpg');
                formData.append('dress_id', action.payload.dress_id);

                const res = await fetch('/api/sync-photo', {
                    method: 'POST',
                    body: formData
                });

                if (res.ok) {
                    console.log('📷 Photo uploaded for dress ID', action.payload.dress_id);
                    await dressStore.removeItem(key);
                }
            }
        } catch (err) {
            console.error("❌ Sync failed:", err);
        }
    }
}

// Auto sync when back online
window.addEventListener("online", syncActions);
