import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Trash2 } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';
import Header from '@/components/forum/Header';
import VoteButton from '@/components/forum/VoteButton';
import CommentCard from '@/components/forum/CommentCard';
import { router } from '@inertiajs/react';

/**
 * Post/Show - Vista detallada de un post
 * 
 * EXPLICACIÓN:
 * - Muestra el post completo con sus comentarios
 * - Permite agregar nuevos comentarios
 * - El usuario puede votar y eliminar su propio post
 */

interface Comment {
  _id: string;
  content: string;
  votes_count: number;
  created_at: string;
  user: {
    _id: string;
    name: string;
    email: string;
  };
  replies?: Comment[];
}

interface Post {
  _id: string;
  title: string;
  content: string;
  votes_count: number;
  comments_count: number;
  created_at: string;
  user_id: string;
  tags?: string[];
  user: {
    _id: string;
    name: string;
    email: string;
  };
  comments: Comment[];
}

interface PageProps {
  post: Post;
  userVote: 'up' | 'down' | null;
  userCommentVotes?: Record<string, 'up' | 'down'>;
  auth: {
    user: {
      _id: string;
      name: string;
      email: string;
    } | null;
  };
}

export default function Show({ post, userVote, userCommentVotes = {}, auth }: PageProps) {
  const timeAgo = formatDistanceToNow(new Date(post.created_at), {
    addSuffix: true,
    locale: es,
  });

  // Form para nuevo comentario
  const { data, setData, post: submitComment, processing, reset, errors } = useForm({
    content: '',
    post_id: post._id,
  });

  const handleSubmitComment = (e: React.FormEvent) => {
    e.preventDefault();
    submitComment('/comments', {
      preserveScroll: true,
      onSuccess: () => reset(),
    });
  };

  const handleDeletePost = () => {
    if (!confirm('¿Estás seguro de eliminar este post?')) return;

    router.delete(`/posts/${post._id}`, {
      onSuccess: () => {
        router.visit('/');
      },
    });
  };

  const isOwner = auth.user?._id === post.user_id;

  return (
    <>
      <Head title={post.title} />

      <div className="min-h-screen bg-background">
        <Header user={auth.user} />

        <div className="container mx-auto px-4 py-6 max-w-4xl">
          {/* Botón volver */}
          <Button variant="ghost" asChild className="mb-4">
            <Link href="/">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Volver al feed
            </Link>
          </Button>

          {/* Post principal */}
          <Card className="mb-6">
            <CardHeader>
              <div className="flex items-start justify-between">
                <div className="flex items-center space-x-3">
                  <Avatar className="h-12 w-12">
                    <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${post.user.name}`} />
                    <AvatarFallback>{post.user.name.charAt(0).toUpperCase()}</AvatarFallback>
                  </Avatar>
                  <div>
                    <p className="font-medium">{post.user.name}</p>
                    <p className="text-sm text-muted-foreground">
                      @{post.user.name.toLowerCase().replace(/\s+/g, '_')} · {timeAgo}
                    </p>
                  </div>
                </div>
                {isOwner && (
                  <Button
                    variant="ghost"
                    size="icon"
                    onClick={handleDeletePost}
                  >
                    <Trash2 className="h-4 w-4 text-red-500" />
                  </Button>
                )}
              </div>
            </CardHeader>

            <CardContent className="space-y-4">
              <h1 className="text-2xl font-bold">{post.title}</h1>
              <p className="text-base whitespace-pre-wrap">{post.content}</p>

              {post.tags && post.tags.length > 0 && (
                <div className="flex flex-wrap gap-2">
                  {post.tags.map((tag) => (
                    <span
                      key={tag}
                      className="inline-flex items-center px-2.5 py-1 text-sm font-medium rounded-md bg-primary/10 text-primary"
                    >
                      {tag}
                    </span>
                  ))}
                </div>
              )}

              <div className="flex items-center justify-between pt-4 border-t">
                <VoteButton
                  votableType="post"
                  votableId={post._id}
                  votesCount={post.votes_count}
                  userVote={userVote}
                />
                <span className="text-sm text-muted-foreground">
                  {post.comments_count} {post.comments_count === 1 ? 'comentario' : 'comentarios'}
                </span>
              </div>
            </CardContent>
          </Card>

          {/* Formulario de comentarios */}
          {auth.user ? (
            <Card className="mb-6">
              <CardContent className="pt-6">
                <form onSubmit={handleSubmitComment} className="space-y-4">
                  <Textarea
                    placeholder="Escribe un comentario..."
                    value={data.content}
                    onChange={(e) => setData('content', e.target.value)}
                    rows={3}
                    disabled={processing}
                  />
                  {errors.content && (
                    <p className="text-sm text-red-500">{errors.content}</p>
                  )}
                  <div className="flex justify-end">
                    <Button type="submit" disabled={processing || !data.content.trim()}>
                      {processing ? 'Publicando...' : 'Comentar'}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          ) : (
            <Card className="mb-6">
              <CardContent className="pt-6">
                <p className="text-center text-muted-foreground">
                  <Link href="/login" className="text-primary hover:underline">
                    Inicia sesión
                  </Link>
                  {' '}para comentar
                </p>
              </CardContent>
            </Card>
          )}

          {/* Lista de comentarios */}
          <div className="space-y-4">
            <h2 className="text-xl font-bold">
              Comentarios ({post.comments_count})
            </h2>

            {post.comments.length === 0 ? (
              <Card>
                <CardContent className="py-8">
                  <p className="text-center text-muted-foreground">
                    No hay comentarios aún. ¡Sé el primero en comentar!
                  </p>
                </CardContent>
              </Card>
            ) : (
              post.comments.map((comment) => (
                <CommentCard
                  key={comment._id}
                  comment={comment}
                  currentUserId={auth.user?._id}
                  userVote={userCommentVotes[comment._id] || null}
                  userCommentVotes={userCommentVotes}
                />
              ))
            )}
          </div>
        </div>
      </div>
    </>
  );
}
