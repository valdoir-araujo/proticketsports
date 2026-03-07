import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // -----------------------------------------------------------------
    // CORREÇÃO ADICIONADA AQUI
    // -----------------------------------------------------------------
    // Esta 'safelist' (lista de segurança) força o Tailwind a 
    // manter estas classes no ficheiro CSS final, mesmo que ele
    // pense que não estão a ser usadas (como no caso do botão de upload).
    safelist: [
        'bg-teal-500',
        'hover:bg-teal-600',
        'text-slate-500',
        'file:mr-4',
        'file:py-2',
        'file:px-4',
        'file:rounded-full',
        'file:border-0',
        'file:text-sm',
        'file:font-semibold',
        'file:bg-blue-50',
        'file:text-blue-700',
        'hover:file:bg-blue-100',
    ],
    // -----------------------------------------------------------------
    // Fim da correção
    // -----------------------------------------------------------------

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};