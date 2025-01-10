import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
  plugins: [
    symfonyPlugin(),
    viteStaticCopy({
      targets: [
        {
          src: 'resources/assets/images/**/*',
          dest: 'images',
        },
        // {
        //   src: 'resources/assets/videos/**/*',
        //   dest: 'videos',
        // },
        // {
        //   src: 'resources/assets/uploads_or_downloads/**/*',
        //   dest: 'uploads_or_downloads',
        // },
      ],
    }),
  ],
  build: {
    outDir: 'public/build',
    manifest: true,
    rollupOptions: {
      input: {
        app: './resources/js/app.js',
        categories: './resources/js/categories.js',
        transactions: './resources/js/transactions.js',
        verify: './resources/js/verify.js',
        auth: './resources/js/auth.js',
        profile: './resources/js/profile.js',
        forgotPassword: './resources/js/forgot-password.js',
        dashboard: './resources/js/dashboard.js',
        theme: './resources/css/style.css',
      },
    },
  },
  server: {
    watch: {
      usePolling: true, // Useful for environments like Docker
      ignored: ['!**/resources/**'], // Watch only specific folders
    },
    host: true, // Makes the server accessible from your local network
    port: 3000, // Custom port (optional)
  },
  preview: {
    port: 5000, // Preview mode port
    strictPort: true, // Enforce the port
  },
});
