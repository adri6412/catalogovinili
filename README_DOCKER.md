# Docker Setup per Catalogo Vinili

Questo progetto è stato containerizzato usando Docker e Docker Compose.

## Struttura

- **httpdocs/**: Sito PHP con Apache
- **api.catalogovinili.ovh/**: API Node.js/Express
- **Database**: MySQL esterno (configurabile tramite variabili d'ambiente)

## Prerequisiti

- Docker
- Docker Compose

## Avvio

### Configurazione Database Esterno

Prima di avviare i servizi, configura le variabili d'ambiente per il database esterno. Puoi creare un file `.env` nella root del progetto:

```env
DB_HOST=host.docker.internal
DB_USER=catalogo
DB_PASSWORD=Fd883d0ffa.
DB_NAME=adri641_catalogo
```

**Nota**: 
- Su **Windows/Mac**: usa `host.docker.internal` per accedere al database sull'host
- Su **Linux**: usa l'IP dell'host (es. `172.17.0.1`) o l'hostname/IP del server database esterno

### Avvio Servizi

Per avviare tutti i servizi:

```bash
docker-compose up -d
```

## Servizi

- **Sito PHP**: http://localhost:8080
- **API Node.js**: http://localhost:3000
- **Database**: Esterno (configurabile tramite variabili d'ambiente)

## Comandi Utili

### Visualizzare i log
```bash
docker-compose logs -f
```

### Fermare i servizi
```bash
docker-compose down
```

### Ricostruire i container dopo modifiche
```bash
docker-compose up -d --build
```

### Accedere al container PHP
```bash
docker exec -it catalogovinili_php bash
```

### Accedere al container API
```bash
docker exec -it catalogovinili_api sh
```

## Note

- Il sito PHP si connette all'API Node.js tramite il nome del servizio Docker `api` sulla porta interna 3000
- Il database è esterno e deve essere configurato tramite le variabili d'ambiente `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`
- Assicurati che il database esterno sia raggiungibile dalla rete Docker (configura il firewall se necessario)

