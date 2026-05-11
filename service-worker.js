const CACHE_NAME = "jh-solution-cache-v1";
const FILES_TO_CACHE = [
  "index.html",
  "login.html",
  "manifest.json",
  "iconjh-192.png",
  "iconjh-512.png",
  "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
  "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
];

// Installer le service worker et mettre en cache les fichiers essentiels
self.addEventListener("install", event => {
  console.log("[Service Worker] Installation");
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log("[Service Worker] Mise en cache des fichiers");
        return cache.addAll(FILES_TO_CACHE);
      })
      .then(() => self.skipWaiting())
  );
});

// Activation du service worker
self.addEventListener("activate", event => {
  console.log("[Service Worker] Activation");
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            console.log("[Service Worker] Suppression ancien cache :", key);
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Intercepter les requêtes réseau
self.addEventListener("fetch", event => {
  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      if (cachedResponse) return cachedResponse;
      return fetch(event.request).then(response => {
        return caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, response.clone());
          return response;
        });
      }).catch(() => {
        // Fallback offline pour page HTML
        if (event.request.destination === "document") {
          return caches.match("index.html");
        }
      });
    })
  );
});
