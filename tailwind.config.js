/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#B5E93F',
        secondary:{
          100: '#b7eb41',
          200: '#AFD145',
        },
        light_pink: '#fff9e6',
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
