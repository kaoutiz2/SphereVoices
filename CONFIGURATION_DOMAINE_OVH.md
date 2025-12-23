# Configuration du domaine dans OVH

## 🔴 Problème : DNS_PROBE_FINISHED_NXDOMAIN

Cette erreur signifie que le domaine `www.spherevoices.com` n'est pas configuré dans votre hébergement OVH.

## ✅ Solution : Ajouter le domaine dans OVH Multisite

### Étape 1 : Ajouter le domaine dans OVH

1. **Espace client OVH** → **Hébergement** → Votre hébergement
2. Allez dans l'onglet **Multisite**
3. Cliquez sur **Ajouter un domaine ou un sous-domaine**
4. Entrez : `www.spherevoices.com` (ou `spherevoices.com` si vous préférez)
5. **Dossier racine** : `/www` (ou `/www/www` si vous avez la structure doublon)
6. **Activer PHP** : Oui
7. **Version PHP** : 8.2
8. Cliquez sur **Suivant** puis **Valider**

### Étape 2 : Vérifier le DocumentRoot

Après avoir ajouté le domaine, vérifiez que le **Dossier racine** pointe vers :
- `/www` si Git OVH déploie à la racine
- `/www/www` si Git OVH déploie dans `/www` et que vous avez un doublon

### Étape 3 : Vérifier les DNS

1. **Espace client OVH** → **Domaines** → `spherevoices.com`
2. Allez dans l'onglet **Zone DNS**
3. Vérifiez qu'il y a :
   - Un enregistrement **A** pour `www` pointant vers l'IP de votre hébergement
   - Ou un enregistrement **CNAME** pour `www` pointant vers votre hébergement

### Étape 4 : Attendre la propagation DNS

- Les changements DNS peuvent prendre **15 minutes à 48 heures**
- Utilisez un outil comme `https://www.whatsmydns.net` pour vérifier la propagation

## 🔍 Vérification rapide

**Testez avec l'IP de l'hébergement :**
- Si vous connaissez l'IP de votre hébergement OVH, testez : `http://VOTRE_IP/www/`
- Cela permet de vérifier si le problème vient du DNS ou de la configuration

## 📝 Structure attendue après configuration

```
FTP OVH (racine)/
├── www/              ← DocumentRoot configuré dans Multisite
│   ├── index.php
│   ├── .htaccess
│   └── ...
├── vendor/
├── config/
└── .env.production
```

## ⚠️ Important

- Le domaine doit être **ajouté dans Multisite** pour être accessible
- Le **Dossier racine** doit pointer vers `/www` (ou `/www/www` selon votre structure)
- Attendez la **propagation DNS** (peut prendre jusqu'à 48h)
