'use client';

import { useState, useEffect } from 'react';

type Brand = { id: string; name: string; logo: string | null; order: number };

export default function AdminBrands() {
  const [list, setList] = useState<Brand[]>([]);
  const [name, setName] = useState('');
  const [loading, setLoading] = useState(true);

  function load() {
    fetch('/api/admin/brands')
      .then((r) => r.json())
      .then(setList)
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    load();
  }, []);

  async function handleAdd(e: React.FormEvent) {
    e.preventDefault();
    if (!name.trim()) return;
    await fetch('/api/admin/brands', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: name.trim() }) });
    setName('');
    load();
  }

  async function handleDelete(id: string) {
    await fetch('/api/admin/brands?id=' + id, { method: 'DELETE' });
    load();
  }

  if (loading) return null;
  return (
    <div className="mt-8 pt-8 border-t border-surface">
      <h2 className="text-lg font-semibold text-text-light mb-4">Brands</h2>
      <form onSubmit={handleAdd} className="flex gap-2 mb-4">
        <input value={name} onChange={(e) => setName(e.target.value)} placeholder="Brand name" className="flex-1 px-4 py-2 bg-surface border border-background rounded text-text-light" />
        <button type="submit" className="px-4 py-2 bg-accent text-white rounded">Add</button>
      </form>
      <ul className="flex flex-wrap gap-2">
        {list.map((b) => (
          <li key={b.id} className="flex items-center gap-2 bg-surface rounded px-3 py-1">
            <span className="text-text-light">{b.name}</span>
            <button type="button" onClick={() => handleDelete(b.id)} className="text-accent text-sm">×</button>
          </li>
        ))}
      </ul>
    </div>
  );
}
