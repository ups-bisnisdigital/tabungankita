if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    // Determine the path to sw.js. 
    // Since this script is included in files at the root, ./sw.js or sw.js should work.
    // However, to be safe, we can use the root relative path if we know the project structure.
    // But ./sw.js is standard for root files.
    
    navigator.serviceWorker.register('sw.js')
      .then((registration) => {
        console.log('ServiceWorker registration successful with scope: ', registration.scope);
      })
      .catch((err) => {
        console.log('ServiceWorker registration failed: ', err);
      });
  });
}
