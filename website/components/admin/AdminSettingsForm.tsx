'use client';

import { useState, useEffect } from 'react';

type Settings = {
  logo?: string;
  footerText?: string;
  facebook?: string;
  twitter?: string;
  instagram?: string;
  linkedin?: string;
  tireFinderUrl?: string;
};

export default function AdminSettingsForm() {
  const [data, setData] = useState<Settings>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetch('/api/admin/settings')
      .then((r) => r.json())
      .then(setData)
      .finally(() => setLoading(false));
  }, []);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      await fetch('/api/admin/settings', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    } finally {
      setSaving(false);
    }
  }

  if (loading) return <p className="text-muted">Loading...</p>;

  return (
    <form onSubmit={handleSubmit} className="space-y-6 max-w-xl">
      <div>
        <label className="block text-sm font-medium text-muted mb-1">Logo URL</label>
        <input value={data.logo ?? ''} onChange={(e) => setData((d) => ({ ...d, logo: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" placeholder="https://..." />
      </div>
      <div>
        <label className="block text-sm font-medium text-muted mb-1">Footer text</label>
        <textarea value={data.footerText ?? ''} onChange={(e) => setData((d) => ({ ...d, footerText: e.target.value }))} rows={2} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
      </div>
      <div>
        <label className="block text-sm font-medium text-muted mb-1">Tire Finder URL (embed)</label>
        <input value={data.tireFinderUrl ?? ''} onChange={(e) => setData((d) => ({ ...d, tireFinderUrl: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" placeholder="https://online-tire-shop-pro.onrender.com" />
      </div>
      <div>
        <label className="block text-sm font-medium text-muted mb-2">Social links</label>
        {(['facebook', 'twitter', 'instagram', 'linkedin'] as const).map((key) => (
          <input key={key} value={data[key] ?? ''} onChange={(e) => setData((d) => ({ ...d, [key]: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light mb-2" placeholder={key} />
        ))}
      </div>
      <button type="submit" disabled={saving} className="px-4 py-2 bg-accent text-white rounded hover:opacity-90 disabled:opacity-50">{saving ? 'Saving...' : 'Save'}</button>
    </form>
  );
}
