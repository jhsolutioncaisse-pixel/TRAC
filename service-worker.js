self.addEventListener("install", () => {
    console.log("Service Worker installé");
    self.skipWaiting();
});

self.addEventListener("activate", () => {
    console.log("Service Worker activé");
});