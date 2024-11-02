/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{html,js,php}",         // File di root
    "./auth/**/*.{html,js,php}",  // File di folder auth
    "./components/**/*.{html,js,php}", // File di folder components
    "./admin/pages/**/*.{html,js,php}", // File di folder admin/pages
    "./admin/components/**/*.{html,js,php}", // File di folder admin/components
    "./admin/javascript/**/*.{html,js,php,js}", // File di folder admin/javascript
    "./admin/functions/**/*.{html,js,php}", // File di folder admin/functions
  ],
  safelist: [
    // Warna yang sering digunakan
    {
      pattern: /(bg|text|border)-(red|green|blue|yellow|gray)-(100|500|600|700|800)/,
    },
    // Utility classes yang digunakan dalam JavaScript
    'hidden',
    'block',
    'rounded',
    'px-2',
    'px-3',
    'px-4',
    'py-1',
    'py-2',
    'mr-1',
    'mb-4',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}