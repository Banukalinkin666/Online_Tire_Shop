import Link from 'next/link';
import { getHomepageContent, getBrands, getTestimonials, getServices, getSiteSettings } from '@/lib/data';
import TireSizeFinder from '@/components/TireSizeFinder';

export default async function HomePage() {
  const [home, brands, testimonials, services, settings] = await Promise.all([
    getHomepageContent(),
    getBrands(),
    getTestimonials(),
    getServices(),
    getSiteSettings(),
  ]);

  const tireFinderUrl = settings?.tireFinderUrl ?? process.env.TIRE_FINDER_URL ?? 'https://online-tire-shop-pro.onrender.com';
  const whyChooseUs = home?.whyChooseUs ? (JSON.parse(home.whyChooseUs) as { title: string; description: string }[]) : [];

  return (
    <>
      <section className="relative py-12 md:py-16 px-4">
        <div className="container mx-auto max-w-5xl">
          <div className="text-center mb-8 animate-slide-up">
            <h1 className="text-4xl md:text-5xl font-bold text-text-light mb-4">{home?.heroTitle ?? 'Find Your Perfect Tire Size'}</h1>
            <p className="text-muted text-lg max-w-2xl mx-auto">{home?.heroSubtitle ?? 'Enter your vehicle details and get instant recommendations.'}</p>
          </div>
          <div className="animate-fade-in">
            <TireSizeFinder url={tireFinderUrl} />
          </div>
          <div className="text-center mt-6">
            <a href="#services" className="inline-block px-6 py-3 bg-accent text-white font-medium rounded-lg hover:opacity-90 transition-opacity">
              {home?.ctaText ?? 'Get Started'}
            </a>
          </div>
        </div>
      </section>

      {brands.length > 0 && (
        <section className="py-12 bg-surface/50 border-y border-surface">
          <div className="container mx-auto px-4">
            <h2 className="text-2xl font-bold text-center text-text-light mb-8">Trusted Brands</h2>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 items-center justify-items-center">
              {brands.map((b) => (
                <div key={b.id} className="text-muted font-semibold text-center">{b.name}</div>
              ))}
            </div>
          </div>
        </section>
      )}

      <section id="services" className="py-16 px-4">
        <div className="container mx-auto">
          <h2 className="text-3xl font-bold text-center text-text-light mb-4">Our Services</h2>
          <p className="text-muted text-center max-w-2xl mx-auto mb-12">Quality tire sales, installation, and automotive services.</p>
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            {services.slice(0, 4).map((s) => (
              <div key={s.id} className="bg-surface rounded-xl p-6 border border-background hover:border-accent/30 transition-colors">
                <div className="text-accent text-2xl font-bold mb-2">{s.title}</div>
                <p className="text-muted text-sm">{s.description}</p>
              </div>
            ))}
          </div>
          <div className="text-center mt-8">
            <Link href="/services" className="text-accent font-medium hover:underline">View all services →</Link>
          </div>
        </div>
      </section>

      {whyChooseUs.length > 0 && (
        <section className="py-16 bg-surface/50 px-4">
          <div className="container mx-auto">
            <h2 className="text-3xl font-bold text-center text-text-light mb-12">Why Choose Us</h2>
            <div className="grid md:grid-cols-3 gap-8">
              {whyChooseUs.map((item, i) => (
                <div key={i} className="text-center animate-slide-up" style={{ animationDelay: `${i * 0.1}s` }}>
                  <h3 className="text-xl font-semibold text-accent mb-2">{item.title}</h3>
                  <p className="text-muted">{item.description}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {testimonials.length > 0 && (
        <section className="py-16 px-4">
          <div className="container mx-auto">
            <h2 className="text-3xl font-bold text-center text-text-light mb-12">What Our Customers Say</h2>
            <div className="grid md:grid-cols-3 gap-6">
              {testimonials.map((t) => (
                <blockquote key={t.id} className="bg-surface rounded-xl p-6 border border-background">
                  <p className="text-muted italic mb-4">&ldquo;{t.content}&rdquo;</p>
                  <footer className="text-text-light font-medium">{t.name}{t.role ? `, ${t.role}` : ''}</footer>
                  <div className="text-accent mt-1">{'★'.repeat(t.rating)}</div>
                </blockquote>
              ))}
            </div>
          </div>
        </section>
      )}

      <section className="py-16 bg-accent/10 border-t border-surface px-4">
        <div className="container mx-auto text-center">
          <h2 className="text-3xl font-bold text-text-light mb-4">Ready to Find Your Tires?</h2>
          <p className="text-muted mb-6 max-w-xl mx-auto">Use our tire finder above or get in touch for personalized help.</p>
          <Link href="/contact" className="inline-block px-8 py-3 bg-accent text-white font-medium rounded-lg hover:opacity-90 transition-opacity">
            Contact Us
          </Link>
        </div>
      </section>
    </>
  );
}
