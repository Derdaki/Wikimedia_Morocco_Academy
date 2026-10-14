module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./public/assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          red: "#A6192E",
          green: "#14866D",
          navy: "#141827",
          paper: "#F8F9FB",
          line: "#E5E7EB",
          ink: "#1F2937"
        }
      },
      fontFamily: {
        sans: ["Inter", "Cairo", "Noto Sans Arabic", "system-ui", "sans-serif"]
      },
      boxShadow: {
        soft: "0 18px 45px rgba(20, 24, 39, 0.08)"
      }
    }
  },
  plugins: []
};
