import { getAboutContent } from '@/lib/data';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'About Us',
  description: 'Learn about our experience, mission, and commitment to quality tire service.',
};

export default async function AboutPage() {
  const about = await getAboutContent();
  const certs = about?.certifications ? (JSON.parse(about.certifications) as string[]) : [];
  const images = about?.images ? (JSON.parse(about.images) as string[]) : [];

  return (
    <div className="container mx-auto px-4 py-12 max-w-4xl">
      <h1 className="text-4xl font-bold text-text-light mb-8">About Us</h1>
      <p className="text-muted text-lg leading-relaxed mb-8">{about?.intro ?? 'We are a trusted tire and automotive service provider.'}</p>

      <section className="mb-12">
        <h2 className="text-2xl font-bold text-accent mb-4">Our Mission</h2>
        <p className="text-muted">{about?.mission ?? 'To provide quality tires and service.'}</p>
      </section>
      <section className="mb-12">
        <h2 className="text-2xl font-bold text-accent mb-4">Our Vision</h2>
        <p className="text-muted">{about?.vision ?? 'To be the most trusted provider in our community.'}</p>
      </section>
      <section className="mb-12">
        <h2 className="text-2xl font-bold text-accent mb-4">Experience</h2>
        <p className="text-muted">{about?.experience ?? 'Years of experience serving our customers.'}</p>
      </section>
      {certs.length > 0 && (
        <section className="mb-12">
          <h2 className="text-2xl font-bold text-accent mb-4">Certifications</h2>
          <ul className="list-disc list-inside text-muted space-y-1">
            {certs.map((c, i) => (
              <li key={i}>{c}</li>
            ))}
          </ul>
        </section>
      )}
      {images.length > 0 && (
        <section>
          <h2 className="text-2xl font-bold text-accent mb-4">Workshop</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {images.map((src, i) => (
              <div key={i} className="aspect-video bg-surface rounded-lg overflow-hidden">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={src} alt={`Workshop ${i + 1}`} className="w-full h-full object-cover" />
              </div>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
