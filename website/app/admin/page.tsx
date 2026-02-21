import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';

export default async function AdminRootPage() {
  const session = await getSession();
  if (session) redirect('/admin/dashboard');
  redirect('/admin/login');
}
