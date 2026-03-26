/ Voir tous les records d'annonces
Object.entries(localStorage)
  .filter(([k]) => k.startsWith('announcement_dismissed_'))
  .forEach(([k, v]) => console.log(k, JSON.parse(v)))

// Simuler un dismiss il y a 4h (pour tester recurrence 3h)
const key = 'announcement_dismissed_TON_ID'
const rec = JSON.parse(localStorage.getItem(key))
rec.dismissedAt = Date.now() - 4 * 3600 * 1000
localStorage.setItem(key, JSON.stringify(rec))
// → rechargement de page = popup réapparaît