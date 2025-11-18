import { router } from '@inertiajs/react';
import { ArrowUp, ArrowDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useState } from 'react';

/**
 * VoteButton - Botones de upvote/downvote
 * 
 * EXPLICACIÓN FLUJO INERTIA:
 * 1. Usuario hace clic en upvote/downvote
 * 2. Hacemos router.post() de Inertia
 * 3. Laravel procesa el voto en VoteController
 * 4. Laravel retorna back() o redirect()
 * 5. Inertia actualiza automáticamente el componente con los nuevos datos
 * 6. No necesitamos manejar estados complejos, Inertia lo hace por nosotros
 */

interface VoteButtonProps {
  votableType: 'post' | 'comment';
  votableId: string;
  votesCount: number;
  userVote?: 'up' | 'down' | null;
}

export default function VoteButton({
  votableType,
  votableId,
  votesCount,
  userVote,
}: VoteButtonProps) {
  const [isVoting, setIsVoting] = useState(false);

  const handleVote = (type: 'up' | 'down') => {
    if (isVoting) return;

    setIsVoting(true);

    console.log('Enviando voto:', { votableType, votableId, type });

    // Enviar voto a Laravel usando Inertia
    router.post(
      '/votes',
      {
        votable_type: votableType,
        votable_id: votableId,
        type: type,
      },
      {
        preserveScroll: true,
        preserveState: false, // Importante: recargar datos frescos
        onSuccess: () => console.log('Voto exitoso!'),
        onError: (errors) => console.error('Error al votar:', errors),
        onFinish: () => {
          console.log('Voto finalizado');
          setIsVoting(false);
        },
      }
    );
  };

  return (
    <div className="flex items-center space-x-1">
      {/* Upvote */}
      <Button
        variant={userVote === 'up' ? 'default' : 'ghost'}
        size="icon"
        className="h-8 w-8"
        onClick={() => handleVote('up')}
        disabled={isVoting}
      >
        <ArrowUp className="h-4 w-4" />
      </Button>

      {/* Contador */}
      <span className={`text-sm font-medium min-w-[2rem] text-center ${
        votesCount > 0 ? 'text-green-600' : votesCount < 0 ? 'text-red-600' : 'text-muted-foreground'
      }`}>
        {votesCount}
      </span>

      {/* Downvote */}
      <Button
        variant={userVote === 'down' ? 'default' : 'ghost'}
        size="icon"
        className="h-8 w-8"
        onClick={() => handleVote('down')}
        disabled={isVoting}
      >
        <ArrowDown className="h-4 w-4" />
      </Button>
    </div>
  );
}
