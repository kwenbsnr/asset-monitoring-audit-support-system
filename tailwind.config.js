/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
  ],
  safelist: [
    "bg-red-500",
    "text-white",
    "p-4",
    "rounded-lg",
    "shadow-lg",
    "text-center",
    "text-xl",
    "font-bold"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}