import { Head, Link, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { ArrowLeft } from 'lucide-react';
import Header from '@/components/forum/Header';

/**
 * Post/Create - Formulario para crear nuevo post
 * 
 * EXPLICACIÓN useForm de Inertia:
 * - useForm es un hook de Inertia para manejar formularios
 * - Maneja el estado del form, validación, y envío automático
 * - No necesitamos useState ni manejar el submit manualmente
 * - Los errores de validación vienen automáticamente desde Laravel
 */

interface PageProps {
  auth: {
    user: {
      _id: string;
      name: string;
      email: string;
    } | null;
  };
  errors?: {
    title?: string;
    content?: string;
    tags?: string;
  };
}

export default function Create({ auth, errors }: PageProps) {
  const { data, setData, post, processing, reset } = useForm({
    title: '',
    content: '',
    tags: [] as string[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/posts', {
      preserveScroll: true,
      onSuccess: () => {
        reset();
      },
    });
  };

  return (
    <>
      <Head title="Crear Nuevo Post" />

      <div className="min-h-screen bg-background">
        <Header user={auth.user} />

        <div className="container mx-auto px-4 py-6 max-w-3xl">
          {/* Botón volver */}
          <Button variant="ghost" asChild className="mb-4">
            <Link href="/">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Volver al feed
            </Link>
          </Button>

          {/* Formulario */}
          <Card>
            <CardHeader>
              <CardTitle>Crear Nuevo Post</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-6">
                {/* Título */}
                <div className="space-y-2">
                  <Label htmlFor="title">Título</Label>
                  <Input
                    id="title"
                    type="text"
                    placeholder="¿Cuál es tu pregunta o tema?"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    disabled={processing}
                    className={errors?.title ? 'border-red-500' : ''}
                  />
                  {errors?.title && (
                    <p className="text-sm text-red-500">{errors.title}</p>
                  )}
                </div>

                {/* Contenido */}
                <div className="space-y-2">
                  <Label htmlFor="content">Contenido</Label>
                  <Textarea
                    id="content"
                    placeholder="Describe tu pregunta o comparte tu conocimiento..."
                    rows={10}
                    value={data.content}
                    onChange={(e) => setData('content', e.target.value)}
                    disabled={processing}
                    className={errors?.content ? 'border-red-500' : ''}
                  />
                  {errors?.content && (
                    <p className="text-sm text-red-500">{errors.content}</p>
                  )}
                  <p className="text-xs text-muted-foreground">
                    {data.content.length} caracteres
                  </p>
                </div>

                {/* Tags */}
                <div className="space-y-2">
                  <Label htmlFor="tags">Tags</Label>
                  <Input
                    id="tags"
                    type="text"
                    placeholder="Laravel, MongoDB, React (separados por comas)"
                    value={data.tags.join(', ')}
                    onChange={(e) => {
                      const tagsString = e.target.value;
                      const tagsArray = tagsString
                        .split(',')
                        .map(tag => tag.trim())
                        .filter(tag => tag.length > 0);
                      setData('tags', tagsArray);
                    }}
                    disabled={processing}
                    className={errors?.tags ? 'border-red-500' : ''}
                  />
                  {errors?.tags && (
                    <p className="text-sm text-red-500">{errors.tags}</p>
                  )}
                  <p className="text-xs text-muted-foreground">
                    {data.tags.length > 0 ? `${data.tags.length} tag(s): ${data.tags.join(', ')}` : 'Agrega tags separados por comas'}
                  </p>
                </div>

                {/* Botones */}
                <div className="flex items-center justify-end space-x-2">
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => router.visit('/')}
                    disabled={processing}
                  >
                    Cancelar
                  </Button>
                  <Button
                    type="submit"
                    disabled={processing || !data.title.trim() || !data.content.trim()}
                  >
                    {processing ? 'Publicando...' : 'Publicar Post'}
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>

          {/* Tips */}
          <Card className="mt-6">
            <CardContent className="pt-6">
              <h3 className="font-semibold mb-2">Tips para un buen post:</h3>
              <ul className="text-sm text-muted-foreground space-y-1 list-disc list-inside">
                <li>Usa un título claro y descriptivo</li>
                <li>Proporciona contexto suficiente</li>
                <li>Si es una pregunta, explica qué has intentado</li>
                <li>Usa formato markdown si necesitas resaltar código</li>
                <li>Sé respetuoso con la comunidad</li>
              </ul>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
