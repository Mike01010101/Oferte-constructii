const staticCacheName = 'site-static-v1';
const dynamicCacheName = 'site-dynamic-v1';

// Resursele esențiale care formează "app shell"-ul
const assets = [
    '/',
    '/home',
    '/manifest.json',
    '/images/logo.png',
    // Adaugă aici CSS-ul și JS-ul principal dacă știi calea exactă după build
    // Exemplu: '/build/assets/app.12345.css'
    // Dar pentru început, le lăsăm să fie prinse în cache-ul dinamic
];

// Evenimentul de instalare: se adaugă "app shell"-ul în cache
self.addEventListener('install', evt => {
    evt.waitUntil(
        caches.open(staticCacheName).then(cache => {
            console.log('caching shell assets');
            return cache.addAll(assets);
        })
    );
});

// Evenimentul de activare: se curăță cache-urile vechi
self.addEventListener('activate', evt => {
    evt.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => key !== staticCacheName && key !== dynamicCacheName)
                .map(key => caches.delete(key))
            );
        })
    );
});

// Evenimentul fetch: răspunde cu resurse din cache sau de pe rețea
self.addEventListener('fetch', evt => {
    // Nu stoca în cache request-urile non-GET
    if (evt.request.method !== 'GET') {
        return;
    }
    
    evt.respondWith(
        caches.match(evt.request).then(cacheRes => {
            // Dacă găsim în cache, returnăm direct
            if (cacheRes) {
                return cacheRes;
            }
            // Altfel, facem fetch de pe rețea
            return fetch(evt.request).then(fetchRes => {
                // Și adăugăm răspunsul în cache-ul dinamic pentru viitor
                return caches.open(dynamicCacheName).then(cache => {
                    cache.put(evt.request.url, fetchRes.clone());
                    return fetchRes;
                });
            });
        }).catch(() => {
            // Dacă totul eșuează (e.g., offline și nu e în cache), poți returna o pagină de fallback
            // return caches.match('/offline.html'); // Necesită crearea paginii offline.html
        })
    );
});