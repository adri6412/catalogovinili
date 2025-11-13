# Quick Start - Deploy su Portainer

## Procedura Rapida

### 1. Preparazione File

Assicurati di avere tutti i file del progetto caricati sul server dove gira Portainer, oppure usa un repository Git.

### 2. Creare Stack in Portainer

1. Accedi a Portainer
2. Vai su **Stacks** → **Add stack**
3. Nome: `catalogovinili`

### 3. Metodo di Deploy

**Opzione A: Web Editor (Più Semplice)**
- Copia il contenuto di `docker-compose.yml`
- Incollalo nell'editor web di Portainer
- Vai al punto 4

**Opzione B: Git Repository**
- Repository URL: `https://github.com/tuonome/tuorepo.git`
- Branch: `main`
- Compose path: `/docker-compose.yml`

### 4. Configurare Variabili d'Ambiente

Nell'editor di Portainer, nella sezione **Environment variables**, aggiungi:

```
DB_HOST=tuo-server-database.com
DB_USER=catalogo
DB_PASSWORD=Fd883d0ffa.
DB_NAME=adri641_catalogo
PHP_PORT=8080
API_PORT=3000
```

**IMPORTANTE**: Sostituisci `tuo-server-database.com` con l'hostname o IP del tuo database MySQL esterno.

### 5. Deploy

1. Clicca **Deploy the stack**
2. Attendi che i container vengano costruiti e avviati
3. Controlla i log per verificare che tutto funzioni

### 6. Verifica

- **Sito PHP**: http://tuo-server:8080
- **API Node.js**: http://tuo-server:3000

## Troubleshooting Rapido

**Container non si avvia?**
- Controlla i log: Containers → [nome container] → Logs
- Verifica le variabili d'ambiente

**Errore connessione database?**
- Verifica che `DB_HOST` sia corretto
- Assicurati che il database permetta connessioni remote
- Controlla firewall/porte

**API non risponde?**
- Verifica che il container `catalogovinili_api` sia Running
- Controlla i log dell'API

## Aggiornamento

Per aggiornare dopo modifiche:
1. Stacks → `catalogovinili` → Editor
2. Modifica il compose file
3. Update the stack

