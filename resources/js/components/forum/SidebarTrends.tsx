import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { TrendingUp } from 'lucide-react';

/**
 * SidebarTrends - Sidebar con tendencias del foro
 * 
 * EXPLICACIÓN:
 * - Muestra los temas/tags más populares
 * - Los datos vienen de Laravel vía Inertia
 * - Se actualiza automáticamente cuando cambia el estado
 */

interface Trend {
  _id: string;
  name: string;
  slug: string;
  posts_count: number;
  category: string;
}

interface SidebarTrendsProps {
  trends: Trend[];
}

export default function SidebarTrends({ trends }: SidebarTrendsProps) {
  // Colores por categoría
  const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
      'Bases de Datos': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
      'Backend': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
      'Frontend': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      'Optimización': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
      'Desarrollo': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    };
    return colors[category] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center space-x-2">
          <TrendingUp className="h-5 w-5 text-orange-500" />
          <span>Tendencias</span>
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          {trends.length === 0 ? (
            <p className="text-sm text-muted-foreground text-center py-4">
              No hay tendencias disponibles
            </p>
          ) : (
            trends.map((trend) => (
              <Link
                key={trend._id}
                href={`/trends/${trend.slug}`}
                className="block group"
              >
                <div className="flex items-center justify-between p-2 rounded-lg hover:bg-accent transition-colors">
                  <div className="flex flex-col space-y-1">
                    <Badge
                      variant="secondary"
                      className={`w-fit ${getCategoryColor(trend.category)}`}
                    >
                      {trend.category}
                    </Badge>
                    <span className="font-medium text-sm group-hover:text-primary transition-colors">
                      {trend.name}
                    </span>
                    <span className="text-xs text-muted-foreground">
                      {trend.posts_count} {trend.posts_count === 1 ? 'post' : 'posts'}
                    </span>
                  </div>
                </div>
              </Link>
            ))
          )}
        </div>
      </CardContent>
    </Card>
  );
}
