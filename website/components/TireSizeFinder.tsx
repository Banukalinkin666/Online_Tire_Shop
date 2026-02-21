'use client';

/**
 * Embeds the existing Tire Size Finder app (e.g. hosted on Render).
 * DO NOT rebuild the finder – this component only embeds it via iframe.
 */
export default function TireSizeFinder({ url }: { url: string }) {
  const src = url?.startsWith('http') ? url : 'https://online-tire-shop-pro.onrender.com';
  return (
    <div className="w-full rounded-xl overflow-hidden border border-surface shadow-xl animate-fade-in">
      <iframe
        src={src}
        title="Tire Size Finder"
        className="w-full min-h-[640px] h-[70vh] border-0 block"
        loading="lazy"
      />
    </div>
  );
}
