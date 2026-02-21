'use client';

import { useState, useEffect } from 'react';

type AboutData = {
  intro?: string;
  mission?: string;
  vision?: string;
  experience?: string;
  certifications?: string;
  images?: string;
};

export default function AdminAboutForm() {
  const [data, setData] = useState<AboutData>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetch('/api/admin/about')
      .then((r) => r.json())
      .then(setData)
      .finally(() => setLoading(false));
  }, []);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      await fetch('/api/admin/about', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    } finally {
      setSaving(false);
    }
  }

  if (loading) return <p className="text-muted">Loading...</p>;

  return (
    <form onSubmit={handleSubmit} className="space-y-6 max-w-2xl">
      {(['intro', 'mission', 'vision', 'experience'] as const).map((key) => (
        <div key={key}>
          <label className="block text-sm font-medium text-muted mb-1">{key.charAt(0).toUpperCase() + key.slice(1)}</label>
          <textarea value={data[key] ?? ''} onChange={(e) => setData((d) => ({ ...d, [key]: e.target.value }))} rows={3} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
        </div>
      ))}
      <div>
        <label className="block text-sm font-medium text-muted mb-1">Certifications (JSON array, e.g. [&quot;Item 1&quot;, &quot;Item 2&quot;])</label>
        <input value={data.certifications ?? ''} onChange={(e) => setData((d) => ({ ...d, certifications: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
      </div>
      <div>
        <label className="block text-sm font-medium text-muted mb-1">Images (JSON array of URLs)</label>
        <input value={data.images ?? ''} onChange={(e) => setData((d) => ({ ...d, images: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
      </div>
      <button type="submit" disabled={saving} className="px-4 py-2 bg-accent text-white rounded hover:opacity-90 disabled:opacity-50">{saving ? 'Saving...' : 'Save'}</button>
    </form>
  );
}
