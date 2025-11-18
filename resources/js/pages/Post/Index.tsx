import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import Header from '@/components/forum/Header';
import PostCard from '@/components/forum/PostCard';
import SidebarTrends from '@/components/forum/SidebarTrends';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { router } from '@inertiajs/react';

/**
 * Post/Index - Feed principal del foro
 * 
 * EXPLICACIÓN INERTIA:
 * - Este componente recibe props directamente desde PostController@index
 * - Los props son: { posts, trends, userVotes, sort }
 * - Laravel envía los datos como JSON automáticamente
 * - Cuando cambiamos el sort, Inertia hace una petición AJAX y actualiza solo este componente
 * - NO recargamos toda la página, solo actualizamos el contenido
 */

interface Post {
  _id: string;
  title: string;
  content: string;
  votes_count: number;
  comments_count: number;
  created_at: string;
  user: {
    _id: string;
    name: string;
    email: string;
  };
}

interface Trend {
  _id: string;
  name: string;
  slug: string;
  posts_count: number;
  category: string;
}

interface PageProps {
  posts: {
    data: Post[];
    links: any[];
    current_page: number;
    last_page: number;
  };
  trends: Trend[];
  userVotes?: Record<string, 'up' | 'down'>;
  sort: 'recent' | 'popular';
  auth: {
    user: {
      _id: string;
      name: string;
      email: string;
    } | null;
  };
}

export default function Index({ posts, trends, userVotes = {}, sort, auth }: PageProps) {
  const handleSortChange = (value: string) => {
    router.get('/', { sort: value }, { preserveState: true, preserveScroll: true });
  };

  return (
    <>
      <Head title="ForoDB - Foro de Base de Datos" />
      
      <div className="min-h-screen bg-background">
        <Header user={auth.user} />

        <div className="container mx-auto px-4 py-6">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Feed principal */}
            <div className="lg:col-span-2 space-y-4">
              {/* Header del feed */}
              <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold">Inicio</h1>
                <div className="flex items-center space-x-2">
                  {/* Filtro de ordenamiento */}
                  <Select value={sort} onValueChange={handleSortChange}>
                    <SelectTrigger className="w-[140px]">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="recent">Recientes</SelectItem>
                      <SelectItem value="popular">Populares</SelectItem>
                    </SelectContent>
                  </Select>

                  {/* Botón nuevo post */}
                  {auth.user && (
                    <Button asChild>
                      <Link href="/posts/create/new">
                        <Plus className="h-4 w-4 mr-2" />
                        Nuevo Post
                      </Link>
                    </Button>
                  )}
                </div>
              </div>

              {/* Lista de posts */}
              {posts.data.length === 0 ? (
                <div className="text-center py-12">
                  <p className="text-muted-foreground mb-4">
                    No hay posts disponibles
                  </p>
                  {auth.user && (
                    <Button asChild>
                      <Link href="/posts/create/new">Crear el primer post</Link>
                    </Button>
                  )}
                </div>
              ) : (
                <div className="space-y-4">
                  {posts.data.map((post) => (
                    <PostCard
                      key={post._id}
                      post={post}
                      userVote={userVotes[post._id] || null}
                    />
                  ))}
                </div>
              )}

              {/* Paginación */}
              {posts.last_page > 1 && (
                <div className="flex items-center justify-center space-x-2 mt-6">
                  {posts.links.map((link, index) => (
                    <Button
                      key={index}
                      variant={link.active ? 'default' : 'outline'}
                      size="sm"
                      disabled={!link.url}
                      onClick={() => {
                        if (link.url) {
                          router.get(link.url);
                        }
                      }}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ))}
                </div>
              )}
            </div>

            {/* Sidebar */}
            <div className="space-y-4">
              {/* Botón nuevo post móvil */}
              {auth.user && (
                <Button className="w-full lg:hidden" asChild>
                  <Link href="/posts/create/new">
                    <Plus className="h-4 w-4 mr-2" />
                    Nuevo Post
                  </Link>
                </Button>
              )}

              {/* Tendencias */}
              <SidebarTrends trends={trends} />
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
