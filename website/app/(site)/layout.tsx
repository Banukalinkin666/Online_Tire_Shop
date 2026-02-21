import { getSiteSettings } from '@/lib/data';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export default async function SiteLayout({ children }: { children: React.ReactNode }) {
  const settings = await getSiteSettings();
  return (
    <>
      <Navbar logoUrl={settings?.logo ?? undefined} />
      <main className="flex-1">{children}</main>
      <Footer
        footerText={settings?.footerText ?? undefined}
        facebook={settings?.facebook ?? undefined}
        twitter={settings?.twitter ?? undefined}
        instagram={settings?.instagram ?? undefined}
        linkedin={settings?.linkedin ?? undefined}
      />
    </>
  );
}
