import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const SESSION_COOKIE = 'ot_admin_session';

export function middleware(req: NextRequest) {
  const path = req.nextUrl.pathname;
  if (path.startsWith('/admin') && !path.startsWith('/admin/login')) {
    const session = req.cookies.get(SESSION_COOKIE)?.value;
    if (!session) {
      return NextResponse.redirect(new URL('/admin/login', req.url));
    }
  }
  return NextResponse.next();
}

export const config = { matcher: ['/admin/:path*'] };
