<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Content;
use App\Models\Event;

final class PublicController extends Controller
{
    public function home(): void
    {
        $heroImages = json_decode(setting('home_hero_images', '[]'), true) ?: [];
        $heroImages = is_array($heroImages) ? array_values(array_filter($heroImages, 'is_string')) : [];

        if (!$heroImages) {
            $heroImages = [
                '/assets/images/plaza.jpg',
                'https://commons.wikimedia.org/wiki/Special:FilePath/Ait%20Benhaddou.jpg?width=900',
                '/assets/images/rug.jpg',
                'https://commons.wikimedia.org/wiki/Special:FilePath/Atlas%20Mountains,%20Fez,%20Morocco.jpg?width=900',
            ];
        }
        if (count($heroImages) === 3 && str_contains((string) $heroImages[2], 'Moroccan%20carpet')) {
            $heroImages[] = '/assets/images/atlas.jpg';
        }

        $this->view('public/home', [
            'title' => 'Wikimedia Maroc',
            'metaDescription' => locale() === 'ar' ? (setting('site_description_ar') ?: 'ويكيميديا المغرب تدعم المعرفة الحرة والتراث المغربي.') : setting('site_description'),
            'heroImages' => $heroImages,
            'featuredProjects' => Content::featuredProjects(3),
            'events' => Event::upcoming(3),
            'news' => Content::latestNews(3),
        ]);
    }

    public function events(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'category_id' => $_GET['category_id'] ?? '',
            'location' => trim((string) ($_GET['location'] ?? '')),
            'period' => $_GET['period'] ?? 'upcoming',
        ];

        $this->view('public/events', [
            'title' => 'Événements',
            'result' => Event::search($filters, $page, 9),
            'categories' => Event::categories(),
            'filters' => $filters,
        ]);
    }

    public function eventShow(string $slug): void
    {
        $event = Event::findBySlug($slug);

        if (!$event) {
            http_response_code(404);
            $this->view('public/errors/404', ['title' => 'Événement introuvable']);
            return;
        }

        $this->view('public/event-show', [
            'title' => localized($event, 'title'),
            'event' => $event,
            'related' => Event::related((int) $event['id'], $event['category_id'] ? (int) $event['category_id'] : null),
        ]);
    }

    public function projects(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'category_id' => $_GET['category_id'] ?? '',
        ];

        $this->view('public/projects', [
            'title' => 'Nos projets',
            'result' => Content::published('projects', $filters, $page, 9),
            'categories' => Content::categories('projects'),
            'filters' => $filters,
        ]);
    }

    public function projectShow(string $slug): void
    {
        $project = Content::findPublishedBySlug('projects', $slug);

        if (!$project) {
            http_response_code(404);
            $this->view('public/errors/404', ['title' => 'Projet introuvable']);
            return;
        }

        $this->view('public/project-show', [
            'title' => localized($project, 'title'),
            'project' => $project,
        ]);
    }

    public function news(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'category_id' => $_GET['category_id'] ?? '',
        ];

        $this->view('public/news', [
            'title' => 'Actualités',
            'result' => Content::published('news', $filters, $page, 9),
            'categories' => Content::categories('news'),
            'filters' => $filters,
        ]);
    }

    public function articleShow(string $slug): void
    {
        $article = Content::findPublishedBySlug('news', $slug);

        if (!$article) {
            http_response_code(404);
            $this->view('public/errors/404', ['title' => 'Article introuvable']);
            return;
        }

        $this->view('public/article-show', [
            'title' => localized($article, 'title'),
            'article' => $article,
        ]);
    }

    public function about(): void
    {
        $this->view('public/about', ['title' => 'À propos']);
    }

    public function credits(): void
    {
        $this->view('public/credits', ['title' => locale() === 'ar' ? 'حقوق الصور' : 'Crédits images']);
    }

    public function team(): void
    {
        $this->view('public/team', [
            'title' => 'Notre équipe',
            'members' => Content::published('team', [], 1, 100)['items'],
        ]);
    }

    public function resources(): void
    {
        $this->view('public/resources', [
            'title' => 'Ressources',
            'resources' => Content::published('resources', [], 1, 100)['items'],
        ]);
    }

    public function contact(): void
    {
        $this->view('public/contact', ['title' => 'Contact']);
        clear_old();
    }

    public function contactSubmit(): void
    {
        $this->verifyCsrf();
        remember_old($_POST);

        if (!empty($_POST['website'])) {
            flash('success', 'Merci, votre message a bien été reçu.');
            $this->redirect('/contact');
        }

        $errors = Validator::validate($_POST, [
            'name' => 'required|max:120',
            'email' => 'required|email|max:160',
            'subject' => 'required|max:180',
            'message' => 'required|max:5000',
        ]);

        if ($errors) {
            $_SESSION['_errors'] = $errors;
            $this->redirect('/contact');
        }

        $stmt = Database::pdo()->prepare(
            'INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent, created_at, updated_at)
             VALUES (:name, :email, :subject, :message, :ip, :agent, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => trim((string) $_POST['name']),
            'email' => mb_strtolower(trim((string) $_POST['email'])),
            'subject' => trim((string) $_POST['subject']),
            'message' => trim((string) $_POST['message']),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);

        clear_old();
        flash('success', 'Merci, votre message a bien été reçu.');
        $this->redirect('/contact');
    }

    public function join(): void
    {
        $this->view('public/join', ['title' => 'Rejoindre la communauté']);
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');

        $urls = [
            '/',
            '/nos-projets',
            '/evenements',
            '/actualites',
            '/ressources',
            '/a-propos',
            '/notre-equipe',
            '/contact',
            '/rejoindre-la-communaute',
        ];

        $pdo = Database::pdo();
        foreach ($pdo->query('SELECT slug FROM events WHERE status = "published"') as $row) {
            $urls[] = '/evenements/' . $row['slug'];
        }
        foreach ($pdo->query('SELECT slug FROM projects WHERE status = "published"') as $row) {
            $urls[] = '/nos-projets/' . $row['slug'];
        }
        foreach ($pdo->query('SELECT slug FROM news WHERE status = "published" AND published_at <= NOW()') as $row) {
            $urls[] = '/actualites/' . $row['slug'];
        }

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $path) {
            echo "  <url><loc>" . e(url($path)) . "</loc></url>\n";
        }
        echo "</urlset>\n";
    }

    public function language(string $locale): void
    {
        if (in_array($locale, ['fr', 'ar'], true)) {
            $_SESSION['locale'] = $locale;
        }

        $this->redirect(safe_back_path((string) ($_SERVER['HTTP_REFERER'] ?? '')));
    }
}
