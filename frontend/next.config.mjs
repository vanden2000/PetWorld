import path from "node:path";
import { fileURLToPath } from "node:url";

/** @type {import('next').NextConfig} */
const projectRoot = path.dirname(fileURLToPath(import.meta.url));
const backendUrl = new URL(
  process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000",
);

const nextConfig = {
  reactCompiler: false,
  turbopack: {
    root: projectRoot,
  },
  images: {
    dangerouslyAllowLocalIP: true,
    remotePatterns: [
      {
        protocol: backendUrl.protocol.replace(":", ""),
        hostname: backendUrl.hostname,
        port: backendUrl.port,
        pathname: "/image/**",
      },
      {
        protocol: backendUrl.protocol.replace(":", ""),
        hostname: backendUrl.hostname,
        port: backendUrl.port,
        pathname: "/storage/**",
      },
      {
        protocol: backendUrl.protocol.replace(":", ""),
        hostname: backendUrl.hostname,
        port: backendUrl.port,
        pathname: "/uploads/**",
      },
    ],
  },
};

export default nextConfig;
