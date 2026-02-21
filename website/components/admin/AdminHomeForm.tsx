'use client';

import { useState, useEffect } from 'react';
import AdminBrands from './AdminBrands';
import AdminTestimonials from './AdminTestimonials';

type HomeData = {
  heroTitle?: string;
  heroSubtitle?: string;
  ctaText?: string;
  whyChooseUs?: string;
};

type WhyItem = { title: string; description: string };

export default function AdminHomeForm() {
  const [data, setData] = useState<HomeData>({});
  const [whyItems, setWhyItems] = useState<WhyItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetch('/api/admin/home')
      .then((r) => r.json())
      .then((d) => {
        setData({ heroTitle: d.heroTitle, heroSubtitle: d.heroSubtitle, ctaText: d.ctaText });
        try {
          setWhyItems(d.whyChooseUs ? JSON.parse(d.whyChooseUs) : []);
        } catch {
          setWhyItems([]);
        }
      })
      .finally(() => setLoading(false));
  }, []);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      await fetch('/api/admin/home', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...data,
          whyChooseUs: JSON.stringify(whyItems),
        }),
      });
    } finally {
      setSaving(false);
    }
  }

  if (loading) return <p className="text-muted">Loading...</p>;

  return (
    <form onSubmit={handleSubmit} className="space-y-6 max-w-2xl">
      <div>
        <label className="block text-sm font-medium text-muted mb-1">Hero Title</label>
        <input value={data.heroTitle ?? ''} onChange={(e) => setData((d) => ({ ...d, heroTitle: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
      </div>
      <div>
        <label className="block text-sm font-medium text-muted mb-1">Hero Subtitle</label>
        <textarea value={data.heroSubtitle ?? ''} onChange={(e) => setData((d) => ({ ...d, heroSubtitle: e.target.value }))} rows={2} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
      </div>
      <div>
        <label className="block text-sm font-medium text-muted mb-1">CTA Button Text</label>
        <input value={data.ctaText ?? ''} onChange={(e) => setData((d) => ({ ...d, ctaText: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
      </div>
      <div>
        <label className="block text-sm font-medium text-muted mb-2">Why Choose Us (items)</label>
        {whyItems.map((item, i) => (
          <div key={i} className="flex gap-2 mb-2">
            <input placeholder="Title" value={item.title} onChange={(e) => setWhyItems((prev) => prev.map((x, j) => (j === i ? { ...x, title: e.target.value } : x)))} className="flex-1 px-4 py-2 bg-surface border border-background rounded text-text-light" />
            <input placeholder="Description" value={item.description} onChange={(e) => setWhyItems((prev) => prev.map((x, j) => (j === i ? { ...x, description: e.target.value } : x)))} className="flex-1 px-4 py-2 bg-surface border border-background rounded text-text-light" />
            <button type="button" onClick={() => setWhyItems((prev) => prev.filter((_, j) => j !== i))} className="text-accent">Remove</button>
          </div>
        ))}
        <button type="button" onClick={() => setWhyItems((prev) => [...prev, { title: '', description: '' }])} className="text-accent text-sm">+ Add item</button>
      </div>
      <button type="submit" disabled={saving} className="px-4 py-2 bg-accent text-white rounded hover:opacity-90 disabled:opacity-50">{saving ? 'Saving...' : 'Save'}</button>
      <AdminBrands />
      <AdminTestimonials />
    </form>
  );
}
