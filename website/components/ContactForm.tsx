'use client';

import { useState } from 'react';

export default function ContactForm() {
  const [status, setStatus] = useState<'idle' | 'sending' | 'done' | 'error'>('idle');
  const [formData, setFormData] = useState({ name: '', email: '', phone: '', subject: '', message: '' });

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setStatus('sending');
    try {
      const res = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });
      if (!res.ok) throw new Error('Failed');
      setStatus('done');
      setFormData({ name: '', email: '', phone: '', subject: '', message: '' });
    } catch {
      setStatus('error');
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label htmlFor="name" className="block text-sm font-medium text-text-light mb-1">Name *</label>
        <input id="name" type="text" required value={formData.name} onChange={(e) => setFormData((d) => ({ ...d, name: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded-lg text-text-light focus:ring-2 focus:ring-accent" />
      </div>
      <div>
        <label htmlFor="email" className="block text-sm font-medium text-text-light mb-1">Email *</label>
        <input id="email" type="email" required value={formData.email} onChange={(e) => setFormData((d) => ({ ...d, email: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded-lg text-text-light focus:ring-2 focus:ring-accent" />
      </div>
      <div>
        <label htmlFor="phone" className="block text-sm font-medium text-text-light mb-1">Phone</label>
        <input id="phone" type="tel" value={formData.phone} onChange={(e) => setFormData((d) => ({ ...d, phone: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded-lg text-text-light focus:ring-2 focus:ring-accent" />
      </div>
      <div>
        <label htmlFor="subject" className="block text-sm font-medium text-text-light mb-1">Subject</label>
        <input id="subject" type="text" value={formData.subject} onChange={(e) => setFormData((d) => ({ ...d, subject: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded-lg text-text-light focus:ring-2 focus:ring-accent" />
      </div>
      <div>
        <label htmlFor="message" className="block text-sm font-medium text-text-light mb-1">Message *</label>
        <textarea id="message" required rows={4} value={formData.message} onChange={(e) => setFormData((d) => ({ ...d, message: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded-lg text-text-light focus:ring-2 focus:ring-accent" />
      </div>
      <button type="submit" disabled={status === 'sending'} className="w-full py-3 bg-accent text-white font-medium rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity">
        {status === 'sending' ? 'Sending...' : 'Send Message'}
      </button>
      {status === 'done' && <p className="text-green-400 text-sm">Message sent. We&apos;ll get back to you soon.</p>}
      {status === 'error' && <p className="text-red-400 text-sm">Something went wrong. Please try again.</p>}
    </form>
  );
}
