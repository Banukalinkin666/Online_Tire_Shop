'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';

const links = [
  { href: '/admin/dashboard', label: 'Dashboard' },
  { href: '/admin/home', label: 'Home' },
  { href: '/admin/about', label: 'About' },
  { href: '/admin/services', label: 'Services' },
  { href: '/admin/contact', label: 'Contact' },
  { href: '/admin/settings', label: 'Settings' },
];

export default function AdminNav() {
  const router = useRouter();

  async function handleLogout() {
    await fetch('/api/admin/logout', { method: 'POST' });
    router.push('/admin/login');
    router.refresh();
  }

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 h-14 bg-surface border-b border-background flex items-center justify-between px-4">
      <div className="flex items-center gap-6">
        <Link href="/admin/dashboard" className="font-bold text-text-light">Admin</Link>
        {links.map((l) => (
          <Link key={l.href} href={l.href} className="text-muted hover:text-text-light text-sm">{l.label}</Link>
        ))}
      </div>
      <div className="flex items-center gap-4">
        <Link href="/" target="_blank" className="text-muted hover:text-text-light text-sm">View site</Link>
        <button type="button" onClick={handleLogout} className="text-muted hover:text-accent text-sm">Logout</button>
      </div>
    </nav>
  );
}
