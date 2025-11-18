import { router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';
import { Trash2 } from 'lucide-react';
import VoteButton from './VoteButton';
import { useState } from 'react';

/**
 * CommentCard - Tarjeta de comentario
 * 
 * EXPLICACIÓN:
 * - Muestra un comentario individual
 * - Permite votar y eliminar (si es el dueño)
 * - Soporta respuestas anidadas (replies)
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

interface CommentCardProps {
  comment: Comment;
  currentUserId?: string;
  userVote?: 'up' | 'down' | null;
  userCommentVotes?: Record<string, 'up' | 'down'>;
  isReply?: boolean;
}

export default function CommentCard({
  comment,
  currentUserId,
  userVote,
  userCommentVotes = {},
  isReply = false,
}: CommentCardProps) {
  const [isDeleting, setIsDeleting] = useState(false);

  const timeAgo = formatDistanceToNow(new Date(comment.created_at), {
    addSuffix: true,
    locale: es,
  });

  const handleDelete = () => {
    if (!confirm('¿Estás seguro de eliminar este comentario?')) return;

    setIsDeleting(true);
    router.delete(`/comments/${comment._id}`, {
      preserveScroll: true,
      onFinish: () => setIsDeleting(false),
    });
  };

  const isOwner = currentUserId === comment.user._id;

  return (
    <div className={isReply ? 'ml-8 mt-4' : ''}>
      <Card className={isReply ? 'bg-muted/50' : ''}>
        <CardContent className="pt-4">
          <div className="flex items-start space-x-3">
            {/* Avatar */}
            <Avatar className="h-8 w-8">
              <AvatarImage src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${comment.user.name}`} />
              <AvatarFallback>{comment.user.name.charAt(0).toUpperCase()}</AvatarFallback>
            </Avatar>

            <div className="flex-1 space-y-2">
              {/* Header */}
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <span className="text-sm font-medium">{comment.user.name}</span>
                  <span className="text-xs text-muted-foreground">{timeAgo}</span>
                </div>
                {isOwner && (
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    onClick={handleDelete}
                    disabled={isDeleting}
                  >
                    <Trash2 className="h-4 w-4 text-red-500" />
                  </Button>
                )}
              </div>

              {/* Contenido */}
              <p className="text-sm">{comment.content}</p>

              {/* Acciones */}
              <div className="flex items-center space-x-2">
                <VoteButton
                  votableType="comment"
                  votableId={comment._id}
                  votesCount={comment.votes_count}
                  userVote={userCommentVotes?.[comment._id] || userVote || null}
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Respuestas anidadas */}
      {comment.replies && comment.replies.length > 0 && (
        <div className="space-y-2 mt-2">
          {comment.replies.map((reply) => (
            <CommentCard
              key={reply._id}
              comment={reply}
              currentUserId={currentUserId}
              userVote={userCommentVotes[reply._id] || null}
              userCommentVotes={userCommentVotes}
              isReply={true}
            />
          ))}
        </div>
      )}
    </div>
  );
}
