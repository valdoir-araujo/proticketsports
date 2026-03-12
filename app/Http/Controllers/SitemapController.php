<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Evento;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Gera sitemap.xml para o Google e outros buscadores.
     * Inclui: home, listagens (eventos, campeonatos), páginas estáticas e URLs de eventos/campeonatos publicados.
     */
    public function index(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $now = now()->toW3cString();

        $urls = [];

        // Home e páginas fixas
        $fixas = [
            ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $base . route('eventos.public.index', [], false), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $base . route('para-organizadores', [], false), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $base . route('campeonatos.index', [], false), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $base . route('parceiros.index', [], false), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $base . route('contato.index', [], false), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        foreach ($fixas as $u) {
            $urls[] = $this->urlNode($u['loc'], $u['priority'], $u['changefreq'], $now);
        }

        // Eventos publicados (slug)
        $eventos = Evento::where('status', 'publicado')
            ->select('slug', 'updated_at')
            ->orderBy('data_evento', 'desc')
            ->limit(500)
            ->get();

        foreach ($eventos as $e) {
            $urls[] = $this->urlNode(
                $base . route('eventos.public.show', ['evento' => $e->slug], false),
                '0.8',
                'weekly',
                $e->updated_at ? $e->updated_at->toW3cString() : $now
            );
        }

        // Campeonatos
        $campeonatos = Campeonato::select('id', 'updated_at')->orderBy('id')->limit(200)->get();
        foreach ($campeonatos as $c) {
            $urls[] = $this->urlNode(
                $base . route('campeonatos.show', ['campeonato' => $c->id], false),
                '0.7',
                'weekly',
                $c->updated_at ? $c->updated_at->toW3cString() : $now
            );
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= implode("\n", $urls);
        $xml .= "\n</urlset>";

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function urlNode(string $loc, string $priority, string $changefreq, string $lastmod): string
    {
        $loc = htmlspecialchars($loc, ENT_XML1, 'UTF-8');
        return "  <url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>{$changefreq}</changefreq><priority>{$priority}</priority></url>";
    }
}
