'use client';

import Link from 'next/link';
import { useState } from 'react';
import Image from 'next/image';

const navLinks = [
  { href: '/', label: 'Home' },
  { href: '/about', label: 'About' },
  { href: '/services', label: 'Services' },
  { href: '/contact', label: 'Contact' },
];

export default function Navbar({ logoUrl }: { logoUrl?: string | null }) {
  const [open, setOpen] = useState(false);
  return (
    <header className="sticky top-0 z-50 bg-background/95 backdrop-blur border-b border-surface">
      <nav className="container mx-auto px-4 flex items-center justify-between h-16">
        <Link href="/" className="flex items-center gap-2">
          {logoUrl ? (
            <Image src={logoUrl} alt="Logo" width={120} height={40} className="h-8 w-auto object-contain" />
          ) : (
            <span className="text-xl font-bold text-text-light">Online Tire Shop</span>
          )}
        </Link>
        <ul className="hidden md:flex items-center gap-8">
          {navLinks.map((l) => (
            <li key={l.href}>
              <Link href={l.href} className="text-muted hover:text-text-light transition-colors">
                {l.label}
              </Link>
            </li>
          ))}
        </ul>
        <button
          type="button"
          className="md:hidden p-2 text-text-light"
          onClick={() => setOpen((o) => !o)}
          aria-label="Menu"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {open ? <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /> : <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />}
          </svg>
        </button>
      </nav>
      {open && (
        <div className="md:hidden border-t border-surface px-4 py-3 animate-fade-in">
          <ul className="flex flex-col gap-2">
            {navLinks.map((l) => (
              <li key={l.href}>
                <Link href={l.href} className="block py-2 text-muted hover:text-text-light" onClick={() => setOpen(false)}>
                  {l.label}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      )}
    </header>
  );
}
