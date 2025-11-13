# Dove Mettere i File del Progetto per Portainer

Ci sono diverse opzioni per mettere i file del progetto su Portainer. Scegli quella più adatta alla tua situazione.

## Opzione 1: Git Repository (CONSIGLIATO) ⭐

Questa è la soluzione più pulita e professionale.

### Passi:

1. **Crea un repository Git** (GitHub, GitLab, Bitbucket, ecc.)
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/tuonome/catalogovinili.git
   git push -u origin main
   ```

2. **In Portainer**:
   - Vai su **Stacks** → **Add stack**
   - Nome: `catalogovinili`
   - **Build method**: Repository
   - **Repository URL**: `https://github.com/tuonome/catalogovinili.git`
   - **Repository reference**: `main` (o il tuo branch)
   - **Compose path**: `/docker-compose.yml`
   - Configura le variabili d'ambiente
   - **Deploy**

Portainer scaricherà automaticamente tutti i file dal repository Git e costruirà i container.

### Vantaggi:
- ✅ Portainer scarica automaticamente i file
- ✅ Facile aggiornare: basta fare push su Git
- ✅ Versionamento del codice
- ✅ Non devi accedere al server

---

## Opzione 2: Caricare i File sul Server (SSH/FTP)

Se hai accesso SSH o FTP al server dove gira Portainer.

### Passi:

1. **Connettiti al server via SSH**:
   ```bash
   ssh utente@tuo-server.com
   ```

2. **Crea una directory per il progetto**:
   ```bash
   mkdir -p /opt/catalogovinili
   cd /opt/catalogovinili
   ```

3. **Carica i file**:
   
   **Metodo A: Via SCP (da Windows con PowerShell o da Mac/Linux)**:
   ```bash
   scp -r C:\Users\adri6\Downloads\backup_catalogovinili.ovh_2511132038\* utente@tuo-server.com:/opt/catalogovinili/
   ```
   
   **Metodo B: Via FTP**:
   - Usa FileZilla o un altro client FTP
   - Connettiti al server
   - Carica tutti i file nella directory `/opt/catalogovinili`

4. **In Portainer**:
   - Vai su **Stacks** → **Add stack**
   - Nome: `catalogovinili`
   - **Build method**: Web editor
   - Copia il contenuto di `docker-compose.yml`
   - **IMPORTANTE**: Modifica i path nel docker-compose.yml:
     ```yaml
     build:
       context: /opt/catalogovinili/api.catalogovinili.ovh
       dockerfile: Dockerfile
     ```
     ```yaml
     build:
       context: /opt/catalogovinili/httpdocs
       dockerfile: Dockerfile
     ```
   - Configura le variabili d'ambiente
   - **Deploy**

### Vantaggi:
- ✅ Controllo diretto sui file
- ✅ Funziona senza Git

### Svantaggi:
- ❌ Devi avere accesso SSH/FTP
- ❌ Aggiornamenti manuali

---

## Opzione 3: Volume Mount da Host (Se Portainer è su Docker)

Se Portainer gira su Docker e hai accesso al file system dell'host.

### Passi:

1. **Copia i file sul server host** in una directory accessibile:
   ```bash
   /opt/catalogovinili/
   ├── docker-compose.yml
   ├── httpdocs/
   │   ├── Dockerfile
   │   └── ... (tutti i file PHP)
   └── api.catalogovinili.ovh/
       ├── Dockerfile
       ├── app.js
       └── ... (tutti i file Node.js)
   ```

2. **In Portainer**:
   - Usa il web editor con i path assoluti come nell'Opzione 2
   - Oppure monta i volumi se necessario

---

## Opzione 4: Portainer Agent + Upload (Portainer Business)

Se hai Portainer Business, puoi usare l'upload diretto dei file.

1. Vai su **Stacks** → **Add stack**
2. Scegli **Upload** come metodo
3. Carica il file `docker-compose.yml`
4. Portainer ti permetterà di caricare anche gli altri file necessari

---

## ⚠️ IMPORTANTE: Path nel docker-compose.yml

Quando usi file locali sul server, devi modificare i path nel `docker-compose.yml`:

### Path Relativi (se i file sono nella stessa directory):
```yaml
build:
  context: ./api.catalogovinili.ovh
  dockerfile: Dockerfile
```

### Path Assoluti (se i file sono in una directory specifica):
```yaml
build:
  context: /opt/catalogovinili/api.catalogovinili.ovh
  dockerfile: Dockerfile
```

---

## 📋 Checklist File Necessari

Assicurati di avere questi file:

```
catalogovinili/
├── docker-compose.yml          ← File principale
├── httpdocs/
│   ├── Dockerfile              ← Dockerfile PHP
│   ├── config.php
│   ├── index.php
│   └── ... (tutti i file PHP)
└── api.catalogovinili.ovh/
    ├── Dockerfile              ← Dockerfile Node.js
    ├── app.js
    ├── package.json
    └── ... (tutti i file Node.js)
```

---

## 🎯 Raccomandazione

**Per produzione**: Usa **Opzione 1 (Git Repository)** - è la più pulita e facile da mantenere.

**Per test rapidi**: Usa **Opzione 2 (SSH)** se hai già accesso al server.

---

## Domande Frequenti

**Q: Posso usare un repository privato?**
A: Sì, Portainer supporta autenticazione Git. Inserisci username e password/token quando configuri il repository.

**Q: Dove vengono salvati i file quando uso Git?**
A: Portainer li scarica temporaneamente durante il build. Non vengono salvati permanentemente sul server.

**Q: Come aggiorno i file dopo il deploy?**
A: 
- Con Git: fai push delle modifiche, poi in Portainer vai su Stacks → [nome stack] → Editor → Update
- Con file locali: modifica i file sul server, poi ricostruisci lo stack

