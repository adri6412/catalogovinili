# Istruzioni Upload File su Server

## Metodo 1: Via SCP (da Windows PowerShell o Mac/Linux)

### Da Windows (PowerShell):

```powershell
# Installa OpenSSH se non ce l'hai già
# Poi esegui:
scp -r C:\Users\adri6\Downloads\backup_catalogovinili.ovh_2511132038\* utente@tuo-server.com:/opt/catalogovinili/
```

### Da Mac/Linux:

```bash
scp -r /percorso/backup_catalogovinili.ovh_2511132038/* utente@tuo-server.com:/opt/catalogovinili/
```

---

## Metodo 2: Via FileZilla (GUI - Più Facile)

1. **Scarica FileZilla**: https://filezilla-project.org/
2. **Connettiti al server**:
   - Host: `sftp://tuo-server.com` (o IP)
   - Username: il tuo username SSH
   - Password: la tua password SSH
   - Porta: 22
3. **Naviga** nella directory `/opt/catalogovinili` sul server
4. **Trascina** tutti i file dalla cartella locale alla cartella remota

---

## Metodo 3: Via WinSCP (Windows)

1. **Scarica WinSCP**: https://winscp.net/
2. **Connettiti** con le credenziali SSH
3. **Trascina** i file dalla sinistra (locale) alla destra (server)

---

## Struttura Finale sul Server

Dopo l'upload, sul server dovresti avere:

```
/opt/catalogovinili/
├── docker-compose.yml
├── docker-compose-portainer-local.yml  (usa questo se carichi i file manualmente)
├── httpdocs/
│   ├── Dockerfile
│   ├── config.php
│   ├── index.php
│   └── ... (tutti gli altri file PHP)
└── api.catalogovinili.ovh/
    ├── Dockerfile
    ├── app.js
    ├── package.json
    └── ... (tutti gli altri file Node.js)
```

---

## Dopo l'Upload

1. **In Portainer**: Stacks → Add stack
2. **Nome**: `catalogovinili`
3. **Web editor**: Copia il contenuto di `docker-compose-portainer-local.yml`
4. **Modifica i path** se necessario (dovrebbero essere già corretti se hai usato `/opt/catalogovinili`)
5. **Configura variabili d'ambiente**:
   - `DB_HOST=tuo-database-server.com`
   - `DB_USER=catalogo`
   - `DB_PASSWORD=Fd883d0ffa.`
   - `DB_NAME=adri641_catalogo`
6. **Deploy**

---

## Verifica Permessi

Dopo l'upload, verifica i permessi:

```bash
ssh utente@tuo-server.com
cd /opt/catalogovinili
ls -la
# Se necessario, aggiusta i permessi:
chmod -R 755 /opt/catalogovinili
```

