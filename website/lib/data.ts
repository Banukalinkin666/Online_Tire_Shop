import { prisma } from './prisma';

export async function getHomepageContent() {
  return prisma.homepageContent.findFirst();
}

export async function getAboutContent() {
  return prisma.aboutContent.findFirst();
}

export async function getServices() {
  return prisma.service.findMany({ orderBy: { order: 'asc' } });
}

export async function getContactContent() {
  return prisma.contactContent.findFirst();
}

export async function getSiteSettings() {
  return prisma.siteSettings.findFirst();
}

export async function getBrands() {
  return prisma.brand.findMany({ orderBy: { order: 'asc' } });
}

export async function getTestimonials() {
  return prisma.testimonial.findMany({ orderBy: { order: 'asc' } });
}
