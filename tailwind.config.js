/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  safelist: [
    'bg-green-200', 'text-green-800',
    'bg-emerald-200', 'text-emerald-800',
    'bg-yellow-200', 'text-yellow-800',
    'bg-orange-200', 'text-orange-800',
    'bg-red-200', 'text-red-800',
    'bg-gray-200', 'text-gray-600',
  ],
  theme: {
    extend: {
        screens: {
            'xs-max': { max: '360px' },
          },
      colors: {
        primary: '#B5E93F',
        secondary:{
          100: '#b7eb41',
          200: '#AFD145',
        },
        dark_green: '#326916',
        body: '#f3f2ee',
        dark: '#001e00',
      },
      fontFamily: {
        body: ['Nunito'],
        helvetica: ['Helvetica'],
        quicksand: ['Quicksand', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
      },
      fontSize: {
        'xxs': '0.625rem', // 10px
        'xxxs': '0.5rem',  // 8px
      },
      height: {
        '128': '32rem',
        '150': '40rem',
      }
    },
  },
  plugins: [
    require('@tailwindcss/aspect-ratio'),
],
}
