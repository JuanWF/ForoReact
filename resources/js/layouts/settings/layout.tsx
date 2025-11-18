import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Card, CardContent } from '@/components/ui/card';
import { cn, isSameUrl, resolveUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';
import Header from '@/components/forum/Header';

const settingsNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: edit(),
        icon: null,
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: null,
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: null,
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: null,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<any>().props;
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    return (
        <div className="min-h-screen bg-background">
            <Header user={auth?.user || null} />

            <div className="container mx-auto px-4 py-6 max-w-4xl">
                <div className="mb-6">
                    <h1 className="text-3xl font-bold">Settings</h1>
                    <p className="text-muted-foreground mt-1">
                        Manage your profile and account settings
                    </p>
                </div>

                <div className="flex flex-col md:flex-row gap-6">
                    {/* Navigation Tabs */}
                    <nav className="flex md:flex-col gap-1 overflow-x-auto md:min-w-[200px]">
                        {settingsNavItems.map((item, index) => (
                            <Button
                                key={`${resolveUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('justify-start whitespace-nowrap', {
                                    'bg-muted': isSameUrl(
                                        currentPath,
                                        item.href,
                                    ),
                                })}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4 mr-2" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>

                    <Separator className="md:hidden" />

                    {/* Content */}
                    <div className="flex-1">
                        <Card>
                            <CardContent className="pt-6">
                                {children}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    );
}
