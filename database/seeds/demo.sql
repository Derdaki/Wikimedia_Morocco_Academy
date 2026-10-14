SET NAMES utf8mb4;

INSERT INTO event_categories (name_fr, name_ar, slug, created_at, updated_at) VALUES
('Atelier', 'ورشة', 'atelier', NOW(), NOW()),
('Concours photo', 'مسابقة صور', 'concours-photo', NOW(), NOW()),
('Rencontre communautaire', 'لقاء مجتمعي', 'rencontre-communautaire', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO project_categories (name_fr, name_ar, slug, created_at, updated_at) VALUES
('Patrimoine', 'تراث', 'patrimoine', NOW(), NOW()),
('Éducation', 'تعليم', 'education', NOW(), NOW()),
('Données ouvertes', 'بيانات مفتوحة', 'donnees-ouvertes', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO news_categories (name_fr, name_ar, slug, created_at, updated_at) VALUES
('Communauté', 'مجتمع', 'communaute', NOW(), NOW()),
('Projets', 'مشاريع', 'projets', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO settings (setting_key, setting_value, updated_at) VALUES
('site_name', 'Wikimedia Maroc', NOW()),
('site_description', 'Wikimedia Maroc promeut la connaissance libre, le patrimoine marocain et la contribution aux projets Wikimedia.', NOW()),
('site_logo', 'https://commons.wikimedia.org/wiki/Special:FilePath/Wikimedia%20MA%20User%20Group.svg?width=180', NOW()),
('contact_email', 'contact@wikimedia.ma', NOW()),
('footer_description', 'Wikimedia Maroc est un groupe d’utilisateurs reconnu qui promeut la connaissance libre et soutient la communauté Wikimedia au Maroc.', NOW()),
('join_url', 'https://meta.wikimedia.org/wiki/Wikimedia_MA_User_Group', NOW()),
('facebook_url', '', NOW()),
('x_url', '', NOW()),
('instagram_url', '', NOW()),
('linkedin_url', '', NOW()),
('youtube_url', '', NOW()),
('home_hero_images', '["/assets/images/plaza.jpg","/assets/images/ksar.jpg","/assets/images/rug.jpg","/assets/images/atlas.jpg"]', NOW())
ON DUPLICATE KEY UPDATE updated_at = updated_at;

INSERT INTO projects (category_id, title_fr, title_ar, slug, cover_image, excerpt_fr, excerpt_ar, content_fr, content_ar, project_status, status, is_featured, sort_order, created_at, updated_at)
SELECT pc.id, 'Wiki Loves Monuments Maroc', 'ويكي تهوى المعالم المغرب', 'wiki-loves-monuments-maroc',
'/assets/images/ksar.jpg',
'Concours et campagne de documentation des monuments et sites patrimoniaux du Maroc.',
'حملة لتوثيق المعالم والمواقع التراثية في المغرب.',
'Démonstration: cette fiche illustre un projet de campagne photo autour des monuments du Maroc. Remplacez ce contenu par les informations officielles du projet.',
'بيانات تجريبية: استبدل هذا المحتوى بالمعلومات الرسمية للمشروع.',
'active', 'published', 1, 1, NOW(), NOW()
FROM project_categories pc WHERE pc.slug = 'patrimoine'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO projects (category_id, title_fr, title_ar, slug, cover_image, excerpt_fr, excerpt_ar, content_fr, content_ar, project_status, status, is_featured, sort_order, created_at, updated_at)
SELECT pc.id, 'Ateliers Wikipédia', 'ورشات ويكيبيديا', 'ateliers-wikipedia',
'/assets/images/plaza.jpg',
'Formations pratiques pour apprendre à contribuer, sourcer et améliorer des articles.',
'تدريبات عملية لتعلم التحرير وتوثيق المصادر.',
'Démonstration: les ateliers peuvent être adaptés aux universités, associations et institutions culturelles.',
'بيانات تجريبية موجهة للتعديل من لوحة التحكم.',
'active', 'published', 1, 2, NOW(), NOW()
FROM project_categories pc WHERE pc.slug = 'education'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO projects (category_id, title_fr, title_ar, slug, cover_image, excerpt_fr, excerpt_ar, content_fr, content_ar, project_status, status, is_featured, sort_order, created_at, updated_at)
SELECT pc.id, 'Wikidata Maroc', 'ويكي بيانات المغرب', 'wikidata-maroc',
'/assets/images/atlas.jpg',
'Structurer les données ouvertes liées au patrimoine, aux lieux et aux personnalités marocaines.',
'تنظيم البيانات المفتوحة المرتبطة بالمغرب.',
'Démonstration: ce projet met en avant les données ouvertes et les liens vers Wikidata.',
'بيانات تجريبية قابلة للتعديل.',
'active', 'published', 1, 3, NOW(), NOW()
FROM project_categories pc WHERE pc.slug = 'donnees-ouvertes'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO events (category_id, title_fr, title_ar, slug, short_description_fr, short_description_ar, description_fr, description_ar, cover_image, start_at, end_at, location, organizer, registration_url, status, created_at, updated_at)
SELECT ec.id, 'Atelier Wikipédia', 'ورشة ويكيبيديا', 'atelier-wikipedia-2026',
'Initiation à la contribution sur Wikipédia, avec sources et bonnes pratiques.',
'مقدمة في المساهمة في ويكيبيديا.',
'Démonstration: atelier d’initiation pour apprendre à créer un compte, modifier un article et citer des sources fiables.',
'بيانات تجريبية حول ورشة تعريفية.',
'/assets/images/plaza.jpg',
'2026-10-18 10:00:00', '2026-10-18 13:00:00', 'Rabat', 'Wikimedia Maroc', '', 'published', NOW(), NOW()
FROM event_categories ec WHERE ec.slug = 'atelier'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO events (category_id, title_fr, title_ar, slug, short_description_fr, short_description_ar, description_fr, description_ar, cover_image, start_at, end_at, location, organizer, registration_url, status, created_at, updated_at)
SELECT ec.id, 'Wiki Loves Monuments', 'ويكي تهوى المعالم', 'wiki-loves-monuments-2026',
'Concours photo pour valoriser les monuments et sites patrimoniaux du Maroc.',
'مسابقة صور لإبراز التراث المغربي.',
'Démonstration: campagne de contribution photo autour des monuments du Maroc.',
'بيانات تجريبية حول مسابقة الصور.',
'/assets/images/ksar.jpg',
'2026-11-01 09:00:00', '2026-11-30 18:00:00', 'En ligne', 'Wikimedia Maroc', '', 'published', NOW(), NOW()
FROM event_categories ec WHERE ec.slug = 'concours-photo'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO events (category_id, title_fr, title_ar, slug, short_description_fr, short_description_ar, description_fr, description_ar, cover_image, start_at, end_at, location, organizer, registration_url, status, created_at, updated_at)
SELECT ec.id, 'Rencontre communautaire', 'لقاء مجتمعي', 'rencontre-communautaire-2026',
'Échangeons sur nos projets et idées pour la communauté Wikimedia Maroc.',
'لقاء لتبادل الأفكار حول مشاريع المجتمع.',
'Démonstration: rencontre ouverte pour préparer les prochaines activités.',
'بيانات تجريبية حول لقاء مفتوح.',
'/assets/images/rug.jpg',
'2026-11-15 15:00:00', '2026-11-15 17:00:00', 'Casablanca', 'Wikimedia Maroc', '', 'published', NOW(), NOW()
FROM event_categories ec WHERE ec.slug = 'rencontre-communautaire'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO news (category_id, title_fr, title_ar, slug, featured_image, excerpt_fr, excerpt_ar, content_fr, content_ar, author, status, published_at, created_at, updated_at)
SELECT nc.id, 'Lancement du nouveau portail Wikimedia Maroc', 'إطلاق بوابة ويكيميديا المغرب الجديدة', 'lancement-portail-wikimedia-maroc',
'/assets/images/atlas.jpg',
'Un espace pour suivre les événements, projets, ressources et actualités de la communauté.',
'فضاء لمتابعة الفعاليات والمشاريع والموارد.',
'Démonstration: cet article présente le portail et doit être remplacé par une actualité officielle avant publication en production.',
'بيانات تجريبية يجب استبدالها قبل النشر الفعلي.',
'Wikimedia Maroc', 'published', '2026-10-14 09:00:00', NOW(), NOW()
FROM news_categories nc WHERE nc.slug = 'communaute'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO team_members (name, slug, role_fr, role_ar, bio_fr, bio_ar, photo, sort_order, is_visible, created_at, updated_at) VALUES
('Équipe Wikimedia Maroc', 'equipe-wikimedia-maroc', 'Coordination communautaire', 'تنسيق المجتمع', 'Démonstration: remplacez cette fiche par les membres réels et leurs biographies.', 'بيانات تجريبية.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Wikimedia%20MA%20User%20Group.svg?width=400', 1, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO resources (title_fr, title_ar, slug, description_fr, description_ar, resource_type, file_url, status, sort_order, created_at, updated_at) VALUES
('Guide de contribution Wikipédia', 'دليل المساهمة في ويكيبيديا', 'guide-contribution-wikipedia', 'Démonstration: ajoutez ici un guide officiel ou un lien pédagogique.', 'بيانات تجريبية.', 'link', 'https://fr.wikipedia.org/wiki/Aide:Comment_modifier_une_page', 'published', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();
