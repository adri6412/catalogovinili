# Deploy su Portainer - Catalogo Vinili

Questa guida spiega come fare il deploy del progetto Catalogo Vinili su Portainer.

## Prerequisiti

- Portainer installato e accessibile
- Accesso SSH o file system al server dove gira Portainer
- Database MySQL esterno già configurato e raggiungibile

## Metodo 1: Deploy tramite Portainer Stacks (Consigliato)

### Passo 1: Preparare i file

Assicurati di avere questi file nella root del progetto:
- `docker-compose.yml`
- `httpdocs/Dockerfile`
- `api.catalogovinili.ovh/Dockerfile`

### Passo 2: Accedere a Portainer

1. Accedi all'interfaccia web di Portainer
2. Vai su **Stacks** nel menu laterale
3. Clicca su **Add stack**

### Passo 3: Configurare lo Stack

1. **Nome dello Stack**: `catalogovinili` (o un nome a tua scelta)

2. **Metodo di deploy**: Scegli una delle opzioni:

   **Opzione A: Web editor (per progetti piccoli)**
   - Copia il contenuto di `docker-compose.yml`
   - Incollalo nell'editor web
   - Vai al Passo 4

   **Opzione B: Repository Git (consigliato)**
   - Se hai un repository Git, inserisci l'URL del repository
   - Specifica il branch (es. `main` o `master`)
   - Specifica il path al file docker-compose.yml (es. `/docker-compose.yml`)
   - Portainer scaricherà automaticamente i file

   **Opzione C: Upload file**
   - Se hai accesso al file system del server, puoi caricare il file docker-compose.yml

### Passo 4: Configurare le Variabili d'Ambiente

Nell'editor web editor di Portainer, modifica le variabili d'ambiente nella sezione `php`:

```yaml
environment:
  - DB_HOST=tuo-server-database.com  # o IP del database
  - DB_USER=catalogo
  - DB_PASSWORD=Fd883d0ffa.
  - DB_NAME=adri641_catalogo
  - API_URL=http://api:3000
```

Oppure usa le variabili d'ambiente di Portainer:
- Clicca su **Environment variables**
- Aggiungi:
  - `DB_HOST` = `tuo-server-database.com`
  - `DB_USER` = `catalogo`
  - `DB_PASSWORD` = `Fd883d0ffa.`
  - `DB_NAME` = `adri641_catalogo`

### Passo 5: Deploy

1. Clicca su **Deploy the stack**
2. Portainer inizierà a costruire e avviare i container
3. Monitora i log per verificare che tutto funzioni correttamente

### Passo 6: Verificare i Container

1. Vai su **Containers** nel menu
2. Dovresti vedere:
   - `catalogovinili_api` (API Node.js)
   - `catalogovinili_php` (Sito PHP)

3. Verifica che entrambi siano in stato **Running**

## Metodo 2: Deploy tramite Git Repository

Se preferisci usare un repository Git:

### Passo 1: Preparare il Repository

1. Assicurati che il repository contenga:
   - `docker-compose.yml`
   - `httpdocs/Dockerfile`
   - `api.catalogovinili.ovh/Dockerfile`
   - Tutti i file del progetto

### Passo 2: Configurare in Portainer

1. Vai su **Stacks** > **Add stack**
2. Nome: `catalogovinili`
3. **Build method**: Repository
4. **Repository URL**: `https://github.com/tuonome/tuorepo.git` (o il tuo URL)
5. **Repository reference**: `main` (o il tuo branch)
6. **Compose path**: `/docker-compose.yml`
7. **Environment variables**: Aggiungi le variabili d'ambiente come sopra
8. Clicca **Deploy the stack**

## Configurazione Porte

Dopo il deploy, configura le porte se necessario:

1. Vai su **Containers**
2. Clicca sul container `catalogovinili_php`
3. Vai su **Duplicate/Edit**
4. Nella sezione **Published ports**, verifica:
   - `8080:80` per il sito PHP
   - `3000:3000` per l'API Node.js

## Configurazione Database Esterno

### Se il database è su un server remoto:

1. Assicurati che il server database permetta connessioni remote
2. Configura `DB_HOST` con l'hostname o IP del server database
3. Verifica che la porta MySQL (3306) sia aperta nel firewall

### Se il database è sulla stessa macchina di Portainer:

1. Usa `host.docker.internal` come `DB_HOST` (Windows/Mac)
2. Su Linux, usa l'IP dell'host Docker (solitamente `172.17.0.1`)

## Troubleshooting

### I container non si avviano

1. Controlla i log: **Containers** > Clicca sul container > **Logs**
2. Verifica le variabili d'ambiente
3. Controlla che il database sia raggiungibile

### Errore di connessione al database

1. Verifica che `DB_HOST` sia corretto
2. Testa la connessione dal container PHP:
   ```bash
   docker exec -it catalogovinili_php ping tuo-server-database.com
   ```
3. Verifica che il firewall permetta connessioni dalla rete Docker

### L'API non risponde

1. Verifica che il container `catalogovinili_api` sia in esecuzione
2. Controlla i log dell'API
3. Verifica che la porta 3000 sia esposta correttamente

## Aggiornamento

Per aggiornare lo stack dopo modifiche:

1. Vai su **Stacks** > Clicca su `catalogovinili`
2. Clicca su **Editor**
3. Modifica il `docker-compose.yml` se necessario
4. Clicca su **Update the stack**
5. Portainer ricostruirà i container se necessario

## Backup

Portainer mantiene automaticamente i volumi Docker. Per backup manuale:

1. Vai su **Volumes**
2. Esporta i volumi se necessario
3. Per il database esterno, usa i tuoi strumenti di backup standard

## Note Importanti

- Il database è esterno, quindi assicurati che sia sempre raggiungibile
- Le modifiche ai file PHP vengono sincronizzate grazie al volume mount
- Per modifiche permanenti, modifica i file nel repository e ri-deploy
- Monitora i log regolarmente per problemi

