import AdminServicesPage from '@/components/admin/AdminServicesPage';

export default function AdminServicesRoute() {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-text-light mb-6">Manage Services</h1>
      <AdminServicesPage />
    </div>
  );
}
