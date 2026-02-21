import { NextRequest, NextResponse } from 'next/server';
import { getSession } from '@/lib/auth';
import { prisma } from '@/lib/prisma';
import { z } from 'zod';

const schema = z.object({ name: z.string().min(1), role: z.string().optional(), content: z.string().min(1), rating: z.number().min(1).max(5).optional(), order: z.number().optional() });

export async function GET() {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  const list = await prisma.testimonial.findMany({ orderBy: { order: 'asc' } });
  return NextResponse.json(list);
}

export async function POST(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  try {
    const body = await req.json();
    const data = schema.parse(body);
    const t = await prisma.testimonial.create({ data: { name: data.name, role: data.role ?? null, content: data.content, rating: data.rating ?? 5, order: data.order ?? 0 } });
    return NextResponse.json(t);
  } catch (err) {
    if (err instanceof z.ZodError) return NextResponse.json({ error: err.flatten() }, { status: 400 });
    return NextResponse.json({ error: 'Create failed' }, { status: 500 });
  }
}

export async function DELETE(req: NextRequest) {
  const session = await getSession();
  if (!session) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  const { searchParams } = new URL(req.url);
  const id = searchParams.get('id');
  if (!id) return NextResponse.json({ error: 'id required' }, { status: 400 });
  await prisma.testimonial.delete({ where: { id } });
  return NextResponse.json({ success: true });
}
