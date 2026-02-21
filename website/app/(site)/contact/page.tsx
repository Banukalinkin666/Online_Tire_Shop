import { getContactContent } from '@/lib/data';
import ContactForm from '@/components/ContactForm';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Contact',
  description: 'Get in touch for tire and automotive services.',
};

export default async function ContactPage() {
  const contact = await getContactContent();
  const hours = contact?.workingHours?.split('\n') ?? [];

  return (
    <div className="container mx-auto px-4 py-12 max-w-5xl">
      <h1 className="text-4xl font-bold text-text-light mb-8">Contact Us</h1>
      <div className="grid md:grid-cols-2 gap-12">
        <div>
          <h2 className="text-xl font-bold text-accent mb-4">Get in Touch</h2>
          <address className="text-muted not-italic space-y-2">
            <p>{contact?.address ?? '123 Automotive Way'}</p>
            <p><a href={`tel:${contact?.phone ?? ''}`} className="hover:text-text-light">{contact?.phone ?? ''}</a></p>
            <p><a href={`mailto:${contact?.email ?? ''}`} className="hover:text-text-light">{contact?.email ?? ''}</a></p>
          </address>
          {contact?.whatsapp && (
            <a
              href={`https://wa.me/${contact.whatsapp.replace(/\D/g, '')}`}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            >
              WhatsApp
            </a>
          )}
          {hours.length > 0 && (
            <div className="mt-6">
              <h3 className="font-semibold text-text-light mb-2">Working Hours</h3>
              <ul className="text-muted space-y-1">
                {hours.map((line, i) => (
                  <li key={i}>{line}</li>
                ))}
              </ul>
            </div>
          )}
          <div className="mt-8">
            <ContactForm />
          </div>
        </div>
        <div>
          {contact?.mapEmbedUrl && (
            <div className="rounded-xl overflow-hidden border border-surface aspect-video">
              <iframe
                src={contact.mapEmbedUrl}
                width="100%"
                height="100%"
                style={{ border: 0, minHeight: 300 }}
                allowFullScreen
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
                title="Map"
              />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
