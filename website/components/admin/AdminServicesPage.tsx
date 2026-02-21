'use client';

import { useState, useEffect } from 'react';

type Service = { id: string; title: string; description: string; icon: string; order: number };

export default function AdminServicesPage() {
  const [list, setList] = useState<Service[]>([]);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState<Service | null>(null);
  const [form, setForm] = useState({ title: '', description: '', icon: 'wrench' });

  function load() {
    fetch('/api/admin/services')
      .then((r) => r.json())
      .then(setList)
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    load();
  }, []);

  async function handleSave(e: React.FormEvent) {
    e.preventDefault();
    if (editing) {
      await fetch(`/api/admin/services/${editing.id}`, { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(form) });
      setEditing(null);
    } else {
      await fetch('/api/admin/services', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(form) });
    }
    setForm({ title: '', description: '', icon: 'wrench' });
    load();
  }

  async function handleDelete(id: string) {
    if (!confirm('Delete this service?')) return;
    await fetch(`/api/admin/services/${id}`, { method: 'DELETE' });
    load();
  }

  if (loading) return <p className="text-muted">Loading...</p>;

  return (
    <div className="space-y-6">
      <form onSubmit={handleSave} className="bg-surface rounded-lg p-6 border border-background max-w-xl space-y-4">
        <h2 className="text-lg font-semibold text-text-light">{editing ? 'Edit Service' : 'Add Service'}</h2>
        <div>
          <label className="block text-sm text-muted mb-1">Title</label>
          <input required value={form.title} onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))} className="w-full px-4 py-2 bg-background border border-surface rounded text-text-light" />
        </div>
        <div>
          <label className="block text-sm text-muted mb-1">Description</label>
          <textarea required value={form.description} onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))} rows={2} className="w-full px-4 py-2 bg-background border border-surface rounded text-text-light" />
        </div>
        <div>
          <label className="block text-sm text-muted mb-1">Icon name</label>
          <input value={form.icon} onChange={(e) => setForm((f) => ({ ...f, icon: e.target.value }))} className="w-full px-4 py-2 bg-background border border-surface rounded text-text-light" />
        </div>
        <div className="flex gap-2">
          <button type="submit" className="px-4 py-2 bg-accent text-white rounded hover:opacity-90">{editing ? 'Update' : 'Add'}</button>
          {editing && <button type="button" onClick={() => { setEditing(null); setForm({ title: '', description: '', icon: 'wrench' }); }} className="px-4 py-2 bg-surface text-muted rounded">Cancel</button>}
        </div>
      </form>
      <ul className="space-y-2">
        {list.map((s) => (
          <li key={s.id} className="flex items-center justify-between bg-surface rounded-lg p-4 border border-background">
            <div>
              <span className="font-medium text-text-light">{s.title}</span>
              <p className="text-muted text-sm">{s.description.slice(0, 80)}...</p>
            </div>
            <div className="flex gap-2">
              <button type="button" onClick={() => { setEditing(s); setForm({ title: s.title, description: s.description, icon: s.icon }); }} className="text-accent text-sm">Edit</button>
              <button type="button" onClick={() => handleDelete(s.id)} className="text-red-400 text-sm">Delete</button>
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
}
