'use client';

import { useState, useEffect } from 'react';

type ContactData = {
  address?: string;
  phone?: string;
  email?: string;
  workingHours?: string;
  whatsapp?: string;
  mapEmbedUrl?: string;
};

type Message = { id: string; name: string; email: string; phone: string | null; subject: string | null; message: string; read: boolean; createdAt: string };

export default function AdminContactPage() {
  const [data, setData] = useState<ContactData>({});
  const [messages, setMessages] = useState<Message[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  function loadContact() {
    return fetch('/api/admin/contact').then((r) => r.json()).then(setData);
  }
  function loadMessages() {
    return fetch('/api/admin/contact/messages').then((r) => r.json()).then(setMessages);
  }

  useEffect(() => {
    Promise.all([loadContact(), loadMessages()]).finally(() => setLoading(false));
  }, []);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      await fetch('/api/admin/contact', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    } finally {
      setSaving(false);
    }
  }

  async function markRead(id: string, read: boolean) {
    await fetch('/api/admin/contact/messages?id=' + id, { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ read }) });
    loadMessages();
  }

  if (loading) return <p className="text-muted">Loading...</p>;

  return (
    <div className="grid md:grid-cols-2 gap-8">
      <form onSubmit={handleSubmit} className="space-y-4">
        <h2 className="text-lg font-semibold text-text-light">Business Info</h2>
        {(['address', 'phone', 'email', 'workingHours', 'whatsapp', 'mapEmbedUrl'] as const).map((key) => (
          <div key={key}>
            <label className="block text-sm text-muted mb-1">{key}</label>
            {key === 'address' || key === 'workingHours' ? (
              <textarea value={data[key] ?? ''} onChange={(e) => setData((d) => ({ ...d, [key]: e.target.value }))} rows={2} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
            ) : (
              <input value={data[key] ?? ''} onChange={(e) => setData((d) => ({ ...d, [key]: e.target.value }))} className="w-full px-4 py-2 bg-surface border border-background rounded text-text-light" />
            )}
          </div>
        ))}
        <button type="submit" disabled={saving} className="px-4 py-2 bg-accent text-white rounded hover:opacity-90 disabled:opacity-50">{saving ? 'Saving...' : 'Save'}</button>
      </form>
      <div>
        <h2 className="text-lg font-semibold text-text-light mb-4">Contact Form Submissions</h2>
        <ul className="space-y-3 max-h-[500px] overflow-y-auto">
          {messages.map((m) => (
            <li key={m.id} className={`bg-surface rounded p-4 border ${m.read ? 'border-background opacity-75' : 'border-accent/50'}`}>
              <div className="flex justify-between items-start">
                <div>
                  <p className="font-medium text-text-light">{m.name} &lt;{m.email}&gt;</p>
                  {m.phone && <p className="text-muted text-sm">{m.phone}</p>}
                  {m.subject && <p className="text-muted text-sm">{m.subject}</p>}
                  <p className="text-muted text-sm mt-1">{m.message}</p>
                  <p className="text-muted text-xs mt-1">{new Date(m.createdAt).toLocaleString()}</p>
                </div>
                <button type="button" onClick={() => markRead(m.id, !m.read)} className="text-accent text-xs">{m.read ? 'Mark unread' : 'Mark read'}</button>
              </div>
            </li>
          ))}
        </ul>
        {messages.length === 0 && <p className="text-muted">No messages yet.</p>}
      </div>
    </div>
  );
}
