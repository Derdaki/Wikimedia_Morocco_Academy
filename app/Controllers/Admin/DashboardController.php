<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Content;
use App\Models\Dashboard;
use App\Models\Event;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'metrics' => Dashboard::metrics(),
            'upcoming' => Event::upcoming(5),
            'articles' => Content::latestNews(5),
            'activity' => Dashboard::latestActivity(),
            'messages' => Dashboard::unreadMessages(),
        ], 'admin');
    }
}
