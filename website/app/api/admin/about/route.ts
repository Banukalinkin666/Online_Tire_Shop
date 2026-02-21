import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const schema = z.object({
  intro: z.string().optional(),
  mission: z.string().optional(),
  vision: z.string().optional(),
  experience: z.string().optional(),
  certifications: z.string().optional(),
  images: z.string().optional(),
});

export async function GET() {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  const data = await prisma.aboutContent.findFirst();
  return NextResponse.json(data ?? {});
}

export async function PATCH(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  try {
    const body = await req.json();
    const data = schema.parse(body);
    let row = await prisma.aboutContent.findFirst();
    if (!row) {
      row = await prisma.aboutContent.create({
        data: {
          intro: data.intro ?? '',
          mission: data.mission ?? '',
          vision: data.vision ?? '',
          experience: data.experience ?? '',
          certifications: data.certifications ?? '[]',
          images: data.images ?? '[]',
        },
      });
    } else {
      row = await prisma.aboutContent.update({
        where: { id: row.id },
        data: {
          ...(data.intro !== undefined && { intro: data.intro }),
          ...(data.mission !== undefined && { mission: data.mission }),
          ...(data.vision !== undefined && { vision: data.vision }),
          ...(data.experience !== undefined && { experience: data.experience }),
          ...(data.certifications !== undefined && { certifications: data.certifications }),
          ...(data.images !== undefined && { images: data.images }),
        },
      });
    }
    return NextResponse.json(row);
  } catch (err) {
    if (err instanceof z.ZodError) return NextResponse.json({ error: err.flatten() }, { status: 400 });
    return NextResponse.json({ error: 'Update failed' }, { status: 500 });
  }
}
