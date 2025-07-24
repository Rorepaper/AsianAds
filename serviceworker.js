const CACHE_NAME = 'pwa-habits-cache-v1';
const urlsToCache = [
  './',
  './AsianAds/index.html',
  './AsianAds/manifest.json',
  './icon-192.png', 
  './icon-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
  );
});
