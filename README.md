Object.keys(localStorage)
  .filter(k => k.startsWith('announcement_dismissed_'))
  .forEach(k => {
    try {
      const rec = JSON.parse(localStorage.getItem(k))
      const date = new Date(rec.dismissedAt).toLocaleString()
      const expiresIn = rec.dismissedAt 
        ? Math.round((rec.dismissedAt + (3600 * 1000) - Date.now()) / 1000) + 's'
        : 'jamais'
      console.log(k.replace('announcement_dismissed_', ''), '→', date, '| expire dans:', expiresIn, '| forcedReset:', rec.forcedResetAtSnapshot)
    } catch {
      console.log(k, '→ ancien format:', localStorage.getItem(k))
    }
  })
