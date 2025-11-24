# Catalogo Vinili

Applicazione web per la gestione di un catalogo di vinili, con funzionalità di riconoscimento automatico delle copertine tramite AI (OpenAI).

## Funzionalità

*   **Gestione Catalogo**: Aggiungi, modifica e visualizza la tua collezione di vinili.
*   **AI Recognition**: Carica una foto della copertina e l'AI estrarrà automaticamente Artista, Titolo, Anno e Genere.
*   **Docker Ready**: Pronto per il deployment con Docker e Docker Compose.

## Configurazione

L'applicazione utilizza variabili d'ambiente per la configurazione sensibile. Assicurati di impostare le seguenti variabili nel tuo ambiente di deployment (es. Portainer, .env):

| Variabile | Descrizione | Default |
| :--- | :--- | :--- |
| `OPENAI_API_KEY` | **Richiesto**. La tua chiave API di OpenAI (inizia con `sk-...`). | `YOUR_OPENAI_API_KEY` |
| `DB_HOST` | Host del database MySQL. | `localhost` |
| `DB_USER` | Utente del database. | `catalogo` |
| `DB_PASSWORD` | Password del database. | `Fd883d0ffa.` |
| `DB_NAME` | Nome del database. | `adri641_catalogo` |

## Deployment con Docker

1.  Clona il repository.
2.  Configura le variabili d'ambiente nel file `docker-compose.yml` o nel tuo gestore di container (Portainer).
3.  Avvia i container:

```bash
docker-compose up -d --build
```

**Nota per Portainer**: Se le modifiche non vengono rilevate, assicurati di fare "Pull latest image" o di forzare una ricostruzione del container.

## Struttura

*   `httpdocs/`: Codice sorgente PHP dell'applicazione.
*   `api.catalogovinili.ovh/`: API Node.js (se utilizzata).
*   `docker-compose.yml`: Configurazione per Docker Compose.
