import { defineConfig } from "vite";
import path from "node:path";
import fullReload from "vite-plugin-full-reload";

export default defineConfig(({ command }) => ({
  base: command === "serve" ? "/" : "/assets/dist/",
  plugins: [
    fullReload(["site/snippets/**/*.php", "site/templates/**/*.php"])
  ],
  server: {
    host: "127.0.0.1",
    port: 5173,
    strictPort: true,
    watch: {
      usePolling: true,
      interval: 100
    }
  },
  build: {
    manifest: true,
    outDir: path.resolve("assets/dist"),
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: path.resolve("src/js/main.js"),
        ausstellung: path.resolve("src/js/ausstellung.js"),
        reise: path.resolve("src/js/reise.js")
      }
    }
  }
}));
