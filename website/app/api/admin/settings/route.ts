import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const schema = z.object({
  logo: z.string().optional(),
  footerText: z.string().optional(),
  facebook: z.string().optional(),
  twitter: z.string().optional(),
  instagram: z.string().optional(),
  linkedin: z.string().optional(),
  tireFinderUrl: z.string().optional(),
});

export async function GET() {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  const data = await prisma.siteSettings.findFirst();
  return NextResponse.json(data ?? {});
}

export async function PATCH(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  try {
    const body = await req.json();
    const data = schema.parse(body);
    let row = await prisma.siteSettings.findFirst();
    if (!row) {
      row = await prisma.siteSettings.create({
        data: {
          logo: data.logo ?? null,
          footerText: data.footerText ?? null,
          facebook: data.facebook ?? null,
          twitter: data.twitter ?? null,
          instagram: data.instagram ?? null,
          linkedin: data.linkedin ?? null,
          tireFinderUrl: data.tireFinderUrl ?? null,
        },
      });
    } else {
      row = await prisma.siteSettings.update({
        where: { id: row.id },
        data: {
          ...(data.logo !== undefined && { logo: data.logo }),
          ...(data.footerText !== undefined && { footerText: data.footerText }),
          ...(data.facebook !== undefined && { facebook: data.facebook }),
          ...(data.twitter !== undefined && { twitter: data.twitter }),
          ...(data.instagram !== undefined && { instagram: data.instagram }),
          ...(data.linkedin !== undefined && { linkedin: data.linkedin }),
          ...(data.tireFinderUrl !== undefined && { tireFinderUrl: data.tireFinderUrl }),
        },
      });
    }
    return NextResponse.json(row);
  } catch (err) {
    if (err instanceof z.ZodError) return NextResponse.json({ error: err.flatten() }, { status: 400 });
    return NextResponse.json({ error: 'Update failed' }, { status: 500 });
  }
}
