# Refacto templates — Notes de migration

## Fichiers modifiés

### `templates/base.html.twig`
- GTM dédupliqué : une seule variable `gtm_id` selon l'environnement
- Suppression du double `<link rel="icon">` redondant
- Noscript GTM déplacé dans `<body>` (avant le block body)

### `templates/bundles/EasyAdminBundle/layout.html.twig`
- Styles inline groupés dans un seul `<style>` dans `head_stylesheets`
- Variables Twig (`user_menu_avatar`, `user_menu_dropdown`, `settings_dropdown`) déplacées juste avant leur usage
- Condition `currentUrl` simplifiée (`requestUri` au lieu de `getRequestUri()`)
- Duplication du footer de navigation éliminée via `locale == 'fr' ? ... : ...`
- Language switcher mobile simplifié avec un `for` sur `{ fr: 'FR', en: 'EN' }`
- Aucune logique ni comportement modifié

### `templates/bundles/EasyAdminBundle/menu.html.twig`
- Lien "Announcement Popups" ajouté dans `main_menu_before`
- Script `close-sidebar` nettoyé (optional chaining `?.remove()`)

## Fichiers créés / remplacés

### `templates/admin/announcement_popup/index.html.twig`
- Étend maintenant `ea.templatePath('layout')` au lieu de `base.html.twig`
- Utilise les blocks `content_title`, `page_actions`, `main`
- Table stylisée Bootstrap (card, badges, hover)
- Toggle actif/inactif inline (badge vert/gris cliquable)

### `templates/admin/announcement_popup/new.html.twig`
### `templates/admin/announcement_popup/edit.html.twig`
- Même pattern : étend le layout EA
- Formulaire en grille Bootstrap 2 colonnes
- Sections EN / FR bien séparées visuellement
- Bouton retour dans `page_actions`

## Ajouter d'autres pages admin custom dans le menu

Dans `templates/bundles/EasyAdminBundle/menu.html.twig`,
dupliquer le bloc lien dans `{% block main_menu_before %}` :

```twig
<a href="{{ path('ma_nouvelle_route') }}"
   class="d-flex align-items-center px-3 py-2 font-open text-sm text-dark hover:bg-gray-100 rounded"
   style="gap:8px; text-decoration:none;">
    <i class="fa fa-ICON" style="width:16px; text-align:center;"></i>
    {{ locale == 'fr' ? 'Libellé FR' : 'Label EN' }}
</a>
```

Les liens EasyAdmin natifs (CRUD) restent gérés par le `configureMenuItems()`
du `DashboardController.php` — pas besoin de les toucher ici.
