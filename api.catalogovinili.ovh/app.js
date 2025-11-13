const express = require('express');
const Ebay = require('ebay-node-api');

const app = express();
const port = 3000;

const ebay = new Ebay({
  clientID: "AdrianoF-PriceSea-PRD-8e5603d2f-9b34c74b",
  clientSecret: "PRD-e5603d2f50a8-26ce-4126-80d5-3d08",
  body: {
    grant_type: "client_credentials",
    scope: "https://api.ebay.com/oauth/api_scope"
  }
});

app.use(express.json());

// Abilita CORS per permettere chiamate dal browser
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
  if (req.method === 'OPTIONS') {
    return res.sendStatus(200);
  }
  next();
});

app.get('/search', async (req, res) => {
  try {
    const { keyword } = req.query;

    if (!keyword) {
      return res.status(400).json({ error: 'Il parametro "keyword" è richiesto.' });
    }

    // Ottieni l'access token
    await ebay.getAccessToken();

    // Esegui la ricerca eBay con la keyword fornita
    const searchData = await ebay.searchItems({
      keyword,
      limit: 20,
    });
    const parsedsearch = JSON.parse(searchData);
    res.json(parsedsearch);

  } catch (error) {
    console.error(error);
    res.status(500).json({ error: 'Errore durante la ricerca su eBay.' });
  }
});

app.listen(port, '0.0.0.0', () => {
  console.log(`Server avviato su http://0.0.0.0:${port}`);
});
