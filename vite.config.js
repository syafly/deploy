import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/app/index.css', 
                'resources/app/index.js',
                'resources/halaman/login/index.css',
                'resources/halaman/login/index.js',
                'resources/halaman/home/index.css',
                'resources/halaman/home/index.js',
                'resources/halaman/reservasi/index.js',
                'resources/halaman/reservasi/index.css',
                'resources/halaman/siswa/index.css',
                'resources/halaman/siswa/index.js',
                'resources/halaman/siswa/update.js',
                'resources/halaman/siswa/create.js',
                'resources/halaman/penilaian/index.css',
                'resources/halaman/penilaian/index.js',
                'resources/halaman/scan/index.css',
                'resources/halaman/scan/index.js',
                'resources/halaman/absensi/index.css',
                'resources/halaman/absensi/index.js'
            ],
            refresh: true,
        }),
    ],
});
