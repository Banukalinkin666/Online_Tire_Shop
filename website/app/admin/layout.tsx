import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import AdminNav from '@/components/AdminNav';

export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  const session = await getSession();
  return (
    <div className="min-h-screen bg-background">
      {session ? (
        <>
          <AdminNav />
          <div className="pt-16">{children}</div>
        </>
      ) : (
        <>{children}</>
      )}
    </div>
  );
}
