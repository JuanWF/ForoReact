import { Link } from '@inertiajs/react';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { MessageSquare } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';
import VoteButton from './VoteButton';

/**
 * PostCard - Tarjeta de post para el feed
 * 
 * EXPLICACIÓN:
 * - Este componente muestra un resumen del post en el feed
 * - Recibe los datos del post desde Laravel vía Inertia
 * - Usa componentes de shadcn/ui para el diseño
 */

interface PostCardProps {
  post: {
    _id: string;
    title: string;
    content: string;
    votes_count: number;
    comments_count: number;
    created_at: string;
    tags?: string[];
    user: {
      _id: string;
      name: string;
      email: string;
    };
  };
  userVote?: 'up' | 'down' | null;
}

export default function PostCard({ post, userVote }: PostCardProps) {
  // Formatear fecha relativa (hace 5h, hace 2d, etc.)
  const timeAgo = formatDistanceToNow(new Date(post.created_at), {
    addSuffix: true,
    locale: es,
  });

  // Truncar contenido para preview
  const contentPreview = post.content.length > 150
    ? post.content.substring(0, 150) + '...'
    : post.content;

  return (
    <Card className="overflow-hidden hover:shadow-md transition-shadow">
      <CardHeader className="pb-3">
        <div className="flex items-start justify-between">
          <div className="flex items-center space-x-3">
            <Avatar className="h-10 w-10">
              <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${post.user.name}`} />
              <AvatarFallback>{post.user.name.charAt(0).toUpperCase()}</AvatarFallback>
            </Avatar>
            <div>
              <p className="text-sm font-medium">{post.user.name}</p>
              <p className="text-xs text-muted-foreground">@{post.user.name.toLowerCase().replace(/\s+/g, '_')}</p>
            </div>
          </div>
          <span className="text-xs text-muted-foreground">{timeAgo}</span>
        </div>
      </CardHeader>

      <CardContent className="pb-3">
        <Link
          href={`/posts/${post._id}`}
          className="block space-y-2 hover:opacity-80 transition-opacity"
        >
          <h3 className="text-lg font-semibold line-clamp-2">{post.title}</h3>
          <p className="text-sm text-muted-foreground line-clamp-3">
            {contentPreview}
          </p>
          {post.tags && post.tags.length > 0 && (
            <div className="flex flex-wrap gap-2 mt-2">
              {post.tags.map((tag) => (
                <span
                  key={tag}
                  className="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-primary/10 text-primary"
                >
                  {tag}
                </span>
              ))}
            </div>
          )}
        </Link>
      </CardContent>

      <CardFooter className="border-t pt-3">
        <div className="flex items-center justify-between w-full">
          {/* Votos */}
          <VoteButton
            votableType="post"
            votableId={post._id}
            votesCount={post.votes_count}
            userVote={userVote}
          />

          {/* Comentarios */}
          <Link
            href={`/posts/${post._id}`}
            className="flex items-center space-x-2 text-sm text-muted-foreground hover:text-foreground transition-colors"
          >
            <MessageSquare className="h-4 w-4" />
            <span>{post.comments_count}</span>
          </Link>
        </div>
      </CardFooter>
    </Card>
  );
}
