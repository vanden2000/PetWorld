/** @type {import('next').NextConfig} */
const backendUrl = new URL(
  process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000",
);

const nextConfig = {
  reactCompiler: true,
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
    ],
  },
};

export default nextConfig;
