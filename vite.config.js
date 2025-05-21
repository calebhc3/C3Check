import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  server: {
    host: '0.0.0.0',   // ESSENCIAL pra aceitar conexões externas
    port: 5173,        // ou a porta que você escolheu
    hmr: {
      host: '162.240.171.178',  // IP externo do servidor (pra WebSocket funcionar)
      protocol: 'ws',
      port: 5173,
    },
  },
  plugins: [laravel({
    input: ['resources/css/app.css', 'resources/js/app.js'],
    refresh: true,
  })],
});
