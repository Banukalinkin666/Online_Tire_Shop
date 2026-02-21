import './globals.css';

export const metadata = {
  title: { default: 'Online Tire Shop | Quality Tires & Expert Service', template: '%s | Online Tire Shop' },
  description: 'Find your perfect tire size and expert tire service.',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body className="min-h-screen flex flex-col antialiased bg-background text-text-light">{children}</body>
    </html>
  );
}
