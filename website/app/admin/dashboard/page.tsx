import Link from 'next/link';
import { prisma } from '@/lib/prisma';

export default async function AdminDashboardPage() {
  const [messagesCount, servicesCount, testimonialsCount] = await Promise.all([
    prisma.contactMessage.count(),
    prisma.service.count(),
    prisma.testimonial.count(),
  ]);

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-text-light mb-6">Dashboard</h1>
      <div className="grid md:grid-cols-3 gap-4">
        <Link href="/admin/contact" className="bg-surface rounded-lg p-6 border border-background hover:border-accent/30">
          <div className="text-3xl font-bold text-accent">{messagesCount}</div>
          <div className="text-muted">Contact messages</div>
        </Link>
        <div className="bg-surface rounded-lg p-6 border border-background">
          <div className="text-3xl font-bold text-accent">{servicesCount}</div>
          <div className="text-muted">Services</div>
        </div>
        <div className="bg-surface rounded-lg p-6 border border-background">
          <div className="text-3xl font-bold text-accent">{testimonialsCount}</div>
          <div className="text-muted">Testimonials</div>
        </div>
      </div>
      <div className="mt-8">
        <h2 className="text-lg font-semibold text-text-light mb-2">Quick links</h2>
        <ul className="text-muted space-y-1">
          <li><Link href="/admin/home" className="hover:text-accent">Edit Home content</Link></li>
          <li><Link href="/admin/about" className="hover:text-accent">Edit About content</Link></li>
          <li><Link href="/admin/services" className="hover:text-accent">Manage Services</Link></li>
          <li><Link href="/admin/contact" className="hover:text-accent">Contact info & messages</Link></li>
          <li><Link href="/admin/settings" className="hover:text-accent">Site settings</Link></li>
        </ul>
      </div>
    </div>
  );
}
