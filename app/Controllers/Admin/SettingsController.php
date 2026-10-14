<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Setting;

final class SettingsController extends Controller
{
    public function edit(): void
    {
        $this->requireRole('super_admin');

        $this->view('admin/settings/index', [
            'title' => 'Website Settings',
            'settings' => Setting::all(),
        ], 'admin');
    }

    public function update(): void
    {
        $this->requireRole('super_admin');
        $this->verifyCsrf();

        $rules = ['site_name' => 'required|max:180', 'contact_email' => 'email|max:160',
            'site_logo' => 'asset|max:500', 'site_description' => 'max:1000', 'footer_description' => 'max:2000'];
        foreach (['facebook_url', 'x_url', 'instagram_url', 'linkedin_url', 'youtube_url', 'join_url'] as $key) $rules[$key] = 'url|max:500';
        $errors = \App\Core\Validator::validate($_POST, $rules);
        foreach (explode("\n", (string) ($_POST['home_hero_images'] ?? '')) as $image) {
            if (!safe_url(trim($image))) $errors['home_hero_images'] = 'Veuillez fournir une URL valide.';
        }
        if ($errors) {
            flash('error', implode(' ', array_unique($errors)));
            $this->redirect('/admin/settings');
        }

        $settings = [
            'site_name' => $_POST['site_name'] ?? 'Wikimedia Maroc',
            'site_description' => $_POST['site_description'] ?? '',
            'site_logo' => $_POST['site_logo'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? '',
            'footer_description' => $_POST['footer_description'] ?? '',
            'site_description_ar' => $_POST['site_description_ar'] ?? '',
            'footer_description_ar' => $_POST['footer_description_ar'] ?? '',
            'facebook_url' => $_POST['facebook_url'] ?? '',
            'x_url' => $_POST['x_url'] ?? '',
            'instagram_url' => $_POST['instagram_url'] ?? '',
            'linkedin_url' => $_POST['linkedin_url'] ?? '',
            'youtube_url' => $_POST['youtube_url'] ?? '',
            'join_url' => $_POST['join_url'] ?? '',
            'home_hero_images' => array_values(array_filter(array_map('trim', explode("\n", (string) ($_POST['home_hero_images'] ?? ''))))),
        ];

        Setting::updateMany($settings);
        activity('update', 'settings', null, 'Paramètres du site modifiés');
        flash('success', 'Paramètres enregistrés.');
        $this->redirect('/admin/settings');
    }
}
