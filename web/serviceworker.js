self.addEventListener('install', function(event) {
  caches.delete('sw-cache');
  event.waitUntil(
    caches.open('sw-cache').then(function(cache) {

      return cache.add('css/admin.css');
    })
  );
});
 
self.addEventListener('fetch', function(event) {
  caches.delete('sw-cache');
  event.respondWith(
    caches.match(event.request).then(function(response) {
      return response || fetch(event.request);
    })
  );
});