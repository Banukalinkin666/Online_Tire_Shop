import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
  const hashed = await bcrypt.hash('admin123', 12);
  await prisma.user.upsert({
    where: { email: 'admin@example.com' },
    create: { email: 'admin@example.com', password: hashed, name: 'Admin' },
    update: {},
  });

  const homeCount = await prisma.homepageContent.count();
  if (homeCount === 0) {
    await prisma.homepageContent.create({
      data: {
        heroTitle: 'Find Your Perfect Tire Size',
        heroSubtitle: 'Enter your vehicle details and get instant tire recommendations. Fast, accurate, and free.',
        ctaText: 'Find Your Tires',
        whyChooseUs: JSON.stringify([
          { title: 'Accurate Fitment', description: 'Our database is updated with the latest vehicle and tire specifications.' },
          { title: 'Expert Support', description: 'Our team is here to help you choose the right tires.' },
          { title: 'Quality Brands', description: 'We carry top tire brands you can trust.' },
        ]),
      },
    });
  }

  await prisma.brand.createMany({
    data: [
      { name: 'Michelin', order: 1 },
      { name: 'Bridgestone', order: 2 },
      { name: 'Goodyear', order: 3 },
      { name: 'Continental', order: 4 },
      { name: 'Pirelli', order: 5 },
      { name: 'Dunlop', order: 6 },
    ],
    skipDuplicates: true,
  });

  await prisma.testimonial.createMany({
    data: [
      { name: 'John D.', role: 'Customer', content: 'Found the exact tire size in seconds. Great service!', rating: 5, order: 1 },
      { name: 'Sarah M.', role: 'Fleet Manager', content: 'We use this for all our vehicles. Saves us a lot of time.', rating: 5, order: 2 },
      { name: 'Mike T.', role: 'Customer', content: 'Clear results and fair prices. Highly recommend.', rating: 5, order: 3 },
    ],
    skipDuplicates: true,
  });

  const aboutCount = await prisma.aboutContent.count();
  if (aboutCount === 0) {
    await prisma.aboutContent.create({
      data: {
        intro: 'We have been serving drivers with quality tires and expert advice for over 15 years. Our mission is to make tire buying simple and stress-free.',
        mission: 'To provide every driver with the right tires for their vehicle and driving needs, backed by honest advice and quality service.',
        vision: 'To be the most trusted tire and automotive service provider in our community.',
        experience: '15+ years in the tire industry. Thousands of satisfied customers.',
        certifications: JSON.stringify(['ASE Certified', 'Manufacturer Authorized Dealer']),
        images: JSON.stringify([]),
      },
    });
  }

  await prisma.service.createMany({
    data: [
      { title: 'Tire Sales', description: 'Full range of passenger, SUV, and truck tires from top brands.', icon: 'tire', order: 1 },
      { title: 'Tire Installation', description: 'Professional mounting, balancing, and installation by certified technicians.', icon: 'wrench', order: 2 },
      { title: 'Wheel Alignment', description: 'Precise alignment services to extend tire life and improve handling.', icon: 'alignment', order: 3 },
      { title: 'Tire Repair', description: 'Puncture repair and tire maintenance to keep you safe on the road.', icon: 'repair', order: 4 },
    ],
    skipDuplicates: true,
  });

  const contactCount = await prisma.contactContent.count();
  if (contactCount === 0) {
    await prisma.contactContent.create({
      data: {
        address: '123 Automotive Way, Suite 100, Your City, ST 12345',
        phone: '+1 (555) 123-4567',
        email: 'info@onlinetireshop.com',
        workingHours: 'Mon–Fri: 8:00 AM – 6:00 PM\nSat: 9:00 AM – 4:00 PM\nSun: Closed',
        whatsapp: '+15551234567',
        mapEmbedUrl: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.9663094500003!2d-74.00425878428698!3d40.74076684379132!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQ0JzI2LjgiTiA3NMKwMDAnMTUuMyJX!5e0!3m2!1sen!2sus!4v1234567890',
      },
    });
  }

  const settingsCount = await prisma.siteSettings.count();
  if (settingsCount === 0) {
    await prisma.siteSettings.create({
      data: {
        footerText: '© 2024 Online Tire Shop. All rights reserved. Quality tires, expert service.',
        tireFinderUrl: process.env.TIRE_FINDER_URL || 'https://online-tire-shop-pro.onrender.com',
      },
    });
  }
}

main()
  .then(() => {
    console.log('Seed completed.');
    process.exit(0);
  })
  .catch((e) => {
    console.error(e);
    process.exit(1);
  });
