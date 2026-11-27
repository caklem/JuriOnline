import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  cacheComponents: true,
  partialPrefetching: true,
  async rewrites() {
    // File publik Laravel (juknis, banner) tetap satu origin lewat Next.
    return [
      { source: "/storage/:path*", destination: "http://127.0.0.1:8082/storage/:path*" },
    ];
  },
  experimental: {
    // Bukti transfer 2MB dan dokumen juknis 10MB; sisakan ruang untuk overhead multipart.
    serverActions: {
      bodySizeLimit: "12mb",
    },
  },
};

export default nextConfig;
