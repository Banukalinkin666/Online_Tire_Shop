import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const schema = z.object({
  address: z.string().optional(),
  phone: z.string().optional(),
  email: z.string().optional(),
  workingHours: z.string().optional(),
  whatsapp: z.string().optional(),
  mapEmbedUrl: z.string().optional(),
});

export async function GET() {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  const data = await prisma.contactContent.findFirst();
  return NextResponse.json(data ?? {});
}

export async function PATCH(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  try {
    const body = await req.json();
    const data = schema.parse(body);
    let row = await prisma.contactContent.findFirst();
    if (!row) {
      row = await prisma.contactContent.create({
        data: {
          address: data.address ?? '',
          phone: data.phone ?? '',
          email: data.email ?? '',
          workingHours: data.workingHours ?? '',
          whatsapp: data.whatsapp ?? null,
          mapEmbedUrl: data.mapEmbedUrl ?? null,
        },
      });
    } else {
      row = await prisma.contactContent.update({
        where: { id: row.id },
        data: {
          ...(data.address !== undefined && { address: data.address }),
          ...(data.phone !== undefined && { phone: data.phone }),
          ...(data.email !== undefined && { email: data.email }),
          ...(data.workingHours !== undefined && { workingHours: data.workingHours }),
          ...(data.whatsapp !== undefined && { whatsapp: data.whatsapp }),
          ...(data.mapEmbedUrl !== undefined && { mapEmbedUrl: data.mapEmbedUrl }),
        },
      });
    }
    return NextResponse.json(row);
  } catch (err) {
    if (err instanceof z.ZodError) return NextResponse.json({ error: err.flatten() }, { status: 400 });
    return NextResponse.json({ error: 'Update failed' }, { status: 500 });
  }
}
