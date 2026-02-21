'use client';

import { useState, useEffect } from 'react';

type Testimonial = { id: string; name: string; role: string | null; content: string; rating: number; order: number };

export default function AdminTestimonials() {
  const [list, setList] = useState<Testimonial[]>([]);
  const [form, setForm] = useState({ name: '', role: '', content: '', rating: 5 });
  const [loading, setLoading] = useState(true);

  function load() {
    fetch('/api/admin/testimonials')
      .then((r) => r.json())
      .then(setList)
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    load();
  }, []);

  async function handleAdd(e: React.FormEvent) {
    e.preventDefault();
    if (!form.name.trim() || !form.content.trim()) return;
    await fetch('/api/admin/testimonials', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: form.name.trim(), role: form.role.trim() || undefined, content: form.content.trim(), rating: form.rating }),
    });
    setForm({ name: '', role: '', content: '', rating: 5 });
    load();
  }

  async function handleDelete(id: string) {
    await fetch('/api/admin/testimonials?id=' + id, { method: 'DELETE' });
    load();
  }

  if (loading) return null;
  return (
    <div className="mt-8 pt-8 border-t border-surface">
      <h2 className="text-lg font-semibold text-text-light mb-4">Testimonials</h2>
      <form onSubmit={handleAdd} className="space-y-2 mb-4 max-w-xl">
        <input value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} placeholder="Name" className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
        <input value={form.role} onChange={(e) => setForm((f) => ({ ...f, role: e.target.value }))} placeholder="Role (optional)" className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
        <textarea value={form.content} onChange={(e) => setForm((f) => ({ ...f, content: e.target.value }))} placeholder="Content" rows={2} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
        <input type="number" min={1} max={5} value={form.rating} onChange={(e) => setForm((f) => ({ ...f, rating: parseInt(e.target.value, 10) || 5 }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
        <button type="submit" className="px-4 py-2 bg-accent text-white rounded">Add</button>
      </form>
      <ul className="space-y-2">
        {list.map((t) => (
          <li key={t.id} className="flex justify-between items-start bg-surface rounded p-4 border border-background">
            <div>
              <p className="font-medium text-text-light">{t.name}{t.role ? `, ${t.role}` : ''}</p>
              <p className="text-muted text-sm">{t.content}</p>
            </div>
            <button type="button" onClick={() => handleDelete(t.id)} className="text-accent text-sm">Delete</button>
          </li>
        ))}
      </ul>
    </div>
  );
}
