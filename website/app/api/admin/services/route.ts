import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const schema = z.object({ title: z.string().min(1), description: z.string().min(1), icon: z.string().optional(), order: z.number().optional() });

export async function GET() {
  const list = await prisma.service.findMany({ orderBy: { order: 'asc' } });
  return NextResponse.json(list);
}

export async function POST(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  try {
    const body = await req.json();
    const data = schema.parse(body);
    const s = await prisma.service.create({ data: { title: data.title, description: data.description, icon: data.icon ?? 'wrench', order: data.order ?? 0 } });
    return NextResponse.json(s);
  } catch (err) {
    if (err instanceof z.ZodError) return NextResponse.json({ error: err.flatten() }, { status: 400 });
    return NextResponse.json({ error: 'Create failed' }, { status: 500 });
  }
}
