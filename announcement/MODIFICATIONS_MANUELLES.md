# Fichiers à modifier manuellement

## assets/App.tsx
Ajouter l'import et wrapper :

```tsx
import { AnnouncementPopupProvider } from './components/ui/AnnouncementPopupProvider'

// Dans le return, wrapper autour des routes :
<AnnouncementPopupProvider>
  <BrowserRouter>
    ...
  </BrowserRouter>
</AnnouncementPopupProvider>
```

## src/Controller/Admin/DashboardController.php
Ajouter le lien menu dans configureMenuItems() :

```php
yield MenuItem::linkToRoute('Announcement Popups', 'fa fa-bell', 'admin_announcement_popup_index');
```

## SQL — migration manuelle (phpMyAdmin)
```sql
ALTER TABLE announcement_popup
ADD COLUMN title_fr VARCHAR(255) DEFAULT NULL,
ADD COLUMN content_fr LONGTEXT DEFAULT NULL,
ADD COLUMN image_url_fr VARCHAR(500) DEFAULT NULL;
```
