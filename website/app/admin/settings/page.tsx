import AdminSettingsForm from '@/components/admin/AdminSettingsForm';

export default function AdminSettingsPage() {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-text-light mb-6">Site Settings</h1>
      <AdminSettingsForm />
    </div>
  );
}
