import { getServices } from '@/lib/data';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Services',
  description: 'Tire sales, installation, alignment, and repair services.',
};

export default async function ServicesPage() {
  const services = await getServices();

  return (
    <div className="container mx-auto px-4 py-12">
      <h1 className="text-4xl font-bold text-text-light mb-4">Our Services</h1>
      <p className="text-muted text-lg mb-12">Quality tire and automotive services tailored to your needs.</p>
      <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        {services.map((s) => (
          <article key={s.id} className="bg-surface rounded-xl p-6 border border-background hover:border-accent/30 transition-colors">
            <h2 className="text-xl font-bold text-accent mb-2">{s.title}</h2>
            <p className="text-muted">{s.description}</p>
          </article>
        ))}
      </div>
      {services.length === 0 && <p className="text-muted">No services listed yet. Check back soon.</p>}
    </div>
  );
}
