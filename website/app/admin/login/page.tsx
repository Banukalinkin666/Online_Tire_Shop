'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

export default function AdminLoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const res = await fetch('/api/admin/login', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, password }) });
      const data = await res.json();
      if (!res.ok) {
        setError(data.error ?? 'Login failed');
        return;
      }
      router.push('/admin/dashboard');
      router.refresh();
    } catch {
      setError('Something went wrong');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center px-4">
      <form onSubmit={handleSubmit} className="w-full max-w-sm bg-surface rounded-xl p-8 border border-background">
        <h1 className="text-2xl font-bold text-text-light mb-6">Admin Login</h1>
        <div className="space-y-4">
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-muted mb-1">Email</label>
            <input id="email" type="email" required value={email} onChange={(e) => setEmail(e.target.value)} className="w-full px-4 py-2 bg-background border border-surface rounded-lg text-text-light" />
          </div>
          <div>
            <label htmlFor="password" className="block text-sm font-medium text-muted mb-1">Password</label>
            <input id="password" type="password" required value={password} onChange={(e) => setPassword(e.target.value)} className="w-full px-4 py-2 bg-background border border-surface rounded-lg text-text-light" />
          </div>
          {error && <p className="text-accent text-sm">{error}</p>}
          <button type="submit" disabled={loading} className="w-full py-3 bg-accent text-white font-medium rounded-lg hover:opacity-90 disabled:opacity-50">
            {loading ? 'Signing in...' : 'Sign in'}
          </button>
        </div>
      </form>
    </div>
  );
}
