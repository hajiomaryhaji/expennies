/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.twig',
    './resources/**/*.js', 
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {},
  plugins: [
    require('flowbite/plugin')
]
}

