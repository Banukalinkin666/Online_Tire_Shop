import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const schema = z.object({
  heroTitle: z.string().optional(),
  heroSubtitle: z.string().optional(),
  ctaText: z.string().optional(),
  whyChooseUs: z.string().optional(), // JSON string
});

export async function GET() {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  const data = await prisma.homepageContent.findFirst();
  return NextResponse.json(data ?? {});
}

export async function PATCH(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  try {
    const body = await req.json();
    const data = schema.parse(body);
    let row = await prisma.homepageContent.findFirst();
    if (!row) {
      row = await prisma.homepageContent.create({
        data: {
          heroTitle: data.heroTitle ?? 'Find Your Perfect Tire Size',
          heroSubtitle: data.heroSubtitle ?? '',
          ctaText: data.ctaText ?? 'Get Started',
          whyChooseUs: data.whyChooseUs ?? '[]',
        },
      });
    } else {
      row = await prisma.homepageContent.update({
        where: { id: row.id },
        data: {
          ...(data.heroTitle !== undefined && { heroTitle: data.heroTitle }),
          ...(data.heroSubtitle !== undefined && { heroSubtitle: data.heroSubtitle }),
          ...(data.ctaText !== undefined && { ctaText: data.ctaText }),
          ...(data.whyChooseUs !== undefined && { whyChooseUs: data.whyChooseUs }),
        },
      });
    }
    return NextResponse.json(row);
  } catch (err) {
    if (err instanceof z.ZodError) return NextResponse.json({ error: err.flatten() }, { status: 400 });
    return NextResponse.json({ error: 'Update failed' }, { status: 500 });
  }
}
